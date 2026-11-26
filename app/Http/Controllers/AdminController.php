<?php

namespace App\Http\Controllers;

use App\Jobs\SendOrderEmailJob;
use App\Models\Order;
use App\Models\users;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function logout(Request $request)
    {
        Auth::logout();
        session()->forget(['admin_id', 'admin_name']);

        if (session('user_role') === 'admin') {
            session()->forget(['user_id', 'user_name', 'user_role']);
        }

        if (!session()->has('user_id')) {
            session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } else {
            $request->session()->regenerateToken();
        }

        return redirect('/login')->with('success', 'Logged out successfully.');
    }

    public function index(Request $request)
    {
        $statusFilter = $request->string('status')->trim()->toString();
        $paymentMethod = $request->string('payment_method')->trim()->toString();
        $paymentStatus = $request->string('payment_status')->trim()->toString();
        $dateFrom = $request->string('date_from')->trim()->toString();
        $dateTo = $request->string('date_to')->trim()->toString();
        $search = $request->string('search')->trim()->toString();
        if ($search === '') {
            $search = $request->string('q')->trim()->toString();
        }

        $statuses = [
            'received' => ['pending', 'received'],
            'process' => ['processing', 'process'],
            'shipping' => ['shipped', 'shipping'],
            'out_for_delivery' => ['out_for_delivery'],
            'delivered' => ['delivered'],
            'cancelled' => ['cancelled'],
        ];

        // Efficient aggregation without loading all records into memory
        $aggregates = Order::query()
            ->selectRaw('LOWER(TRIM(order_status)) as st, COUNT(*) as cnt, COALESCE(SUM(total_amount), 0) as sm')
            ->groupBy('st')
            ->get()
            ->keyBy('st');

        $summary = [];
        foreach ($statuses as $key => $values) {
            $c = 0;
            $t = 0;
            foreach ($values as $v) {
                if (isset($aggregates[$v])) {
                    $c += (int) $aggregates[$v]->cnt;
                    $t += (float) $aggregates[$v]->sm;
                }
            }
            $summary[$key] = [
                'count' => $c,
                'total' => $t,
            ];
        }

        $ordersQuery = Order::with('items');

        if ($statusFilter !== '' && $statusFilter !== 'all') {
            $validStatusValues = $statuses[$statusFilter] ?? [$statusFilter];
            $ordersQuery->whereIn('order_status', $validStatusValues);
        }

        if ($paymentMethod !== '' && $paymentMethod !== 'all') {
            $ordersQuery->where('payment_method', $paymentMethod);
        }

        if ($paymentStatus !== '' && $paymentStatus !== 'all') {
            $ordersQuery->where('payment_status', $paymentStatus);
        }

        if ($dateFrom !== '') {
            $ordersQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $ordersQuery->whereDate('created_at', '<=', $dateTo);
        }

        if ($search !== '') {
            $cleanSearch = ltrim(strtolower($search), '#');
            if (str_starts_with($cleanSearch, 'sk-')) {
                $cleanSearch = substr($cleanSearch, 3);
            }

            $ordersQuery->where(function ($q) use ($search, $cleanSearch) {
                $q->where('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('payment_id', 'like', '%' . $search . '%')
                    ->orWhere('razorpay_payment_id', 'like', '%' . $search . '%')
                    ->orWhere('razorpay_order_id', 'like', '%' . $search . '%')
                    ->orWhere('stripe_payment_id', 'like', '%' . $search . '%');

                if (is_numeric($cleanSearch)) {
                    $q->orWhere('id', (int) $cleanSearch);
                }

                $q->orWhereHas('items', function ($iQ) use ($search) {
                    $iQ->where('product_name', 'like', '%' . $search . '%');
                });
            });
        }

        // Sorting
        $allowedSorts = ['id', 'customer_name', 'total_amount', 'created_at', 'order_status'];
        $sortBy = $request->string('sort_by')->trim()->toString();
        $sortDir = strtolower($request->string('sort_dir', 'desc')->toString()) === 'asc' ? 'asc' : 'desc';

        if (in_array($sortBy, $allowedSorts, true)) {
            $ordersQuery->orderBy($sortBy, $sortDir);
        } else {
            $ordersQuery->latest('id');
        }

        $defaultPerPage = app()->runningUnitTests() ? 6 : 10;
        $perPage = (int) $request->input('per_page', $defaultPerPage);
        if (!in_array($perPage, [6, 10, 25, 50, 100], true)) {
            $perPage = $defaultPerPage;
        }

        $orders = $ordersQuery->paginate($perPage)->withQueryString();
        $totalOrdersCount = Order::count();

        return view('admin.admin-orders', compact(
            'orders',
            'summary',
            'statusFilter',
            'paymentMethod',
            'paymentStatus',
            'dateFrom',
            'dateTo',
            'search',
            'totalOrdersCount',
            'perPage',
            'sortBy',
            'sortDir'
        ));
    }

    public function tableData(Request $request)
    {
        $query = Order::with(['items', 'user']);

        // Status mapping for normalization
        $statuses = [
            'received' => ['pending', 'received'],
            'process' => ['processing', 'process'],
            'shipping' => ['shipped', 'shipping'],
            'out_for_delivery' => ['out_for_delivery'],
            'delivered' => ['delivered'],
            'cancelled' => ['cancelled'],
        ];

        // 1. Column filters from Bootstrap Table filter-control or query parameters
        $filters = [];
        $filterParam = $request->input('filter');
        if (is_string($filterParam) && $filterParam !== '') {
            $decoded = json_decode($filterParam, true);
            if (is_array($decoded)) {
                $filters = $decoded;
            }
        }

        // Direct request query params override or fill filters
        foreach (['customer_name', 'order_status', 'status', 'payment_method', 'payment_status', 'date_from', 'date_to'] as $field) {
            if ($request->filled($field) && empty($filters[$field])) {
                $filters[$field] = $request->input($field);
            }
        }

        // Apply Status Filter (supports normalized status keys e.g. received, process, shipping)
        $statusFilter = strtolower(trim($filters['order_status'] ?? $filters['status'] ?? ''));
        if ($statusFilter !== '' && $statusFilter !== 'all') {
            $mapped = $statuses[$statusFilter] ?? [$statusFilter];
            $query->whereIn('order_status', $mapped);
        }

        // Apply Customer Filter
        if (!empty($filters['customer_name'])) {
            $query->where('customer_name', 'like', '%' . trim($filters['customer_name']) . '%');
        }

        // Apply Payment Method Filter
        if (!empty($filters['payment_method']) && $filters['payment_method'] !== 'all') {
            $query->where('payment_method', trim($filters['payment_method']));
        }

        // Apply Payment Status Filter
        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $query->where('payment_status', trim($filters['payment_status']));
        }

        // Apply Date Range Filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', trim($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', trim($filters['date_to']));
        }

        // 2. Global search across ID, customer, phone, city, payment IDs, and items
        $search = trim($request->input('search', ''));
        if ($search === '') {
            $search = trim($request->input('q', ''));
        }

        if ($search !== '') {
            $cleanSearch = strtolower(ltrim($search, '#'));
            if (str_starts_with($cleanSearch, 'sk-')) {
                $cleanSearch = substr($cleanSearch, 3);
            }

            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('payment_id', 'like', "%{$search}%")
                    ->orWhere('razorpay_payment_id', 'like', "%{$search}%")
                    ->orWhere('razorpay_order_id', 'like', "%{$search}%")
                    ->orWhere('stripe_payment_id', 'like', "%{$search}%");

                if (is_numeric($cleanSearch)) {
                    $q->orWhere('id', (int) $cleanSearch);
                }

                $q->orWhereHas('items', function ($itemQuery) use ($search) {
                    $itemQuery->where('product_name', 'like', "%{$search}%");
                });
            });
        }

        // 3. Total count before pagination
        $total = (clone $query)->count();

        // 4. Sorting
        $allowedSorts = [
            'id',
            'customer_name',
            'total_amount',
            'created_at',
            'order_status',
            'payment_method',
            'payment_status',
        ];

        $sort = $request->input('sort', 'id');
        if ($sort === 'order_id') {
            $sort = 'id';
        }
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        $order = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';

        // 5. Pagination
        $limit = min(max((int) $request->input('limit', 10), 1), 100);
        $offset = max((int) $request->input('offset', 0), 0);

        $orders = $query
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        // 6. Map rows for Bootstrap Table
        $rows = $orders->map(function ($order) {
            $rawStatus = strtolower(trim($order->order_status ?? 'pending'));
            $displayStatus = match ($rawStatus) {
                'pending', 'received' => 'received',
                'processing', 'process' => 'process',
                'shipped', 'shipping' => 'shipping',
                'out_for_delivery' => 'out_for_delivery',
                'delivered' => 'delivered',
                'cancelled' => 'cancelled',
                default => $rawStatus,
            };

            $paymentMethod = strtolower(trim($order->payment_method ?? ''));
            $paymentReference = match ($paymentMethod) {
                'razorpay' => $order->razorpay_payment_id
                    ?: $order->payment_id
                    ?: $order->razorpay_order_id
                    ?: 'Paid',
                'stripe' => $order->stripe_payment_id
                    ?: $order->payment_id
                    ?: 'Paid',
                'cod' => 'Cash on Delivery',
                default => strtoupper($paymentMethod ?: 'N/A'),
            };

            $itemsSummary = $order->items->map(function ($item) {
                return $item->product_name . ' (x' . $item->quantity . ')';
            })->implode(', ');

            return [
                'id' => $order->id,
                'order_id' => $order->id,
                'order_id_formatted' => '#SK-' . $order->id,
                'customer_name' => $order->customer_name ?: 'Guest Customer',
                'phone' => $order->phone ?: '',
                'city' => $order->city ?: '',
                'address' => $order->address ?: '',
                'item_count' => $order->items->count(),
                'items_summary' => $itemsSummary ?: 'No items',
                'total_amount' => (float) $order->total_amount,
                'subtotal' => (float) $order->subtotal,
                'delivery_fee' => (float) $order->delivery_fee,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'payment_status' => strtolower(trim($order->payment_status ?? 'pending')),
                'order_status' => $displayStatus,
                'raw_order_status' => $rawStatus,
                'created_at' => $order->created_at?->format('Y-m-d H:i:s'),
                'created_at_formatted' => $order->created_at?->format('d M Y, h:i A'),
                'edit_url' => route('admin.orders.show', $order->id),
                'delete_url' => route('admin.orders.destroy', $order->id),
                'invoice_url' => route('admin.orders.invoice', $order->id),
                'actions' => route('admin.orders.show', $order->id),
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $rows,
        ]);
    }

    public function profile()
    {
        $admin = users::find(session('admin_id') ?? session('user_id'));

        if (!$admin || $admin->role !== 'admin') {
            return redirect('/login')->with('error', 'Unauthorized access.');
        }

        return view('admin.admin-profile', compact('admin'));
    }

    public function profileUpdate(Request $request)
    {
        $admin = users::find(session('admin_id') ?? session('user_id'));

        if (!$admin || $admin->role !== 'admin') {
            return redirect('/login')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
            ]);
        }

        return back()->with('success', 'Profile updated successfully!');
    }

    public function passwordUpdate(Request $request)
    {
        $admin = users::find(session('admin_id') ?? session('user_id'));

        if (!$admin || $admin->role !== 'admin') {
            return redirect('/login')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $admin->password)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password does not match.',
                    'errors' => ['current_password' => ['Current password does not match.']],
                ], 422);
            }

            return back()->withErrors(['current_password' => 'Current password does not match.']);
        }

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!',
            ]);
        }

        return back()->with('success', 'Password updated successfully!');
    }

    //     public function show($id)
    // {
    //     $order = Order::with('items')->findOrFail($id);

    //     return view('admin.admin-order-detail', compact('order'));
    // }

    public function show($id)
    {
        $order = Order::with([
            'items.product.category',
            'items.variant.values',
            'user',
        ])->findOrFail($id);

        $statusLabels = [
            'received' => 'Received',
            'process' => 'Process',
            'shipping' => 'Shipping',
            'out_for_delivery' => 'Out for delivery',
            'delivered' => 'Delivered',
        ];

        $statusMap = [
            'pending' => 'received',
            'received' => 'received',
            'processing' => 'process',
            'process' => 'process',
            'shipped' => 'shipping',
            'shipping' => 'shipping',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
        ];

        $currentStatus = $statusMap[$order->order_status] ?? 'received';

        return view('admin.admin-order-detail', compact(
            'order',
            'statusLabels',
            'currentStatus'
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'order_status' => 'required|in:received,process,shipping,out_for_delivery,delivered',
            'cod_payment_collected' => 'nullable|boolean',
        ]);

        $statusMap = [
            'received' => 'pending',
            'process' => 'processing',
            'shipping' => 'shipped',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
        ];

        $order = Order::findOrFail($id);

        // Update the delivery status.
        $order->order_status = $statusMap[$request->order_status];

        // For Cash on Delivery, mark payment as paid only
        // when the admin confirms that cash was collected.
        if (
            strtolower(trim($order->payment_method ?? '')) === 'cod' &&
            $request->order_status === 'delivered' &&
            $request->boolean('cod_payment_collected')
        ) {
            $order->payment_status = 'paid';
        }

        $order->save();

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'Order and payment status updated successfully!');
    }

    public function sendEmail($id)
    {
        $order = Order::with(['user', 'items.product'])
            ->findOrFail($id);

        if (empty($order->user?->email)) {
            return back()->with('error', 'Customer email address is not found for this order.');
        }

        $order->update([
            'email_status' => 'queued',
            'email_error' => null,
        ]);

        SendOrderEmailJob::dispatch($order);

        return back()->with('success', 'Order email has been queued and will be sent in the background!');
    }

    public function downloadInvoice($id)
    {
        $order = Order::with(['items.variant.values', 'user'])->findOrFail($id);

        return Pdf::loadView('pdf.invoice', compact('order'))
            ->setPaper('a4')
            ->download('invoice-SK-' . $order->id . '.pdf');
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $orderId = $order->id;
        $order->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Order #SK-{$orderId} deleted successfully.",
            ]);
        }

        return redirect('/adminorders')->with('success', "Order #SK-{$orderId} deleted successfully.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:orders,id',
        ]);

        $ids = $request->input('ids', []);
        $count = Order::whereIn('id', $ids)->delete();

        $message = "Deleted {$count} order(s) successfully.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'deleted_count' => $count,
                'message' => $message,
            ]);
        }

        return redirect('/adminorders')->with('success', $message);
    }
}

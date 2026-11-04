<?php

namespace App\Http\Controllers;

use App\Mail\orderMail;
use App\Models\Order;
use App\Models\users;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function logout()
    {
        session()->flush();

        return redirect('/login')->with('success', 'Logged out successfully.');
    }

    public function index(Request $request)
    {
        $statusFilter = $request->string('status')->trim()->toString();
        $search = $request->string('search')->trim()->toString();
        if ($search === '') {
            $search = $request->string('q')->trim()->toString();
        }

        $allOrders = Order::with('items')->latest('id')->get();

        $statuses = [
            'received' => ['pending', 'received'],
            'process' => ['processing', 'process'],
            'shipping' => ['shipped', 'shipping'],
            'out_for_delivery' => ['out_for_delivery'],
            'delivered' => ['delivered'],
            'cancelled' => ['cancelled'],
        ];

        $summary = [];

        foreach ($statuses as $key => $values) {
            $filteredOrders = $allOrders->filter(function ($order) use ($values) {
                return in_array(
                    strtolower(trim($order->order_status ?? 'pending')),
                    $values
                );
            });

            $summary[$key] = [
                'count' => $filteredOrders->count(),
                'total' => $filteredOrders->sum('total_amount'),
            ];
        }

        $ordersQuery = Order::with('items')->latest('id');

        if ($statusFilter !== '' && $statusFilter !== 'all') {
            $validStatusValues = $statuses[$statusFilter] ?? [$statusFilter];
            $ordersQuery->whereIn('order_status', $validStatusValues);
        }

        if ($search !== '') {
            $cleanSearch = ltrim(strtolower($search), '#');
            if (str_starts_with($cleanSearch, 'sk-')) {
                $cleanSearch = substr($cleanSearch, 3);
            }

            $ordersQuery->where(function ($q) use ($search, $cleanSearch) {
                $q->where('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%')
                    ->orWhere('payment_id', 'like', '%'.$search.'%')
                    ->orWhere('razorpay_payment_id', 'like', '%'.$search.'%')
                    ->orWhere('razorpay_order_id', 'like', '%'.$search.'%')
                    ->orWhere('stripe_payment_id', 'like', '%'.$search.'%');

                if (is_numeric($cleanSearch)) {
                    $q->orWhere('id', (int) $cleanSearch);
                }

                $q->orWhereHas('items', function ($iQ) use ($search) {
                    $iQ->where('product_name', 'like', '%'.$search.'%');
                });
            });
        }

        $orders = $ordersQuery->paginate(6)->withQueryString();
        $totalOrdersCount = $allOrders->count();

        return view('admin.admin-orders', compact('orders', 'summary', 'statusFilter', 'search', 'totalOrdersCount'));
    }

    public function profile()
    {
        $admin = users::find(session('user_id'));

        if (! $admin || $admin->role !== 'admin') {
            return redirect('/login')->with('error', 'Unauthorized access.');
        }

        return view('admin.admin-profile', compact('admin'));
    }

    public function profileUpdate(Request $request)
    {
        $admin = users::find(session('user_id'));

        if (! $admin || $admin->role !== 'admin') {
            return redirect('/login')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$admin->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function passwordUpdate(Request $request)
    {
        $admin = users::find(session('user_id'));

        if (! $admin || $admin->role !== 'admin') {
            return redirect('/login')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match.']);
        }

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

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

        Mail::to($order->user->email)
            ->send(new orderMail($order));

        return back()->with('success', 'Order email sent successfully!');
    }

    public function downloadInvoice($id)
    {
        $order = Order::with(['items.variant.values', 'user'])->findOrFail($id);

        return Pdf::loadView('pdf.invoice', compact('order'))
            ->setPaper('a4')
            ->download('invoice-SK-'.$order->id.'.pdf');
    }
}

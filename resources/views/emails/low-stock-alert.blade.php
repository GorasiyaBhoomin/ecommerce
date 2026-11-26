<!DOCTYPE html>
<html>

<head>
    <title>Shopkart Low Stock Alert</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f4f6f8; padding:20px;">

    <div style="max-width:700px; margin:auto; background:white; padding:25px; border-radius:10px;">

        <h2 style="color:#234987;">Shopkart Inventory Alert</h2>

        <p>Hello Admin,</p>

        <p>The following product variants have low or zero stock.</p>

        <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse; text-align:left;">

            <thead>
                <tr style="background:#234987; color:white;">
                    <th>Product</th>
                    <th>Variant</th>
                    <th>SKU</th>
                    <th>Stock</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($alerts as $alert)
                    <tr style="border-bottom:1px solid #ddd;">
                        <td>{{ $alert['product_name'] }}</td>
                        <td>{{ $alert['variant_name'] }}</td>
                        <td>{{ $alert['sku'] }}</td>
                        <td>{{ $alert['stock_qty'] }}</td>
                        <td>
                            @if($alert['status'] === 'Out of Stock')
                                <span style="color:red;">Out of Stock</span>
                            @else
                                <span style="color:#e58b00;">Low Stock</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>

        <p style="margin-top:25px;">
            Please restock these products as soon as possible.
        </p>

        <p>Regards,<br><strong>Shopkart Admin System</strong></p>

    </div>

</body>

</html>
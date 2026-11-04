<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <form method="post" action="{{ route('rozorpay.payment') }}">
        @csrf
        Amount <input type="text" name="amount">
        <button type="submit">pay</button>
    </form>
</body>

</html>
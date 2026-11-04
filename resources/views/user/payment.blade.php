<button id="rzp-button1">Pay</button>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    var options = {
        "key": "{{env('RAZORPAY_KEY')}}", // Enter the Key ID generated from the Dashboard
        "amount": "{{$amount}}", // Amount is in currency subunits. 
        "currency": "INR",
        "name": "Acme Corp", //your business name
        "description": "Test Transaction",
        "handler": function (response) {
            var payid = response.razorpay_payment_id;
            alert("payment success:" + payid);
        }
    "image": "https://example.com/your_logo",
        "order_id": "{{orderId}}", // This is a sample Order ID. Pass the `id` obtained in the response of Step 1
        "callback_url": "https://eneqd3r9zrjok.x.pipedream.net/",
        "prefill": { //We recommend using the prefill parameter to auto-fill customer's contact information especially their phone number
            "name": "<name>", //your customer's name
            "email": "<email>",
            "contact": "<phone>" //Provide the customer's phone number for better conversion rates 
        },
        "notes": {
            "address": "Razorpay Corporate Office"
        },
        "theme": {
            "color": "#3399cc"
        }
    };
    var rzp1 = new Razorpay(options);
    document.getElementById('rzp-button1').onclick = function (e) {
        rzp1.open();
    }
</script>
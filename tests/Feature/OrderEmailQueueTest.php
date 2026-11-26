<?php

use App\Jobs\SendOrderEmailJob;
use App\Mail\orderMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('admin clicking send email pushes SendOrderEmailJob on emails queue', function () {
    Queue::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $customer = User::factory()->create([
        'role' => 'user',
        'email' => 'customer@example.com',
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'customer_name' => 'Customer Bhoomin',
        'phone' => '9876543210',
        'address' => '123 Ring Road',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'postal_code' => '395007',
        'subtotal' => 5000,
        'delivery_fee' => 0,
        'total_amount' => 5000,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $response = $this->withSession([
        'admin_id' => $admin->id,
        'admin_name' => $admin->name,
    ])->post("/adminorderdetail/{$order->id}/send-email");

    $response->assertRedirect()
        ->assertSessionHas('success', 'Order email has been queued and will be sent in the background!');

    expect($order->fresh()->email_status)->toBe('queued');

    Queue::assertPushedOn('emails', SendOrderEmailJob::class, function ($job) use ($order) {
        return $job->order->id === $order->id
            && $job->tries === 3
            && $job->backoff === 30;
    });
});

test('non-admin user cannot dispatch SendOrderEmailJob', function () {
    Queue::fake();

    $customer = User::factory()->create([
        'role' => 'user',
        'email' => 'hacker@example.com',
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'customer_name' => 'Customer Bhoomin',
        'phone' => '9876543210',
        'address' => '123 Ring Road',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'postal_code' => '395007',
        'subtotal' => 5000,
        'delivery_fee' => 0,
        'total_amount' => 5000,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $response = $this->withSession([
        'user_id' => $customer->id,
        'user_name' => $customer->name,
    ])->post("/adminorderdetail/{$order->id}/send-email");

    $response->assertRedirect('/login');

    Queue::assertNothingPushed();
});

test('SendOrderEmailJob handle sends orderMail to the customer', function () {
    Mail::fake();

    $customer = User::factory()->create([
        'role' => 'user',
        'email' => 'customer.receive@example.com',
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'customer_name' => 'Customer Receive',
        'phone' => '9876543210',
        'address' => '123 Ring Road',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'postal_code' => '395007',
        'subtotal' => 5000,
        'delivery_fee' => 0,
        'total_amount' => 5000,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $job = new SendOrderEmailJob($order);
    $job->handle();

    Mail::assertSent(orderMail::class, function ($mail) use ($customer, $order) {
        return $mail->hasTo($customer->email)
            && $mail->order->id === $order->id;
    });

    expect($order->fresh()->email_status)->toBe('sent');
    expect($order->fresh()->email_sent_at)->not->toBeNull();
});

test('SendOrderEmailJob logs error when job fails', function () {
    $customer = User::factory()->create([
        'role' => 'user',
        'email' => 'failing.customer@example.com',
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'customer_name' => 'Failing Customer',
        'phone' => '9876543210',
        'address' => '123 Ring Road',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'postal_code' => '395007',
        'subtotal' => 5000,
        'delivery_fee' => 0,
        'total_amount' => 5000,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    Log::shouldReceive('error')
        ->once()
        ->withArgs(function ($message, $context) use ($order) {
            return str_contains($message, "Failed to send queued order email for order ID {$order->id}")
                && $context['order_id'] === $order->id;
        });

    $job = new SendOrderEmailJob($order);
    $job->failed(new RuntimeException('Mail server connection timed out'));

    expect($order->fresh()->email_status)->toBe('failed');
    expect($order->fresh()->email_error)->toContain('Mail server connection timed out');
});

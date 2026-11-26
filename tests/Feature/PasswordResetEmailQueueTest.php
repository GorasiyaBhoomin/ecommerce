<?php

use App\Jobs\SendPasswordResetEmailJob;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('web forgot password form pushes SendPasswordResetEmailJob on emails queue', function () {
    Queue::fake();

    $user = User::factory()->create([
        'email' => 'forgotweb@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->post('/forgot-password', [
        'email' => 'forgotweb@example.com',
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    Queue::assertPushedOn('emails', SendPasswordResetEmailJob::class, function ($job) {
        return $job->user->email === 'forgotweb@example.com'
            && str_contains($job->resetUrl, 'reset-password')
            && $job->tries === 3
            && $job->backoff === 30;
    });

    $record = DB::table('password_reset_tokens')->where('email', 'forgotweb@example.com')->first();
    expect($record)->not->toBeNull();
});

test('api forgot password request pushes SendPasswordResetEmailJob on emails queue', function () {
    Queue::fake();

    $user = User::factory()->create([
        'email' => 'forgotapi@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/forgot-password', [
        'email' => 'forgotapi@example.com',
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => true,
            'message' => 'Password reset link sent to your email address.',
        ]);

    Queue::assertPushedOn('emails', SendPasswordResetEmailJob::class, function ($job) {
        return $job->user->email === 'forgotapi@example.com'
            && str_contains($job->resetUrl, 'reset-password');
    });
});

test('SendPasswordResetEmailJob handle sends ResetPasswordMail to user', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'customer.reset@example.com',
    ]);

    $resetUrl = 'http://localhost:8000/reset-password/sampletoken?email=customer.reset@example.com';

    $job = new SendPasswordResetEmailJob($user, $resetUrl);
    $job->handle();

    Mail::assertSent(ResetPasswordMail::class, function ($mail) use ($resetUrl) {
        return $mail->hasTo('customer.reset@example.com')
            && $mail->resetUrl === $resetUrl;
    });
});

test('SendPasswordResetEmailJob logs error when job fails', function () {
    $user = User::factory()->create([
        'email' => 'failing.reset@example.com',
    ]);

    Log::shouldReceive('error')
        ->once()
        ->withArgs(function ($message, $context) use ($user) {
            return str_contains($message, "Failed to send password reset email to user ID {$user->id}")
                && $context['user_id'] === $user->id
                && $context['email'] === 'failing.reset@example.com';
        });

    $job = new SendPasswordResetEmailJob($user, 'http://localhost:8000/reset-password/sample');
    $job->failed(new RuntimeException('SMTP connection timeout'));
});

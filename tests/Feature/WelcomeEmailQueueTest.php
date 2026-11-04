<?php

use App\Jobs\SendWelcomeEmailJob;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('web user registration pushes SendWelcomeEmailJob on emails queue', function () {
    Queue::fake();

    $response = $this->post('/register', [
        'name' => 'JohnDoe',
        'email' => 'johndoe@example.com',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
        'phone' => '9876543210',
    ]);

    $response->assertRedirect('/login')
        ->assertSessionHas('success');

    Queue::assertPushedOn('emails', SendWelcomeEmailJob::class, function ($job) {
        return $job->user->email === 'johndoe@example.com'
            && $job->user->name === 'JohnDoe'
            && $job->tries === 3
            && $job->backoff === 30;
    });
});

test('api user registration pushes SendWelcomeEmailJob on emails queue', function () {
    Queue::fake();

    $response = $this->postJson('/api/register', [
        'name' => 'Jane Smith',
        'email' => 'janesmith@example.com',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
        'phone' => '9876543211',
    ]);

    $response->assertCreated()
        ->assertJson([
            'status' => true,
            'message' => 'Registration successful',
        ]);

    Queue::assertPushedOn('emails', SendWelcomeEmailJob::class, function ($job) {
        return $job->user->email === 'janesmith@example.com'
            && $job->user->name === 'Jane Smith';
    });
});

test('job handle method sends WelcomeEmail to user via Mail facade', function () {
    Mail::fake();

    $user = User::create([
        'name' => 'Alice Worker',
        'email' => 'alice@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9888877777',
        'role' => 'user',
    ]);

    $job = new SendWelcomeEmailJob($user);
    $job->handle();

    Mail::assertSent(WelcomeEmail::class, function ($mail) use ($user) {
        return $mail->hasTo('alice@example.com')
            && $mail->user->id === $user->id;
    });
});

test('job failed method logs error via Log facade', function () {
    Log::shouldReceive('error')
        ->once()
        ->withArgs(function ($message, $context) {
            return str_contains($message, 'Failed to send welcome email')
                && str_contains($message, 'bob@example.com')
                && isset($context['exception']);
        });

    $user = User::create([
        'name' => 'Bob Error',
        'email' => 'bob@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9888866666',
        'role' => 'user',
    ]);

    $job = new SendWelcomeEmailJob($user);
    $job->failed(new Exception('Simulated SMTP connection timeout'));
});

class FailingQueueVerificationJob implements ShouldQueue
{
    use Dispatchable, \Illuminate\Queue\InteractsWithQueue, \Illuminate\Queue\SerializesModels, Queueable;

    public int $tries = 1;

    public function handle(): void
    {
        throw new RuntimeException('Deliberate failure to test failed_jobs table');
    }
}

test('database queue processes valid jobs and moves exhausted jobs to failed_jobs table', function () {
    config(['queue.default' => 'database']);
    Mail::fake();

    $user = User::create([
        'name' => 'Queue Runner',
        'email' => 'queuerunner@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9888855555',
        'role' => 'user',
    ]);

    // 1. Dispatch into database queue
    SendWelcomeEmailJob::dispatch($user)->onQueue('emails');
    expect(DB::table('jobs')->where('queue', 'emails')->count())->toBe(1);

    // 2. Process job via worker
    Artisan::call('queue:work', [
        '--once' => true,
        '--queue' => 'emails',
    ]);

    expect(DB::table('jobs')->where('queue', 'emails')->count())->toBe(0);
    Mail::assertSent(WelcomeEmail::class, fn ($mail) => $mail->hasTo('queuerunner@example.com'));

    // 3. Confirm a deliberately failing job lands in failed_jobs
    FailingQueueVerificationJob::dispatch()->onQueue('emails');
    expect(DB::table('jobs')->where('queue', 'emails')->count())->toBe(1);

    Artisan::call('queue:work', [
        '--once' => true,
        '--queue' => 'emails',
    ]);

    expect(DB::table('jobs')->where('queue', 'emails')->count())->toBe(0);
    expect(DB::table('failed_jobs')->count())->toBe(1);

    $failedJob = DB::table('failed_jobs')->first();
    expect($failedJob->exception)->toContain('Deliberate failure to test failed_jobs table');
});

<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->user = User::create([
        'name' => 'Regular Customer',
        'email' => 'customer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'user',
    ]);
});

test('admin can log in and access admin orders panel', function () {
    $response = $this->post('/login', [
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/adminorders');
    $response->assertSessionHas('admin_id', $this->admin->id);

    $adminPage = $this->get('/adminorders');
    $adminPage->assertStatus(200);
});

test('logging in as user preserves existing admin session and does not kick admin out', function () {
    // 1. Admin logs in
    $this->post('/login', [
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);

    expect(session('admin_id'))->toBe($this->admin->id);

    // 2. User logs in in the same session
    $userLoginResponse = $this->post('/login', [
        'email' => 'customer@example.com',
        'password' => 'password123',
    ]);

    $userLoginResponse->assertRedirect('/index');
    expect(session('user_id'))->toBe($this->user->id);
    expect(session('admin_id'))->toBe($this->admin->id);

    // 3. Refreshing / visiting admin panel still succeeds without redirecting to login
    $adminOrdersResponse = $this->get('/adminorders');
    $adminOrdersResponse->assertStatus(200);

    // 4. Visiting user profile still succeeds
    $userProfileResponse = $this->get('/profile');
    $userProfileResponse->assertStatus(200);
});

test('logging out from user side preserves admin session', function () {
    // Both are logged in
    $response = $this->withSession([
        'admin_id' => $this->admin->id,
        'admin_name' => $this->admin->name,
        'user_id' => $this->user->id,
        'user_name' => $this->user->name,
        'user_role' => 'user',
    ])->post('/logout');

    $response->assertRedirect('/login');

    // User session is cleared, but admin_id remains intact
    $response->assertSessionMissing('user_id');
    $response->assertSessionHas('admin_id', $this->admin->id);

    // Admin panel can still be accessed/refreshed without redirect
    $adminPage = $this->get('/adminorders');
    $adminPage->assertStatus(200);
});

test('logging out from admin side preserves user session', function () {
    // Both are logged in
    $response = $this->withSession([
        'admin_id' => $this->admin->id,
        'admin_name' => $this->admin->name,
        'user_id' => $this->user->id,
        'user_name' => $this->user->name,
        'user_role' => 'user',
    ])->post('/admin/logout');

    $response->assertRedirect('/login');

    // Admin session is cleared, but user_id remains intact
    $response->assertSessionMissing('admin_id');
    $response->assertSessionHas('user_id', $this->user->id);

    // User profile can still be accessed/refreshed without redirect
    $userPage = $this->get('/profile');
    $userPage->assertStatus(200);
});

test('admin login preserves active user session', function () {
    // 1. User logs in first
    $this->post('/login', [
        'email' => 'customer@example.com',
        'password' => 'password123',
    ]);
    expect(session('user_id'))->toBe($this->user->id);

    // 2. Admin logs in
    $response = $this->post('/login', [
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);
    $response->assertRedirect('/adminorders');

    // Both sessions are active
    expect(session('user_id'))->toBe($this->user->id);
    expect(session('admin_id'))->toBe($this->admin->id);

    // Both pages can be refreshed and accessed
    $this->get('/adminorders')->assertStatus(200);
    $this->get('/profile')->assertStatus(200);
});

test('admin login redirects directly to admin orders if already authenticated', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])->get('/adminlogin');
    $response->assertRedirect(route('admin.orders.index'));

    $response2 = $this->withSession(['admin_id' => $this->admin->id])->get('/admin/login');
    $response2->assertRedirect(route('admin.orders.index'));
});

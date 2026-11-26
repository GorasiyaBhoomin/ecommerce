<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    User::create([
        'id' => 1,
        'name' => 'Hiral Patel',
        'email' => 'hiral@example.com',
        'password' => Hash::make('password'),
        'role' => 'user',
    ]);
});

test('user profile page can be rendered at /profile', function () {
    $response = $this->withSession(['user_id' => 1])->get('/profile');

    $response->assertStatus(200);
    $response->assertSee('My Profile &amp; Account Settings', false);
    $response->assertSee('Hiral Patel');
});

test('user profile page can be rendered at /userprofile', function () {
    $response = $this->withSession(['user_id' => 1])->get('/userprofile');

    $response->assertStatus(200);
    $response->assertSee('Hiral Patel');
});

test('user profile page can be rendered at /user/profile', function () {
    $response = $this->withSession(['user_id' => 1])->get('/user/profile');

    $response->assertStatus(200);
    $response->assertSee('Hiral Patel');
});

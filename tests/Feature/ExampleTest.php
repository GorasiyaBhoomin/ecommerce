<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('the home page loads when sessions are stored in the database', function () {
    config(['session.driver' => 'database']);

    $this->get('/')->assertOk();
});

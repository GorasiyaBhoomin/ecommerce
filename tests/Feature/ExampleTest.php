<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('the home page loads when sessions are stored in the database', function () {
    config(['session.driver' => 'database']);

    $this->get('/')->assertOk();
})->uses(RefreshDatabase::class);

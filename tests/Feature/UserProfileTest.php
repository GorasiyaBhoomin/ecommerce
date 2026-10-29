<?php

test('user profile page can be rendered at /profile', function () {
    $response = $this->get('/profile');

    $response->assertStatus(200);
    $response->assertSee('My Profile &amp; Account Settings', false);
    $response->assertSee('Hiral Patel');
});

test('user profile page can be rendered at /userprofile', function () {
    $response = $this->get('/userprofile');

    $response->assertStatus(200);
    $response->assertSee('Hiral Patel');
});

test('user profile page can be rendered at /user/profile', function () {
    $response = $this->get('/user/profile');

    $response->assertStatus(200);
    $response->assertSee('Hiral Patel');
});

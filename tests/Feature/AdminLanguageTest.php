<?php

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view the languages page with presets and metrics', function () {
    Language::create([
        'name' => 'English',
        'native_name' => 'English',
        'code' => 'en',
        'is_default' => true,
        'is_active' => true,
    ]);

    $response = $this->withSession(['user_id' => 1])->get(route('admin.languages.index'));

    $response->assertOk();
    $response->assertSee('Languages');
    $response->assertSee('English');
    $response->assertSee('Add New Language');
});

test('admin can switch the active locale', function () {
    Language::create([
        'name' => 'English',
        'native_name' => 'English',
        'code' => 'en',
        'is_default' => true,
        'is_active' => true,
    ]);

    Language::create([
        'name' => 'Gujarati',
        'native_name' => 'ગુજરાતી',
        'code' => 'gu',
        'is_default' => false,
        'is_active' => true,
    ]);

    $response = $this->withSession(['user_id' => 1])
        ->post(route('admin.languages.switch', 'gu'));

    $response->assertSessionHas('locale', 'gu');
});

test('admin can toggle a language active status', function () {
    $lang = Language::create([
        'name' => 'French',
        'native_name' => 'Français',
        'code' => 'fr',
        'is_default' => false,
        'is_active' => false,
    ]);

    $response = $this->withSession(['user_id' => 1])
        ->patch(route('admin.languages.toggle', $lang));

    $response->assertSessionHas('success');
    expect($lang->fresh()->is_active)->toBeTrue();
});

test('admin can change default language', function () {
    $en = Language::create([
        'name' => 'English',
        'native_name' => 'English',
        'code' => 'en',
        'is_default' => true,
        'is_active' => true,
    ]);

    $gu = Language::create([
        'name' => 'Gujarati',
        'native_name' => 'ગુજરાતી',
        'code' => 'gu',
        'is_default' => false,
        'is_active' => true,
    ]);

    $response = $this->withSession(['user_id' => 1])
        ->patch(route('admin.languages.default', $gu));

    $response->assertSessionHas('success');
    expect($gu->fresh()->is_default)->toBeTrue();
    expect($en->fresh()->is_default)->toBeFalse();
});

<?php

use App\Providers\AppServiceProvider;
use Illuminate\Contracts\Encryption\Encrypter;
use Tests\TestCase;

uses(TestCase::class);

test('it assigns a valid encryption key when APP_KEY is empty', function () {
    $originalKey = config('app.key');
    $path = storage_path('app/app.key');
    $previousContents = is_file($path) ? file_get_contents($path) : null;

    try {
        config(['app.key' => '']);

        (new AppServiceProvider(app()))->register();

        expect(config('app.key'))->toStartWith('base64:');

        app()->forgetInstance('encrypter');

        expect(app('encrypter'))->toBeInstanceOf(Encrypter::class);
    } finally {
        config(['app.key' => $originalKey]);
        app()->forgetInstance('encrypter');

        if ($previousContents === null) {
            if (is_file($path)) {
                unlink($path);
            }
        } else {
            file_put_contents($path, $previousContents);
        }
    }
});

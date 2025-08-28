<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => 'Backup-server',

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    |
    | This value determines the "version" your application is currently running
    | in. You may want to follow the "Semantic Versioning" - Given a version
    | number MAJOR.MINOR.PATCH when an update happens: https://semver.org.
    |
    */

    'version' => app('git.version'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. This can be overridden using
    | the global command line "--env" option when calling commands.
    |
    */

    'env' => 'development',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [
        App\Providers\AppServiceProvider::class,
        Intonate\TinkerZero\TinkerZeroServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Spatie\BackupServer\BackupServerServiceProvider::class,
        Illuminate\Events\EventServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
    ],

    'aliases' => [
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Mail'         => Illuminate\Support\Facades\Mail::class,
        'Queue'        => Illuminate\Support\Facades\Queue::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
    ],

];

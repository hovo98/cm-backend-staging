<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'root' => env('AWS_BUCKET_ROOT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],
        'tmp' => [
            'driver' => 'local',
            'root' => sys_get_temp_dir(),
        ],

        'do' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID_DO'),
            'secret' => env('AWS_SECRET_ACCESS_KEY_DO'),
            'region' => env('AWS_DEFAULT_REGION_DO'),
            'bucket' => env('AWS_BUCKET_DO'),
            'endpoint' => env('AWS_URL_DO'),
            'root' => env('AWS_BUCKET_ROOT_DO'),
        ],

        'cm-server-storage' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('profile-images') => storage_path('app/profile-images'),

        public_path('company-logos') => storage_path('app/company-logos'),

        public_path('polygon-locations') => storage_path('app/polygon-locations'),

        public_path('storage') => storage_path('app/public'),
    ],

];

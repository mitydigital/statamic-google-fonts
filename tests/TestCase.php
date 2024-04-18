<?php

namespace MityDigital\StatamicGoogleFonts\Tests;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use MityDigital\StatamicGoogleFonts\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Spatie\GoogleFonts\GoogleFonts;
use Spatie\GoogleFonts\GoogleFontsServiceProvider;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;use Statamic\Extend\Manifest;

abstract class TestCase extends OrchestraTestCase
{
    //

    public function disk(): Filesystem
    {
        $diskName = config('google-fonts.disk');

        return Storage::disk($diskName);
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            //GoogleFontsServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {

        parent::getEnvironmentSetUp($app);

        config()->set('filesystems.disks', array_merge(
            config('filesystems.disks'),
            [
                'fonts' => [
                    'driver' => 'local',
                    'root' => __DIR__.'/fonts',
                    'url' => env('APP_URL').'/storage',
                    'visibility' => 'public',
                ],
            ],
        ));

        config()->set('google-fonts.fonts', [
            'default' => 'default-font-url',
            'code' => 'code-font-url'
        ]);
        config()->set('google-fonts.disk', 'fonts');
        config()->set('google-fonts.path', '');
        config()->set('google-fonts.inline', true);
        config()->set('google-fonts.fallback', false);
        config()->set('google-fonts.user_agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15');
        config()->set('google-fonts.preload', false);


        $app->singleton(GoogleFonts::class, function (Application $app) {
            return new GoogleFonts(
                filesystem: $app->make(FilesystemManager::class)->disk(config('google-fonts.disk')),
                path: config('google-fonts.path'),
                inline: config('google-fonts.inline', false),
                fallback: config('google-fonts.fallback'),
                userAgent: config('google-fonts.user_agent'),
                fonts: config('google-fonts.fonts'),
                preload: config('google-fonts.preload'),
            );
        });

        $app->make(Manifest::class)->manifest = [
            /*'spatie/laravel-google-fonts' => [
                'id'        => 'spatie/laravel-google-fonts',
                'namespace' => 'Spatie\\GoogleFonts',
            ],*/
            'mitydigital/statamic-google-fonts' => [
                'id'        => 'mitydigital/statamic-google-fonts',
                'namespace' => 'MityDigital\\StatamicGoogleFonts',
            ],
        ];
    }
}

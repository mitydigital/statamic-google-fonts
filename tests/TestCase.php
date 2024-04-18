<?php

namespace MityDigital\StatamicGoogleFonts\Tests;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Application;
use MityDigital\StatamicGoogleFonts\ServiceProvider;
use Spatie\GoogleFonts\GoogleFonts;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

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
            'code' => 'code-font-url',
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
    }
}

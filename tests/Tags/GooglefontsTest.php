<?php

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\GoogleFonts\Fonts;
use Spatie\GoogleFonts\GoogleFonts;
use Statamic\Facades\Parse;
use Statamic\Facades\Site;
use Statamic\Facades\URL;

beforeEach(function () {
    Http::fake();
    Storage::fake(config('google-fonts.disk'));

    // partially mock the Google Fonts class
    $this->mock = Mockery::mock(GoogleFonts::class, [
        $this->app->make(FilesystemManager::class)->disk(config('google-fonts.disk')),
        config('google-fonts.path'),
        config('google-fonts.inline'),
        config('google-fonts.fallback'),
        config('google-fonts.user_agent'),
        config('google-fonts.fonts'),
        config('google-fonts.preload'),
    ])->makePartial();
});

test('it can load the default font', function () {
    $tag = '{{ googlefonts }}'; // no param means "default"
    $data = [];
    $response = trim((string) Parse::template($tag, $data));

    // <link href="default-font-url" rel="stylesheet" type="text/css">
    expect($response)->toStartWith('<link')
        ->toContain('href="default-font-url"');
});

test('it can load a specific named font', function () {
    $tag = '{{ googlefonts:code }}'; // load the "code" font
    $data = [];
    $response = trim((string) Parse::template($tag, $data));

    expect($response)->toStartWith('<link')
        ->toContain('href="code-font-url"');
});

test('it throws an exception for an undefined font', function () {
    $tag = '{{ googlefonts:not-defined }}'; // try to load the "not-defined" font
    $data = [];
    Parse::template($tag, $data); // this will throw the exception

})->throws(RuntimeException::class);

test('it correctly applies full URLs for multisite', function () {

    // set base tag data
    $tag = '{{ googlefonts }}'; // no param means "default"
    $data = [];

    // return a special Fonts object that contains expected CSS
    $this->mock->shouldReceive('load')->andReturnUsing(function(){
        return new Fonts('http://',
            'localizedUrl',
            "@font-face {
                          font-family: 'Inter';
                          font-style: normal;
                          font-weight: 400;
                          font-display: swap;
                          src: url(http://localhost/storage/fonts/3fae105f7a/splusjakartasansv8ldioaomqnqcsa88c7o9yz4kmcoog4ko70yygg-vbd-e.woff2) format('woff2');
                        }",
            null,
            config('google-fonts.inline'));
    });
    $this->instance(GoogleFonts::class, $this->mock);

    // expect "http://localhost" in the fonts
    $this->assertStringContainsString('src: url(http://localhost/storage/', $this->mock->load()->toHtml());


    // do a simple straightforward call - should be "localhost"
    /*
    <style>
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url(http://localhost/storage/fonts/3fae105f7a/splusjakartasansv8ldioaomqnqcsa88c7o9yz4kmcoog4ko70yygg-vbd-e.woff2) format('woff2');
        }
    </style>
    */
    $response = trim((string) Parse::template($tag, $data));
    $this->assertStringNotContainsString('src: url(https://www.my-multi-site.com/storage', $response);
    $this->assertStringContainsString('src: url(http://localhost/storage', $response);

    //
    // configure multi-site
    //
    Site::setConfig('sites', [
        'default' => [
            'name' => config('app.name'),
            'locale' => 'en_US',
            'url' => '/',
        ],
        'multi' => [
            'name' => 'Multi Site',
            'locale' => 'en_AU',
            'url' => 'https://my-multi-site.com',
        ]
    ]);

    // set the multi-site URL
    URL::shouldReceive('getSiteUrl')->andReturn('https://www.my-multi-site.com/');
    expect(URL::getSiteUrl())->toBe('https://www.my-multi-site.com/');

    // do another call, and should not be localhost, but use the newly set domain
    /*
    <style>
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url(http://localhost/storage/fonts/3fae105f7a/splusjakartasansv8ldioaomqnqcsa88c7o9yz4kmcoog4ko70yygg-vbd-e.woff2) format('woff2');
        }
    </style>
     */
    $response = trim((string) Parse::template($tag, $data));
    $this->assertStringContainsString('src: url(https://www.my-multi-site.com/storage', $response);
    $this->assertStringNotContainsString('src: url(http://localhost/storage', $response);
});
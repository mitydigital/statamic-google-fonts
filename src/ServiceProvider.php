<?php

namespace MityDigital\StatamicGoogleFonts;

use MityDigital\StatamicGoogleFonts\Tags\Googlefonts as GooglefontsTag;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    // Register the Googlefonts Tag
    protected $tags = [
        GooglefontsTag::class
    ];
}

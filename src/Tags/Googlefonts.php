<?php

namespace MityDigital\StatamicGoogleFonts\Tags;

use Statamic\Tags\Tags;

class Googlefonts extends Tags
{
    /**
     * The {{ googlefonts }} tag.
     *
     * Loads the "default" Google Font. "default" is one of the keys in
     * your config/google-fonts.php file.
     *
     * Accepts an optional parameter of "font", such as:
     *     {{ googlefonts font="code" }}
     * to download the font with the config key of "code".
     *
     * When omitted, the font param will default to, well, "default"
     *
     * @return string
     */
    public function index()
    {
        // get the font param, or set to "default"
        $font = $this->params->get('font', 'default');

        // load the font
        return $this->_load($font);
    }

    /**
     * Performs the actual loading of the Google Font, just like how
     * the Spatie Laravel Google Fonts package sets up its directive.
     *
     * @param $expression   string  The Google Font to load
     *
     * @return string
     */
    protected function _load($expression = 'default')
    {
        return app(\Spatie\GoogleFonts\GoogleFonts::class)->load($expression)->toHtml();
    }

    /**
     * The {{ googlefonts:font }} tag.
     *
     * Loads the requested Google Font. The "font" is one of the keys in
     * your config/google-fonts.php file.
     *
     * @return string
     */
    public function wildcard($font)
    {
        // load the font with the requested parameter
        return $this->_load($font);
    }
}
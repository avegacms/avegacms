<?php

namespace AvegaCms\Utilities;

class Vite
{
    /**
     * @var bool
     */
    private static bool $initialized = false;

    /**
     * @var array
     */
    private static array $css = [];

    /**
     * @var array
     */
    private static array $js = [];

    /**
     * @return void
     */
    private static function init(): void
    {
        helper('html');

        $path = FCPATH . 'dist/manifest.json';

        $entryFile = env('VITE_ORIGIN') . '/' . env('VITE_RESOURCES_DIR') . '/' . env('VITE_ENTRY_FILE');

        switch (true) {
            // Если включён режим разработки Vite
            case @file_get_contents($entryFile):
                self::$js[] = env('VITE_ORIGIN') . '/@vite/client';
                self::$js[] = $entryFile;
                break;
            // Если существует manifest.json
            case $manifest = @file_get_contents($path):
                $manifest = json_decode($manifest);

                if ( ! is_null($manifest)) {
                    foreach ($manifest as $item) {
                        if (property_exists($item, 'isEntry') && $item->isEntry) {
                            self::$js[] = 'dist/' . $item->file;

                            if (isset($item->css)) {
                                foreach ($item->css as $css) {
                                    self::$css[] = 'dist/' . $css;
                                }
                            }
                        }
                    }
                }
                break;
        }

        self::$initialized = true;
    }

    /**
     * @return string
     */
    public static function css(): string
    {
        if ( ! self::$initialized) {
            self::init();
        }

        return implode(
            PHP_EOL,
            array_map(
                fn ($src) => link_tag(
                    [
                        'href'  => $src,
                        'rel'   => 'stylesheet',
                        'type'  => "text/css",
                        'media' => 'all',
                    ]
                ),
                self::$css
            )
        );
    }

    /**
     * @return string
     */
    public static function js(): string
    {
        if ( ! self::$initialized) {
            self::init();
        }

        return implode(
            PHP_EOL,
            array_map(
                fn ($src) => script_tag(
                    [
                        'src'     => $src,
                        'type'    => 'module',
                        'charset' => "UTF-8"
                    ]
                ),
                self::$js
            )
        );
    }
}

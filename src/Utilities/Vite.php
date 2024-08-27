<?php

declare(strict_types=1);

namespace AvegaCms\Utilities;

class Vite
{
    private static bool $initialized = false;

    private static array $css = [];

    private static array $js = [];

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
                $manifest  = json_decode($manifest);

                if (null !== $manifest) {
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

    public static function css(): string
    {
        if (! self::$initialized) {
            self::init();
        }

        return implode(
            PHP_EOL,
            array_map(
                static fn ($src) => link_tag(
                    [
                        'href'  => $src,
                        'rel'   => 'stylesheet',
                        'type'  => 'text/css',
                        'media' => 'all',
                    ]
                ),
                self::$css
            )
        );
    }

    public static function js(): string
    {
        if (! self::$initialized) {
            self::init();
        }

        return implode(
            PHP_EOL,
            array_map(
                static fn ($src) => script_tag(
                    [
                        'src'     => $src,
                        'type'    => 'module',
                        'charset' => 'UTF-8',
                    ]
                ),
                self::$js
            )
        );
    }
}

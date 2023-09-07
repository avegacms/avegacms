<?php

if ( ! function_exists('initClientSession')) {
    function initClientSession(): void
    {
        $session = session();

        if ( ! $session->has('avegacms')) {
            $session->set('avegacms',
                [
                    'client'  => [

                        'lang'        => [],
                        'user'        => [],
                        'geolocation' => [

                            'city' => ''
                        ],
                        'confirm'     => [

                            'use_cookie' => false,
                            'gdpr'       => false
                        ]
                    ],
                    'modules' => [],
                    'admin'   => []
                ]
            );
        }
    }
}

if ( ! function_exists('arrayToObject')) {
    function arrayToObject(array $array): object
    {
        return (object) array_map(function ($item) {
            if (is_array($item)) {
                return arrayToObject($item);
            }
            return $item;
        }, $array);
    }
}

if ( ! function_exists('getTree')) {
    /**
     * @param  array  $input
     * @param  int  $parentId
     * @return array
     */
    function getTree(array $input, int $parentId = 0): array
    {
        $outputArray = [];

        foreach ($input as $item) {
            if ($item['parent'] == $parentId) {
                $children = getTree($input, $item['id']);

                if ( ! empty($children)) {
                    $item['list'] = $children;
                }

                $outputArray[] = $item;
            }
        }

        return $outputArray;
    }
}

if ( ! function_exists('settings')) {
    /**
     * Provides a convenience interface to the Settings service.
     *
     * @param  mixed  $value
     *
     * @return array|bool|float|int|object|Settings|string|void|null
     * @phpstan-return ($key is null ? Settings : ($value is null ? array|bool|float|int|object|string|null : void))
     */
    function settings(?string $key = null, $value = null, $config = null)
    {
        /** @var Settings $setting */
        $setting = service('settings');

        if (empty($key)) {
            throw new InvalidArgumentException('$key cannot be empty');
        }

        // Getting the value?
        if (count(func_get_args()) === 1) {
            return $setting->get($key);
        }

        // Setting the value
        $setting->set($key, $value, $config);
    }
}
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
     * @param  array  $data
     * @return array
     */
    function getTree(array $data = []): array
    {
        $tree = [];

        if ( ! empty($data)) {
            foreach ($data as $id => &$node) {
                if (isset($node['parent'])) {
                    if ( ! $node['parent']) {
                        $tree[$id] = &$node;
                    } else {
                        $data[$node['parent']]['list'][$id] = &$node;
                    }
                }
            }
        }

        return $tree;
    }
}
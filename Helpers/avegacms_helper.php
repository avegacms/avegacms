<?php

if (!function_exists('initClientSession')) {
    function initClientSession(): void
    {
        $session = session();

        if (!$session->has('avegacms')) {
            $session->set('avegacms', [
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
            ]);
        }
    }
}
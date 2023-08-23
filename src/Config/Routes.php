<?php

namespace Config;

use AvegaCms\Controllers\Api\Public\Login;
use AvegaCms\Controllers\Api\Admin\Settings;
use AvegaCms\Controllers\Api\Admin\Content\Pages;
use AvegaCms\Controllers\Api\Admin\Settings\{Locales, Modules, Permissions, Roles, Users};

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('AvegaCms\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `avegacms/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

$routes->group('api', function ($routes) {
    $routes->group('public', ['namespace' => 'AvegaCms\Controllers\Api\Public'], function ($routes) {
        $routes->group('content', function ($routes) {
            $routes->get('/', 'Content::index');
        });

        $routes->post('login/(:segment)', [Login::class, 'index/$1']);
    });
    $routes->group('admin', ['namespace' => 'AvegaCms\Controllers\Api\Admin', 'filter' => 'auth'], function ($routes) {
        $routes->group('content', function ($routes) {
            $routes->group('pages', function ($routes) {
                $routes->get('/', [Pages::class, 'index']);
            });
        });

        $routes->group('settings', function ($routes) {
            $routes->group('locales', function ($routes) {
                $routes->get('/', [Locales::class, 'index']);
                $routes->get('(:num)/edit', [Locales::class, 'edit/$1']);
                $routes->post('/', [Locales::class, 'create']);
                $routes->put('(:num)', [Locales::class, 'update/$1']);
                $routes->patch('(:num)', [Locales::class, 'update/$1']);
                $routes->delete('(:num)', [Locales::class, 'delete/$1']);
            });
            $routes->group('modules', function ($routes) {
                $routes->get('/', [Modules::class, 'index']);
                $routes->get('(:num)', [Modules::class, 'show/$1']);
                $routes->post('/', [Modules::class, 'create']);
                $routes->put('(:num)', [Modules::class, 'update/$1']);
                $routes->patch('(:num)', [Modules::class, 'update/$1']);
                $routes->delete('(:num)', [Modules::class, 'delete/$1']);
            });
            $routes->group('permissions', function ($routes) {
                $routes->get('(:num)/(:num)', [Permissions::class, 'actions/$1/$2']);
                $routes->get('(:num)/edit', [Permissions::class, 'edit/$1']);
                $routes->put('(:num)', [Permissions::class, 'update/$1']);
                $routes->delete('(:num)', [Permissions::class, 'delete/$1']);
            });
            $routes->group('roles', function ($routes) {
                $routes->get('/', [Roles::class, 'index']);
                $routes->get('(:num)/edit', [Roles::class, 'edit/$1']);
                $routes->post('/', [Roles::class, 'create']);
                $routes->put('(:num)', [Roles::class, 'update/$1']);
                $routes->patch('(:num)', [Roles::class, 'update/$1']);
                $routes->delete('(:num)', [Roles::class, 'delete/$1']);
            });
            $routes->group('users', function ($routes) {
                $routes->get('/', [Users::class, 'index']);
                $routes->get('new', [Users::class, 'new']);
                $routes->get('(:num)/edit', [Users::class, 'edit/$1']);
                $routes->post('/', [Users::class, 'create']);
                $routes->put('(:num)', [Users::class, 'update/$1']);
                $routes->patch('(:num)', [Users::class, 'update/$1']);
                $routes->delete('(:num)', [Users::class, 'delete/$1']);
            });
            //$routes->get('/', [Settings::class, 'index']);
        });
    });
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}

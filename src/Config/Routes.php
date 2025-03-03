<?php

declare(strict_types=1);

use AvegaCms\Controllers\Api\Admin\Pages;
use AvegaCms\Controllers\Api\Admin\Profile;
use AvegaCms\Controllers\Api\Admin\Settings\EmailTemplate;
use AvegaCms\Controllers\Api\Admin\Settings\Locales;
use AvegaCms\Controllers\Api\Admin\Settings\Modules;
use AvegaCms\Controllers\Api\Admin\Settings\Navigations;
use AvegaCms\Controllers\Api\Admin\Settings\Permissions;
use AvegaCms\Controllers\Api\Admin\Settings\Roles;
use AvegaCms\Controllers\Api\Admin\Settings\Settings;
use AvegaCms\Controllers\Api\Admin\Settings\Users;
use AvegaCms\Controllers\Api\AvegaCmsAPI;
use AvegaCms\Controllers\Api\Public\Login;
use AvegaCms\Controllers\Content;
use AvegaCms\Controllers\Seo;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('api', static function (RouteCollection $routes) {
    $routes->group('public', ['namespace' => 'AvegaCms\Controllers\Api\Public'], static function (RouteCollection $routes) {
        $routes->get('logout', [Login::class, 'logout']);
        $routes->get('logout/(:segment)', [[Login::class, 'logout'], '$1']);
        $routes->post('login/(:segment)', [[Login::class, 'index'], '$1']);
    });
    $routes->group(
        'admin',
        ['namespace' => 'AvegaCms\Controllers\Api\Admin'],
        static function (RouteCollection $routes) {
            $routes->group('pages', static function (RouteCollection $routes) {
                $routes->get('/', [Pages::class, 'index']);
                $routes->get('new', [Pages::class, 'new']);
                $routes->get('(:num)/edit', [[Pages::class, 'edit'], '$1']);
                $routes->post('/', [Pages::class, 'create']);
                $routes->put('(:num)', [[Pages::class, 'update'], '$1']);
                $routes->patch('(:num)', [[Pages::class, 'patch'], '$1']);
                $routes->delete('(:num)', [[Pages::class, 'delete'], '$1']);
            });
            $routes->group('settings', static function (RouteCollection $routes) {
                $routes->group('locales', static function (RouteCollection $routes) {
                    $routes->get('/', [Locales::class, 'index']);
                    $routes->get('(:num)/edit', [[Locales::class, 'edit'], '$1']);
                    $routes->post('/', [Locales::class, 'create']);
                    $routes->put('(:num)', [[Locales::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Locales::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Locales::class, 'delete'], '$1']);
                });
                $routes->group('modules', static function (RouteCollection $routes) {
                    $routes->get('/', [Modules::class, 'index']);
                    $routes->get('(:num)', [[Modules::class, 'show'], '$1']);
                    $routes->post('/', [Modules::class, 'create']);
                    $routes->put('(:num)', [[Modules::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Modules::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Modules::class, 'delete'], '$1']);
                });
                $routes->group('navigations', static function (RouteCollection $routes) {
                    $routes->get('/', [Navigations::class, 'index']);
                    $routes->get('new', [Navigations::class, 'new']);
                    $routes->get('(:num)/edit', [[Navigations::class, 'edit'], '$1']);
                    $routes->post('/', [Navigations::class, 'create']);
                    $routes->put('(:num)', [[Navigations::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Navigations::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Navigations::class, 'delete'], '$1']);
                });
                $routes->group('permissions', static function (RouteCollection $routes) {
                    $routes->get('(:num)/(:num)', [[Permissions::class, 'actions'], '$1', '$2']);
                    $routes->get('(:num)/edit', [[Permissions::class, 'edit'], '$1']);
                    $routes->put('(:num)', [[Permissions::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Permissions::class, 'delete'], '$1']);
                });
                $routes->group('roles', static function (RouteCollection $routes) {
                    $routes->get('/', [Roles::class, 'index']);
                    $routes->get('(:num)/edit', [Roles::class, 'edit/$1']);
                    $routes->get('(:num)/permissions', [Roles::class, 'permissions/$1']);
                    $routes->post('/', [Roles::class, 'create']);
                    $routes->put('(:num)', [[Roles::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Roles::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Roles::class, 'delete'], '$1']);
                });
                $routes->group('users', static function (RouteCollection $routes) {
                    $routes->get('/', [Users::class, 'index']);
                    $routes->get('new', [Users::class, 'new']);
                    $routes->get('(:num)/edit', [[Users::class, 'edit'], '$1']);
                    $routes->post('/', [Users::class, 'create']);
                    $routes->put('(:num)', [[Users::class, 'update'], '$1']);
                    $routes->put('upload/(:num)', [[Users::class, 'upload'], '$1']);
                    $routes->patch('(:num)', [[Users::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Users::class, 'delete'], '$1']);
                });
                $routes->group('settings', static function (RouteCollection $routes) {
                    $routes->get('/', [Settings::class, 'index']);
                    $routes->post('/', [Settings::class, 'create']);
                    $routes->get('new', [Settings::class, 'new']);
                    $routes->get('(:num)/edit', [[Settings::class, 'edit'], '$1']);
                    $routes->put('(:num)', [[Settings::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Settings::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Settings::class, 'delete'], '$1']);
                });
                $routes->group('email_template', static function (RouteCollection $routes) {
                    $routes->get('/', [EmailTemplate::class, 'index']);
                    $routes->get('(:num)/edit', [[EmailTemplate::class, 'edit'], '$1']);
                    $routes->post('/', [EmailTemplate::class, 'create']);
                    $routes->put('(:num)', [[EmailTemplate::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[EmailTemplate::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[EmailTemplate::class, 'delete'], '$1']);
                });
            });
            $routes->get('profile', [Profile::class, 'index']);
        }
    );
});

$routes->get('robots.txt', [Seo::class, 'robots']);
$routes->get('sitemap.xml', [Seo::class, 'sitemap']);

$routes->match(
    ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
    'api/(:any)',
    [AvegaCmsAPI::class, 'apiMethodNotFound'],
    ['priority' => 10000]
);

$routes->get('(.*)', [Content::class, 'index'], ['priority' => 10001]);

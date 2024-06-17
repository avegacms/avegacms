<?php

use CodeIgniter\Router\RouteCollection;

use AvegaCms\Controllers\{Content, Seo};
use AvegaCms\Controllers\Api\AvegaCmsAPI;
use AvegaCms\Controllers\Api\Public\{Login, Content as ContentApi};
use AvegaCms\Controllers\Api\Admin\Profile;
use AvegaCms\Controllers\Api\Admin\Content\{Pages, Rubrics, Posts};
use AvegaCms\Controllers\Api\Admin\Settings\{Locales,
    Modules,
    Navigations,
    Permissions,
    Roles,
    Settings,
    Users,
    EmailTemplate
};

/**
 * @var RouteCollection $routes
 */

$routes->group('api', function (RouteCollection $routes) {
    $routes->group('public', ['namespace' => 'AvegaCms\Controllers\Api\Public'], function (RouteCollection $routes) {
        $routes->group('content', function (RouteCollection $routes) {
            $routes->get('/', [ContentApi::class, 'index']);
        });
        $routes->get('logout', [Login::class, 'logout']);
        $routes->get('logout/(:segment)', [[Login::class, 'logout'], '$1']);
        $routes->post('login/(:segment)', [[Login::class, 'index'], '$1']);
    });
    $routes->group('admin', ['namespace' => 'AvegaCms\Controllers\Api\Admin', 'filter' => 'auth'],
        function (RouteCollection $routes) {
            $routes->get('profile', [Profile::class, 'index']);
            $routes->group('content', function (RouteCollection $routes) {
                $routes->group('pages', function (RouteCollection $routes) {
                    $routes->get('/', [Pages::class, 'index']);
                    $routes->get('new', [Pages::class, 'new']);
                    $routes->get('(:num)/edit', [[Pages::class, 'edit'], '$1']);
                    $routes->post('/', [Pages::class, 'create']);
                    $routes->put('(:num)', [[Pages::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Pages::class, 'patch'], '$1']);
                    $routes->delete('(:num)', [[Pages::class, 'delete'], '$1']);
                });
                $routes->group('posts', function (RouteCollection $routes) {
                    $routes->get('/', [Posts::class, 'index']);
                    $routes->get('new', [Posts::class, 'new']);
                    $routes->get('(:num)/edit', [[Posts::class, 'edit'], '$1']);
                    $routes->post('/', [Posts::class, 'create']);
                    $routes->put('(:num)', [[Posts::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Posts::class, 'patch'], '$1']);
                    $routes->delete('(:num)', [[Posts::class, 'delete'], '$1']);
                });
                $routes->group('rubrics', function (RouteCollection $routes) {
                    $routes->get('/', [Rubrics::class, 'index']);
                    $routes->get('new', [Rubrics::class, 'new']);
                    $routes->get('(:num)/edit', [[Rubrics::class, 'edit'], '$1']);
                    $routes->post('/', [Rubrics::class, 'create']);
                    $routes->put('(:num)', [[Rubrics::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Rubrics::class, 'patch'], '$1']);
                    $routes->delete('(:num)', [[Rubrics::class, 'delete'], '$1']);
                });
            });
            $routes->group('settings', function (RouteCollection $routes) {
                $routes->group('locales', function (RouteCollection $routes) {
                    $routes->get('/', [Locales::class, 'index']);
                    $routes->get('(:num)/edit', [[Locales::class, 'edit'], '$1']);
                    $routes->post('/', [Locales::class, 'create']);
                    $routes->put('(:num)', [[Locales::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Locales::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Locales::class, 'delete'], '$1']);
                });
                $routes->group('modules', function (RouteCollection $routes) {
                    $routes->get('/', [Modules::class, 'index']);
                    $routes->get('(:num)', [[Modules::class, 'show'], '$1']);
                    $routes->post('/', [Modules::class, 'create']);
                    $routes->put('(:num)', [[Modules::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Modules::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Modules::class, 'delete'], '$1']);
                });
                $routes->group('navigations', function (RouteCollection $routes) {
                    $routes->get('/', [Navigations::class, 'index']);
                    $routes->get('new', [Navigations::class, 'new']);
                    $routes->get('(:num)/edit', [[Navigations::class, 'edit'], '$1']);
                    $routes->post('/', [Navigations::class, 'create']);
                    $routes->put('(:num)', [[Navigations::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Navigations::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Navigations::class, 'delete'], '$1']);
                });
                $routes->group('permissions', function (RouteCollection $routes) {
                    $routes->get('(:num)/(:num)', [[Permissions::class, 'actions'], '$1', '$2']);
                    $routes->get('(:num)/edit', [[Permissions::class, 'edit'], '$1']);
                    $routes->put('(:num)', [[Permissions::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Permissions::class, 'delete'], '$1']);
                });
                $routes->group('roles', function (RouteCollection $routes) {
                    $routes->get('/', [Roles::class, 'index']);
                    $routes->get('(:num)/edit', [Roles::class, 'edit/$1']);
                    $routes->post('/', [Roles::class, 'create']);
                    $routes->put('(:num)', [[Roles::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Roles::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Roles::class, 'delete'], '$1']);
                });
                $routes->group('users', function (RouteCollection $routes) {
                    $routes->get('/', [Users::class, 'index']);
                    $routes->get('new', [Users::class, 'new']);
                    $routes->get('(:num)/edit', [[Users::class, 'edit'], '$1']);
                    $routes->post('/', [Users::class, 'create']);
                    $routes->put('(:num)', [[Users::class, 'update'], '$1']);
                    $routes->put('upload/(:num)', [[Users::class, 'upload'], '$1']);
                    $routes->patch('(:num)', [[Users::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Users::class, 'delete'], '$1']);
                });
                $routes->group('settings', function (RouteCollection $routes) {
                    $routes->get('/', [Settings::class, 'index']);
                    $routes->post('/', [Settings::class, 'create']);
                    $routes->get('new', [Settings::class, 'new']);
                    $routes->get('(:num)/edit', [[Settings::class, 'edit'], '$1']);
                    $routes->put('(:num)', [[Settings::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[Settings::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[Settings::class, 'delete'], '$1']);
                });
                $routes->group('email_template', function (RouteCollection $routes) {
                    $routes->get('/', [EmailTemplate::class, 'index']);
                    $routes->get('(:num)/edit', [[EmailTemplate::class, 'edit'], '$1']);
                    $routes->post('/', [EmailTemplate::class, 'create']);
                    $routes->put('(:num)', [[EmailTemplate::class, 'update'], '$1']);
                    $routes->patch('(:num)', [[EmailTemplate::class, 'update'], '$1']);
                    $routes->delete('(:num)', [[EmailTemplate::class, 'delete'], '$1']);
                });
            });
        });
});

$routes->get('robots.txt', [Seo::class, 'robots']);
$routes->get('sitemap.xml', [Seo::class, 'sitemap']);

$routes->match(
    ['get', 'post', 'put', 'patch', 'delete'],
    'api/(:any)', [AvegaCmsAPI::class, 'apiMethodNotFound'],
    ['priority' => 10000]
);

$routes->get('(.*)', [Content::class, 'index'], ['priority' => 10001]);
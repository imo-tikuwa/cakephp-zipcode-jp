<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Route\DashedRoute;

return static function (RouteBuilder $routes) {
    $routes->plugin(
        'ZipcodeJp',
        ['path' => '/zipcode-jp'],
        function (RouteBuilder $routes) {
            $routes->setExtensions(['json']);
            $routes->connect('/:zipcode', ['controller' => 'Search', 'action' => 'index']);
            $routes->fallbacks(DashedRoute::class);
        }
    );

    $routes->plugin(
        'ZipcodeJp',
        ['path' => '/zipcode-jp-test'],
        function (RouteBuilder $routes) {
            $routes->connect('/', ['controller' => 'Test', 'action' => 'index']);
            $routes->fallbacks(DashedRoute::class);
        }
    );
};
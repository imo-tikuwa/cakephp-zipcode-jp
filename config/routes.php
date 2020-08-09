<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::plugin(
    'ZipcodeJp',
    ['path' => '/zipcode-jp'],
    function (RouteBuilder $routes) {
        $routes->setExtensions(['json']);
        $routes->connect('/:zipcode', ['controller' => 'Search', 'action' => 'index']);
        $routes->fallbacks(DashedRoute::class);
    }
);

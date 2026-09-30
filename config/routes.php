<?php
declare(strict_types=1);

use App\Middleware\JwtAuthMiddleware;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->registerMiddleware('jwt', new JwtAuthMiddleware());

        $builder->connect('/', ['controller' => 'Health', 'action' => 'index']);
        $builder->connect('/health', ['controller' => 'Health', 'action' => 'health']);

        $builder->post('/auth/register', ['controller' => 'Auth', 'action' => 'register']);
        $builder->post('/auth/login', ['controller' => 'Auth', 'action' => 'login']);
        $builder->get('/auth/me', ['controller' => 'Auth', 'action' => 'me'])
            ->setMiddleware(['jwt']);

        $builder->get('/items/stats/summary', ['controller' => 'Items', 'action' => 'stats']);
        $builder->get('/items', ['controller' => 'Items', 'action' => 'index']);
        $builder->get('/items/{id}', ['controller' => 'Items', 'action' => 'view'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->post('/items', ['controller' => 'Items', 'action' => 'add'])
            ->setMiddleware(['jwt']);
        $builder->patch('/items/{id}', ['controller' => 'Items', 'action' => 'edit'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+'])
            ->setMiddleware(['jwt']);
        $builder->delete('/items/{id}', ['controller' => 'Items', 'action' => 'delete'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+'])
            ->setMiddleware(['jwt']);

        $builder->get('/categories', ['controller' => 'Categories', 'action' => 'index']);
        $builder->get('/categories/{id}', ['controller' => 'Categories', 'action' => 'view'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->post('/categories', ['controller' => 'Categories', 'action' => 'add'])
            ->setMiddleware(['jwt']);
        $builder->patch('/categories/{id}', ['controller' => 'Categories', 'action' => 'edit'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+'])
            ->setMiddleware(['jwt']);
        $builder->delete('/categories/{id}', ['controller' => 'Categories', 'action' => 'delete'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+'])
            ->setMiddleware(['jwt']);
    });
};

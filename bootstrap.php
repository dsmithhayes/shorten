<?php

require_once 'vendor/autoload.php';

use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app = new Slim\App();

$container = $app->getContainer();

/**
 * Set up the views
 */
$container['view'] = function ($c) {
    $v = new Twig(__DIR__ . '/templates', []);

    $basePath = str_ireplace('index.php', '', $c['request']->getUri()->getBasePath());
    $basePath = rtrim($basePath, '/');
    $v->addExtension(new TwigExtension($c['router'], $basePath));

    return $v;
};

/**
 * Add the routes
 */

$app->get('/', function (Request $req, Response $res) {

    return $this->view->render($res, 'home.twig');
});

return $app;
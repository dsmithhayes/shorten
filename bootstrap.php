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
 * Add the middleware
 */
$app->add(function (Request $req, Response $res, $next) {
    $req = $req->withAttribute('post', $_POST)
               ->withAttribute('get', $_GET);

    return $next($req, $res);
});

/**
 * Routes
 */

// home page
$app->get('/', function (Request $req, Response $res) {
    return $this->view->render($res, 'home.twig');
})->setName('home');

// build the short URL
$app->post('/shorten', function (Request $req, Response $res) {
    $post = $req->getAttribute('post');
    return $this->view->render($res, 'show.twig', [
        'url' => $post['url']
    ]);
})->setName('shorten');

// get the URL
$app->get('/u/{url}', function (Request $req, Response $res, $args) {
    $url = $args['url'];

    // 301 to the URL
    return $res;
})->setName('getUrl');

return $app;
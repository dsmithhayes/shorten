<?php

require_once 'vendor/autoload.php';

use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Shorten\Database;
use Shorten\Url;

$conf = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

foreach (scandir(dirname(__FILE__) . '/conf') as $c) {
    if ($c === '.' || $c === '..') {
        continue;
    }

    $key = preg_replace('/\.php/', '', $c);
    $conf['settings'][$key] = require dirname(__FILE__) . '/conf/' . $c;
}

$app = new Slim\App($conf);

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
 * Set up the PDO instance
 */
$container['pdo'] = function ($c) {
    $db = new Database($c->settings['db']);
    return $db->getPdo();
};

/**
 * Add the middleware
 */

// Adds the GET and POST super globals
$app->add(function (Request $req, Response $res, callable $next) {
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
    $pdo = $this->get('pdo');

    $url = new Url($this->settings['site']['url'], $post['url']);

    $sth = $pdo->prepare("SELECT * FROM `urls` WHERE `from` = :url");
    $sth->execute([ ':url' => $url->getFromUrl() ]);

    if (($row = $sth->fetch())) {
        $url = $row['to'];
    } else {
        $insert = "INSERT INTO `urls` (`from`, `to`, `hash`) 
                   VALUES ('{$url->getFromUrl()}', 
                           '{$url->getToUrl()}', 
                           '{$url->getHash()}')";

        $sth = $pdo->exec($insert);

        if ($sth) {
            $url = $url->getToUrl();
        } else {
            throw new Exception('Error adding link to the database: ' . $pdo->errorInfo()[2]);
        }
    }

    return $this->view->render($res, 'show.twig', [
        'url' => $url
    ]);
})->setName('shorten');

// get the URL
$app->get('/u/{url}', function (Request $req, Response $res, $args) {
    $url = $args['url'];
    $pdo = $this->get('pdo');

    $sth = $pdo->prepare("SELECT * FROM `urls` WHERE `hash` = :url");
    $sth->execute([ ':url' => $url ]);

    if (($row = $sth->fetch())) {
        $from = $row['from'];
    } else {
        return $res->withStatus(500)->getBody()->write('Link does not exist.');
    }

    $insert = "INSERT INTO `logs` (`url_id`) VALUES (`{$row['id']}`)";
    $res = $pdo->exec($insert);

    if (!$res) {
        throw new Exception('Error logging the redirect.');
    }

    return $res->withStatus(302)->withHeader('Location', $from);
})->setName('getUrl');

$app->get('/list[/page/{n}]', function (Request $req, Response $res, $args) {
    if ($args['n']) {
        $pageNumber = (int) $args['n'];
    }

    return $this->view->render($res, 'home.twig');
});

// Return the application to the front controller
return $app;
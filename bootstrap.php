<?php

require_once 'vendor/autoload.php';

use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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

$container['pdo'] = function ($c) {
    $dbConf = $c->get('settings')['db'];
    $dsn = "mysql:host={$dbConf['host']};dbname={$dbConf['dbname']}";
    return new PDO($dsn, $dbConf['username'], $dbConf['password']);
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

    $sth = $pdo->prepare("SELECT * FROM `urls` WHERE `from` = :url");
    $sth->execute([ ':url' => $post['url'] ]);

    if (($row = $sth->fetch())) {
        $url = $row['to'];
    } else {
        $h = hash('crc32', $post['url']);
        $to = $this->get('settings')['site']['url'] . 'u/' . $h;

        $insert = "INSERT INTO `urls` (`from`, `to`, `hash`) VALUES ('{$post['url']}', '{$to}', '{$h}')";
        $sth = $pdo->exec($insert);

        if ($sth) {
            $url = $to;
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

    return $res->withStatus(301)->withHeader('Location', $from);
})->setName('getUrl');

// Return the application to the front controller
return $app;
<?php

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use PsyNexus\Database\Connection;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Set up the database connection
$db = Connection::getInstance();
$container = $app->getContainer();
$container->set(Connection::class, function () use ($db) {
    return $db;
});

// Register middleware
$app->addBodyParsingMiddleware();
$app->add(\PsyNexus\Middleware\RateLimitMiddleware::class);
$app->add(\PsyNexus\Middleware\CorsMiddleware::class);

// Define app routes
$app->group('/events', function (RouteCollectorProxy $group) {
    $group->post('', \PsyNexus\Actions\CreateEventAction::class);
    $group->get('', \PsyNexus\Actions\GetEventsAction::class);
    $group->get('/{slug}', \PsyNexus\Actions\GetEventAction::class);
});

// This route serves the HTML file
$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    $html = file_get_contents(__DIR__ . '/index.html');
    $response->getBody()->write($html);
    return $response;
});

$app->get('/upload-event.html', function (ServerRequestInterface $request, ResponseInterface $response) {
    $html = file_get_contents(__DIR__ . '/upload-event.html');
    $response->getBody()->write($html);
    return $response;
});

$app->get('/assets/js/events.js', function (ServerRequestInterface $request, ResponseInterface $response) {
    $js = file_get_contents(__DIR__ . '/assets/js/events.js');
    $response->getBody()->write($js);
    return $response->withHeader('Content-Type', 'application/javascript');
});

$app->get('/assets/css/style.css', function (ServerRequestInterface $request, ResponseInterface $response) {
    $css = file_get_contents(__DIR__ . '/assets/css/style.css');
    $response->getBody()->write($css);
    return $response->withHeader('Content-Type', 'text/css');
});

// This is a catch-all route for other assets.
$app->get('/{filename:.*}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    $filename = $args['filename'];
    $path = __DIR__ . '/' . $filename;

    if (file_exists($path)) {
        $mime = mime_content_type($path);
        $response->getBody()->write(file_get_contents($path));
        return $response->withHeader('Content-Type', $mime);
    }

    throw new HttpNotFoundException($request, "File not found: " . $filename);
});

// Run app
$app->run();

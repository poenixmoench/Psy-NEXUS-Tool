<?php
require __DIR__ . '/../vendor/autoload.php';
use Slim\Factory\AppFactory;
use PsyNexus\Middleware\RateLimitMiddleware;
use PsyNexus\Actions\CreateEventAction;
use PsyNexus\Actions\GetEventAction;
use PsyNexus\Database\Connection;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

// Erstelle den Container mit PHP-DI
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    'db' => function (ContainerInterface $container) {
        return Connection::getInstance();
    },
    CreateEventAction::class => function (ContainerInterface $container) {
        return new CreateEventAction($container->get('db'), new \PsyNexus\Utilities\Slugger());
    },
    GetEventAction::class => function (ContainerInterface $container) {
        return new GetEventAction($container->get('db'));
    }
]);
$container = $containerBuilder->build();

// Setze den Container für die App
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->add(new RateLimitMiddleware($container->get('db')));
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->post('/events', CreateEventAction::class);
$app->get('/events/{slug}', GetEventAction::class);

$app->run();

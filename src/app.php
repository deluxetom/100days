<?php

use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Predis\Silex\ClientsServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;

$app = new Application();
$app->register(new DoctrineServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new SecurityServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new RememberMeServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new ClientsServiceProvider());
$app->register(new SwiftmailerServiceProvider());

$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    // globals
    $twig->addGlobal('sitename', '100 Day Challenge');

    return $twig;
}));

$app['repository.user'] = $app->share(function ($app) {
    return new Days\Repository\User($app['dbs']['100days'], $app['dbs']['100days']);
});
$app['repository.counter'] = $app->share(function ($app) {
    return new Days\Repository\Counter($app['dbs']['100days'], $app['dbs']['100days']);
});
$app['repository.series'] = $app->share(function ($app) {
    return new Days\Repository\Series($app['dbs']['100days'], $app['dbs']['100days']);
});
$app['repository.comment'] = $app->share(function ($app) {
    return new Days\Repository\Comment($app['dbs']['100days'], $app['dbs']['100days']);
});

return $app;

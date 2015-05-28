<?php

use Silex\Provider\MonologServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;

// doctrine
$app['dbs.options'] = array (
    '100days' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => '100days',
        'user'      => '100days',
        'password'  => '100days',
        'charset'   => 'utf8',
    )
);

// security
$app['security.firewalls'] = array(
    'profiler' => array('pattern' => '^/(_(profiler|wdt)|css|images|js)/'),
    'login' => array('pattern' => '^/login/$'),
    'default' => array(
        'pattern' => '^.*$',
        'remember_me' => array(
            'key'                => '100daychallenge',
            'always_remember_me' => true,
        ),
        'form' => array(
            'login_path' => '/login/',
            'check_path' => '/login_check',
        ),
        'logout' => array('logout_path' => '/logout'),
        'users' => $app->share(function () use ($app) {
            return new Sctr\Encoder\Security\UserProvider($app['dbs']['videosz']);
        }),
    ),
);
$app['security.encoder.digest'] = $app->share(function ($app) {
    return new MessageDigestPasswordEncoder('sha1', false, 1);
});

// enable the debug mode
$app['debug'] = true;

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../var/logs/silex_dev.log',
));

$app->register(new WebProfilerServiceProvider(), array(
    'profiler.cache_dir' => __DIR__.'/../var/cache/profiler',
));

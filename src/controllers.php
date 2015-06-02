<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app->before(function () use ($app) {
    if ($token = $app['security']->getToken()) {
        $app['session']->start();
        if (!$app['session']->get('name')) {
            // get account's info > name, timezone etc (from an account repo)
            $app['session']->set('userId', $token->getUser()->getId());
            $app['session']->set('name', $token->getUser()->getName());
            $app['session']->set('username', $token->getUser()->getUsername());
        }
    }
});

$app->mount('/', new Days\Controller\IndexController);
$app->mount('/login', new Days\Controller\LoginController);
$app->mount('/user', new Days\Controller\UserController);
$app->mount('/counter', new Days\Controller\CounterController);
$app->mount('/series', new Days\Controller\SeriesController);
$app->mount('/leaderboard', new Days\Controller\LeaderboardController);

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    $templates = array(
        'Error/'.$code.'.html',
        'Error/'.substr($code, 0, 2).'x.html',
        'Error/'.substr($code, 0, 1).'xx.html',
        'Error/default.html',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});

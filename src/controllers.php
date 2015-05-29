<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app->before(function () use ($app) {
    if ($token = $app['security']->getToken()) {
        $app['session']->start();
        if (!$app['session']->get('name') || !$app['session']->get('timezone')) {
            // get account's info > name, timezone etc (from an account repo)
            $app['session']->set('userId', $token->getUser()->getId());
            $app['session']->set('name', $token->getUser()->getName());
            $app['session']->set('username', $token->getUser()->getUsername());
        }
    }
});

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('homepage');

$app->mount('/login', new Days\Controller\LoginController);
$app->mount('/user', new Days\Controller\UserController);
$app->mount('/counter', new Days\Controller\CounterController);

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

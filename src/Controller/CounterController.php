<?php
namespace Days\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;


class CounterController implements ControllerProviderInterface
{
    public function addAction(Request $request, Application $app, $type, $nb)
    {
        $counter = $app['repository.counter']->findOneByConditions(['userId' => $app['session']->get('userId'), 'type' => $type]);
        if (isset($counter['userId'])) {
            $app['repository.counter']->increment('nb', $nb, ['userId' => $app['session']->get('userId'), 'type' => $type]);
        } else {
            $app['repository.counter']->insert(['userId' => $app['session']->get('userId'), 'type' => $type, 'nb' => $nb]);
        }
        return 1;
    }

    public function resetAction(Request $request, Application $app)
    {
        $app['repository.counter']->deleteByConditions(['userId' => $app['session']->get('userId')]);
        return 1;
    }

    public function connect(Application $app)
    {
        $ct = $app['controllers_factory'];

        $ct->match("/add/{type}/{nb}", 'Days\Controller\CounterController::addAction')
            ->bind("counter-add");

        $ct->match("/reset", 'Days\Controller\CounterController::resetAction')
            ->bind("counter-reset");

        return $ct;
    }
}
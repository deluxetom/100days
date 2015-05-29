<?php
namespace Days\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;


class CounterController implements ControllerProviderInterface
{
    public function addAction(Request $request, Application $app, $type, $nb, $date)
    {
        $counter = $app['repository.counter']->findOneByConditions(['userId' => $app['session']->get('userId'), 'type' => $type, 'date' => $date]);
        if (isset($counter['userId'])) {
            $app['repository.counter']->increment('nb', $nb, ['userId' => $app['session']->get('userId'), 'type' => $type, 'date' => $date]);
        } else {
            $app['repository.counter']->insert(['userId' => $app['session']->get('userId'), 'type' => $type, 'date' => $date, 'nb' => $nb]);
        }
        return 1;
    }

    public function resetAction(Request $request, Application $app, $date)
    {
        $app['repository.counter']->deleteByConditions(['userId' => $app['session']->get('userId'), 'date' => $date]);
        return 1;
    }

    public function connect(Application $app)
    {
        $ct = $app['controllers_factory'];

        $ct->match("/add/{type}/{nb}/{date}", 'Days\Controller\CounterController::addAction')
            ->value('date', date("Y-m-d"))
            ->bind("counter-add");

        $ct->match("/reset/{date}", 'Days\Controller\CounterController::resetAction')
            ->value('date', date("Y-m-d"))
            ->bind("counter-reset");

        return $ct;
    }
}
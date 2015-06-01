<?php
namespace Days\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;


class SeriesController implements ControllerProviderInterface
{
    public function addAction(Request $request, Application $app, $nb, $date)
    {
        $counter = $app['repository.series']->findOneByConditions(['userId' => $app['session']->get('userId'), 'date' => $date]);
        if (isset($counter['userId'])) {
            $app['repository.series']->increment('nb', $nb, ['userId' => $app['session']->get('userId'), 'date' => $date]);
        } else {
            $app['repository.series']->insert(['userId' => $app['session']->get('userId'), 'date' => $date, 'nb' => $nb]);
        }
        return 1;
    }

    public function connect(Application $app)
    {
        $se = $app['controllers_factory'];

        $se->match("/add/{nb}/{date}", 'Days\Controller\SeriesController::addAction')
            ->value('date', date("Y-m-d"))
            ->bind("series-add");

        return $se;
    }
}
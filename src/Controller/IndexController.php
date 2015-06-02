<?php
namespace Days\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;


class IndexController implements ControllerProviderInterface
{
    public function indexAction(Request $request, Application $app)
    {
        $counters = [];
        $tmpCounters = $app['repository.counter']->findAll(['userId'=>$app['session']->get('userId')], [], ['date'=>'ASC']);
        foreach ($tmpCounters as $counter) {
            if (isset($counters[$counter['type']])) {
                $counters[$counter['type']] += $counter['nb'];
            } else {
                $counters[$counter['type']] = $counter['nb'];
            }
        }
        foreach (['pushups', 'squats', 'situps', 'jumpingjacks'] as $activity) {
            if (!isset($counters[$activity])) {
                $counters[$activity] = 0;
            }
        }

        $series = [];
        $tmpSeries = $app['repository.series']->findAll(['userId'=>$app['session']->get('userId')], [], ['date'=>'ASC']);
        for ($i=0;$i<$app['lifetime'];$i++) {
            $date = date("Y-m-d", strtotime($app['start_date'] . " + $i days"));
            $nb = 0;
            foreach ($tmpSeries as $se) {
                if ($se['date'] == $date) {
                    $nb = $se['nb'];
                    break;
                }
            }
            $series[] = ['date'=>$date, 'nb'=>$nb];
        }

        return $app['twig']->render('index.html.twig', array(
            'series'    => $series,
            'counters'  => $counters,
            'minDate'   => $app['start_date'],
            'maxDate'   => date("Y-m-d", strtotime($app['start_date'] . " + " . $app['lifetime'] . " days")),
        ));
    }

    public function connect(Application $app)
    {
        $se = $app['controllers_factory'];

        $se->match("/", 'Days\Controller\IndexController::indexAction')
            ->bind("homepage");

        return $se;
    }
}
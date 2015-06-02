<?php
namespace Days\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;


class SeriesController implements ControllerProviderInterface
{
    public function addAction(Request $request, Application $app, $nb, $date)
    {
        if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/i', $date) && $date>=$app['start_date'] && $date<=date("Y-m-d", strtotime($app['start_date'] . " + " . ($app['lifetime']-1) . " days"))) {
            $counter = $app['repository.series']->findOneByConditions(['userId' => $app['session']->get('userId'), 'date' => $date]);
            if (isset($counter['userId'])) {
                $app['repository.series']->increment('nb', $nb, ['userId' => $app['session']->get('userId'), 'date' => $date]);
            } else {
                $app['repository.series']->insert(['userId' => $app['session']->get('userId'), 'date' => $date, 'nb' => $nb]);
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
            $series[] = ['day'=>$date, 'sets'=>$nb];
        }
        return json_encode($series);
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
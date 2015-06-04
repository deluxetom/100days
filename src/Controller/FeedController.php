<?php
namespace Days\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;


class FeedController implements ControllerProviderInterface
{
    public function indexAction(Request $request, Application $app)
    {
        if ($forUserId = $request->get('fid')) {
            $forDate = $request->get('fdt');
            $comment = $request->get('comment');
            if ($comment != '') {
                $app['repository.comment']->insert([
                    'userId'    => $app['session']->get('userId'),
                    'comment'   => $comment,
                    'forUserId' => $forUserId,
                    'forDate'   => $forDate,
                ]);
            }
            return $app->redirect($app['url_generator']->generate('feed'));
        }
        $topY = $app['repository.user']->topFive(date("Y-m-d", strtotime('- 1 day')));
        $topD = $app['repository.user']->topFive(date("Y-m-d"));

        $series = $app['repository.series']->feed();
        for ($s=0;$s<count($series);$s++) {
            $comments = $app['repository.comment']->findAll(['forDate'=>$series[$s]['date'], 'forUserId'=>$series[$s]['userId']],[],['timestamp'=>'ASC']);
            for ($c=0;$c<count($comments);$c++) {
                $comments[$c]['user'] = $app['repository.user']->findByPk($comments[$c]['userId']);
            }
            $series[$s]['comments'] = $comments;
        }

        return $app['twig']->render('feed.html.twig', array(
            'topY' => $topY,
            'topD' => $topD,
            'series' => $series,
        ));
    }

    public function connect(Application $app)
    {
        $se = $app['controllers_factory'];

        $se->match("/", 'Days\Controller\FeedController::indexAction')
            ->bind("feed");

        return $se;
    }
}
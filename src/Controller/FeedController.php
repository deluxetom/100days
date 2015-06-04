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

        $lastDate = [];
        $comments = [];
        $tmpCom = $app['repository.comment']->findAll(['forDate'=>'0000-00-00'], [], ['timestamp'=>'DESC']);
        for ($c=0;$c<count($tmpCom);$c++) {
            if (isset($comments[date("Y-m-d", strtotime($tmpCom[$c]['timestamp']))][$tmpCom[$c]['forUserId']])) {
                $comments[date("Y-m-d", strtotime($tmpCom[$c]['timestamp']))][$tmpCom[$c]['forUserId']][] = $tmpCom[$c];
            } else {
                $comments[date("Y-m-d", strtotime($tmpCom[$c]['timestamp']))][$tmpCom[$c]['forUserId']] = [$tmpCom[$c]];
                $lastDate[date("Y-m-d", strtotime($tmpCom[$c]['timestamp']))][$tmpCom[$c]['forUserId']] = $tmpCom[$c]['timestamp'];
            }
        }
        foreach ($lastDate as $date => $tab) {
            foreach ($tab as $forUserId => $timestamp) {
                $forUser = $app['repository.user']->findByPk($forUserId);
                $serie = [
                    'userId' => $forUserId,
                    'username' => $forUser['username'],
                    'name' => $forUser['name'],
                    'fid' => $forUser['fid'],
                    'nb' => 0,
                    'date' => '0000-00-00',
                    'timestamp' => $timestamp,
                    'comments' => [],
                ];
                foreach ($comments[$date][$forUserId] as $comment) {
                    $user = $app['repository.user']->findByPk($comment['userId']);
                    $serie['comments'][] = ['user' => $user, 'comment' => $comment['comment']];
                }
                rsort($serie['comments']);

                if (count($series) > 0) {
                    $tmp = [];
                    for ($s = 0; $s < count($series); $s++) {
                        if ($s == 0 && $serie['timestamp'] > $series[$s]['timestamp']) {
                            $tmp[] = $serie;
                            $tmp[] = $series[$s];
                        } else if (!isset($series[$s + 1]) || ($serie['timestamp'] <= $series[$s]['timestamp'] && $serie['timestamp'] > $series[$s + 1]['timestamp'])) {
                            $tmp[] = $series[$s];
                            $tmp[] = $serie;
                        } else {
                            $tmp[] = $series[$s];
                        }
                    }
                    $series = $tmp;
                } else {
                    $series[] = $serie;
                }
            }
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
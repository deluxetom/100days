<?php
namespace Days\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;


class LeaderboardController implements ControllerProviderInterface
{
    public function indexAction(Application $app)
    {

        return $app['twig']->render('leaderboard.html.twig', array(

        ));
    }

    public function connect(Application $app)
    {
        $se = $app['controllers_factory'];

        $se->match("/", 'Days\Controller\LeaderboardController::indexAction')
            ->bind("leaderboard");

        return $se;
    }
}
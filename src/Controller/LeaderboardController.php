<?php
namespace Days\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;


class LeaderboardController implements ControllerProviderInterface
{
    public function indexAction(Application $app)
    {
        $users = $app['repository.user']->leaderBoard();
        return $app['twig']->render('leaderboard.html.twig', array(
            'users' => $users,
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
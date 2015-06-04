<?php
namespace Days\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;


class FeedController implements ControllerProviderInterface
{
    public function indexAction(Request $request, Application $app)
    {

        return $app['twig']->render('feed.html.twig', array(

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
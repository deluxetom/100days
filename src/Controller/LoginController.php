<?php
namespace Days\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LoginController
 * @package Days\Controller
 * @author thomas@sctr.net
 */
class LoginController implements ControllerProviderInterface
{
    /**
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function loginAction(Request $request, Application $app)
    {
        return $app['twig']->render('login.html.twig', array(
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));

    }

    public function connect(Application $app)
    {
        $login = $app['controllers_factory'];

        $login->match("/", 'Days\Controller\LoginController::loginAction')
            ->bind("login");

        return $login;
    }
}
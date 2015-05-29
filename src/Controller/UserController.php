<?php
namespace Days\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

/**
 * Class LoginController
 * @package Days\Controller
 * @author thomas@sctr.net
 */
class UserController implements ControllerProviderInterface
{
    /**
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function profileAction(Request $request, Application $app)
    {
        $error = [];
        $action = $request->get('action');
        if (isset($action) && $action == 'update') {
            $error    = [];
            $name     = $request->get('name');
            //$username = $request->get('username');
            //$email    = $request->get('email');
            $password = $request->get('password');

            if (!$name) {
                $error[] = "Invalid Name";
            }
            /*
            if (!$username) {
                $error[] = "Invalid Username";
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error[] = "Invalid Email";
            }
            */
            if (count($error) == 0) {
                $update = ['name' => $name];
                if ($password && $password != 'oldpassword100days') {
                    $salt = md5($password);
                    $encoder = new MessageDigestPasswordEncoder();
                    $crypted = $encoder->encodePassword($password, $salt);
                    $update['password'] = $crypted;
                    $update['salt'] = $salt;
                }

                if ($app['repository.user']->update($update, ['userId' => $app['session']->get('userId')]) != false) {
                    $error[] = "Profile Updated";
                } else {
                    $error[] = "Technical error please retry later";
                }
            }
        }
        $user = $app['repository.user']->findByPk($app['session']->get('userId'));

        return $app['twig']->render('User/profile.html.twig', [
            'user' => $user,
            'error' => $error,
        ]);
    }

    public function settingsAction(Request $request, Application $app)
    {
        return 'User Settings';
    }

    public function connect(Application $app)
    {
        $user = $app['controllers_factory'];

        $user->match("/profile", 'Days\Controller\UserController::profileAction')
            ->bind("user-profile");

        $user->match("/settings", 'Days\Controller\UserController::settingsAction')
            ->bind("user-settings");

        return $user;
    }
}
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
        $success = [];
        $action = $request->get('action');
        if (isset($action) && $action == 'update') {
            $error    = [];
            $name     = $request->get('name');
            //$username = $request->get('username');
            //$email    = $request->get('email');
            $password = $request->get('password');
            $fid = $request->get('fid');

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
                $update = ['name' => $name, 'fid' => $fid];
                if ($password && $password != 'oldpassword100days') {
                    $salt = md5($password);
                    $encoder = new MessageDigestPasswordEncoder();
                    $crypted = $encoder->encodePassword($password, $salt);
                    $update['password'] = $crypted;
                    $update['salt'] = $salt;
                }

                if ($app['repository.user']->update($update, ['userId' => $app['session']->get('userId')]) != false) {
                    $success[] = "Profile Updated";
                } else {
                    $error[] = "Technical error please retry later";
                }
            }
        }
        $user = $app['repository.user']->findByPk($app['session']->get('userId'));

        return $app['twig']->render('User/profile.html.twig', [
            'user'      => $user,
            'error'     => $error,
            'success'   => $success,
        ]);
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function publicProfileAction(Request $request, Application $app, $username)
    {
        $yesterday = date("Y-m-d", strtotime('- 1 day'));
        $today = date("Y-m-d");

        $user = $app['repository.user']->findOneBy('username', $username);
        $user['total'] = 0;
        $user['yesterday'] = 0;
        $user['today'] = 0;

        $series = [];
        $tmpSeries = $app['repository.series']->findAll(['userId'=>$user['userId']], [], ['date'=>'ASC']);
        for ($i=0;$i<$app['lifetime'];$i++) {
            $date = date("Y-m-d", strtotime($app['start_date'] . " + $i days"));
            $nb = 0;
            foreach ($tmpSeries as $se) {
                if ($se['date'] == $date) {
                    $nb = $se['nb'];
                    break;
                }
            }
            if ($date == $yesterday) {
                $user['yesterday'] = $nb;
            } else if ($date == $today) {
                $user['today'] = $nb;
            }
            $user['total'] += $nb;
            $series[] = ['date'=>$date, 'nb'=>$nb];
        }
        return $app['twig']->render('User/public-profile.html.twig', [
            'user'      => $user,
            'series'    => $series,
        ]);
    }

    public function connect(Application $app)
    {
        $user = $app['controllers_factory'];

        $user->match("/profile", 'Days\Controller\UserController::profileAction')
            ->bind("user-profile");

        $user->match("/profile/{username}", 'Days\Controller\UserController::publicProfileAction')
            ->bind("user-profile-public");

        return $user;
    }
}
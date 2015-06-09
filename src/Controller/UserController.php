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
            $fid = str_replace('http://www.facebook.com/', '', $request->get('fid'));

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
        if (!isset($user['userId'])) {
            $app->abort(404, "Page Not Found");
        }

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
        $comments = $app['repository.comment']->findAll(['forUserId'=>$user['userId'], 'forDate'=>'0000-00-00'], [], ['timestamp'=>'ASC']);
        for ($c=0;$c<count($comments);$c++) {
            $comments[$c]['user'] = $app['repository.user']->findByPk($comments[$c]['userId']);
        }
        return $app['twig']->render('User/public-profile.html.twig', [
            'user'      => $user,
            'series'    => $series,
            'comments'  => $comments,
        ]);
    }

    public function commentAction(Request $request, Application $app)
    {
        if ($forUserId = $request->get('fid')) {
            $forDate = $request->get('fdt');
            $username = $request->get('usn');
            $comment = strip_tags($request->get('comment'));
            if ($comment != '') {
                $app['repository.comment']->insert([
                    'userId'    => $app['session']->get('userId'),
                    'comment'   => $comment,
                    'forUserId' => $forUserId,
                    'forDate'   => $forDate,
                ]);
            }
            return $app->redirect($app['url_generator']->generate('user-profile-public', ['username' => $username]) . "#comments");
        }
        return $app->redirect($app['url_generator']->generate('leaderboard'));
    }

    public function connect(Application $app)
    {
        $user = $app['controllers_factory'];

        $user->match("/profile", 'Days\Controller\UserController::profileAction')
            ->bind("user-profile");

        $user->match("/profile/{username}", 'Days\Controller\UserController::publicProfileAction')
            ->bind("user-profile-public");

        $user->match("/comment", 'Days\Controller\UserController::commentAction')
            ->bind("user-comment");

        return $user;
    }
}
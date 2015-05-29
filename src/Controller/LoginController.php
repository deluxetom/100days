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
class LoginController implements ControllerProviderInterface
{
    /**
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function loginAction(Request $request, Application $app)
    {
        $action= $request->get('action');
        if (isset($action) && $action == 'register') {
            $error    = [];
            $action   = 'register';
            $name     = $request->get('name');
            $username = $request->get('username');
            $email    = $request->get('email');
            $password = $request->get('password');
            $confirmPassword = $request->get('confirm-password');
            if (!$name) {
                $error[] = "Invalid Name";
            }
            if (!$username) {
                $error[] = "Invalid Username";
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error[] = "Invalid Email";
            }
            if (!$password) {
                $error[] = "Invalid Password";
            } else if (!$confirmPassword || $password != $confirmPassword) {
                $error[] = "Confirm Password must be the same as Password";
            }
            if (count($error) == 0) {
                if ($app['repository.user']->emailExists($email)) {
                    $error[] = "This email is already registred. If you forgot your login information, please use the 'retrieve password' form.";
                } else {
                    $salt = md5($password);
                    $encoder = new MessageDigestPasswordEncoder();
                    $crypted = $encoder->encodePassword($password, $salt);

                    $user = $app['repository.user']->insert(
                        [
                            'name'      => $name,
                            'username'  => $username,
                            'password'  => $crypted,
                            'email'     => $email,
                            'enabled'   => 1,
                            'roles'     => 'ROLE_USER',
                            'salt'      => $salt,
                        ]
                    );
                    if ($user != false) {
                        $error[] = "Registration complete! You can now login.";
                        $name = '';
                        $email = '';
                        $action = 'login';
                    } else {
                        $error[] = "Technical error please retry later";
                    }
                }
            }
            return $app['twig']->render('login.html.twig', array(
                'error'         => $error,
                'action'        => $action,
                'name'          => $name,
                'username'      => $username,
                'last_username' => $username,
                'password'      => $password,
                'email'         => $email,
            ));
        } else {
            return $app['twig']->render('login.html.twig', array(
                'error'         => $app['security.last_error']($request),
                'last_username' => $app['session']->get('_security.last_username'),
                'action'        => 'login',
            ));
        }
    }

    public function connect(Application $app)
    {
        $login = $app['controllers_factory'];

        $login->match("/", 'Days\Controller\LoginController::loginAction')
            ->bind("login");

        return $login;
    }
}
<?php
namespace Days\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Days\Security\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\DBAL\Connection;

class UserProvider implements UserProviderInterface
{
    private $conn;
    private $redis;

    public function __construct(Connection $conn, $predisClient)
    {
        $this->conn = $conn;
        $this->redis = $predisClient;
    }

    public function loadUserByUsername($username)
    {
        if (!$user = unserialize($this->redis->get('user:'.$username))) {
            $stmt = $this->conn->executeQuery('SELECT * FROM user WHERE username = ?', array(strtolower($username)));
            if (!$user = $stmt->fetch()) {
                throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            }
            $this->redis->set('user:'.$username, serialize($user));
            $this->redis->expire('user:'.$username, 600);
        }

        if ($user['enabled'] == 0) {
            throw new UsernameNotFoundException('Account not validated');
        }
        $user_roles = explode(',', $user['roles']);
        $authorized_roles = array('ROLE_ADMIN', 'ROLE_USER');
        $roles = array_intersect($user_roles, $authorized_roles);

        if (count($roles) == 0) {
            throw new AccessDeniedException(sprintf('%s isn\'t allowed to access this area', $user['username']));
        }

        $userApp = new User(
            $user['username'],
            $user['password'],
            $user['salt'],
            $user['email'],
            $user['name'],
            $user['userId'],
            $roles,
            true,
            true,
            true,
            true
        );

        return $userApp;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}
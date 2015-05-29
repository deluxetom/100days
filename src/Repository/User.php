<?php
namespace Days\Repository;

class User extends Repository
{
    public function __construct($dbConnectorRead, $dbConnectorWrite)
    {
        parent::__construct($dbConnectorRead, $dbConnectorWrite, 'user', 'userId');
    }
    public function emailExists($email)
    {
        $member = $this->findOneBy('email', $email);
        return isset($member['userId']);
    }

    public function usernameExists($username)
    {
        $member = $this->findOneBy('username', $username);
        return isset($member['userId']);
    }
}
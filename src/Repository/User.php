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

    public function leaderBoard()
    {
        return $this->dbRead->createQueryBuilder()
            ->select('u.username, u.name, u.fid, SUM(s.nb) AS nbSeries')
            ->from('user', 'u')
            ->from('series', 's')
            ->andWhere('u.userId=s.userId')
            ->groupBy('u.userId')
            ->orderBy('nbSeries', 'DESC')
            ->execute()
            ->fetchAll();
    }
}
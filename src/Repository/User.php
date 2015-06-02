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
        $query = "SELECT u.username, u.name, u.fid, SUM(s.nb) AS nbSeries
                  FROM user AS u
                  LEFT JOIN series AS s ON u.userId=s.userId
                  GROUP BY u.userId
                  ORDER BY nbSeries DESC, u.userId ASC";
        $statement = $this->dbRead->prepare($query);
        $statement->execute();
        return $statement->fetchAll();
    }
}
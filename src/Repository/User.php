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
        $query = "SELECT u.userId, u.username, u.name, u.fid, SUM(s.nb) AS nbSeries
                  FROM user AS u
                  LEFT JOIN series AS s ON u.userId=s.userId
                  GROUP BY u.userId
                  ORDER BY nbSeries DESC, u.userId ASC";
        $statement = $this->dbRead->prepare($query);
        $statement->execute();
        $users = $statement->fetchAll();
        $yesterday = date("Y-m-d", strtotime('- 1 day'));
        $today = date("Y-m-d");
        for($i=0;$i<count($users);$i++) {
            $query = "SELECT s.date, s.nb
                  FROM series AS s
                  WHERE s.userId='".$users[$i]['userId']."' AND (s.date='" . $yesterday . "' OR  s.date='" . $today . "')";
            $statement = $this->dbRead->prepare($query);
            $statement->execute();
            $rows = $statement->fetchAll();
            foreach($rows as $row) {
                if ($row['date'] == $yesterday) {
                    $users[$i]['yesterday'] = $row['nb'];
                } else {
                    $users[$i]['today'] = $row['nb'];
                }
            }
        }
        return $users;
    }

    public function topFive($date)
    {
        $query = "SELECT u.userId, u.username, u.name, u.fid, s.nb, s.date
                  FROM user AS u
                  LEFT JOIN series AS s ON u.userId=s.userId AND s.date='$date'
                  ORDER BY s.nb DESC, u.userId ASC LIMIT 5";
        $statement = $this->dbRead->prepare($query);
        $statement->execute();
        return $statement->fetchAll();
    }
}
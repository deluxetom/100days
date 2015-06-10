<?php
namespace Days\Repository;

class Comment extends Repository
{
    public function __construct($dbConnectorRead, $dbConnectorWrite)
    {
        parent::__construct($dbConnectorRead, $dbConnectorWrite, 'comment', 'userId');
    }

    public function feed($nbDays = 4)
    {
        $query = "SELECT u.userId, u.username, u.name, u.fid, c.forDate, c.forUserId, c.comment, c.timestamp
                  FROM user AS u, comment AS c
                  WHERE u.userId=c.userId AND c.forDate='0000-00-00' AND c.timestamp>='" . date("Y-m-d", strtotime("-$nbDays days")) . "'
                  ORDER BY c.timestamp DESC";
        $statement = $this->dbRead->prepare($query);
        $statement->execute();
        return $statement->fetchAll();
    }
}
<?php
namespace Days\Repository;

class Series extends Repository
{
    public function __construct($dbConnectorRead, $dbConnectorWrite)
    {
        parent::__construct($dbConnectorRead, $dbConnectorWrite, 'series', 'userId');
    }

    public function feed($nbDays = 4)
    {
        $query = "SELECT u.userId, u.username, u.name, u.fid, s.nb, s.date, s.timestamp
                  FROM user AS u, series AS s
                  WHERE u.userId=s.userId AND s.date>='" . date("Y-m-d", strtotime("-$nbDays days")) . "'
                  ORDER BY s.date DESC, s.timestamp DESC";
        $statement = $this->dbRead->prepare($query);
        $statement->execute();
        return $statement->fetchAll();
    }
}
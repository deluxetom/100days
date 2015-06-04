<?php
namespace Days\Repository;

class Series extends Repository
{
    public function __construct($dbConnectorRead, $dbConnectorWrite)
    {
        parent::__construct($dbConnectorRead, $dbConnectorWrite, 'series', 'userId');
    }

    public function feed($nbDays = 3)
    {
        $query = "SELECT u.userId, u.username, u.name, u.fid, s.nb, s.date
                  FROM user AS u, series AS s
                  WHERE u.userId=s.userId AND s.date BETWEEN '" . date("Y-m-d", strtotime("- $nbDays days")) . "' AND '" . date("Y-m-d") . "'
                  ORDER BY s.date DESC, s.timestamp DESC";

        $statement = $this->dbRead->prepare($query);
        $statement->execute();
        return $statement->fetchAll();
    }
}
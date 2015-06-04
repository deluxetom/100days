<?php
namespace Days\Repository;

class Comment extends Repository
{
    public function __construct($dbConnectorRead, $dbConnectorWrite)
    {
        parent::__construct($dbConnectorRead, $dbConnectorWrite, 'comment', 'userId');
    }


}
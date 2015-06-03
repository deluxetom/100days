<?php
namespace Days\Repository;

class Series extends Repository
{
    public function __construct($dbConnectorRead, $dbConnectorWrite)
    {
        parent::__construct($dbConnectorRead, $dbConnectorWrite, 'series', 'userId');
    }
}
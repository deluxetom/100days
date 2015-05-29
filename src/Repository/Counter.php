<?php
namespace Days\Repository;

class Counter extends Repository
{
    public function __construct($dbConnectorRead, $dbConnectorWrite)
    {
        parent::__construct($dbConnectorRead, $dbConnectorWrite, 'counter', 'userId');
    }


}
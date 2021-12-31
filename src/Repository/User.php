<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class User
{
    public function __construct(private Connection $connection)
    {
    }
}
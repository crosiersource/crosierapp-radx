<?php

namespace App\Business;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

class ConnectionUtils
{

    public Connection $logsConnection;

    public ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
        $this->logsConnection = $this->managerRegistry->getManager('logs')->getConnection();
    }

}
<?php

namespace App\Listener;

use Doctrine\DBAL\Event\ConnectionEventArgs;

/**
 * Doctrine connection listener
 */
class PdoListener
{
    public function postConnect(ConnectionEventArgs $args)
    {
        $args->getConnection()
            ->exec("SET time_zone = 'America/New_York'");
    }
}
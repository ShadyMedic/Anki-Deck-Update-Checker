<?php

namespace AnkiDeckUpdateChecker\Models;

use DateTime;

class StatisticsManager
{
    public function logUse(int $packageId, string $id) : bool
    {
        $bytes = file_put_contents('stats/'.date('Y-m-d').'_'.$packageId, $id.PHP_EOL, FILE_APPEND);
        return ($bytes !== false);
    }

    public function loadStats($packageId)
    {
        //TODO
    }

    public function aggregateStats(string $since = '2023-01-01') //Since before this webapp was in development
    {
        //TODO
    }
}


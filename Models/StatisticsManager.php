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
        $until = date('Y-m-d', strtotime("-1 days"));
        $db = Db::connect();

        do {
            echo 'Processing statistics from '.$until.':<br>';
            $files = glob('stats/'.$until.'*');
            print_r($files);
            foreach ($files as $file) {
                $packageId = explode('_', $file)[1];
                $entries = file($file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
                $entries = array_unique($entries);
                $count = count($entries);
                $statement = $db->prepare('INSERT INTO stat (date,package_id,users) VALUES (?,?,?)');
                $result = $statement->execute([$until, $packageId, $count]);
                if ($result) {
                    unlink($file);
                }
            }
            $until = date('Y-m-d', strtotime($until) - 86400); //Move to the previous day
        } while (!empty($files) && $until !== $since);
    }
}


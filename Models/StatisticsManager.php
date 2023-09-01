<?php

namespace AnkiDeckUpdateChecker\Models;

use DateTime;
use PDO;

class StatisticsManager
{
    public function logUse(int $packageId, string $id) : bool
    {
        $bytes = file_put_contents('stats/'.date('Y-m-d').'_'.$packageId, $id.PHP_EOL, FILE_APPEND);
        return ($bytes !== false);
    }

    public function visualizeStats(int $packageId) : string
    {
        $data = $this->loadStats($packageId, 30, true);
        ob_start();

        # <code generated by Bing AI>
        $maxValue = max($data);
        if ($maxValue <= 10) {
            $rounding = 1;
        } elseif ($maxValue <= 50) {
            $rounding = 5;
        } else {
            $rounding = pow(10, floor(log10($maxValue)) - 1);
            if ($rounding < 10) {
                $rounding = 10;
            }
        }
        $yMax = ceil($maxValue / $rounding) * $rounding;
        $yStep = $yMax / 10;

        $width = 800;
        $height = 600;
        $padding = 64;
        $xStep = ($width - 2 * $padding) / (count($data) - 1);
        $yScale = ($height - 2 * $padding) / $yMax;

        echo '<svg width="' . $width . '" height="' . $height . '">';
        echo '<rect width="100%" height="100%" fill="white" />';

        for ($i = 0; $i <= $yMax; $i += $yStep) {
            $y = $height - $padding - $i * $yScale - 24;
            echo '<line x1="' . $padding . '" y1="' . $y . '" x2="' . ($width - $padding) . '" y2="' . $y . '" stroke="#eee" />';
            echo '<text x="' . ($padding - 8) . '" y="' . ($y + 4) . '" font-family="Arial" font-size="14" text-anchor="end">' . round($i) . '</text>';
        }

        echo '<text x="' . ($width / 2) . '" y="' . ($height) . '" font-family="Arial" font-size="14" text-anchor="middle">Date</text>';
        echo '<text x="' . ($padding / 2) . '" y="' . (($height - 24) / 2) . '" font-family="Arial" font-size="14" text-anchor="middle" transform="rotate(-90,' . ($padding / 2) . ',' . (($height - 24) / 2) . ')">Users</text>';

        $points = [];
        $i = 0;
        foreach ($data as $date => $value) {
            $x = $padding + $i * $xStep;
            $y = $height - $padding - $value * $yScale - 24;
            echo '<circle cx="' . $x . '" cy="' . $y . '" r="4" fill="black" />';
            if (count($points)) {
                echo '<line x1="' . end($points)[0] . '" y1="' . end($points)[1] .
                    '" x2="' .$x. '" y2="' .$y. '" stroke="black" />';
            }
            $date = date_create_from_format('Y-m-d', $date)->format('M / d');
            echo '<text x="' .$x. '" y="' .$height-45 . '" font-family="Arial" font-size="14" text-anchor="middle" transform="rotate(-90,' .$x. ',' .$height-45 . ')">' .$date. '</text>';
            array_push($points, [$x, $y]);
            ++$i;
        }
        echo '</svg>';
        # </code generated by Bing AI>

        $svg = ob_get_contents();
        ob_end_clean();

        return $svg;
    }

    public function loadStats(int $packageId, int $limit, bool $zerofill) : array
    {
        $db = Db::connect();
        $statement = $db->prepare(
            'SELECT date,users FROM stat WHERE package_id = ? AND date >= DATE(NOW()) - INTERVAL ? DAY ORDER BY date LIMIT ?;');
        $statement->execute([$packageId, $limit + 1, $limit]);
        $stats = $statement->fetchAll(PDO::FETCH_KEY_PAIR);

        if ($zerofill) {
            $current = new DateTime();
            $current->modify('-'. $limit+1 .' days');
        } else {
            $current = date_create_from_format('Y-m-d', array_key_first($stats));
        }
        $yesterday = new DateTime();
        $result = [];
        for ($yesterday->modify('-1 day'); $current <= $yesterday; $current->modify('+1 day')) {
            $date = $current->format('Y-m-d');
            if (array_key_exists($date, $stats)) {
                $result[$date] = $stats[$date];
            } else {
                $result[$date] = 0;
            }
        }
        return $result;
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


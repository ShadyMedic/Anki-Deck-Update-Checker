<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\StatisticsManager;
use AnkiDeckUpdateChecker\Models\UserException;

class AggregateStats extends Controller
{

    private const ACCESS_KEY = 'secret';

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        //Check authorization
        $key = $_POST['key'] ?? null;
        if (is_null($key)) {
            throw new UserException('No access key was provided.', 401010);
        }

        if ($key !== self::ACCESS_KEY) {
            throw new UserException('The access key is not valid.', 403006);
        }

        //Inspired by https://stackoverflow.com/a/15273676/14011077
        ignore_user_abort(true);
        set_time_limit(0);
        ob_start();
        echo 'Statistic-aggregating job has been started on the server.';
        header('Connection: close');
        header('Content-Length: '.ob_get_length());
        header("HTTP/1.1 202 Accepted");
        ob_end_flush();
        @ob_flush();
        flush();
        fastcgi_finish_request();

        $manager = new StatisticsManager();
        $manager->aggregateStats();

        self::$views = []; //Don't output any HTML
        exit;
    }
}
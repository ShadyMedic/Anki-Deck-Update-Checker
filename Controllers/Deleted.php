<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\UserException;

class Deleted extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        self::$data['layout']['page_id'] = 'deleted';
        self::$data['layout']['title'] = 'Package Successfully Deleted';

        $packageId = array_shift($args);
        self::$data['deleted']['packageId'] = $packageId;

        self::$views[] = 'deleted';

        return 200;
    }
}


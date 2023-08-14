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
        if (is_null($packageId)) {
            throw new UserException('No package ID was specified', 400006);
        }
        self::$data['deleted']['packageId'] = $packageId;

        self::$views[] = 'deleted';

        return 200;
    }
}


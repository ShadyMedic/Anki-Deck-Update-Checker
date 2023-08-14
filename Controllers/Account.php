<?php

namespace AnkiDeckUpdateChecker\Controllers;

class Account extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        self::$data['layout']['page_id'] = 'account';
        self::$data['layout']['title'] = 'Account Setup';

        self::$views[] = 'account';
        self::$cssFiles[] = 'upload';
        self::$cssFiles[] = 'create';
        self::$jsFiles[] = 'account';

        return 200;
    }
}


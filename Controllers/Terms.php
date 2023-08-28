<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Controllers\Controller;

class Terms extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        self::$data['layout']['page_id'] = 'terms';
        self::$data['layout']['title'] = 'Terms of Service';

        self::$views[] = 'terms';

        return 200;
    }
}


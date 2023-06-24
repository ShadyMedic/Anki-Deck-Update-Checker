<?php

namespace AnkiDeckUpdateChecker\Controllers;

class Index extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        self::$data['layout']['page_id'] = 'index';
        self::$data['layout']['title'] = 'Anki Deck Update Checker';

        self::$views[] = 'index';

        return 200;
    }
}


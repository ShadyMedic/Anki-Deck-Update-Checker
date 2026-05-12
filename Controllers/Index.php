<?php

namespace AnkiDeckUpdateChecker\Controllers;

/**
 * @see Controller
 */
class Index extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        self::$data['layout']['page_id'] = 'index';
        self::$data['layout']['title'] = 'Medické Anki s autoupdaterem';

        self::$views[] = 'index';

        return 200;
    }
}


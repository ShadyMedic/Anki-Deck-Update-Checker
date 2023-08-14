<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\PackageManager;

/**
 * @see Controller
 */
class Browse extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        self::$data['layout']['page_id'] = 'browse';
        self::$data['layout']['title'] = 'Public Anki Decks';

        $manager = new PackageManager();
        self::$data['browse']['packages'] = $manager->getPublicPackages();

        self::$views[] = 'browse';
        self::$cssFiles[] = 'browse';

        return 200;
    }
}


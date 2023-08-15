<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\PackageManager;

class Manage extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        self::$data['layout']['page_id'] = 'manage';
        self::$data['layout']['title'] = 'My Published Anki Decks';

        self::$data['manage']['packages'] = [];
        self::$data['manage']['latestKey'] = '';

        $key = $_POST['key'] ?? null;
        if (!is_null($key)) {
            $manager = new PackageManager();
            self::$data['manage']['Packages'] = $manager->getOwnedPackages($key);
            self::$data['manage']['latestKey'] = $key;
        }

        self::$views[] = 'manage';
        self::$cssFiles[] = 'manage';
        self::$jsFiles[] = 'manage';

        return 200;
    }
}


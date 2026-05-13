<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\Package;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\StatisticsManager;
use AnkiDeckUpdateChecker\Models\UserException;

class Stats extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        $packageId = array_shift($args) ?? null;
        $key = $_POST['key'] ?? null;

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('Balíček s tímto ID nebyl nalezen.', 404009);
        }

        if ($package->isDeleted()) {
            throw new UserException('Tento balíček byl smazán.', 410008);
        }

        //Do authentication
        if (is_null($key)) {
            throw new UserException('Editační klíč nebyl poskytnut.', 401008);
        }
        $tools = new PackageManager();
        if (!$tools->checkWriteAccess($packageId, $key)) {
            throw new UserException('Editační klíč pro tento balíček není platný.', 403004);
        }

        if ($package->getVersion() === 0) {
            throw new UserException('Tento balíček zatím nebyl nahrán.', 406003);
        }

        self::$data['layout']['page_id'] = 'stats';
        self::$data['layout']['title'] = 'Statistiky pro balíček '.$package->getName();

        $manager = new StatisticsManager();
        self::$data['stats']['packageId'] = $packageId;
        self::$data['stats']['key'] = $key;
        self::$data['stats']['graphSvg'] = $manager->visualizeStats($packageId);

        self::$views[] = 'stats';

        return 200;
    }
}

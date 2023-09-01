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
        $key = $_POST['key'];

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('No package with this ID was found.', 404009);
        }

        if ($package->isDeleted()) {
            throw new UserException('This package was deleted.', 410008);
        }

        //Do authentication
        if (is_null($key)) {
            throw new UserException('No editing key was provided.', 401008);
        }
        $tools = new PackageManager();
        if (!$tools->checkWriteAccess($packageId, $key)) {
            throw new UserException('The editing key for this package is not valid.', 403004);
        }

        if ($package->getVersion() === 0) {
            throw new UserException('This package hasn\'t been uploaded yet.', 406003);
        }

        self::$data['layout']['page_id'] = 'stats';
        self::$data['layout']['title'] = 'Statistics for '.$package->getName();

        $manager = new StatisticsManager();
        self::$data['stats']['packageId'] = $packageId;
        self::$data['stats']['graphSvg'] = $manager->visualizeStats($packageId);

        self::$views[] = 'stats';

        return 200;
    }
}
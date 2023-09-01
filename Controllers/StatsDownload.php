<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\Package;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\StatisticsManager;
use AnkiDeckUpdateChecker\Models\UserException;

class StatsDownload extends Controller
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
            throw new UserException('No package with this ID was found.', 404010);
        }

        if ($package->isDeleted()) {
            throw new UserException('This package was deleted.', 410009);
        }


        //Do authentication
        if (is_null($key)) {
            throw new UserException('No editing key was provided.', 401009);
        }
        $tools = new PackageManager();
        if (!$tools->checkWriteAccess($packageId, $key)) {
            throw new UserException('The editing key for this package is not valid.', 403005);
        }


        if ($package->getVersion() === 0) {
            throw new UserException('This package hasn\'t been uploaded yet.', 406004);
        }

        $manager = new StatisticsManager();
        $stats = $manager->loadStats($packageId, 36525, false); //"All" stat, aka stats from the last 100 years, doubt anyone will require more

        self::$views = []; //Don't output any HTML
        self::$views[] = 'file-outputs/stats-download';
        self::$data['statsdownload']['data'] = $stats;

        return 200;
    }
}


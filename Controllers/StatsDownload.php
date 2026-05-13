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
        $key = $_POST['key'] ?? null;

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('Balíček s tímto ID nebyl nalezen.', 404010);
        }

        if ($package->isDeleted()) {
            throw new UserException('Tento balíček byl smazán.', 410009);
        }


        //Do authentication
        if (is_null($key)) {
            throw new UserException('Nebyl poskytnut žádný editační klíč.', 401009);
        }
        $tools = new PackageManager();
        if (!$tools->checkWriteAccess($packageId, $key)) {
            throw new UserException('Editační klíč pro tento balíček není platný.', 403005);
        }


        if ($package->getVersion() === 0) {
            throw new UserException('Tento balíček nebyl zatím nahrán.', 406004);
        }

        $manager = new StatisticsManager();
        $stats = $manager->loadStats($packageId, 36525, false); //"All" stat, aka stats from the last 100 years, doubt anyone will require more

        self::$views = []; //Don't output any HTML
        self::$views[] = 'file-outputs/stats-download';
        self::$data['statsdownload']['data'] = $stats;

        return 200;
    }
}


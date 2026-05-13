<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\Package;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\UserException;

class Update extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        $packageId = array_shift($args);
        $currentVersion = array_shift($args);
        $accessCode = $_GET['key'] ?? null; //Access code (private packages only)

        if ($packageId === 'legacy') {
            $packageId = $_GET['id'] ?? null;
            $currentVersion = $_GET['current'] ?? null;

            if (is_null($packageId)) {
                throw new UserException('Nebylo specifikováno ID balíčku.', 400003);
            }
            if (is_null($currentVersion)) {
                throw new UserException('Nebyla specifikována aktuální verze balíčku.', 400004);
            }
        }

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('Balíček s tímto ID nebyl nalezen.', 404008);
        }

        if ($package->isDeleted()) {
            throw new UserException('Tento balíček byl smazán.', 410007);
        }

        //Do authentication
        $authenticator = new PackageManager();
        if (!$authenticator->checkReadAccess($packageId, $accessCode)) {
            throw new UserException('Tento balíček je soukromý a přístupový klíč buďto chybí, nebo je nesprávný.', 401007);
        }

        if ($currentVersion < $package->getVersion()) {
            header('Location: '.$package->getDownloadLink());
            exit();
        }

        self::$data['layout']['page_id'] = 'update';
        self::$data['layout']['title'] = 'Tvůj Anki balíček je aktuální.';
        //TODO this will need to be redone when remote file hosting is implemented
        self::$data['uptodate']['DownloadLink'] = $package->getDownloadLink();
        self::$views[] = 'up-to-date';

        return 200;
    }
}


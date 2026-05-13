<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\Package;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\UserException;
use AnkiDeckUpdateChecker\Models\StatisticsManager;

class CheckUpdate extends Controller
{
    const PEPPER = 'secret'; //CHANGE THIS ON PRODUCTION TO SOMETHING LONG AND RANDOMLY GENERATED

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
                throw new UserException('Nebylo specifikováno žádné ID balíčku.', 400001);
            }
            if (is_null($currentVersion)) {
                throw new UserException('Nebylo specifikováno žádné číslo verze balíčku.', 400002);
            }
        }

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('Balíček s tímto ID nebyl nalezen.', 404007);
        }

        if ($package->isDeleted()) {
            throw new UserException('Tento balíček byl smazán.', 410006);
        }

        //Do authentication
        $authenticator = new PackageManager();
        if (!$authenticator->checkReadAccess($packageId, $accessCode)) {
            throw new UserException('Tento balíček je soukromý a přístupový klíč chybí nebo je nesprávný.', 401006);
        }

        self::$views = []; //Don't output any HTML

        //Log usage
        $logger = new StatisticsManager();
        $id = self::PEPPER; //Add secret pepper
        $id .= $_SERVER['REMOTE_ADDR']; //IP address
        $id .= date('dDjlNSwzWFmMntLoXxYy'); //Add some day-specific salt
        $id = sha1($id);
        $logger->logUse($packageId, $id);

        if ($currentVersion >= $package->getVersion()) {
            self::$views[] = 'file-outputs/up-to-date';
        } else {
            self::$views[] = 'file-outputs/outdated';
        }

        return 200;
    }
}


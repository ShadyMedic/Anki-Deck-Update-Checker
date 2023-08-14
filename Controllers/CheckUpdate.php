<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\Package;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\UserException;

class CheckUpdate extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        $packageId = array_shift($args);
        $currentVersion = array_shift($args);
        $accessCode = $_GET['key'] ?? null; //Access code (private packages only)

        if (is_null($packageId)) {
            throw new UserException('No package ID was specified', 400007);
        }

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('No package with this ID was found.', 404007);
        }

        if ($package->isDeleted()) {
            throw new UserException('This package was deleted.', 410006);
        }

        //Do authentication
        $authenticator = new PackageManager();
        if (!$authenticator->checkReadAccess($packageId, $accessCode)) {
            throw new UserException('This package is private and the access key is either wrong or missing.', 401006);
        }

        self::$views = []; //Don't output any HTML

        if ($currentVersion >= $package->getVersion()) {
            self::$views[] = 'image-outputs/up-to-date';
        } else {
            self::$views[] = 'image-outputs/outdated';
        }

        return 200;
    }
}


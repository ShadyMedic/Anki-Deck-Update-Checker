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

        if (is_null($packageId)) {
            throw new UserException('No package ID was specified', 400008);
        }

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('No package with this ID was found.', 404008);
        }

        if ($package->isDeleted()) {
            throw new UserException('This package was deleted.', 410007);
        }

        //Do authentication
        $authenticator = new PackageManager();
        if (!$authenticator->checkReadAccess($packageId, $accessCode)) {
            throw new UserException('This package is private and the access key is either wrong or missing.', 401007);
        }

        if ($currentVersion < $package->getVersion()) {
            header('Location: '.$package->getDownloadLink());
            exit();
        }

        self::$data['layout']['page_id'] = 'update';
        self::$data['layout']['title'] = 'Your Anki Deck Is up-to-date';
        self::$data['uptodate']['downloadLink'] =
        'http://'.$_SERVER['SERVER_NAME'].$package->getDownloadLink().(empty($accessCode) ? '' : '?key='.$accessCode);
        self::$views[] = 'up-to-date';

        return 200;
    }
}


<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\Package;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\UserException;

class DeckDownload extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        $packageId = array_shift($args) ?? null;
        $accessKey = $_GET['key'] ?? null; //Filled in only for protected decks

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('No package with this ID was found.', 404002);
        }

        if ($package->isDeleted()) {
            throw new UserException('This package was deleted.', 410002);
        }

        $authenticator = new PackageManager();
        if (!$authenticator->checkReadAccess($packageId, $accessKey)) {
            throw new UserException('This package is private and the access key is either wrong or missing.', 401002);
        }

        if ($package->getVersion() === 0) {
            throw new UserException('This package hasn\'t been uploaded yet.', 406002);
        }

        if (!file_exists('decks/'.$packageId.'.apkg')) {
            throw new UserException('This package file doesn\'t exist on our servers.', 404003);
        }

        self::$views = []; //Don't output any HTML
        self::$views[] = 'file-outputs/deck-download';
        self::$data['deckdownload']['Package'] = $package;

        return 200;
    }
}


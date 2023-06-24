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

        if (is_null($packageId)) {
            throw new UserException('No package ID was specified.', 400002);
        }

        $authenticator = new PackageManager();
        $authenticator->checkReadAccess($packageId, $accessKey);

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('No package with this ID was found.', 404002);
        }

        if ($package->getVersion() === 0) {
            throw new UserException('This package hasn\'t been uploaded yet.', 406002);
        }

        if (!file_exists('decks/'.$packageId.'.apkg')) {
            throw new UserException('This package file doesn\'t exist on our servers.', 404003);
        }

        self::$views = []; //Don't generate any webpage

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $package->getName() . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize('decks/'.$packageId.'.apkg'));
        readfile('decks/'.$packageId.'.apkg');

        return 200; //TODO: replaced exit(), will this break something?
    }
}


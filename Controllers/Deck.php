<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\Package;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\UserException;

class Deck extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        $packageId = array_shift($args) ?? null;
        
        if (!is_numeric($packageId) && $packageId === 'legacy') {
            $packageId = $_GET['id'] ?? null;
            
            if (is_null($packageId)) {
                throw new UserException('No package ID was specified', 400005);
            }
        }
        
        if (!empty($args)) {
            self::$data['deck']['uploadAction'] = array_shift($args);
        }

        $accessKey = $_GET['key'] ?? null; //Filled in only for protected decks

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('No package with this ID was found.', 404001);
        }

        if ($package->isDeleted()) {
            throw new UserException('This package was deleted.', 410001);
        }

        $authenticator = new PackageManager();
        if (!$authenticator->checkReadAccess($packageId, $accessKey)) {
            throw new UserException('This package is private and the access key is either wrong or missing.', 401001);
        }

        if ($package->getVersion() === 0) {
            throw new UserException('This package hasn\'t been uploaded yet.', 406001);
        }

        $queryString = "/$packageId/<span style=\"color: gold;\">".$package->getVersion()."</span>".(empty($accessKey) ? '' : "&amp;key=$accessKey");

        self::$data['layout']['page_id'] = 'deck-info';
        self::$data['layout']['Title'] = $package->getName();

        self::$data['deck']['Package'] = $package;
        self::$data['deck']['packageId'] = $packageId;
        self::$data['deck']['accessKey'] = $accessKey;
        self::$data['deck']['queryString'] = $queryString;
        self::$data['deck']['downloadLink'] = $package->getDownloadLink();

        self::$views[] = 'deck';

        return 200;
    }
}


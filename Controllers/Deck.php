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

        $queryString = "?id=$packageId&amp;current=<span style=\"color: gold;\">".$package->getVersion()."</span>".(empty($accessKey) ? '' : "&amp;key=$accessKey");

        self::$data['layout']['page_id'] = 'deck-info';
        self::$data['layout']['title'] = $package->getName();

        self::$data['deck']['package'] = $package;
        self::$data['deck']['packageId'] = $packageId;
        self::$data['deck']['accessKey'] = $accessKey;
        self::$data['deck']['queryString'] = $queryString;
        self::$data['deck']['downloadLink'] = 'http://'.$_SERVER['SERVER_NAME'].'/deck/'.$package->getId().(empty($accessKey) ? '' : "&amp;key=$accessKey");

        self::$views[] = 'deck';

        return 200;
    }
}


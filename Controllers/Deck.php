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
        $accessKey = $_GET['key'] ?? null; //Filled in only for protected decks

        if (is_null($packageId)) {
            throw new UserException('No package ID was specified.', 400001);
        }

        $authenticator = new PackageManager();
        $authenticator->checkReadAccess($packageId, $accessKey);

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('No package with this ID was found.', 404001);
        }

        if ($package->getVersion() === 0) {
            throw new UserException('This package hasn\'t been uploaded yet.', 406001);
        }

        $queryString = "?id=$packageId&amp;current=<span style=\"color: gold;\">".$package->getVersion()."</span>".(empty($accessKey) ? '' : "&amp;key=$accessKey");

        self::$data['layout']['page_id'] = 'browse';
        self::$data['layout']['title'] = $package->getName();

        self::$data['deck']['package'] = $package;
        self::$data['deck']['packageId'] = $packageId;
        self::$data['deck']['accessKey'] = $accessKey;
        self::$data['deck']['queryString'] = $queryString;

        self::$views[] = 'deck';

        return 200;
    }
}

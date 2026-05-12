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
            throw new UserException('Balíček s tímto ID nebyl nalezen.', 404002);
        }

        if ($package->isDeleted()) {
            throw new UserException('Tento balíček byl smazán.', 410002);
        }

        $authenticator = new PackageManager();
        if (!$authenticator->checkReadAccess($packageId, $accessKey)) {
            throw new UserException('Tento balíček je soukromý a přístupový klíč buďto chybí nebo je nesprávný.', 401002);
        }

        if ($package->getVersion() === 0) {
            throw new UserException('Tento balíček zatím nebyl nahrán.', 406002);
        }

        if (!file_exists('decks/'.$packageId.'.apkg')) {
            throw new UserException('Soubor s tímto balíčkem na našem serveru neexistuje.', 404003);
        }

        self::$views = []; //Don't output any HTML
        self::$views[] = 'file-outputs/deck-download';
        self::$data['deckdownload']['Package'] = $package;

        return 200;
    }
}


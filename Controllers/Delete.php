<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\CategoryManager;
use AnkiDeckUpdateChecker\Models\Package;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\UserException;

class Delete extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        $packageId = array_shift($args) ?? null;
        $key = $_POST['key'] ?? null;

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('Balíček s tímto ID nebyl nalezen.', 404005);
        }

        if ($package->isDeleted()) {
            throw new UserException('Tento balíček byl smazán.', 410005);
        }

        //Do authentication
        if (is_null($key)) {
            throw new UserException('Nebyl poskytnut editační klíč.', 401005);
        }
        $tools = new PackageManager();
        if (!$tools->checkWriteAccess($packageId, $key)) {
            throw new UserException('Editační klíč pro tento balíček není platný.', 403003);
        }

        $deckName = $package->getName();
        $error = null;

        if (isset($_POST['confirm-key'])) {
            $confirmation = trim($_POST['confirm-key']);

            $tools = new PackageManager();

            if ($tools->checkWriteAccess($packageId, $confirmation)) {
                $tools->delete($package);
                (new CategoryManager())->recalculateDeckCounts();
                $this->redirect('/deleted/'.$packageId);
            } else {
                $error = 'Editační klíč je nesprávný.';
            }
        }

        self::$data['layout']['page_id'] = 'delete';
        self::$data['layout']['title'] = 'Smazat balíček';

        self::$data['delete']['DeckName'] = $deckName ?? null;
        self::$data['delete']['key'] = $key ?? null;
        self::$data['delete']['error'] = $error;

        self::$views[] = 'delete';
        self::$cssFiles[] = 'create';

        return 200;
    }
}


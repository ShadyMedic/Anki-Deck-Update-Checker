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
            throw new UserException('No package with this ID was found.', 404005);
        }

        if ($package->isDeleted()) {
            throw new UserException('This package was deleted.', 410005);
        }

        //Do authentication
        if (is_null($key)) {
            throw new UserException('No editing key was provided.', 401005);
        }
        $tools = new PackageManager();
        if (!$tools->checkWriteAccess($packageId, $key)) {
            throw new UserException('The editing key for this package is not valid.', 403003);
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
                $error = 'Editing key is incorrect';
            }
        }

        self::$data['layout']['page_id'] = 'delete';
        self::$data['layout']['title'] = 'Delete Deck';

        self::$data['delete']['DeckName'] = $deckName ?? null;
        self::$data['delete']['key'] = $key ?? null;
        self::$data['delete']['error'] = $error;

        self::$views[] = 'delete';
        self::$cssFiles[] = 'create';

        return 200;
    }
}


<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\Package;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\UserException;

class Edit extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        $packageId = array_shift($args) ?? null;
        $originalKey = $_POST['key'] ?? null;

        if (is_null($packageId)) {
            throw new UserException('No package ID was specified.', 400004);
        }

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('No package with this ID was found.', 404004);
        }

        if ($package->isDeleted()) {
            throw new UserException('This package was deleted.', 410004);
        }

        //Do authentication
        if (is_null($originalKey)) {
            throw new UserException('No editing key was provided.', 401003);
        }
        $tools = new PackageManager();
        if (!$tools->checkWriteAccess($packageId, $originalKey)) {
            throw new UserException('The editing key for this package is not valid.', 403002);
        }

        $deckName = $package->getName();
        $author = $package->getAuthor();
        $public = $package->isPublic();
        $errors = array();
        $saves = array();

        if (isset($_POST['deck-name'])) { //Form was submitted, webpage loading is also POST because of "key" submission
            $deckName = trim($_POST['deck-name']);
            $author = trim($_POST['author']);
            $justPublished = isset($_POST['public']) && $_POST['public'] === 'on';
            $public = $public || $justPublished;
            $key = trim($_POST['new-key']);

            $edits = array();

            try {
                if ($tools->validateName($deckName)) {
                    $edits['filename'] = $deckName;
                    $saves[] = 'Deck name was saved';
                }
            } catch (UserException $e) {
                $errors[] = $e->getMessage();
            }

            try {
                if ($tools->validateAuthor($author)) {
                    $edits['author'] = $author;
                    $saves[] = 'Author was saved';
                }
            } catch (UserException $e) {
                $errors[] = $e->getMessage();
            }

            try {
                if ($tools->validateEditKey($key)) {
                    $edits['edit_key'] = $key;
                    $saves[] = 'Edit key was saved';
                }
            } catch (UserException $e) {
                $errors[] = $e->getMessage();
            }

            if ($justPublished) {
                $edits['access_key'] = null;
                $saves[] = 'Package was published';
            }

            if (empty($errors)) {
                $package->update($edits);
            }
        }

        self::$data['layout']['page_id'] = 'new-deck';
        self::$data['layout']['title'] = 'Edit Deck Details';

        self::$data['edit']['key'] = $originalKey;
        self::$data['edit']['deckName'] = $deckName ?? null;
        self::$data['edit']['author'] = $author ?? null;
        self::$data['edit']['public'] = $public ?? null;
        self::$data['edit']['newKey'] = $key ?? $originalKey;
        self::$data['edit']['errors'] = $errors;
        self::$data['edit']['saves'] = $saves;

        self::$views[] = 'edit';
        self::$cssFiles[] = 'create';
        self::$jsFiles[] = 'auth-fill'; //For loading key-input and author-input

        return 200;
    }
}


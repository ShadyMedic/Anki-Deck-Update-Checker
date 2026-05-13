<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\CategoryManager;
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

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('Balíček s tímto ID nebyl nalezen.', 404004);
        }

        if ($package->isDeleted()) {
            throw new UserException('Tento baliček byl smazán.', 410004);
        }

        //Do authentication
        if (is_null($originalKey)) {
            throw new UserException('Nebyl poskytnut žádný editační klíč.', 401003);
        }
        $tools = new PackageManager();
        if (!$tools->checkWriteAccess($packageId, $originalKey)) {
            throw new UserException('Editační klíč pro tento balíček není platný.', 403002);
        }

        $category = $package->getCategory();
        $deckName = $package->getName();
        $author = $package->getAuthor();
        $public = $package->isPublic();
        $errors = array();
        $saves = array();

        if (isset($_POST['deck-name'])) { //Form was submitted, webpage loading is also POST because of "key" submission
            $category = trim($_POST['category']);
            $deckName = trim($_POST['deck-name']);
            $author = trim($_POST['author']);
            $justPublished = isset($_POST['public']) && $_POST['public'] === 'on';
            $public = $public || $justPublished;
            $key = trim($_POST['new-key']);

            $edits = array();

            try {
                if ($tools->validateCategory($category)) {
                    $edits['category_id'] = $category;
                    $saves[] = 'Kategorie uložena';
                }
            } catch (UserException $e) {
                $errors[] = $e->getMessage();
            }

            try {
                if ($tools->validateName($deckName)) {
                    $edits['name'] = $deckName;
                    $saves[] = 'Název balíčku uložen';
                }
            } catch (UserException $e) {
                $errors[] = $e->getMessage();
            }

            try {
                if ($tools->validateAuthor($author)) {
                    $edits['author'] = $author;
                    $saves[] = 'Autor uložen';
                }
            } catch (UserException $e) {
                $errors[] = $e->getMessage();
            }

            try {
                if ($tools->validateEditKey($key)) {
                    $edits['edit_key'] = $key;
                    $saves[] = 'Editační klíč uložen';
                }
            } catch (UserException $e) {
                $errors[] = $e->getMessage();
            }

            if ($justPublished) {
                $edits['access_key'] = null;
                $saves[] = 'Balíček publikován';
            }

            if (!empty($edits)) {
                $package->update($edits);
                if (in_array('Kategorie uložena', $saves)) {
                    (new CategoryManager())->recalculateDeckCounts();
                }
            }
        }

        self::$data['layout']['page_id'] = 'new-deck';
        self::$data['layout']['title'] = 'Upravit detaily balíčku';

        self::$data['edit']['key'] = $originalKey;
        self::$data['edit']['categories'] = (new CategoryManager())->loadCategories();
        self::$data['edit']['category'] = $category ?? null;
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


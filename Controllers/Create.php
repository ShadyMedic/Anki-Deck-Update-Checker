<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\CategoryManager;
use AnkiDeckUpdateChecker\Models\Package;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\UserException;
use Exception;

class Create extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        if (!empty($_POST)) {
            $category = trim($_POST['category']);
            $deckName = trim($_POST['deck-name']);
            $author = trim($_POST['author']);
            $public = isset($_POST['public']);
            $key = trim($_POST['key']);
            $error = null;

            $tools = new PackageManager();
            try {
                $tools->validateCategory($category);
                $tools->validateName($deckName);
                $tools->validateAuthor($author);
                $tools->validateEditKey($key);
            } catch (UserException $e) {
                $error = $e->getMessage();
            }

            if (!isset($error)) {
                if (!$public) {
                    try {
                        $accessKey = $tools->generateAccessKey();
                    } catch (Exception $e) {
                        $error = 'Something went wrong while creating an access key for your private deck. '.
                            'Either make the deck public, or try again later please.';
                        $accessKey = null;
                    }
                } else {
                    $accessKey = null;
                }

                $package = new Package();
                $package->create(array(
                    'category' => $category,
                    'name' => $deckName,
                    'author' => $author,
                    'accessKey' => $accessKey,
                    'editKey' => $key
                ));

                $packageId = $package->getId();
                self::$data['create']['createdId'] = $packageId;
            }
        }

        self::$data['layout']['page_id'] = 'new-deck';
        self::$data['layout']['title'] = 'Upload New Deck';

        self::$data['create']['category'] = $category ?? null;
        self::$data['create']['deckName'] = $deckName ?? null;
        self::$data['create']['author'] = $author ?? null;
        self::$data['create']['public'] = $public ?? null;
        self::$data['create']['key'] = $key ?? null;
        self::$data['create']['categories'] = (new CategoryManager())->loadCategories() ?? null;
        self::$data['create']['error'] = $error ?? null;

        self::$views[] = 'create';
        if (isset(self::$data['create']['createdId'])) {
            //Include front-end scripts only if the generated webpage isn't a quick front-end redirect.
            self::$cssFiles = [];
            self::$jsFiles = [];
        } else {
            self::$cssFiles[] = 'create';
            self::$jsFiles[] = 'auth-fill';
        }

        return 200;
    }
}


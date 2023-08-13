<?php

namespace AnkiDeckUpdateChecker\Controllers;

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
            $deckName = trim($_POST['deck-name']);
            $author = trim($_POST['author']);
            $public = isset($_POST['public']);
            $key = trim($_POST['key']);
            $error = null;

            $tools = new PackageManager();
            try {
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
                    'name' => $deckName,
                    'author' => $author,
                    'accessKey' => $accessKey,
                    'editKey' => $key
                ));

                $packageId = $package->getId();

                $url = '/upload/'.$packageId.($public);
                $this->redirect($url);
            }
        }

        self::$data['layout']['page_id'] = 'new-deck';
        self::$data['layout']['title'] = 'Upload New Deck';

        self::$data['create']['deckName'] = $deckName ?? null;
        self::$data['create']['author'] = $author ?? null;
        self::$data['create']['public'] = $public ?? null;
        self::$data['create']['key'] = $key ?? null;
        self::$data['create']['error'] = $error ?? null;

        self::$views[] = 'create';
        self::$cssFiles[] = 'create';
        self::$jsFiles[] = 'create';

        return 200;
    }
}


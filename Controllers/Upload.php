<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\Package;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\UserException;

class Upload extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        $packageId = array_shift($args) ?? null;
        $key = $_POST['key'] ?? null;

        if (is_null($packageId)) {
            throw new UserException('Missing package ID.', 400003);
        }

        $package = new Package();
        $package->load($packageId);
        $nextVersion = $package->getVersion() + 1;
        $queryString = "/$packageId/$nextVersion".(($package->isPublic()) ? '' : ('?key='.$package->getAccessKey()));
        if (isset($_FILES['package'])) { //Form was submitted, webpage loading is also POST because of "key" submission
            try {
                $authenticator->checkFileUpload($_FILES['package']);
            } catch (UserException $e) {
                $error = $e->getMessage();
            }

            if (!isset($error)) {
                move_uploaded_file($_FILES['package']['tmp_name'], 'decks/'.$packageId.'.apkg');

                $authenticator->update($package);

                if ($package->getVersion() === 1) {
                    $url = '/uploaded/'.$packageId.(($package->isPublic()) ? '' : ('?key='.$package->getAccessKey()));
                } else {
                    $url = '/updated/'.$packageId.(($package->isPublic()) ? '' : ('?key='.$package->getAccessKey()));
                }

                $this->redirect($url);
            }
        }

        self::$data['layout']['page_id'] = 'upload';
        self::$data['layout']['title'] = 'Upload the Anki Package File';

        self::$data['upload']['queryString'] = $queryString;
        self::$data['upload']['packageId'] = $packageId;
        self::$data['upload']['accessKey'] = $package->getAccessKey();
        self::$data['upload']['key'] = $key;
        self::$data['upload']['error'] = $error ?? '';

        self::$views[] = 'upload';
        self::$cssFiles[] = 'upload';
        self::$jsFiles[] = 'auth-fill';

        return 200;
    }
}


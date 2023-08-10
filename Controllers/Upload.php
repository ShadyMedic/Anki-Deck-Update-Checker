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
        $key = $_POST['key'] ?? null; //This will be filled in only when submitting the upload form
        $accessKey = $_REQUEST['access-key'] ?? null;

        if (is_null($packageId)) {
            throw new UserException('Missing package ID.', 400003);
        }

        $package = new Package();
        $package->load($packageId);
        $nextVersion = $package->getVersion() + 1;
        $queryString = "/$packageId/$nextVersion".((empty($accessKey)) ? '' : '?key='.$accessKey);

        if (!empty($_POST)) {
            $authenticator = new PackageManager();

            //Do authentication
            if (!$authenticator->checkWriteAccess($packageId, $key)) {
                throw new UserException('The editing key for this package is not valid.', 401001);
            }

            try {
                $authenticator->checkFileUpload($_FILES['package']);
            } catch (UserException $e) {
                $error = $e->getMessage();
            }

            if (!isset($error)) {
                move_uploaded_file($_FILES['package']['tmp_name'], 'decks/'.$packageId.'.apkg');

                $authenticator->update($package);

                echo $package->getVersion();
                if ($package->getVersion() === 1) {
                    $url = '/uploaded/'.$packageId.((empty($accessKey)) ? '' : '?key='.$accessKey);
                } else {
                    $url = '/updated/'.$packageId.((empty($accessKey)) ? '' : '?key='.$accessKey);
                }

                $this->redirect($url);
            }
        }

        self::$data['layout']['page_id'] = 'upload';
        self::$data['layout']['title'] = 'Upload the Anki Package File';

        self::$data['upload']['queryString'] = $queryString;
        self::$data['upload']['packageId'] = $packageId;
        self::$data['upload']['accessKey'] = $accessKey;
        self::$data['upload']['error'] = $error ?? '';

        self::$views[] = 'upload';
        self::$cssFiles[] = 'upload';
        self::$jsFiles[] = 'upload';

        return 200;
    }
}


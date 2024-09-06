<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\CategoryManager;
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
        $minor = (!empty($args) && array_shift($args) === 'minor');

        $package = new Package();
        $packageFound = $package->load($packageId);

        if (!$packageFound) {
            throw new UserException('No package with this ID was found.', 404006);
        }

        if ($package->isDeleted()) {
            throw new UserException('This package was deleted.', 410003);
        }

        if ($package->getVersion() === 0 && $minor) {
            throw new UserException('The first upload of the package cannot be marked as minor.', 400006);
        }

        //Do authentication
        if (is_null($key)) {
            throw new UserException('No editing key was provided.', 401004);
        }
        $authenticator = new PackageManager();
        if (!$authenticator->checkWriteAccess($packageId, $key)) {
            throw new UserException('The editing key for this package is not valid.', 403001);
        }

        $nextVersion = ($minor ? $package->getVersion() : $package->getVersion() + 1);
        $queryString = "/$packageId/$nextVersion".(($package->isPublic()) ? '' : ('?key='.$package->getAccessKey()));
        if (isset($_FILES['package-file']) || isset($_POST['package-link']) || isset($_FILES['package-info'])) {
            //Form was submitted, webpage loading is also POST because of "key" and "minor" submission
            try {
                $authenticator->checkFileUpload($_FILES['package']);
            } catch (UserException $e) {
                $error = $e->getMessage();
            }

            if (!isset($error)) {
                move_uploaded_file($_FILES['package']['tmp_name'], 'decks/'.$packageId.'.apkg');

                $authenticator->update($package, $minor);

                if ($package->getFullVersion() === '1.0') {
                    (new CategoryManager())->recalculateDeckCounts(); //New package uploaded --> recalculate deck counts
                    $url = '/uploaded/'.$packageId.(($package->isPublic()) ? '' : ('?key='.$package->getAccessKey()));
                } else if ($package->getMinorVersion() === 0) {
                    $url = '/updated/'.$packageId.(($package->isPublic()) ? '' : ('?key='.$package->getAccessKey()));
                } else {
                    $url = '/patched/'.$packageId.(($package->isPublic()) ? '' : ('?key='.$package->getAccessKey()));
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
        self::$data['upload']['minor'] = $minor;
        self::$data['upload']['firstRelease'] = $package->getVersion() === 0;

        self::$views[] = 'upload';
        self::$cssFiles[] = 'upload';
        self::$jsFiles[] = 'auth-fill';
        self::$jsFiles[] = 'upload';

        return 200;
    }
}


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
        if (isset($_POST['type'])) { //Form submitted, webpage loading is POST too because of key and minor submissions
            $detailsLink = 'LOCAL';
            $downloadLink = 'LOCAL';
            switch ($_POST['type']) {
                case 'file':
                    try {
                        $authenticator->checkFileUpload($_FILES['package-file']);
                    } catch (UserException $e) {
                        $error = $e->getMessage();
                    }
                    if (empty($error)) {
                        move_uploaded_file($_FILES['package-file']['tmp_name'], 'decks/'.$packageId.'.apkg');
                    }
                    break;
                case 'link':
                    try {
                        $authenticator->checkRemoteDownloadLink($_POST['package-link']);
                        $downloadLink = $_POST['package-link'];
                        if (file_exists('decks/'.$packageId.'.apkg')) {
                            unlink('decks/'.$packageId.'.apkg');
                        }
                    } catch (UserException $e) {
                        $error = $e->getMessage();
                    }
                    break;
                case 'remote':
                    try {
                        $authenticator->checkRemoteInfoLink($_POST['package-info']);
                        $downloadLink = 'REMOTE';
                        $detailsLink = $_POST['package-info'];
                        if (file_exists('decks/'.$packageId.'.apkg')) {
                            unlink('decks/'.$packageId.'.apkg');
                        }
                    } catch (UserException $e) {
                        $error = $e->getMessage();
                    }
                    break;
                default:
                    throw new UserException("The specified package type is invalid.", 400007);
            }

            if (!isset($error)) {
                $authenticator->update($package, $minor, $detailsLink, $downloadLink);

                if ($detailsLink !== 'LOCAL') {
                    if ($package->getFullVersion() === '1.0') {
                        (new CategoryManager())->recalculateDeckCounts();
                    }
                    $url = '/linked/'.$packageId.(($package->isPublic()) ? '' : ('?key='.$package->getAccessKey()));
                } else if ($package->getFullVersion() === '1.0') {
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


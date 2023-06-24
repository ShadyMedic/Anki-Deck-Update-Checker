<?php

namespace AnkiDeckUpdateChecker\Models;

class PackageManager
{

    public function update(int $packageId, string $downloadLink = null) : bool
    {
        $package = new Package();
        $package->load($packageId);

        $downloadLink = $downloadLink ?? '/deck.php?id='.$package->getId();

        return $package->update(array(
            'download_link' => $downloadLink,
            'version' => $package->getVersion() + 1
        ));
    }

    /**
     * @throws UserException
     */
    public function validateName(string $deckName) : bool
    {
        if (empty($deckName)) {
            throw new UserException("Deck name mustn't be empty");
        }

        if (mb_strlen($deckName) > 122) {
            throw new UserException("Deck name is too long.");
        }

        return true;
    }

    /**
     * @throws UserException
     */
    public function validateAuthor(string $author) : bool
    {
        if (empty($author)) {
            throw new UserException("Author's name mustn't be empty");
        }
        if (mb_strlen($author) > 31) {
            throw new UserException("Author's name is too long.");
        }

        return true;
    }

    /**
     * @throws UserException
     */
    public function validateEditKey(string $key) : bool
    {
        if (strlen($key) < 6) {
            throw new UserException("Editing key is too short – 6 characters minimum.");
        }
        if (strlen($key) > 31) {
            throw new UserException("Editing key is too long.");
        }
        if (preg_match('/[^A-Za-z0-9]/', $key)) {
            throw new UserException("The editing key may only contain letters and numbers.");
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    public function generateAccessKey() : string
    {
        return substr(bin2hex(random_bytes(16)), 1); //31 characters
    }

    public function checkWriteAccess(int $packageId, string $key) : bool
    {
        $db = Db::connect();
        $statement = $db->prepare('SELECT COUNT(*) AS "cnt" FROM package WHERE package_id = ? AND edit_key = ? LIMIT 1');
        $statement->execute(array($packageId, $key));
        return ($statement->fetch()['cnt'] === 1);
    }

    public function checkReadAccess(int $packageId, ?string $accessKey) : bool
    {
        $db = Db::connect();
        $statement = $db->prepare('SELECT COUNT(*) AS "cnt" FROM package WHERE package_id = ? AND (access_key = ? OR access_key IS NULL) LIMIT 1');
        $statement->execute(array($packageId, $accessKey));
        return ($statement->fetch()['cnt'] === 1);
    }

    /**
     * @throws UserException
     */
    public function checkFileUpload(array $fileUploadInfo)
    {
        $fileSize = $fileUploadInfo['size'];
        $tmpFileName = $fileUploadInfo['tmp_name'];
        $uploadError = $fileUploadInfo['error'];

        if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE || $fileSize > 8388608) {
            throw new UserException("Your package file is too large – maximum allowed size is 8 MB for now.");
        } else if ($uploadError === UPLOAD_ERR_NO_FILE) {
            throw new UserException("No file was selected.");
        } else if (!empty($uploadError)) {
            throw new UserException("An error occurred while uploading the file. Please try again later.");
        }
    }

    public function getPublicPackages() : array
    {
        $query = '
            SELECT package_id,filename,author,version,updated_at FROM package
            WHERE access_key IS NULL AND version > 0 AND download_link IS NOT NULL
            ORDER BY updated_at DESC;
        ';

        $db = Db::connect();
        $statement = $db->prepare($query);
        $statement->execute(array());
        return $statement->fetchAll();
    }

    public function getOwnedPackages(string $key) : array
    {
        $query = '
            SELECT package_id,filename,access_key,version,updated_at FROM package
            WHERE edit_key = ?
            ORDER BY updated_at DESC;
        ';

        $db = Db::connect();
        $statement = $db->prepare($query);
        $statement->execute(array($key));
        return $statement->fetchAll();
    }
}


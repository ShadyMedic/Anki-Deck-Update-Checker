<?php

namespace AnkiDeckUpdateChecker\Models;

class PackageManager
{

    public function update(Package $package, bool $minor, string $detailsLink = 'LOCAL', string $downloadLink = 'LOCAL') : bool
    {
        if ($minor) {
            $package->minorVersion();
        } else {
            $package->newVersion();
        }

        return $package->update(array(
            'download_link' => $downloadLink,
            'details_link' => $detailsLink,
            'version' => $package->getVersion(),
            'minor_version' => $package->getMinorVersion(),
            'updated_at' => date(DATE_W3C)
        ));
    }

    /**
     * @throws UserException
     */
    public function validateCategory(int $categoryId) : bool
    {
        $manager = new CategoryManager();
        if (!$manager->categoryExists($categoryId)) {
            throw new UserException('Category wasn\'t found');
        }
        return true;
    }

    /**
     * @throws UserException
     */
    public function validateName(string $deckName) : bool
    {
        if (empty($deckName)) {
            throw new UserException('Deck name mustn\'t be empty');
        }

        // Replace with "(str_ends_with($deckName, '.apkg'))" in PHP version 8 and newer
        if (substr($deckName, -5) === '.apkg') {
            throw new UserException('Deck name should not contain the ".apkg" file extension.');
        }

        if (mb_strlen($deckName) > 63) {
            throw new UserException('Deck name is too long.');
        }

        if (mb_strlen($deckName) < 3) {
        throw new UserException('Deck name is too short.');
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
        $uploadError = $fileUploadInfo['error'];

        if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE || $fileSize > 26214400) {
            throw new UserException("Your package file is too large – maximum allowed size is 20 MiB for now.");
        } else if ($uploadError === UPLOAD_ERR_NO_FILE) {
            throw new UserException("No file was selected.");
        } else if (!empty($uploadError)) {
            throw new UserException("An error occurred while uploading the file. Please try again later.");
        }
    }

    /**
     * @param $url
     * @return void
     * @throws UserException
     */
    public function checkRemoteDownloadLink($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $headers = explode("\r\n", $header);
        $contentDisposition = array_filter($headers, function($element)
            {return (stripos($element, 'Content-Disposition:') === 0); })[0];
        curl_close($ch);
        if ($httpCode >= 300 || (
            strpos($contentType, 'application/octet-stream') === false &&
            strpos($contentDisposition, 'attachment') === false)
        ) {
            throw new UserException("The provided link does not seem to lead to a direct download of an APKG file.");
        }
        //TODO test this
    }

    /**
     * @param $url
     * @return void
     * @throws UserException
     */
    public function checkRemoteInfoLink($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 300) {
            throw new UserException("The provided link does not seem to lead to an existing webpage.");
        }
        //TODO test this
    }

    public function getPublicPackages(int $categoryId) : array
    {
        $query = '
            SELECT package_id,name,author,version,minor_version,details_link,updated_at FROM package
            WHERE access_key IS NULL AND version > 0 AND download_link IS NOT NULL AND category_id = ? AND deleted = 0
            ORDER BY updated_at DESC;
        ';

        $db = Db::connect();
        $statement = $db->prepare($query);
        $statement->execute([$categoryId]);
        return $statement->fetchAll();
    }

    public function getOwnedPackages(string $key) : array
    {
        $query = '
            SELECT package_id,category.name AS `category`,package.name,access_key,version,minor_version,updated_at
            FROM package
            JOIN category ON package.category_id = category.category_id
            WHERE edit_key = ?
            ORDER BY updated_at DESC;
        ';

        $db = Db::connect();
        $statement = $db->prepare($query);
        $statement->execute(array($key));
        return $statement->fetchAll();
    }

    public function delete(Package $package)
    {
        //Purge from database
        $package->delete();

        //Delete package file
        unlink('decks/'.$package->getId().'.apkg');

        //Delete aggregated and aggregated statistics
        $manager = new StatisticsManager();
        $manager->deleteStats($package->getId());

        unset($package);
    }
}


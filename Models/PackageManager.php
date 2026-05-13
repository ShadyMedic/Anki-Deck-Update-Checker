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
            throw new UserException('Kategorie nebyla nalezena.');
        }
        return true;
    }

    /**
     * @throws UserException
     */
    public function validateName(string $deckName) : bool
    {
        if (empty($deckName)) {
            throw new UserException('Název balíčku nesmí být prázdný.');
        }

        // Replace with "(str_ends_with($deckName, '.apkg'))" in PHP version 8 and newer
        if (substr($deckName, -5) === '.apkg') {
            throw new UserException('Název balíčku nemá končit souborovou příponou ".apkg"');
        }

        if (mb_strlen($deckName) > 63) {
            throw new UserException('Název balíčku je moc dlouhý.');
        }

        if (mb_strlen($deckName) < 3) {
        throw new UserException('Název balíčku je moc krátký.');
    }

        return true;
    }

    /**
     * @throws UserException
     */
    public function validateAuthor(string $author) : bool
    {
        if (empty($author)) {
            throw new UserException("Jméno autora nesmí být prázdné.");
        }
        if (mb_strlen($author) > 31) {
            throw new UserException("Jméno autora je příliš dlouhé.");
        }

        return true;
    }

    /**
     * @throws UserException
     */
    public function validateEditKey(string $key) : bool
    {
        if (strlen($key) < 6) {
            throw new UserException("Editační klíč je moc krátký – musí mít alespoň 6 znaků.");
        }
        if (strlen($key) > 31) {
            throw new UserException("Editační klíč je moc dlouhý.");
        }
        if (preg_match('/[^A-Za-z0-9]/', $key)) {
            throw new UserException("Editační klíč může obsahovat pouze číslice a písmena anglické abecedy.");
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
            throw new UserException("Tvůj soubor s balíčkem je moc velký, aktuálně je maximální povolená velikost souboru 20 MiB.");
        } else if ($uploadError === UPLOAD_ERR_NO_FILE) {
            throw new UserException("Nebyl vybrán žádný soubor.");
        } else if (!empty($uploadError)) {
            throw new UserException("Během nahrávání souboru došlo k chybě.");
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
        $contentDisposition = @array_filter($headers, function($element)
            {return (stripos($element, 'Content-Disposition:') === 0); })[0];
        curl_close($ch);
        if ($httpCode >= 300 || (
            strpos($contentType, 'application/octet-stream') === false &&
            strpos($contentDisposition, 'attachment') === false)
        ) {
            throw new UserException("Poskytnutý odkaz zřejmě nevede na přímé stažení APKG souboru.");
        }
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
            throw new UserException("Poskytnutý odkaz zřejmě nevede na existující webovou stránku.");
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


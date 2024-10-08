<?php

namespace AnkiDeckUpdateChecker\Models;

use DateTime;
use PDOException;

class Package implements DatabaseRecord, Sanitizable
{
    private ?int $packageId = null;
    private ?int $version = 0;
    private ?int $minorVersion = 0;
    private ?string $accessKey = null;
    private ?string $detailsLink = null;
    private ?string $downloadLink = null;
    private ?int $categoryId = 1;
    private ?string $name = null;
    private ?string $author = null;
    private ?string $editKey = null;
    private DateTime $updatedAt;
    private bool $deleted = false;

    public function create(array $data): bool
    {
        $category = $data['category'];
        $author = $data['author'];
        $name = $data['name'];
        $editKey = $data['editKey'];
        $accessKey = $data['accessKey'];

        $db = Db::connect();
        $query = (empty($accessKey)) ?
            'INSERT INTO package (category_id, name, author, edit_key) VALUES (?,?,?,?)' :
            'INSERT INTO package (access_key, category_id, name, author, edit_key) VALUES (?,?,?,?,?)';
        $parameters = (empty($accessKey)) ?
            array($category, $name, $author, $editKey) :
            array($accessKey, $category, $name, $author, $editKey);

        try {
            $statement = $db->prepare($query);
            $statement->execute($parameters);
            $this->packageId = $db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }

    public function update(array $data): bool
    {
        if (is_null($this->packageId)) {
            throw new \BadMethodCallException('The package cannot be updated, because its ID wasn\'t specified');
        }

        unset($data['updated_at']);
        $columns = array_keys($data);

        $columnString = '';
        foreach ($columns as $column) {
            $columnString .= $column . ' = :' . $column . ', ';
        }
        $columnString = rtrim($columnString, ', ');

        $db = Db::connect();
        $query = 'UPDATE package SET ' . $columnString . ' WHERE package_id = :package_id';
        $statement = $db->prepare($query);
        return $statement->execute(array_merge($data, ['package_id' => $this->getId()]));
    }

    public function load(int $id): bool
    {
        $db = Db::connect();
        $statement = $db->prepare('SELECT * FROM package WHERE package_id = ? LIMIT 1');
        $statement->execute(array($id));
        if ($statement->rowCount() === 0) {
            return false;
        }
        $data = $statement->fetch();

        $this->packageId = $data['package_id'];
        $this->version = $data['version'];
        $this->minorVersion = $data['minor_version'];
        $this->accessKey = $data['access_key'];
        $this->detailsLink = $data['details_link'];
        $this->downloadLink = $data['download_link'];
        $this->categoryId = $data['category_id'];
        $this->name = $data['name'];
        $this->author = $data['author'];
        $this->editKey = $data['edit_key'];
        $this->updatedAt = new DateTime($data['updated_at']);
        $this->deleted = $data['deleted'];

        return true;
    }

    public function delete(): bool
    {
        if (is_null($this->packageId)) {
            throw new \BadMethodCallException('The package cannot be deleted, because its ID wasn\'t specified');
        }

        return $this->update([
            'version' => null,
            'minor_version' => null,
            'access_key' => null,
            'download_link' => null,
            'details_link' => null,
            'name' => null,
            'author' => null,
            'edit_key' => null,
            'updated_at' => null,
            'deleted' => true
        ]);
    }

    public function getId(): ?int
    {
        return $this->packageId;
    }

    public function getCategory(): ?int
    {
        return $this->categoryId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function getMinorVersion(): ?int
    {
        return $this->minorVersion;
    }

    public function getFullVersion(): ?string
    {
        return $this->version . '.' . $this->minorVersion;
    }

    public function newVersion(): void
    {
        $this->version++;
        $this->minorVersion = 0;
    }

    public function minorVersion(): void
    {
        $this->minorVersion++;
    }

    public function isPublic(): bool
    {
        return $this->accessKey === null;
    }

    public function getAccessKey(): ?string
    {
        return $this->accessKey;
    }

    public function hasLocalDetailsPage(): bool
    {
        return $this->detailsLink === 'LOCAL';
    }

    public function isHostedLocally(): bool
    {
        return $this->downloadLink === 'LOCAL';
    }

    public function getDetailsLink() : ?string
    {
        $wasCreatedLocally = $this->detailsLink === 'LOCAL';
        if ($wasCreatedLocally) {
            $detailsLink = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].'/deck/'.$this->packageId;
            if (!empty($this->accessKey)) {
                $detailsLink .= '?key='.$this->accessKey;
            }
        } else {
            $detailsLink = $this->detailsLink;
        }

        return $detailsLink;
    }

    public function getDownloadLink() : ?string
    {
        $isHostedLocally = $this->downloadLink === 'LOCAL';
        if ($isHostedLocally) {
            $downloadLink = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].'/deck/'.$this->packageId.'/download';
            if (!empty($this->accessKey)) {
                $downloadLink .= '?key='.$this->accessKey;
            }
        } else {
            $downloadLink = $this->downloadLink;
        }

        return $downloadLink;
    }

    public function getUpdatedDate() : DateTime
    {
        return $this->updatedAt;
    }

    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Method responsible for sanitizing all applicable attributes of the current instance.
     * The after this method is called, the state of the instance should no longer be changed and no other
     * methods should be called.
     * @return void
     */
    public function sanitize(): void
    {
        $this->name = htmlspecialchars($this->name, ENT_QUOTES);
        $this->author = htmlspecialchars($this->author, ENT_QUOTES);
        $this->detailsLink = htmlspecialchars($this->detailsLink, ENT_QUOTES);
        $this->downloadLink = htmlspecialchars($this->downloadLink, ENT_QUOTES);
        //$this->editKey = htmlspecialchars($this->editKey, ENT_QUOTES); # No, because it's only ever displayed to whoever set it
    }
}


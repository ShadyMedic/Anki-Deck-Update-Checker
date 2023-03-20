<?php

namespace Models;

use DateTime;
use PDOException;

class Package implements DatabaseRecord
{
    private ?int $packageId = null;
    private ?int $version = 0;
    private ?string $accessKey = null;
    private ?string $downloadLink = null;
    private ?string $name = null;
    private ?string $author = null;
    private ?string $editKey = null;
    private DateTime $updatedAt;

    public function create(array $data): bool
    {
        $author = $data['author'];
        $name = $data['name'];
        $editKey = $data['editKey'];
        $accessKey = $data['accessKey'];

        $db = Db::connect();
        $query = (empty($accessKey)) ?
            'INSERT INTO package (filename, author, edit_key) VALUES (?,?,?)' :
            'INSERT INTO package (access_key, filename, author, edit_key) VALUES (?,?,?,?)';
        $parameters = (empty($accessKey)) ?
            array($name.'.apkg', $author, $editKey) :
            array($accessKey, $name.'.apkg', $author, $editKey);

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
        $columns = array_keys($data);
        $values = array_values($data);

        $columnSting = '';
        foreach ($columns as $column) {
            $columnSting .= $column.' = ?, ';
        }
        $columnString = rtrim($columnSting, ', ');

        $db = Db::connect();
        $query = 'UPDATE package SET '.$columnString.' WHERE package_id = ?;';
        $statement = $db->prepare($query);
        return $statement->execute(array_merge($values, [$this->getId()]));
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
        $this->accessKey = $data['access_key'];
        $this->downloadLink = $data['download_link'];
        $this->name = $data['filename'];
        $this->author = $data['author'];
        $this->editKey = $data['edit_key'];
        $this->updatedAt = new DateTime($data['updated_at']);

        return true;
    }

    public function delete(): bool
    {
        // TODO: Implement delete() method.
        return false;
    }

    public function getId(): ?int
    {
        return $this->packageId;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function getDownloadLink() : ?string
    {
        $isHostedLocally = (strpos($this->downloadLink, '/deck.php?') === 0); //Always TRUE for now
        if ($isHostedLocally) {
            $downloadLink = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$this->downloadLink;
        } else {
            $downloadLink = $this->downloadLink;
        }

        if (!empty($this->accessKey)) {
            $downloadLink .= '&key='.$this->accessKey;
        }

        return $downloadLink;
    }
}


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
            array($accessKey, $name.'.apkg', $author, $accessKey);

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
        // TODO: Implement update() method.
        return false;
    }

    public function load(): bool
    {
        // TODO: Implement load() method.
        return false;
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
}


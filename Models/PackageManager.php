<?php

namespace Models;

class PackageManager
{
    public function create() {
        //TODO
    }

    public function update() {
        //TODO
    }

    public function edit() {
        //TODO
    }

    public function delete() {
        //TODO
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
}


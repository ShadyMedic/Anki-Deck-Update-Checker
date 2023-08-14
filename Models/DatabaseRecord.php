<?php

namespace AnkiDeckUpdateChecker\Models;

interface DatabaseRecord
{
    public function create(array $data) : bool;
    public function update(array $data) : bool;
    public function load(int $id) : bool;
    public function delete() : bool;
}


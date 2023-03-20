<?php

namespace Models;

interface DatabaseRecord
{
    public function create(array $data) : bool;
    public function update(array $data) : bool;
    public function load() : bool;
    public function delete() : bool;
}


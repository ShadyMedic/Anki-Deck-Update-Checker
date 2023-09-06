<?php

namespace AnkiDeckUpdateChecker\Models;

use AnkiDeckUpdateChecker\Models\Db;
use PDO;

class CategoryManager
{
    public function loadCategories(bool $full = false) : array
    {
        $db = Db::connect();
        $query = 'SELECT '.($full ? '*' : 'category_id,name').' FROM category ORDER BY category_id;';
        $statement = $db->prepare($query);
        $statement->execute();
        return $statement->fetchAll($full ? PDO::FETCH_ASSOC : PDO::FETCH_KEY_PAIR);
    }

    public function categoryExists(int $categoryId) : bool
    {
        $db = Db::connect();
        $query = 'SELECT COUNT(*) FROM category WHERE category_id = ?;';
        $statement = $db->prepare($query);
        $statement->execute([$categoryId]);
        return $statement->fetchColumn() === 1;
    }

    public function getCategoryName(int $categoryId) : string
    {
        $db = Db::connect();
        $query = 'SELECT name FROM category WHERE category_id = ?;';
        $statement = $db->prepare($query);
        $statement->execute([$categoryId]);
        return $statement->fetchColumn();
    }
}


<?php

namespace AnkiDeckUpdateChecker\Models;

use AnkiDeckUpdateChecker\Models\Db;
use PDO;

class CategoryManager
{
    public function loadCategories() : array
    {
        $db = Db::connect();
        $query = 'SELECT category_id,name FROM category ORDER BY category_id;';
        $statement = $db->prepare($query);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function categoryExists(int $categoryId) : bool
    {
        $db = Db::connect();
        $query = 'SELECT COUNT(*) FROM category WHERE category_id = ?;';
        $statement = $db->prepare($query);
        $statement->execute([$categoryId]);
        return $statement->fetchColumn() === 1;
    }
}


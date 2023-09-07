<?php

namespace AnkiDeckUpdateChecker\Models;

use AnkiDeckUpdateChecker\Models\Db;
use PDO;

class CategoryManager
{
    public function loadCategories(bool $full = false) : array
    {
        $db = Db::connect();
        $query = '
        SELECT '.($full ? '*' : 'category_id,name').'
        FROM category
        ORDER BY
            CASE
                WHEN category_id = 0 THEN 0
                ELSE 1
            END,
            package_count DESC
        ;';
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

    public function recalculateDeckCounts() : bool
    {
        $db = Db::connect();
        $query = '
            UPDATE category
            LEFT JOIN (
                SELECT category_id, COUNT(*) as package_count
                FROM package
                WHERE version > 0 AND download_link IS NOT NULL AND access_key IS NULL
                GROUP BY category_id
            ) as package_counts ON category.category_id = package_counts.category_id
            SET category.package_count = package_counts.package_count;
        ';
        $statement = $db->prepare($query);
        return $statement->execute([]);
    }
}


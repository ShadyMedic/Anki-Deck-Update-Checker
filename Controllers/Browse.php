<?php

namespace AnkiDeckUpdateChecker\Controllers;

use AnkiDeckUpdateChecker\Models\CategoryManager;
use AnkiDeckUpdateChecker\Models\PackageManager;
use AnkiDeckUpdateChecker\Models\UserException;

/**
 * @see Controller
 */
class Browse extends Controller
{

    /**
     * @inheritDoc
     */
    public function process(array $args = []): int
    {
        $category = array_shift($args);
        $tools = new CategoryManager();

        self::$data['layout']['page_id'] = 'browse';

        if (is_null($category)) {
            self::$data['layout']['title'] = 'Public Anki Decks';

            $manager = new PackageManager();
            self::$data['categories']['categories'] = $tools->loadCategories(true);

            self::$views[] = 'categories';
            self::$cssFiles[] = 'browse';
        } else {
            if (!$tools->categoryExists($category)) {
                throw new UserException('Category with the given ID wasn\'t found.', 404011);
            }

            self::$data['layout']['title'] = $tools->getCategoryName($category).' Decks';

            $manager = new PackageManager();
            self::$data['browse']['Packages'] = $manager->getPublicPackages($category);

            self::$views[] = 'browse';
            self::$cssFiles[] = 'browse';
        }

        return 200;
    }
}


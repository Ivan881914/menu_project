<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

$config = require __DIR__ . '/../config.php';

try {
    $conn = DriverManager::getConnection($config);

    $categories = $conn->fetchAllAssociative('SELECT * FROM categories ORDER BY parent_id, id');

    // сортируем категории по parent_id: корневые элементы идут первыми
    usort($categories, function($a, $b) {
        return ($a['parent_id'] === null ? -1 : $a['parent_id']) <=> ($b['parent_id'] === null ? -1 : $b['parent_id']);
    });


    $tree = [];
    $references = [];

    foreach ($categories as &$category) {
        $category['children'] = [];
        $references[$category['id']] = &$category;

        if (empty($category['parent_id'])) {
            $tree[] = &$category;
        } else {
            if (isset($references[$category['parent_id']])) {
                //echo "родитель с id: " . $category['parent_id'] . " для категории: " . $category['name'] . "<br>";
                $references[$category['parent_id']]['children'][] = &$category;
            } else {
                echo "Не найден родитель с id: " . $category['parent_id'] . "<br>";
            }
        }
    }

    // echo "<pre>";
    // print_r($tree);
    // echo "</pre>";

    function displayCategories($categories, $level = 0)
    {
        foreach ($categories as $category) {
            $indent = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $level); #четыре пробела
            echo $indent . htmlspecialchars($category['name']) . "<br>";

            if (!empty($category['children'])) {
                //echo $indent . "-- вызов для " . htmlspecialchars($category['name']) . "<br>";
                displayCategories($category['children'], $level + 1);
            }
        }
    }

    echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Список Меню</title>
</head>
<body>
    <h1>Список Меню</h1>
    <div>";

    displayCategories($tree);

    echo "</div>
</body>
</html>";

} catch (Exception $e) {
    echo "Ошибка при выводе меню: " . $e->getMessage();
}

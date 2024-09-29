<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

$config = require __DIR__ . '/../config.php';

try {
    $conn = DriverManager::getConnection($config);

    $categories = $conn->fetchAllAssociative('SELECT * FROM categories ORDER BY parent_id, id');

    // сортируем категории по parent_id, чтобы сначала добавлялись корневые элементы
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
                $references[$category['parent_id']]['children'][] = &$category;
            }
        }
    }

    // экспорт категорий до первого уровня вложенности
    function exportCategoriesLevel1($categories, &$file, $level = 0)
    {
        foreach ($categories as $category) {
            $indent = str_repeat("    ", $level);
            $line = "{$indent}{$category['name']}\n";
            fwrite($file, $line);

            // Экспортируем только дочерние категории первого уровня
            if (!empty($category['children']) && $level < 1) {
                exportCategoriesLevel1($category['children'], $file, $level + 1);
            }
        }
    }

    $exportDir = __DIR__ . '/../exports';
    if (!is_dir($exportDir)) {
        mkdir($exportDir);
    }

    $exportFile = $exportDir . '/type_b.txt';
    $fileHandle = fopen($exportFile, 'w');

    if (!$fileHandle) {
        throw new \RuntimeException('Не удалось открыть файл для записи: ' . $exportFile);
    }

    exportCategoriesLevel1($tree, $fileHandle);

    fclose($fileHandle);

    echo "Экспорт в type_b.txt завершён успешно.\n";
} catch (Exception $e) {
    echo "Ошибка при экспорте: " . $e->getMessage() . "\n";
}

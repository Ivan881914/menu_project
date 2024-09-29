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

    // экспорт категорий в файл с URL от корня до категории
    function exportCategories($categories, &$file, $parentUrl = '', $level = 0)
    {
        foreach ($categories as $category) {
            $currentUrl = $parentUrl . '/' . $category['alias'];
            $indent = str_repeat("    ", $level); 
            $line = "{$indent}{$category['name']} {$currentUrl}\n";
            fwrite($file, $line);

            if (!empty($category['children'])) {
                exportCategories($category['children'], $file, $currentUrl, $level + 1);
            }
        }
    }

    $exportDir = __DIR__ . '/../exports';
    if (!is_dir($exportDir)) {
        mkdir($exportDir);
    }

    $exportFile = $exportDir . '/type_a.txt';
    $fileHandle = fopen($exportFile, 'w');

    if (!$fileHandle) {
        throw new \RuntimeException('Не удалось открыть файл для записи: ' . $exportFile);
    }

    exportCategories($tree, $fileHandle);

    fclose($fileHandle);

    echo "Экспорт в type_a.txt завершён успешно.\n";
} catch (Exception $e) {
    echo "Ошибка при экспорте: " . $e->getMessage() . "\n";
}

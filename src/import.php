<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

$config = require __DIR__ . '/../config.php';

try {
    $conn = DriverManager::getConnection($config);

    if ($conn === null) {
        throw new \RuntimeException('Не удалось установить соединение с базой данных');
    }

    $conn->beginTransaction();

    function importCategories($categories, $conn, $parentId = null)
    {
        foreach ($categories as $category) {
            // проверяем обязательные поля
            if (!isset($category['name']) || !isset($category['alias'])) {
                continue; // пропускаем некорректные записи
            }

            $conn->insert('categories', [
                'name' => $category['name'],
                'alias' => $category['alias'],
                'parent_id' => $parentId,
            ]);

            $currentId = $conn->fetchOne('SELECT LASTVAL()');

            foreach ($category as $key => $value) {
                if ($key === 'childrens' && is_array($value)) {
                    importCategories($value, $conn, $currentId);
                }
            }
        }
    }

    $jsonData = file_get_contents(__DIR__ . '/../data/categories.json');
    $categories = json_decode($jsonData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \RuntimeException('Ошибка при декодировании JSON: ' . json_last_error_msg());
    }

    importCategories($categories, $conn, null);

    $conn->commit();

    echo "Импорт завершён успешно.\n";
} catch (Exception $e) {
    // откатываем транзакцию в случае ошибки
    if ($conn->isTransactionActive()) {
        $conn->rollBack();
    }
    echo "Ошибка при импорте: " . $e->getMessage() . "\n";
}

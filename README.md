
# Проект Древовидное меню

## Краткое Описание

Этот проект реализует функционал для хранения, обработки и вывода списка меню сайта с неограниченным уровнем вложенности. Проект реализован на PHP (без фреймворков) и PostgreSQL в качестве базы данных. Включает:

- Импорт категорий из файла `categories.json` в базу данных.
- Экспорт категорий в файлы `type_a.txt` и `type_b.txt`.
- Вывод списка меню с отступами на странице `list_menu.php`.

## Необходимое ПО

- PHP 7.4 или выше
- Composer
- PostgreSQL
- Git

## Установка и Настройка Проекта

### 1. Клонирование Репозитория

Клонируйте репозиторий проекта с помощью Git:

```bash
git clone https://github.com/Ivan881914/menu_project.git
cd menu_project
```

### 2. Установка Зависимостей через Composer

Установите все необходимые зависимости с помощью Composer:

```bash
composer install
```

### 3. Настройка Базы Данных PostgreSQL

1. **Создайте базу данных и пользователя**:

```sql
CREATE DATABASE your_database_name;
CREATE USER your_db_user WITH PASSWORD 'your_db_password';
GRANT ALL PRIVILEGES ON DATABASE your_database_name TO your_db_user;
```

2. **Примените SQL-скрипт для создания таблицы**:

```bash
psql -U your_db_user -d your_database_name -f sql/schema.sql
```

### 4. Настройка Подключения к Базе Данных

1. Откройте файл `config.php` и замените значения `'your_database_name'`, `'your_db_user'`, и `'your_db_password'` на свои реальные данные:

```php
<?php
// config.php

return [
    'dbname' => 'your_database_name',
    'user' => 'your_db_user',
    'password' => 'your_db_password',
    'host' => 'localhost',
    'driver' => 'pdo_pgsql',
];
```

## Импорт и Экспорт Данных

### 1. Импорт Категорий из `categories.json`

Выполните импорт:

```bash
php src/import.php
```

### 2. Экспорт Категорий

- **Экспорт в `type_a.txt` (полная иерархия с URL)**:

  ```bash
  php src/export_a.php
  ```

- **Экспорт в `type_b.txt` (не более первого уровня вложенности)**:

  ```bash
  php src/export_b.php
  ```

После выполнения этих команд файлы `type_a.txt` и `type_b.txt` будут созданы в каталоге `exports/`.

## Просмотр Списка Меню

Чтобы посмотреть список категорий в веб-интерфейсе:

### Запуск Встроенного PHP-Сервера

```bash
php -S localhost:8000 -t src/
```

Откройте браузер и перейдите по адресу [http://localhost:8000/list_menu.php](http://localhost:8000/list_menu.php).

## Скрипты и Команды

- **Импорт Категорий**: `php src/import.php`
- **Экспорт Категорий в type_a.txt**: `php src/export_a.php`
- **Экспорт Категорий в type_b.txt**: `php src/export_b.php`
- **Запуск Встроенного PHP-Сервера**: `php -S localhost:8000 -t src/`
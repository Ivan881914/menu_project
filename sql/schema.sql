-- psql -U your_db_user -d menu_db -f sql/schema.sql

CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    alias VARCHAR(255) NOT NULL,
    parent_id INTEGER REFERENCES categories(id) ON DELETE CASCADE
);

-- Индекс для быстрого поиска по parent_id
CREATE INDEX idx_parent_id ON categories(parent_id);

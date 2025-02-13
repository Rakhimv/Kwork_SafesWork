CREATE DATABASE auth_db;

USE auth_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL
);

INSERT INTO users (login, password, role) VALUES
('lg', '1111', 'user'),
('admin', '1111', 'admin');


CREATE TABLE safes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image TEXT,
    number VARCHAR(255),
    name VARCHAR(255),
    description TEXT,
    workshop VARCHAR(255),
    year INT,
    place VARCHAR(255),
    material VARCHAR(255),
    author VARCHAR(255),
    size VARCHAR(255),
    casting VARCHAR(255)
);

ALTER TABLE safes MODIFY COLUMN image LONGTEXT;

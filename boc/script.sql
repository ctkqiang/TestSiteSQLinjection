CREATE DATABASE BANKOFCHINA;

USE BANKOFCHINA;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Insert some dummy users (with plaintext passwords, for demo purposes)
INSERT INTO users (username, password) VALUES ('admin', 'password123');
INSERT INTO users (username, password) VALUES ('user', 'password456');

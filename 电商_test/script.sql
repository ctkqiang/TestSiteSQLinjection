CREATE DATABASE IF NOT EXISTS shopdb;
USE shopdb;

-- 商品表
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    price DECIMAL(10,2)
);

-- 订单表
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255),
    address VARCHAR(255),
    product_id INT
);

-- 插入20件商品
INSERT INTO products (name, price) VALUES 
('超仙连衣裙', 199.00),
('洛丽塔裙', 299.00),
('复古小香风', 399.00),
('汉元素改良', 259.00),
('古风耳环', 89.00),
('樱花和服', 329.00),
('仙气飘飘衬衫', 159.00),
('国潮卫衣', 199.00),
('小众设计包包', 499.00),
('软妹长袜', 49.00),
('梦幻蝴蝶结鞋', 399.00),
('奶油裙子', 289.00),
('水手服套装', 219.00),
('古风扇子', 59.00),
('森系毛衣', 189.00),
('发簪一套', 129.00),
('珍珠项链', 149.00),
('Lolita鞋子', 359.00),
('宫廷复古裙', 429.00),
('软萌兔子包', 239.00);

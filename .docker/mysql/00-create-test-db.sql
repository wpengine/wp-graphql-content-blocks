-- Create test database for PHPUnit tests
CREATE DATABASE IF NOT EXISTS wordpress_unit_test;
GRANT ALL PRIVILEGES ON wordpress_unit_test.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON wordpress_unit_test.* TO 'wordpress'@'%';
FLUSH PRIVILEGES;

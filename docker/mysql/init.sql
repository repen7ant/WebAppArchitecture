CREATE DATABASE IF NOT EXISTS boardy_laravel
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS boardy_api
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL ON boardy_laravel.* TO 'boardy'@'%';
GRANT ALL ON boardy_api.* TO 'boardy'@'%';
FLUSH PRIVILEGES;

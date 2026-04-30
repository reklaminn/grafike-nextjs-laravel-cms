-- Grafike CMS — MariaDB Initialization
-- Runs once when the container is first created (docker-entrypoint-initdb.d).

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Ensure the central database uses the correct charset.
ALTER DATABASE IF EXISTS grafike_main
    CHARACTER SET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

-- Grant the app user the ability to CREATE and DROP databases.
-- stancl/tenancy's MySQLDatabaseManager issues:
--   CREATE DATABASE `tenant_<slug>` CHARACTER SET utf8mb4 ...
--   DROP   DATABASE `tenant_<slug>`
-- when provisioning / deleting tenants.
-- Without this grant the provisioning step throws a 1044 Access denied error.
GRANT ALL PRIVILEGES ON `tenant_%`.* TO '${DB_USERNAME}'@'%';
GRANT CREATE, DROP     ON *.* TO '${DB_USERNAME}'@'%';
FLUSH PRIVILEGES;

#!/bin/bash
# docker-entrypoint-initdb.d/init.sh
# Runs once when the MariaDB container is first created.
# Environment variables (MARIADB_USER, MARIADB_DATABASE) are injected by the
# official mariadb Docker image before this script executes.

set -e

# Ensure the central database uses utf8mb4.
mysql -u root -p"${MARIADB_ROOT_PASSWORD}" <<-EOSQL
    ALTER DATABASE \`${MARIADB_DATABASE}\`
        CHARACTER SET  = utf8mb4
        COLLATE        = utf8mb4_unicode_ci;

    -- stancl/tenancy MySQLDatabaseManager runs:
    --   CREATE DATABASE \`tenant_<slug>\` CHARACTER SET utf8mb4 ...
    --   DROP   DATABASE \`tenant_<slug>\`
    -- when provisioning / deleting a tenant.
    -- The app user needs CREATE/DROP on global scope and full access
    -- to any database whose name starts with "tenant_".
    GRANT ALL PRIVILEGES ON \`tenant_%\`.* TO '${MARIADB_USER}'@'%';
    GRANT CREATE, DROP    ON *.*           TO '${MARIADB_USER}'@'%';
    FLUSH PRIVILEGES;
EOSQL

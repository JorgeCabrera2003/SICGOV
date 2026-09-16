#!/bin/bash
set -e

DB_SYS="${DB_NAME_SYSTEM:-goobv-sistema}"
DB_SEC="${DB_NAME_USER:-goobv-usuarios}"

echo "=== Inicializando bases de datos $DB_SYS y $DB_SEC ==="

mariadb -u root -p"$MYSQL_ROOT_PASSWORD" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS \`$DB_SYS\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE DATABASE IF NOT EXISTS \`$DB_SEC\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOSQL

echo "=== Importando esquema de $DB_SYS ==="
sed "s/{{DB_SYSTEM}}/$DB_SYS/g; s/{{DB_SECURITY}}/$DB_SEC/g" /migrations/goobv-sistema.sql | mariadb -u root -p"$MYSQL_ROOT_PASSWORD" "$DB_SYS"

echo "=== Importando esquema de $DB_SEC ==="
sed "s/{{DB_SYSTEM}}/$DB_SYS/g; s/{{DB_SECURITY}}/$DB_SEC/g" /migrations/goobv-usuarios.sql | mariadb -u root -p"$MYSQL_ROOT_PASSWORD" "$DB_SEC"

echo "=== Inicialización de bases de datos finalizada exitosamente ==="

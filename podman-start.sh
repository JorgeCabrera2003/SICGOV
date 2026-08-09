#!/bin/bash

# Salir si ocurre un error
set -e

echo "🚀 Iniciando entorno SICGOV con Podman..."

# Comprobar si el pod ya existe
if podman pod exists sicgov-pod; then
    echo "⚠️  El pod 'sicgov-pod' ya existe. Deteniéndolo y recreándolo..."
    podman pod stop sicgov-pod || true
    podman pod rm sicgov-pod || true
fi

echo "📦 Creando Pod (sicgov-pod) y exponiendo puerto 8080..."
podman pod create --name sicgov-pod -p 8095:80

echo "🗄️  Levantando base de datos (MariaDB)..."
podman run -d \
    --pod sicgov-pod \
    --name sicgov-db \
    -e MYSQL_ROOT_PASSWORD=root \
    docker.io/library/mariadb:10.11

echo "⏳ Esperando 5 segundos a que la base de datos inicie..."
sleep 5

echo "🏗️  Construyendo imagen de la aplicación (esto puede tardar la primera vez)..."
podman build -t sicgov-app .

echo "🌐 Levantando la aplicación web..."
podman run -d \
    --pod sicgov-pod \
    --name sicgov-web \
    -v .:/var/www/html:z \
    sicgov-app

# Instalar dependencias de composer dentro del contenedor
echo "📦 Instalando dependencias de Composer..."
podman exec -it sicgov-web composer install

echo "✅ Entorno iniciado con éxito."
echo "👉 Aplicación disponible en: http://localhost:8095"
echo "👉 Para inicializar la base de datos, ejecuta: ./setup-db.sh"

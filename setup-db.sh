#!/bin/bash

echo "⚙️  Ejecutando el setup de la base de datos dentro del contenedor..."
echo "Te hará algunas preguntas (escribe 'y' y presiona enter)..."

podman exec -it sicgov-web php database/setup.php

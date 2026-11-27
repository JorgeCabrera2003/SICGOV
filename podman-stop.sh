#!/bin/bash

echo "🛑 Deteniendo y eliminando el entorno SICGOV..."

if podman pod exists sicgov-pod; then
    podman pod stop sicgov-pod
    podman pod rm sicgov-pod
    echo "✅ Entorno detenido y limpiado correctamente."
else
    echo "⚠️  El pod 'sicgov-pod' no existe o ya fue eliminado."
fi

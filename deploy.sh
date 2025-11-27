#!/bin/bash

# Script de Despliegue Automático para Trans Velasco
# Ejecutar en el servidor: ./deploy.sh

echo "🚀 Iniciando despliegue..."

# 1. Obtener los últimos cambios del repositorio
echo "📥 Descargando cambios desde GitHub..."
git pull origin main

# 2. Instalar dependencias de PHP
echo "📦 Instalando dependencias de PHP..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# 3. Instalar dependencias de Node.js y compilar assets
if [ -d "public/build" ]; then
    echo "✅ Assets ya compilados encontrados en public/build. Saltando compilación."
else
    echo "🎨 Compilando assets (Vite)..."
    if command -v npm &> /dev/null; then
        npm install
        npm run build
    else
        echo "⚠️ Node.js/npm no encontrado y no hay assets pre-compilados."
    fi
fi

# 4. Ejecutar migraciones de base de datos
echo "🗄️ Omitiendo migraciones (Base de datos ya configurada)..."
# php artisan migrate --force

# 5. Limpiar y recachear configuración
echo "🧹 Limpiando caché..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "⚙️ Optimizando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Asegurar permisos (ajustar según el usuario del servidor web, usualmente www-data o apache)
echo "🔒 Ajustando permisos..."
chmod -R 775 storage bootstrap/cache
# chown -R $USER:www-data storage bootstrap/cache # Descomentar si es necesario y tienes permisos sudo

# 7. Crear enlace simbólico para assets si no existe (fix para subdirectorio)
if [ ! -L "public/assets" ]; then
    echo "🔗 Creando enlace simbólico para assets..."
    cd public
    ln -s build/assets assets
    cd ..
fi

echo "✅ ¡Despliegue completado exitosamente!"

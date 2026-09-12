# Guia de Crontab y Modulo de Backup — SICGOV

## Entorno de referencia

| Componente | Version / Valor |
|---|---|
| Sistema Operativo | Fedora Linux 42 (Workstation Edition) |
| PHP | 8.4.21 (CLI) |
| PHP binario | `/usr/bin/php` |
| Script de backup | `/home/gobdesarrollo/proyectos/SICGOV/cron_backup.php` |
| Tarea activa en crontab | `57 10 * * * php /home/gobdesarrollo/proyectos/SICGOV/cron_backup.php` |

---

## Que es el crontab

El crontab es el mecanismo del sistema operativo Linux para programar la ejecucion automatica de comandos o scripts en intervalos de tiempo definidos. Cada usuario del sistema tiene su propio crontab, almacenado en `/var/spool/cron/` bajo el nombre de su usuario.

Las tareas registradas son persistentes: sobreviven reinicios del sistema y permanecen activas indefinidamente hasta que el usuario las elimine manualmente.

---

## Sintaxis de una linea CRON

```
MINUTO  HORA  DIA_MES  MES  DIA_SEMANA  COMANDO
```

| Campo | Rango | Descripcion |
|---|---|---|
| MINUTO | 0 - 59 | Minuto de ejecucion |
| HORA | 0 - 23 | Hora en formato 24h |
| DIA_MES | 1 - 31 | Dia del mes |
| MES | 1 - 12 | Mes del ano |
| DIA_SEMANA | 0 - 6 | 0 = Domingo, 6 = Sabado |

Ejemplos practicos:

```bash
# Diario a las 10:57 AM
57 10 * * * php /ruta/al/cron_backup.php

# Semanal cada domingo a las 3:00 AM
0 3 * * 0 php /ruta/al/cron_backup.php

# Mensual el dia 1 a las 2:30 AM
30 2 1 * * php /ruta/al/cron_backup.php
```

---

## Como interactua SICGOV con el crontab

El sistema SICGOV gestiona el crontab de forma automatica desde la interfaz web. Cuando el usuario guarda una nueva programacion en el modal "Programacion del Respaldo Automatico", el `BackupController.php` ejecuta internamente la siguiente logica:

1. Lee la lista de tareas actualmente registradas en el crontab del usuario (`crontab -l`).
2. Elimina cualquier linea anterior que apunte al script `cron_backup.php` del proyecto.
3. Agrega la nueva linea con la expresion CRON calculada a partir de los campos del formulario.
4. Instala el crontab actualizado (`crontab <archivo_temporal>`).

Esto significa que **el usuario nunca necesita abrir una terminal para gestionar el crontab** mientras opere dentro del mismo servidor y el mismo usuario del sistema operativo.

---

## Como verificar el crontab activo

Abrir una terminal y ejecutar:

```bash
crontab -l
```

Salida esperada (ejemplo):

```
57 10 * * * php /home/gobdesarrollo/proyectos/SICGOV/cron_backup.php
```

Si el resultado es `no crontab for <usuario>`, significa que no hay ninguna tarea programada y es necesario instalarla.

---

## Instalacion manual del crontab

Usar este metodo unicamente si:
- Se migra el proyecto a un nuevo servidor.
- Se reinstala el sistema operativo.
- Se crea un nuevo usuario del sistema.
- El crontab fue eliminado accidentalmente con `crontab -r`.

### Metodo 1 — Desde la interfaz del sistema (recomendado)

1. Iniciar el servidor PHP del proyecto.
2. Acceder al sistema SICGOV en el navegador.
3. Ir a **Seguridad y Auditoria > Control de Acceso > Centro de Respaldos**.
4. Hacer clic en el boton **Programacion**.
5. Configurar la frecuencia y la hora deseadas.
6. Hacer clic en **Guardar Programacion**.

El sistema instalara automaticamente la tarea en el crontab del sistema operativo.

### Metodo 2 — Desde la terminal (manual)

Abrir una terminal y ejecutar:

```bash
crontab -e
```

Esto abre el editor de crontab del usuario. Agregar la siguiente linea al final del archivo (ajustar hora y minuto segun se necesite):

```
57 10 * * * /usr/bin/php /home/gobdesarrollo/proyectos/SICGOV/cron_backup.php
```

> [!IMPORTANT]
> Siempre usar la ruta absoluta del binario PHP (`/usr/bin/php`) en el crontab, no el comando corto `php`. El entorno de cron no tiene las mismas variables de entorno que la terminal interactiva y puede no encontrar el binario si se usa la ruta relativa.

Guardar y cerrar el editor. Verificar con `crontab -l`.

### Metodo 3 — Inyeccion directa desde terminal (sin editor)

```bash
(crontab -l 2>/dev/null; echo "57 10 * * * /usr/bin/php /home/gobdesarrollo/proyectos/SICGOV/cron_backup.php") | crontab -
```

Este comando agrega la linea sin abrir ningun editor. Verificar con `crontab -l`.

---

## Compatibilidad del script cron_backup.php

### Dependencias del script

| Dependencia | Como se resuelve |
|---|---|
| PHP 8.0 o superior en CLI | Verificar con `php --version` |
| Archivo `.env` en la raiz del proyecto | Debe existir con `DB_HOST`, `DB_USER`, `DB_PASS` |
| Acceso a `mysqldump` | Nativo, o via Podman/Docker (auto-detectado) |
| Directorio `storage/backups/` con permisos de escritura | Se crea automaticamente si no existe |
| Directorio `logs/` con permisos de escritura | Se crea automaticamente si no existe |

### Deteccion automatica de mysqldump

El script `BackupHelper.php` busca el ejecutable `mysqldump` en el siguiente orden:

1. `mysqldump` (PATH del sistema)
2. `/usr/bin/mysqldump`
3. `/usr/local/bin/mysqldump`
4. `/opt/lampp/bin/mysqldump` (XAMPP)
5. `/Applications/MAMP/Library/bin/mysqldump` (MAMP en macOS)
6. `podman exec codex_mysql_dev mysqldump` (contenedor Podman)
7. `docker exec codex_mysql_dev mysqldump` (contenedor Docker)

Si ninguna opcion es encontrada, el sistema devuelve un error descriptivo al usuario en la interfaz web.

### Instalacion de mysql-client en Fedora (si no esta disponible)

```bash
sudo dnf install -y mariadb
```

Verificar:

```bash
which mysqldump
mysqldump --version
```

### Instalacion de mysql-client en Debian / Ubuntu

```bash
sudo apt install -y default-mysql-client
```

---

## Verificar que el cron se ejecuto correctamente

El script `cron_backup.php` escribe un log detallado en:

```
/home/gobdesarrollo/proyectos/SICGOV/logs/cron_backup.log
```

Para ver las ultimas ejecuciones:

```bash
tail -50 /home/gobdesarrollo/proyectos/SICGOV/logs/cron_backup.log
```

Salida esperada de una ejecucion exitosa:

```
[2026-06-30 10:57:00] SICGOV - Respaldo Automatico Iniciado
[2026-06-30 10:57:00] Frecuencia configurada: diario
[2026-06-30 10:57:00] Fecha/Hora: 2026-06-30 10:57:00
[2026-06-30 10:57:00] Iniciando respaldo de: 'goobv-sistema'
[2026-06-30 10:57:02] Respaldo exitoso: backup_goobv-sistema_2026-06-30_105700.sql (63.96 KB)
[2026-06-30 10:57:02] Iniciando respaldo de: 'goobv-usuarios'
[2026-06-30 10:57:03] Respaldo exitoso: backup_goobv-usuarios_2026-06-30_105702.sql (34.10 KB)
[2026-06-30 10:57:03] RESUMEN:
[2026-06-30 10:57:03]   goobv-sistema -> backup_goobv-sistema_2026-06-30_105700.sql
[2026-06-30 10:57:03]   goobv-usuarios -> backup_goobv-usuarios_2026-06-30_105702.sql
[2026-06-30 10:57:03] Script finalizado con codigo: 0
[2026-06-30 10:57:03] ----------------------------------------
```

Un `codigo: 0` indica ejecucion exitosa. Cualquier valor diferente indica un error.

---

## Probar el script manualmente

Antes de esperar la ejecucion automatica, se puede probar el script desde la terminal:

```bash
php /home/gobdesarrollo/proyectos/SICGOV/cron_backup.php
```

Si genera los archivos `.sql` en `storage/backups/` y el log muestra `codigo: 0`, el cron funcionara correctamente cuando se ejecute de forma automatica.

---

## Migracion a un nuevo servidor

Al migrar el proyecto a un servidor nuevo, seguir estos pasos en orden:

1. Copiar el proyecto al nuevo servidor.
2. Configurar el archivo `.env` con las credenciales de la nueva base de datos.
3. Verificar que PHP y `mysqldump` esten disponibles en el nuevo servidor.
4. Probar el script manualmente: `php cron_backup.php`
5. Instalar el crontab usando el Metodo 1 (interfaz web) o el Metodo 2 (terminal).
6. Verificar con `crontab -l` que la tarea quedo registrada.

---

## Eliminacion del crontab

Para eliminar unicamente la tarea de backup sin borrar otras tareas del crontab:

```bash
crontab -l | grep -v "cron_backup.php" | crontab -
```

Para eliminar todas las tareas del crontab del usuario actual (usar con precaucion):

```bash
crontab -r
```

> [!CAUTION]
> El comando `crontab -r` elimina todas las tareas programadas del usuario sin confirmacion. No hay forma de deshacer esta accion.

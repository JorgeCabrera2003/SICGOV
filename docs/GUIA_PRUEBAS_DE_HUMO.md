# Guía y Reporte de Pruebas de Humo (Smoke Tests) - SICGOV

## 1. Introducción y Propósito

Las **Pruebas de Humo (Smoke Testing)** constituyen la primera línea de defensa en el ciclo de aseguramiento de calidad (QA) y despliegue del sistema **SICGOV**. Su objetivo no es verificar cada cálculo minucioso ni casos de esquina, sino **comprobar de forma ultrarrápida que los componentes vitales del sistema estén operativos** y que la aplicación no sufra un fallo catastrófico al iniciar.

Si una sola prueba de humo falla, se considera que el sistema "está en llamas" y se debe abortar cualquier intento de pase a producción o prueba funcional exhaustiva.

---

## 2. Las Tres Reglas de Oro Aplicadas

1. **Velocidad Extrema (< 1 segundo):** La suite completa de humo se ejecuta en menos de **400 milisegundos**, lo que permite correrla antes de cada commit o en pipelines CI/CD automáticos.
2. **Enfoque de Alto Nivel (Rutas Críticas):** Valida únicamente que los servicios clave (bases de datos, autenticación, autorización y auditoría) respondan con éxito.
3. **Aislamiento e Inocuidad:** Trabaja contra bases de datos dedicadas (`goobv-sistema-test` y `goobv-usuarios-test`) y utiliza mecanismos de limpieza automática (`tearDown`), garantizando que los datos de desarrollo o producción jamás se alteren ni se ensucien con registros residuales.

---

## 3. Arquitectura y Componentes Implementados

### 3.1. Base de Datos de Pruebas Dedicada
Se creó el script de aprovisionamiento desatendido [`database/setup_test_db.php`](file:///c:/xampp/htdocs/SICGOV/database/setup_test_db.php) que:
* Crea desde cero las bases de datos `goobv-sistema-test` y `goobv-usuarios-test`.
* Aplica las migraciones DDL con resolución de claves foráneas entre ambas bases de datos.
* Ejecuta los seeders base (`BusinessSeeder`, `SecuritySeeder`, `PermisosSeeder`) con datos de prueba estandarizados.

### 3.2. Configuración en `phpunit.xml`
El archivo [`phpunit.xml`](file:///c:/xampp/htdocs/SICGOV/phpunit.xml) se configuró para orquestar la suite de pruebas separada:
```xml
<testsuites>
    <testsuite name="Smoke">
        <directory>tests/Smoke</directory>
    </testsuite>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
</testsuites>
<php>
    <env name="APP_ENV" value="testing" force="true"/>
    <env name="DB_NAME_SYSTEM" value="goobv-sistema-test" force="true"/>
    <env name="DB_NAME_USER" value="goobv-usuarios-test" force="true"/>
</php>
```

---

## 4. Lógica de las Pruebas Implementadas

### A. Salud de la Infraestructura (`tests/Smoke/InfraestructuraSmokeTest.php`)
| Prueba | Lógica y Objetivo |
| :--- | :--- |
| `test_variables_de_entorno_criticas_cargadas` | Comprueba que `DB_HOST`, `DB_USER`, `DB_NAME_SYSTEM` y `DB_NAME_USER` existan y que apunten a los esquemas de pruebas. |
| `test_directorios_esenciales_existen_y_tienen_permisos` | Verifica la presencia de `/logs`, `/config` y `/public`, y confirma permisos de escritura en `/logs`. |
| `test_conexion_base_datos_sistema_activa` | Ejecuta un ping `SELECT 1` directo a `goobv-sistema-test` vía PDO. |
| `test_conexion_base_datos_seguridad_activa` | Ejecuta un ping `SELECT 1` directo a `goobv-usuarios-test` vía PDO. |
| `test_tablas_criticas_existen_en_ambas_bases_de_datos` | Inspecciona el catálogo mediante `SHOW TABLES` validando tablas vitales (`persona`, `producto`, `mesa`, `usuario`, `rol`, `modulo`, `permiso`, `bitacora`). |

### B. Seguridad y Control de Acceso (`tests/Smoke/SeguridadSmokeTest.php`)
| Prueba | Lógica y Objetivo |
| :--- | :--- |
| `test_autenticacion_exitosa_con_credenciales_validas` | Simula la petición `'sesion'` con la cédula del administrador (`V-00000000`) y clave válida (`1234`), confirmando respuesta HTTP 200 y validación positiva. |
| `test_autenticacion_rechaza_credenciales_invalidas` | Verifica que contraseñas erróneas sean rechazadas inmediatamente con HTTP 401 y `verificacion: false`. |
| `test_sesion_activa_permite_acceso_sin_errores` | Inicializa `$_SESSION['user']` y ejecuta `Helper::verificarSesion()`, validando que el flujo continúe sin desvíos ni excepciones. |
| `test_carga_de_matriz_de_permisos_por_rol` | Ejecuta `Helper::TraerPermisos('cliente')` con el rol SuperUsuario y verifica que retorne la estructura con los permisos `ver`, `registrar`, `modificar` y `eliminar`. |
| `test_hashes_de_claves_en_base_de_datos_son_validos` | Inspecciona las contraseñas en la base de datos de usuarios asegurando que utilicen algoritmos criptográficos válidos reconocidos por PHP (`password_get_info`). |

### C. Auditoría y Registro en Bitácora (`tests/Smoke/BitacoraSmokeTest.php`)
| Prueba | Lógica y Objetivo |
| :--- | :--- |
| `test_registro_exitoso_de_accion_en_bitacora` | Invoca `Helper::Bitacora()` enviando acción, módulo, detalle, y estados anterior/nuevo en formato array. Verifica la inserción en la base de datos y la conversión a JSON. |
| `test_bitacora_retorna_false_si_no_hay_usuario_en_sesion` | Comprueba que ante peticiones sin sesión, la bitácora devuelva `false` de forma silenciosa sin interrumpir la ejecución ni arrojar errores de base de datos. |
| `test_modelo_bitacora_puede_listar_eventos` | Valida que el método de consulta `listar` del modelo `Bitacora` devuelva un arreglo válido de auditoría. |

---

## 5. Guía de Replicación y Ejecución

### Paso 1: Asegurar Servicios Activos
Asegúrate de que el servicio de base de datos MySQL/MariaDB de XAMPP esté en ejecución (puerto 3306).

### Paso 2: Aprovisionar Base de Datos de Prueba (Automático)
Ejecuta el script desatendido desde la raíz del proyecto para crear y sembrar las bases de datos de test:
```bash
php database/setup_test_db.php
```

### Paso 3: Ejecución de las Pruebas

#### Correr únicamente las Pruebas de Humo (Recomendado para pre-commit / despliegue rápido):
```bash
vendor/bin/phpunit --testsuite Smoke
```
*O usando PHP directamente en Windows:*
```powershell
php vendor/phpunit/phpunit/phpunit --testsuite Smoke
```

#### Correr únicamente las Pruebas Unitarias tradicionales:
```bash
vendor/bin/phpunit --testsuite Unit
```

#### Correr toda la batería de pruebas (Humo + Unitarias):
```bash
vendor/bin/phpunit
```

---

## 6. Informe Detallado de Resultados

* **Fecha de Ejecución:** Noviembre 2026
* **Motor de Pruebas:** PHPUnit 11.5.57
* **Entorno:** PHP 8.2.12 (CLI) / MariaDB 10.4.32

```
PHPUnit 11.5.57 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.2.12
Configuration: C:\xampp\htdocs\SICGOV\phpunit.xml

.............                                                     13 / 13 (100%)

Time: 00:00.377, Memory: 10.00 MB

OK (13 tests, 52 assertions)
```

### Resumen de Cobertura de Humo
| Módulo / Dominio | Archivo | Pruebas | Aserciones | Tiempo | Estado |
| :--- | :--- | :---: | :---: | :---: | :---: |
| **Infraestructura (Health Checks)** | `InfraestructuraSmokeTest.php` | 5 | 24 | ~35 ms | **PASÓ (100%)** |
| **Seguridad y Control de Acceso** | `SeguridadSmokeTest.php` | 5 | 18 | ~170 ms | **PASÓ (100%)** |
| **Auditoría (Bitácora)** | `BitacoraSmokeTest.php` | 3 | 10 | ~95 ms | **PASÓ (100%)** |
| **TOTAL SMOKE SUITE** | | **13** | **52** | **~377 ms** | **100% OPERATIVO** |

El sistema se encuentra en un estado saludable y preparado para despliegues continuos seguros.


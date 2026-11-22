# Manual Integral y Guía Maestra de Pruebas Unitarias 2.0: PHPUnit en SICGOV

Esta **Guía Maestra 2.0** expande y profundiza los fundamentos de pruebas unitarias con **PHPUnit 11**. Incluye la explicación detallada de cada línea de código, la semántica de namespaces y declaraciones `use`, el debate arquitectónico **DRY vs DAMP** (`setUp()` vs Métodos Fábrica), y un tutorial completo para **instalar y configurar PHPUnit desde cero en cualquier proyecto PHP**.

---

## ÍNDICE GENERAL
1. [Instalación y Configuración de PHPUnit desde Cero (Cualquier Proyecto)](#1-instalación-y-configuración-de-phpunit-desde-cero-cualquier-proyecto)
2. [Desglose Anatómico: Explicación Línea por Línea de Encabezados y Namespaces](#2-desglose-anatómico-explicación-línea-por-línea-de-encabezados-y-namespaces)
3. [El Debate de Arquitectura de Pruebas: DRY vs DAMP (`setUp()` vs Métodos Fábrica)](#3-el-debate-de-arquitectura-de-pruebas-dry-vs-damp-setup-vs-métodos-fábrica)
4. [Estructura de Directorios y Convenciones de Nombres](#4-estructura-de-directorios-y-convenciones-de-nombres)
5. [Arquitectura de Aislamiento: Mocks de PDO y Simulación de Base de Datos](#5-arquitectura-de-aislamiento-mocks-de-pdo-y-simulación-de-base-de-datos)
6. [Sintaxis y Semántica: El Ciclo AAA (Arrange, Act, Assert)](#6-sintaxis-y-semántica-el-ciclo-aaa-arrange-act-assert)
7. [Catálogo Completo de Aserciones de PHPUnit](#7-catálogo-completo-de-aserciones-de-phpunit)
8. [Matriz de Pruebas por Mérito (5 Escenarios Obligatorios)](#8-matriz-de-pruebas-por-mérito-5-escenarios-obligatorios)
9. [Guía de Comandos de Ejecución y Opciones Avanzadas](#9-guía-de-comandos-de-ejecución-y-opciones-avanzadas)
10. [Cómo Interpretar Resultados y Resolver Errores de Consola](#10-cómo-interpretar-resultados-y-resolver-errores-de-consola)
11. [Plantilla Boilerplate Maestra para Nuevos Módulos](#11-plantilla-boilerplate-maestra-para-nuevos-módulos)

---

## 1. Instalación y Configuración de PHPUnit desde Cero (Cualquier Proyecto)

Si deseas implementar este mismo sistema de pruebas en cualquier otro proyecto PHP (sea un MVC propio, un microservicio o una API), sigue estos pasos:

### Paso 1: Verificar Requisitos de PHP
PHPUnit 11 requiere **PHP 8.2 o PHP 8.3**. Asegúrate de que las extensiones necesarias estén habilitadas en tu `php.ini`:
```ini
extension=zip
extension=mbstring
extension=pdo
extension=pdo_mysql
```

### Paso 2: Inicializar Composer e Instalar PHPUnit
En la terminal, dentro de la raíz del proyecto, ejecuta:
```bash
composer require --dev phpunit/phpunit:^11.5
```
*El parámetro `--dev` asegura que PHPUnit se guarde en `require-dev` de tu `composer.json`, evitando que viaje a servidores de producción cuando compiles para despliegue final.*

### Paso 3: Configurar el Autoloading PSR-4 en `composer.json`
Abre `composer.json` y define el mapeo de namespaces:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    }
}
```

Luego de editar el archivo, regenera el autoloader de Composer:
```bash
composer dump-autoload
```

### Paso 4: Crear la Estructura de Carpetas
Crea las siguientes carpetas en la raíz:
```text
mkdir -p tests/Unit
mkdir -p tests/Feature
```

### Paso 5: Crear el Archivo de Configuración `phpunit.xml`
En la raíz del proyecto, crea `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         bootstrap="tests/bootstrap.php"
         colors="true"
         stopOnFailure="false"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

* **`bootstrap`**: Archivo PHP que se ejecuta antes de cualquier prueba.
* **`colors="true"`**: Habilita colores ANSI en la terminal (Verde = Pasó, Rojo = Falló).
* **`stopOnFailure="false"`**: Permite correr toda la suite sin detenerse en el primer error.
* **`cacheDirectory`**: Almacena el resultado para optimizar ejecuciones subsiguientes.

### Paso 6: Crear el Archivo `tests/bootstrap.php`
Crea `tests/bootstrap.php` para cargar el autoloader y definir constantes del proyecto:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Definir constantes de rutas si tu framework las utiliza
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
```

### Paso 7: Comprobar la Instalación
Ejecuta en consola:
```bash
php vendor/bin/phpunit --version
```
*Deberás ver: `PHPUnit 11.x.x by Sebastian Bergmann and contributors.`*

---

## 2. Desglose Anatómico: Explicación Línea por Línea de Encabezados y Namespaces

Al abrir cualquier clase de prueba en SICGOV (como `tests/Unit/NoticiaTest.php`), encontramos el siguiente bloque:

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\Security\Noticia;
use PDO;
use PDOStatement;
use PDOException;
use Exception;
```

¿Qué es, qué hace y por qué es obligatoria cada una de estas líneas?

---

### 2.1 `namespace Tests\Unit;`
* **¿Qué es?**  
  Un espacio de nombres (*namespace*) es el mecanismo que tiene PHP para organizar clases en paquetes lógicos y evitar colisiones de nombres (por ejemplo, si tienes dos clases llamadas `Noticia`).
* **¿Qué hace aquí?**  
  Le indica al motor de PHP y al autoloader PSR-4 de Composer que la clase que se definirá en este archivo (`NoticiaTest`) pertenece al namespace `Tests\Unit`.
* **Regla de oro:**  
  Debe coincidir con la ruta del archivo relativa a la carpeta raíz definida en `composer.json`. Como `Tests\` apunta a `tests/`, la ruta `tests/Unit/NoticiaTest.php` **debe** tener obligatoriamente `namespace Tests\Unit;`.

---

### 2.2 `use PHPUnit\Framework\TestCase;`
* **¿Qué es?**  
  La clase maestra del framework PHPUnit de la cual deben heredar **todas** las clases de prueba (`class NoticiaTest extends TestCase`).
* **¿Qué hace aquí?**  
  Nos brinda acceso a:
  1. Todos los métodos de aserción: `$this->assertEquals()`, `$this->assertTrue()`, `$this->assertCount()`, etc.
  2. Métodos de creación de dobles de prueba: `$this->createMock()`, `$this->getMockBuilder()`.
  3. Ganchos de ciclo de vida: `setUp()`, `tearDown()`, `setUpBeforeClass()`.

---

### 2.3 `use PHPUnit\Framework\MockObject\MockObject;`
* **¿Qué es?**  
  Una interfaz proporcionada por PHPUnit que identifica a los objetos simulados (*Mocks*).
* **¿Qué hace aquí?**  
  Se utiliza para el tipado estricto (*Type Hinting*) en PHP 8. Cuando escribimos:
  ```php
  private function createNoticiaMock(PDO&MockObject $mockPdo): Noticia&MockObject
  ```
  La sintaxis `PDO&MockObject` es un **Intersection Type** de PHP 8.1+. Le dice a PHP: *"Este parámetro debe ser un objeto que sea una instancia de PDO Y AL MISMO TIEMPO implemente la interfaz MockObject de PHPUnit"*. Esto garantiza que el autocompletado del IDE reconozca tanto los métodos de PDO (`prepare`, `beginTransaction`) como los de PHPUnit (`expects`, `method`, `willReturn`).

---

### 2.4 `use App\Models\Security\Noticia;`
* **¿Qué es?**  
  La importación de la clase del sistema que estamos evaluando (en testing se le conoce como **SUT**: *System Under Test*).
* **¿Qué hace aquí?**  
  Permite instanciar directamente `new Noticia()` o pasar `Noticia::class` al constructor de mocks, en lugar de tener que escribir el nombre completamente calificado `\App\Models\Security\Noticia` cada vez.

---

### 2.5 `use PDO;` y `use PDOStatement;`
* **¿Qué son?**  
  Clases nativas del núcleo de PHP para la gestión de bases de datos relacionales (*PHP Data Objects*).
* **¿Por qué son necesarias si son nativas de PHP?**  
  Porque este archivo tiene declarado un `namespace Tests\Unit;`. Si estás dentro de un namespace y escribes `new PDO()`, PHP buscará la clase `Tests\Unit\PDO`. Al declarar `use PDO;`, le indicamos explícitamente a PHP que nos referimos al `\PDO` del espacio de nombres global.
* **¿Qué hace `PDOStatement`?**  
  Representa una sentencia preparada (`$stmt`). Al mockear consultas (`SELECT`, `INSERT`), necesitamos mockear tanto la conexión (`PDO`) como la sentencia que ejecuta la consulta (`PDOStatement`).

---

### 2.6 `use PDOException;`
* **¿Qué es?**  
  La excepción nativa que arroja el driver de base de datos cuando una consulta SQL falla (por ejemplo: error de sintaxis, clave foránea violada o duplicado de clave única).
* **¿Qué hace aquí?**  
  Nos permite simular fallos de base de datos en las pruebas unitarias:
  ```php
  $mockStmt->method('execute')->willThrowException(new PDOException("Error SQL"));
  ```
  Esto permite probar que el modelo capture el error, ejecute `rollBack()` y responda con HTTP 500 sin caerse.

---

### 2.7 `use Exception;`
* **¿Qué es?**  
  La clase base de excepciones de PHP.
* **¿Qué hace aquí?**  
  Nuestros modelos lanzan `new Exception("Mensaje de error")` cuando una validación de formato (cédula, título, fechas) no se cumple. Importar `Exception` nos permite capturarla en los bloques `try / catch` o verificarla con `$this->expectException(Exception::class)`.

---

## 3. El Debate de Arquitectura de Pruebas: DRY vs DAMP (`setUp()` vs Métodos Fábrica)

Cuando tu compañero Leizer comentó:
> *"Eso lo pueden resumir en un setUp. Por ejemplo, el objeto al que se le va a hacer la prueba se le puede dejar como un atributo a la clase de prueba, incluidas las configuraciones, para no repetir esas secuencias de código"*

Hizo una observación teóricamente válida, pero que en la práctica del Unit Testing profesional requiere un análisis profundo entre dos filosofías de diseño:

```
┌───────────────────────────────────────┐       ┌───────────────────────────────────────┐
│        PRINCIPIO "DRY"                │       │        PRINCIPIO "DAMP"               │
│   (Don't Repeat Yourself)             │  VS   │ (Descriptive And Meaningful Phrases)  │
│                                       │       │                                       │
│ • Menos líneas de código.             │       │ • Mayor claridad y legibilidad.       │
│ • Centraliza la creación en setUp().  │       │ • Cada prueba es autocontenida (AAA). │
│ • Puede crear acoplamiento oculto.    │       │ • Control granular sobre cada mock.   │
└───────────────────────────────────────┘       └───────────────────────────────────────┘
```

### 3.1 El Antipatrón del "Mystery Guest" (Huésped Misterioso)
Martin Fowler y Gerard Meszaros (creador de *xUnit Test Patterns*) advierten contra el abuso de `setUp()` para instanciar objetos con Mocks complejos:
* Si en `setUp()` creas `$this->noticiaMock = ...` y preconfiguras un `$this->mockPdo`, la prueba se vuelve dependiente de un estado invisible que está arriba.
* Cuando una prueba falla, el desarrollador tiene que hacer scroll hacia arriba para investigar qué configuró el `setUp()`.
* **Una prueba unitaria debe leerse como un libro de principio a fin:** el *Arrange* debe estar visible en la misma prueba.

### 3.2 ¿Por qué el Método Fábrica (`createNoticiaMock`) es más potente en SICGOV?

#### Razón 1: No todas las pruebas tocan la base de datos
En `NoticiaTest.php`:
* `test_setters_con_datos_vacios_lanzan_excepcion()` prueba validaciones de dominio puro.
* `test_setters_con_datos_incorrectos_fallan()` prueba expresiones regulares.
Ambas usan `new Noticia();` simple. Si usamos `setUp()`, PHPUnit estaría construyendo mocks pesados de PDO para pruebas que no los necesitan.

#### Razón 2: Cada prueba tiene un plan de ejecución SQL diferente
Observa cómo varía la expectativa de la base de datos entre pruebas:

* **En Registrar Noticia Simple:**
  Se espera **1 sola llamada** a `prepare()` para insertar la noticia.
* **En Registrar Noticia con Galería:**
  Se esperan **3 llamadas consecutivas a `prepare()`** con distintos parámetros:
  ```php
  $mockPdo->expects($this->exactly(3))
      ->method('prepare')
      ->willReturnOnConsecutiveCalls($stmtNoticia, $stmtReset, $stmtImagen);
  ```
* **En Eliminar Noticia:**
  Se esperan **2 llamadas a `beginTransaction()`** (una dentro de `ValidarNoticia` y otra en `EliminarNoticia`).

Si el mock de `$mockPdo` estuviera fijado en un `setUp()`, redefinir estas expectativas dinámicas en cada test causaría conflictos y advertencias de PHPUnit. Con el método fábrica:
```php
$noticiaMock = $this->createNoticiaMock($mockPdo);
```
Inyectas el PDO con las expectativas exactas que esa prueba particular necesita.

#### Razón 3: Consistencia con todo el equipo en `dev`
Todas las pruebas existentes en la rama `dev`:
* `MenuTest.php` ➔ `createMenuMock()`
* `ClienteTest.php` ➔ `createClienteMock()`
* `EmpleadoTest.php` ➔ `createEmpleadoMock()`
* `UsuarioTest.php` ➔ `createUsuarioMock()`
* `CategoriaProductoTest.php` ➔ `createCategoriaMock()`

Todas usan el patrón del método fábrica. Mantenerlo en `NoticiaTest` y `ReservacionTest` respeta la homogeneidad del repositorio.

---

## 4. Estructura de Directorios y Convenciones de Nombres

```text
SICGOV/
├── app/
│   ├── Models/
│   │   ├── Security/Noticia.php
│   │   └── System/Reservacion.php
├── tests/
│   ├── bootstrap.php
│   └── Unit/
│       ├── NoticiaTest.php
│       └── ReservacionTest.php
├── phpunit.xml
```

### Reglas de Nomenclatura:
1. **Archivo de Prueba:** `[NombreDelModelo]Test.php` (Ej: `NoticiaTest.php`).
2. **Clase de Prueba:** `class [NombreDelModelo]Test extends TestCase`.
3. **Métodos de Prueba:** Prefijo `test_` seguido de snake_case descriptivo:
   `test_<metodo>_<escenario>_<resultado_esperado>()`

---

## 5. Arquitectura de Aislamiento: Mocks de PDO y Simulación de Base de Datos

En SICGOV, los modelos invocan la base de datos a través de dos métodos heredados de `App\Core\Database`:
* `$this->LlamarConexion()`
* `$this->DestruirConexion()`

Para aislar el modelo, creamos un **Mock Parcial** usando `getMockBuilder()` y `onlyMethods()`:

```php
private function createNoticiaMock(PDO&MockObject $mockPdo): Noticia&MockObject
{
    $mock = $this->getMockBuilder(Noticia::class)
        ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
        ->getMock();

    $mock->method('LlamarConexion')->willReturn($mockPdo);
    $mock->method('DestruirConexion')->willReturn(null);

    return $mock;
}
```

### Tabla de Equivalencias de Simulación SQL:

| Lo que hace el código en producción | Cómo lo simulamos en la prueba |
| :--- | :--- |
| `$pdo->beginTransaction()` | `$mockPdo->expects($this->once())->method('beginTransaction');` |
| `$pdo->commit()` | `$mockPdo->expects($this->once())->method('commit');` |
| `$pdo->rollBack()` | `$mockPdo->expects($this->once())->method('rollBack');` |
| `$pdo->prepare("SELECT...")` | `$mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);` |
| `$stmt->execute()` exitoso | `$mockStmt->expects($this->once())->method('execute')->willReturn(true);` |
| `$stmt->execute()` falla con error SQL | `$mockStmt->method('execute')->willThrowException(new PDOException("Error"));` |
| `$stmt->fetch(PDO::FETCH_ASSOC)` | `$mockStmt->method('fetch')->willReturn(['id' => '1', 'titulo' => 'Hola']);` |
| `$stmt->fetchAll(PDO::FETCH_ASSOC)` | `$mockStmt->method('fetchAll')->willReturn([['id' => '1'], ['id' => '2']]);` |
| `$stmt->rowCount()` | `$mockStmt->method('rowCount')->willReturn(1);` |
| `$stmt->fetchColumn()` (ej: COUNT) | `$mockStmt->method('fetchColumn')->willReturn(0);` |

---

## 6. Sintaxis y Semántica: El Ciclo AAA (Arrange, Act, Assert)

Toda prueba debe estructurarse obligatoriamente en tres fases:

```php
public function test_modificar_noticia_exitoso(): void
{
    // 1. ARRANGE (Preparar dependencias, datos y expectativas)
    $mockPdo = $this->createMock(PDO::class);
    $mockStmt = $this->createMock(PDOStatement::class);

    $mockPdo->expects($this->once())->method('beginTransaction');
    $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
    $mockStmt->expects($this->once())->method('execute')->willReturn(true);
    $mockPdo->expects($this->once())->method('commit');

    $noticiaMock = $this->createNoticiaMock($mockPdo);
    $this->poblarNoticiaValida($noticiaMock);
    $noticiaMock->setTitulo('Título de Noticia Actualizado');

    // 2. ACT (Ejecutar el método que queremos probar)
    $resultado = $noticiaMock->Transaccion(['peticion' => 'modificar']);

    // 3. ASSERT (Verificar que los resultados coincidan exactamente con lo esperado)
    $this->assertEquals(1, $resultado['estado']);
    $this->assertEquals(200, $resultado['response']['resultado']);
    $this->assertEquals('La noticia ha sido modificada', $resultado['response']['mensaje']);
}
```

---

## 7. Catálogo Completo de Aserciones de PHPUnit

| Aserción | Propósito | Ejemplo en SICGOV |
| :--- | :--- | :--- |
| `assertEquals($esperado, $actual)` | Compara igualdad de valor | `$this->assertEquals(200, $res['response']['resultado']);` |
| `assertSame($esperado, $actual)` | Compara igualdad estricta de tipo y valor | `$this->assertSame(1, $res['estado']);` |
| `assertTrue($condicion)` | Verifica que sea estrictamente `true` | `$this->assertTrue($evento['editable']);` |
| `assertFalse($condicion)` | Verifica que sea estrictamente `false` | `$this->assertFalse($res['success']);` |
| `assertCount($num, $array)` | Verifica la cantidad de elementos en un arreglo | `$this->assertCount(2, $clientes);` |
| `assertNotEmpty($valor)` | Verifica que una variable no esté vacía | `$this->assertNotEmpty($res['response']['registro']);` |
| `assertStringContainsString($sub, $str)` | Verifica que un texto contenga una frase | `$this->assertStringContainsString('fechas pasadas', $msg);` |
| `expectException(Class::class)` | Declara que el código siguiente debe lanzar excepción | `$this->expectException(Exception::class);` |
| `expectExceptionMessage('...')` | Verifica el mensaje exacto de la excepción lanzada | `$this->expectExceptionMessage('El contenido es obligatorio.');` |

---

## 8. Matriz de Pruebas por Mérito (5 Escenarios Obligatorios)

Para ilustrar mañana que las pruebas pasan por **méritos reales**:

### Escenario 1: Entradas Vacías
* **Objetivo:** Probar que los campos obligatorios rechacen valores vacíos o espacios en blanco.
* **Comportamiento probado:** El setter arroja una excepción descriptiva antes de llegar a la base de datos.

### Escenario 2: Formatos Inválidos y Límites
* **Objetivo:** Probar que el modelo rechace datos corruptos o maliciosos.
* **Comportamiento probado:**
  * Título menor a 3 caracteres o con etiquetas `<script>` es rechazado.
  * Cédulas con letras (`V-ABC999`) son rechazadas.
  * Horas fuera de reloj (`25:00` o `12:65`) son rechazadas.
  * Fechas inexistentes en el calendario (`2026-02-31`) son rechazadas con `checkdate`.

### Escenario 3: Reglas de Negocio Complejas
* **Objetivo:** Probar la lógica propia del negocio.
* **Comportamiento probado:**
  * **Fechas pasadas:** Si la fecha es anterior a hoy y no está cancelada, responde HTTP `400`.
  * **Horas invertidas:** Si `hora_fin <= hora`, responde HTTP `400`.
  * **Capacidad de Mesa:** Si la mesa es para 2 y se solicitan 6 comensales, responde HTTP `400` con mensaje detallado.
  * **Solapamiento:** Si la mesa ya está reservada en ese horario, responde HTTP `409 Conflict`.

### Escenario 4: Camino Feliz (Persistencia Exitosa)
* **Objetivo:** Probar que los datos válidos se procesen correctamente.
* **Comportamiento probado:** Se prepara el SQL, se ejecuta la transacción completa y se ejecuta `commit()`. Devuelve estado `200` y `success`.

### Escenario 5: Resiliencia ante Fallos en Base de Datos
* **Objetivo:** Probar la tolerancia a fallos.
* **Comportamiento probado:** Si el servidor SQL arroja una `PDOException`, el modelo comprueba `inTransaction()`, ejecuta `rollBack()` y devuelve un estado `500` controlado sin tumbar el sistema.

---

## 9. Guía de Comandos de Ejecución y Opciones Avanzadas

Ejecutar desde la raíz del proyecto en PowerShell o CMD:

### 1. Ejecución completa con resumen de puntos:
```powershell
php vendor/bin/phpunit
```

### 2. Ejecución con especificaciones en lenguaje natural (Modo `--testdox`):
```powershell
php vendor/bin/phpunit --testdox
```
*(Muestra cada método como una frase explicativa legible para el jurado/evaluador).*

### 3. Ejecutar únicamente el módulo de Noticias:
```powershell
php vendor/bin/phpunit tests/Unit/NoticiaTest.php --testdox
```

### 4. Ejecutar únicamente el módulo de Reservaciones:
```powershell
php vendor/bin/phpunit tests/Unit/ReservacionTest.php --testdox
```

### 5. Filtrar por una prueba específica:
```powershell
php vendor/bin/phpunit --filter test_validar_reservacion_solapamiento_mesa_retorna_409
```

### 6. Detener la ejecución en el primer fallo:
```powershell
php vendor/bin/phpunit --stop-on-failure
```

---

## 10. Cómo Interpretar Resultados y Resolver Errores de Consola

### 10.1 Simbología de la Barra de Progreso
* `.` (Punto Verde): Prueba exitosa con todas sus aserciones aprobadas.
* `F` (Failure Rojo): Una aserción falló (`assertEquals` esperaba un valor y llegó otro).
* `E` (Error Amarillo/Rojo): Error de PHP no capturado (ej. llamar a un método que no existe o error fatal).
* `S` (Skipped): Prueba omitida intencionalmente.

### 10.2 Diagnóstico de Fallos Comunes:

#### Caso 1: `Failed asserting that 200 matches expected 400`
* **Causa:** El modelo permitió continuar cuando debía rechazar la operación.
* **Solución:** Revisa las validaciones en el modelo antes de ejecutar el SQL.

#### Caso 2: `Call to undefined method ...`
* **Causa:** El método no existe en la clase o tiene un error de tipeo.
* **Solución:** Verifica la visibilidad (`public`) y el nombre exacto del método.

#### Caso 3: `There is no active transaction`
* **Causa:** El modelo llamó a `$this->LlamarConexion()->rollBack()` sin verificar si había una transacción abierta.
* **Solución:** Protege la llamada con `if ($this->LlamarConexion()->inTransaction()) { $this->LlamarConexion()->rollBack(); }`.

---

## 11. Plantilla Boilerplate Maestra para Nuevos Módulos

Copia y pega esta plantilla para implementar pruebas en cualquier modelo nuevo de SICGOV:

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\MiModulo;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class MiModuloTest extends TestCase
{
    /**
     * Fábrica de aislamiento para el modelo.
     */
    private function createModuloMock(PDO&MockObject $mockPdo): MiModulo&MockObject
    {
        $mock = $this->getMockBuilder(MiModulo::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * 1. Prueba de validación de campos vacíos.
     */
    public function test_campos_obligatorios_vacios_lanzan_excepcion(): void
    {
        $modelo = new MiModulo();

        $this->expectException(Exception::class);
        $modelo->setNombre('');
    }

    /**
     * 2. Prueba de camino feliz (persistencia exitosa).
     */
    public function test_registrar_con_datos_correctos_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');

        $mockModelo = $this->createModuloMock($mockPdo);
        $mockModelo->setId('MODU20261022120000001');
        $mockModelo->setNombre('Nombre Válido');

        $res = $mockModelo->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(200, $res['response']['resultado']);
    }

    /**
     * 3. Prueba de tolerancia a fallos con Rollback.
     */
    public function test_fallo_en_bd_ejecuta_rollback(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willThrowException(new PDOException("Error SQL"));

        $mockPdo->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('rollBack');

        $mockModelo = $this->createModuloMock($mockPdo);
        $mockModelo->setId('MODU20261022120000001');
        $mockModelo->setNombre('Nombre Válido');

        $res = $mockModelo->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(500, $res['response']['resultado']);
    }
}
```

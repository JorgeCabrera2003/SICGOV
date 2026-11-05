# Manual Integral y Guía de Buenas Prácticas: Pruebas Unitarias con PHPUnit en SICGOV

Este documento constituye la guía definitiva para comprender, diseñar, implementar y ejecutar pruebas unitarias bajo **PHPUnit 11** en el sistema **SICGOV**. 

Está redactada con un enfoque paso a paso para que cualquier desarrollador o evaluador pueda replicar el proceso, entender la sintaxis, estructurar nuevas clases de prueba y validar los módulos por **méritos reales de calidad y arquitectura de software**.

---

## 1. Filosofía de Pruebas: ¿Por qué y Cómo Probamos en SICGOV?

### El Principio de "Pruebas por Mérito"
Un sistema de pruebas no debe diseñarse para "engañar" al evaluador o pasar artificialmente. Las pruebas deben ser **exigentes y transparentes**. Si una prueba falla porque el modelo acepta fechas inválidas, horas incoherentes o inyecciones de caracteres, **el error no está en la prueba, está en el sistema**. La regla de oro es:

> **Si una prueba revela una debilidad, se mejora el código del sistema hasta que cumpla el estándar más alto.**

### Pruebas Unitarias vs Pruebas de Integración
* **Prueba de Integración:** Conecta todos los componentes reales (servidor web, Apache, base de datos MySQL viva, red). Es lenta, pesada y si la base de datos no tiene datos idénticos en cada máquina, falla.
* **Prueba Unitaria (Unit Testing):** Aísla una única unidad de código (un método, una clase, una regla de negocio). **No toca la base de datos real**. Se simula el comportamiento de la base de datos usando **Test Doubles (Mocks)**.
* **Ventajas del Unit Testing:**
  1. Se ejecutan cientos de pruebas en **menos de 5 segundos**.
  2. Son **100% deterministas**: pasan en Windows, Linux, Mac o GitHub Actions sin configurar MySQL.
  3. Prueban casos extremos (errores fatales de SQL, duplicados, caídas de red) que en una BD real serían difíciles o destructivos de reproducir.

---

## 2. Requisitos Previos y Configuración del Entorno

### 2.1 Requisitos del Sistema
* **PHP:** Versión `8.1` o superior (el proyecto SICGOV opera con PHP 8.3 CLI).
* **Extensión PHP Zip:** Obligatoria en `php.ini` (`extension=zip`) para que Composer descargue paquetes.
* **Composer:** Versión 2.x instalada globalmente.

### 2.2 Dependencias en `composer.json`
En la raíz del proyecto, el archivo `composer.json` define las dependencias del framework de pruebas:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "App\\Database\\Seeders\\": "database/seeders/"
        },
        "files": [
            "config/modulo_sistema.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "phpunit/phpunit": "^11.5"
    }
}
```

* `"autoload-dev"`: Registra el namespace `Tests\` mapeado a la carpeta `tests/`. Toda clase que creemos dentro de `tests/Unit/` comenzará con el namespace `namespace Tests\Unit;`.
* `phpunit/phpunit`: Instala el motor de pruebas en `./vendor/bin/phpunit`.

### 2.3 Archivo de Configuración `phpunit.xml`
Ubicado en la raíz del proyecto, le indica a PHPUnit cómo debe comportarse:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         bootstrap="tests/bootstrap.php"
         colors="true">
    <testsuites>
        <testsuite name="Tests">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

* `bootstrap="tests/bootstrap.php"`: Archivo que se ejecuta antes de cualquier prueba.
* `colors="true"`: Colorea la salida de la consola (verde para éxito, rojo para errores).
* `<directory>tests</directory>`: Escanea recursivamente la carpeta `tests` buscando clases terminadas en `Test.php`.

### 2.4 Archivo de Arranque `tests/bootstrap.php`
Define el entorno mínimo que necesita SICGOV para no generar advertencias de constantes:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
```

---

## 3. Estructura de Carpetas y Convenciones de Nombres

```text
SICGOV/
│
├── app/
│   ├── Models/
│   │   ├── Security/
│   │   │   └── Noticia.php          <-- Código a probar
│   │   └── System/
│   │       └── Reservacion.php      <-- Código a probar
│
├── tests/
│   ├── bootstrap.php                <-- Inicializador
│   └── Unit/                        <-- Directorio de Pruebas Unitarias
│       ├── NoticiaTest.php          <-- Suite de Noticia (14 tests)
│       ├── ReservacionTest.php      <-- Suite de Reservacion (19 tests)
│       ├── MenuTest.php             <-- Suite de Menú
│       ├── ClienteTest.php          <-- Suite de Clientes
│       ├── UsuarioTest.php          <-- Suite de Usuarios
│       └── CategoriaProductoTest.php<-- Suite de Categorías
│
└── phpunit.xml                      <-- Configuración
```

### Reglas Estrictas de Nomenclatura

1. **Nombre del Archivo de Prueba:**  
   Debe coincidir con la clase que se prueba, agregando el sufijo `Test.php`.
   - Modelo: `Reservacion.php` ➔ Archivo de prueba: `ReservacionTest.php`.
   - Modelo: `Noticia.php` ➔ Archivo de prueba: `NoticiaTest.php`.

2. **Nombre de la Clase de Prueba:**  
   Debe coincidir exactamente con el nombre del archivo y heredar de `PHPUnit\Framework\TestCase`.
   ```php
   namespace Tests\Unit;

   use PHPUnit\Framework\TestCase;

   class ReservacionTest extends TestCase
   {
       // ...
   }
   ```

3. **Nombre de los Métodos de Prueba:**  
   Deben tener el prefijo `test_` y seguir el estándar semántico descriptivo:
   ```text
   test_<accion_o_metodo>_<escenario_o_condicion>_<resultado_esperado>()
   ```
   *Ejemplos claros y autoexplicativos:*
   - `test_setters_con_datos_vacios_lanzan_excepcion()`
   - `test_validar_reservacion_con_fecha_pasada_retorna_400()`
   - `test_validar_reservacion_solapamiento_mesa_retorna_409()`
   - `test_registrar_noticia_sin_imagenes_exitoso()`

---

## 4. Arquitectura de Aislamiento: El Patrón Mock de Base de Datos

En SICGOV, los modelos heredan de `App\Core\Database` y ejecutan consultas mediante PDO llamando a:
```php
$this->LlamarConexion(); // Devuelve una instancia de PDO
$this->DestruirConexion(); // Libera la conexión
```

Para probar la lógica del modelo sin conectarnos a MySQL, creamos un **Mock Parcial** del modelo, donde reemplazamos únicamente esos dos métodos por un objeto simulado (`MockObject`) de `PDO`.

### Cómo crear el Mock en la clase de prueba:

```php
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Reservacion;
use PDO;
use PDOStatement;

class ReservacionTest extends TestCase
{
    private function createReservacionMock(PDO&MockObject $mockPdo): Reservacion&MockObject
    {
        $mock = $this->getMockBuilder(Reservacion::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        // Cuando el modelo llame a LlamarConexion, devolverá nuestro PDO falso
        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }
}
```

### Configuración de Expectativas en los Mocks de PDO y PDOStatement:

| Escenario que queremos simular | Código en la prueba |
| :--- | :--- |
| **Inicio de transacción** | `$mockPdo->expects($this->once())->method('beginTransaction');` |
| **Confirmación de transacción (commit)** | `$mockPdo->expects($this->once())->method('commit');` |
| **Reversión por error (rollback)** | `$mockPdo->expects($this->once())->method('rollBack');` |
| **Preparar consulta SQL** | `$mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);` |
| **Ejecución exitosa de SQL** | `$mockStmt->expects($this->once())->method('execute')->willReturn(true);` |
| **Fallo de base de datos (Excepción)** | `$mockStmt->expects($this->once())->method('execute')->willThrowException(new PDOException("Error"));` |
| **Filas devueltas (rowCount)** | `$mockStmt->expects($this->once())->method('rowCount')->willReturn(1);` |
| **Registro individual (fetch)** | `$mockStmt->expects($this->once())->method('fetch')->willReturn(['id' => '1', 'nombre' => 'Test']);` |
| **Listado de registros (fetchAll)** | `$mockStmt->expects($this->once())->method('fetchAll')->willReturn([...]);` |
| **Conteo directo (fetchColumn)** | `$mockStmt->expects($this->once())->method('fetchColumn')->willReturn(0);` |

---

## 5. Sintaxis y Semántica: El Patrón AAA (Arrange - Act - Assert)

Cada prueba unitaria debe estructurarse obligatoriamente en tres etapas consecutivas:

```php
public function test_modificar_reservacion_exitoso(): void
{
    // =========================================================================
    // 1. ARRANGE (Preparar el escenario)
    // =========================================================================
    // Creamos los mocks de PDO y PDOStatement
    $mockPdo = $this->createMock(PDO::class);
    $mockStmtUpdate = $this->createMock(PDOStatement::class);
    $mockStmtDelMesa = $this->createMock(PDOStatement::class);
    $mockStmtInsMesa = $this->createMock(PDOStatement::class);

    // Definimos qué debe ocurrir paso a paso
    $mockPdo->expects($this->once())->method('beginTransaction');
    $mockPdo->expects($this->exactly(3))
        ->method('prepare')
        ->willReturnOnConsecutiveCalls($mockStmtUpdate, $mockStmtDelMesa, $mockStmtInsMesa);

    $mockStmtUpdate->expects($this->once())->method('execute')->willReturn(true);
    $mockStmtDelMesa->expects($this->once())->method('execute')->willReturn(true);
    $mockStmtInsMesa->expects($this->once())->method('execute')->willReturn(true);
    $mockPdo->expects($this->once())->method('commit');

    // Instanciamos el mock del modelo e inyectamos los datos
    $reservacionMock = $this->createReservacionMock($mockPdo);
    $this->poblarReservacionValida($reservacionMock);
    $reservacionMock->setEstado('CONFIRMADA');

    // =========================================================================
    // 2. ACT (Ejecutar la acción a probar)
    // =========================================================================
    $resultado = $reservacionMock->Transaccion(['peticion' => 'modificar']);

    // =========================================================================
    // 3. ASSERT (Verificar que los resultados coincidan con lo esperado)
    // =========================================================================
    $this->assertEquals(1, $resultado['estado']);
    $this->assertEquals(200, $resultado['response']['resultado']);
    $this->assertEquals('Reservación actualizada exitosamente', $resultado['response']['mensaje']);
}
```

---

## 6. Catálogo de Aserciones Principales de PHPUnit

PHPUnit dispone de aserciones semánticas para validar cualquier resultado:

### 6.1 Igualdad y Tipos
* `$this->assertEquals($esperado, $actual)`: Compara igualdad de valores (`200 == '200'`).
* `$this->assertSame($esperado, $actual)`: Compara identidad estricta de tipo y valor (`200 === 200`).
* `$this->assertNotEquals($noEsperado, $actual)`: Comprueba que el valor no sea igual.

### 6.2 Booleanos y Nulos
* `$this->assertTrue($condicion)`: Asegura que el valor sea estrictamente `true`.
* `$this->assertFalse($condicion)`: Asegura que el valor sea estrictamente `false`.
* `$this->assertNull($valor)`: Asegura que el valor sea `null`.
* `$this->assertNotNull($valor)`: Asegura que no sea `null`.

### 6.3 Cadenas y Textos
* `$this->assertStringContainsString($subcadena, $textoCompleto)`: Verifica que un texto contenga una palabra o frase (ideal para mensajes de error o descripciones).
* `$this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $cadena)`: Compara contra una expresión regular.

### 6.4 Arreglos y Conteo
* `$this->assertCount(3, $array)`: Verifica que el arreglo tenga exactamente 3 elementos.
* `$this->assertArrayHasKey('response', $resultado)`: Verifica que exista una llave asociativa.
* `$this->assertNotEmpty($array)`: Verifica que el arreglo no esté vacío.

### 6.5 Manejo de Excepciones
Para probar que el sistema **rechaza entradas malas lanzando una excepción**, se puede hacer de dos formas:

#### Opción A (Recomendada cuando se evalúa el mensaje específico con `try/catch`):
```php
try {
    $noticia->setTitulo('');
    $this->fail("Se esperaba una Exception porque el título no puede estar vacío.");
} catch (Exception $e) {
    $this->assertStringContainsString("El título contiene caracteres no permitidos", $e->getMessage());
}
```
*Si el código no lanza la excepción, `$this->fail()` hace fallar la prueba inmediatamente.*

#### Opción B (Aserción declarativa):
```php
$this->expectException(Exception::class);
$this->expectExceptionMessage("El contenido de la noticia es obligatorio.");

$noticia->setContenido('   ');
```

---

## 7. La Matriz de Calidad en 5 Escenarios

Cada módulo que agregues a SICGOV debe probarse bajo estos 5 escenarios:

```
┌────────────────────────────────────────────────────────┐
│             MATRIZ DE CALIDAD POR MÉRITOS              │
├────────────────────────────────────────────────────────┤
│ 1. Entradas Vacías       ➔ Rechazo con Excepción       │
│ 2. Formatos Inválidos    ➔ Rechazo por Regex / Longitud│
│ 3. Reglas de Negocio     ➔ 400 / 404 / 409 Conflict    │
│ 4. Camino Feliz          ➔ Status 200 + Commit SQL     │
│ 5. Error en Base Datos   ➔ Status 500 + Rollback SQL   │
└────────────────────────────────────────────────────────┘
```

### Ejemplo Práctico de Reglas de Negocio en Reservaciones:

1. **Bloqueo de Fechas Pasadas:**
   - Si la fecha es `2020-01-01` y el estado no es `CANCELADA`, responde `400` ("No se pueden realizar ni modificar reservaciones en fechas pasadas").
2. **Coherencia de Rango Horario:**
   - Si `hora_fin` es `18:00` y `hora` es `19:00`, responde `400` ("La hora de fin debe ser posterior a la hora de inicio").
3. **Capacidad de Mesas:**
   - Si la mesa tiene capacidad para `2` personas y se solicitan `6` comensales, responde `400` con mensaje descriptivo ("La Mesa #X tiene capacidad para 2 personas, pero la reservación es para 6 comensales").
4. **Solapamiento de Horarios (Conflicto 409):**
   - Si ya existe una reserva activa para esa mesa entre esas horas, responde `409` ("La mesa seleccionada ya se encuentra reservada en el horario especificado").

---

## 8. Guía de Comandos para la Terminal

Todos los comandos se ejecutan desde la raíz del proyecto en PowerShell o CMD:

### 8.1 Ejecución Completa
Ejecuta todas las pruebas del proyecto:
```powershell
php vendor/bin/phpunit
```
*Salida típica:*
```text
PHPUnit 11.5.57 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.3.30
Configuration: C:\xampp\htdocs\SICGOV\phpunit.xml

................................................................. 65 / 78 ( 83%)
.............                                                     78 / 78 (100%)

Time: 00:05.970, Memory: 12.00 MB

OK (78 tests, 432 assertions)
```

### 8.2 Ejecución con Modo Descriptivo (`--testdox`)
Convierte los nombres de métodos `test_algo()` en oraciones legibles en lenguaje natural. **Es la opción perfecta para proyectar en presentaciones**:
```powershell
php vendor/bin/phpunit --testdox
```

### 8.3 Ejecutar Únicamente una Suite de Pruebas

#### Módulo de Noticias:
```powershell
php vendor/bin/phpunit tests/Unit/NoticiaTest.php --testdox
```

#### Módulo de Reservaciones:
```powershell
php vendor/bin/phpunit tests/Unit/ReservacionTest.php --testdox
```

### 8.4 Ejecutar una Prueba Específica por Filtro (`--filter`)
Si deseas correr únicamente la prueba de solapamiento de mesas:
```powershell
php vendor/bin/phpunit --filter test_validar_reservacion_solapamiento_mesa_retorna_409
```

### 8.5 Detener la Ejecución al Primer Fallo (`--stop-on-failure`)
Útil durante el desarrollo de una funcionalidad nueva:
```powershell
php vendor/bin/phpunit --stop-on-failure
```

---

## 9. Cómo Interpretar los Resultados en Consola

### 9.1 La Línea de Progreso
* Cada punto `.` representa **una prueba aprobada con éxito**.
* Una letra `F` (Failure) significa que una **aserción falló** (el código devolvió algo distinto a lo esperado).
* Una letra `E` (Error) significa que hubo un **error fatal de PHP** o una excepción no capturada.
* Una letra `S` (Skipped) significa una prueba omitida intencionalmente.

### 9.2 Lectura de un Fallo (Failure vs Error)

#### Ejemplo de Fallo (Failure):
```text
1) Tests\Unit\ReservacionTest::test_validar_reservacion_con_rango_horario_invalido_retorna_400
Failed asserting that 200 matches expected 400.

C:\xampp\htdocs\SICGOV\tests\Unit\ReservacionTest.php:190
```
* **Interpretación:** En la línea 190 del test, la prueba esperaba que el resultado fuera `400`, pero el modelo devolvió `200`. El modelo no está validando que la hora de fin sea mayor a la de inicio.

#### Ejemplo de Error (Error):
```text
1) Tests\Unit\NoticiaTest::test_algo
Error: Call to undefined method App\Models\Security\Noticia::metodoInexistente()
```
* **Interpretación:** Error de sintaxis o método no declarado en la clase del modelo.

---

## 10. Plantilla Base para Probar Nuevos Módulos

Usa esta plantilla para crear nuevas pruebas en el sistema (ej. `InsumoTest.php`, `PagoTest.php`):

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\MiModulo; // Reemplazar por tu modelo
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class MiModuloTest extends TestCase
{
    /**
     * Mock de conexión a BD
     */
    private function createModelMock(PDO&MockObject $mockPdo): MiModulo&MockObject
    {
        $mock = $this->getMockBuilder(MiModulo::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * 1. Prueba de validación de datos vacíos
     */
    public function test_campos_obligatorios_vacios_lanzan_excepcion(): void
    {
        $modelo = new MiModulo();

        $this->expectException(Exception::class);
        $modelo->setNombre('');
    }

    /**
     * 2. Prueba de camino feliz (registro exitoso)
     */
    public function test_registrar_con_datos_correctos_guarda_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');

        $mockModelo = $this->createModelMock($mockPdo);
        $mockModelo->setId('MODU20261022120000001');
        $mockModelo->setNombre('Nombre Válido');

        $res = $mockModelo->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(200, $res['response']['resultado']);
    }

    /**
     * 3. Prueba de error en BD con Rollback
     */
    public function test_error_en_bd_aplica_rollback(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willThrowException(new PDOException("Error SQL"));

        $mockPdo->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('rollBack');

        $mockModelo = $this->createModelMock($mockPdo);
        $mockModelo->setId('MODU20261022120000001');
        $mockModelo->setNombre('Nombre Válido');

        $res = $mockModelo->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(500, $res['response']['resultado']);
    }
}
```

---

## 11. Recomendaciones para la Exposición / Ilustración de Mañana

1. **Abre la consola en la raíz de SICGOV:**
   ```powershell
   cd c:\xampp\htdocs\SICGOV
   ```
2. **Ejecuta primero la suite con formato descriptivo:**
   ```powershell
   php vendor/bin/phpunit tests/Unit/ReservacionTest.php --testdox
   ```
   *Explica que no son simples "aserciones ciegas", sino especificaciones de negocio vivas: capacidad de mesas, prevención de colisiones en la misma mesa, validación de fechas pasadas y generación para FullCalendar.*

3. **Ejecuta la suite de Noticias:**
   ```powershell
   php vendor/bin/phpunit tests/Unit/NoticiaTest.php --testdox
   ```
   *Muestra cómo se valida la desvinculación de fotos, la selección de imagen principal, los filtros del blog público y la sanitización de títulos.*

4. **Ejecuta la suite global del sistema:**
   ```powershell
   php vendor/bin/phpunit
   ```
   *Destaca que **todas las 78 pruebas de todo el restaurante (menú, clientes, empleados, noticias, reservaciones, usuarios)** pasan en menos de 6 segundos de manera limpia, sin falsos positivos y sin tocar registros reales en la base de datos de producción.*

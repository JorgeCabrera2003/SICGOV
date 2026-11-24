<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Helpers\RegexHelper;

class RegexHelperTest extends TestCase
{
    #[Test]
    public function testValidarFormatoCedula(): void
    {
        $this->assertEquals(1, RegexHelper::ValidarFormatos('V-12345678', 'Cedula'));
        $this->assertEquals(1, RegexHelper::ValidarFormatos('E-87654321', 'Cedula'));
        $this->assertEquals(1, RegexHelper::ValidarFormatos('J-123456789', 'Cedula'));
        $this->assertEquals(0, RegexHelper::ValidarFormatos('12345678', 'Cedula')); // Sin prefijo
        $this->assertEquals(0, RegexHelper::ValidarFormatos('X-12345678', 'Cedula')); // Prefijo inválido
    }

    #[Test]
    public function testValidarFormatoID(): void
    {
        $this->assertEquals(1, RegexHelper::ValidarFormatos('CLIE0012026010112', 'ID'));
        $this->assertEquals(1, RegexHelper::ValidarFormatos('BITA0012026010112', 'ID'));
        $this->assertEquals(0, RegexHelper::ValidarFormatos('id-invalido', 'ID'));
    }

    #[Test]
    public function testValidarFormatoPersona(): void
    {
        $this->assertEquals(1, RegexHelper::ValidarFormatos('Jorge Cabrera', 'Persona'));
        $this->assertEquals(1, RegexHelper::ValidarFormatos('María Pérez', 'Persona'));
        $this->assertEquals(0, RegexHelper::ValidarFormatos('Jo', 'Persona')); // Menos de 3 chars
        $this->assertEquals(0, RegexHelper::ValidarFormatos('User123', 'Persona')); // Números no permitidos
    }

    #[Test]
    public function testValidarFormatoUsuario(): void
    {
        $this->assertEquals(1, RegexHelper::ValidarFormatos('admin_root', 'Usuario'));
        $this->assertEquals(1, RegexHelper::ValidarFormatos('user2026', 'Usuario'));
        $this->assertEquals(0, RegexHelper::ValidarFormatos('usr', 'Usuario')); // Menos de 4 chars
    }

    #[Test]
    public function testValidarFormatoCorreo(): void
    {
        $this->assertEquals(1, RegexHelper::ValidarFormatos('soporte@goodvibes.com', 'Correo'));
        $this->assertEquals(1, RegexHelper::ValidarFormatos('contacto.admin@gmail.com', 'Correo'));
        $this->assertEquals(0, RegexHelper::ValidarFormatos('correo_invalido', 'Correo'));
    }

    #[Test]
    public function testValidarFormatoTelefono(): void
    {
        $this->assertEquals(1, RegexHelper::ValidarFormatos('0414-1234567', 'Telefono'));
        $this->assertEquals(1, RegexHelper::ValidarFormatos('0251-7654321', 'Telefono'));
        $this->assertEquals(0, RegexHelper::ValidarFormatos('04141234567', 'Telefono')); // Sin guión
    }

    #[Test]
    public function testValidarFormatoSexo(): void
    {
        $this->assertEquals(1, RegexHelper::ValidarFormatos('M', 'Sexo'));
        $this->assertEquals(1, RegexHelper::ValidarFormatos('F', 'Sexo'));
        $this->assertEquals(0, RegexHelper::ValidarFormatos('X', 'Sexo'));
    }

    #[Test]
    public function testFormatoDecimal(): void
    {
        $this->assertEquals(12.345, RegexHelper::FormatoDecimal('12.3452'));
        $this->assertEquals(10.0, RegexHelper::FormatoDecimal(10));
        $this->assertEquals(0.0, RegexHelper::FormatoDecimal('no-numero'));
    }
}

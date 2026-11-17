<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Persona;
use Exception;

class PersonaTest extends TestCase
{
    private Persona $persona;

    protected function setUp(): void
    {
        parent::setUp();
        $this->persona = new Persona();
    }

    #[Test]
    public function testSetCedulaValida(): void
    {
        $this->persona->setCedula('v-12345678');
        $this->assertEquals('V-12345678', $this->persona->getCedula());

        $this->persona->setCedula('E-87654321');
        $this->assertEquals('E-87654321', $this->persona->getCedula());

        $this->persona->setCedula('J-123456789');
        $this->assertEquals('J-123456789', $this->persona->getCedula());
    }

    #[Test]
    public function testSetCedulaInvalida(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La cédula debe tener un prefijo válido');
        $this->persona->setCedula('X-12345678');
    }

    #[Test]
    public function testSetNombreValidoEInvalido(): void
    {
        $this->persona->setNombre('María José');
        $this->assertEquals('María José', $this->persona->getNombre());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El nombre debe tener al menos 2 caracteres.');
        $this->persona->setNombre('A');
    }

    #[Test]
    public function testSetNombreConNumerosLanzaExcepcion(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El nombre solo puede contener letras y espacios.');
        $this->persona->setNombre('Juan 123');
    }

    #[Test]
    public function testSetNombreConSimbolosLanzaExcepcion(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El nombre solo puede contener letras y espacios.');
        $this->persona->setNombre('Juan@Perez!');
    }

    #[Test]
    public function testSetApellidoValidoEInvalido(): void
    {
        $this->persona->setApellido('González Peña');
        $this->assertEquals('González Peña', $this->persona->getApellido());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El apellido solo puede contener letras y espacios.');
        $this->persona->setApellido('González123');
    }

    #[Test]
    public function testSetFechaNacimientoValidaEInvalida(): void
    {
        $this->persona->setFechaNacimiento('1995-05-15');
        $this->assertEquals('1995-05-15', $this->persona->getFechaNacimiento());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La fecha de nacimiento debe ser anterior a hoy.');
        $this->persona->setFechaNacimiento(date('Y-m-d', strtotime('+1 day')));
    }

    #[Test]
    public function testSetTelefonoValidoEInvalido(): void
    {
        $this->persona->setTelefono('04141234567');
        $this->assertEquals('0414-1234567', $this->persona->getTelefono());

        $this->persona->setTelefono('');
        $this->assertNull($this->persona->getTelefono());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El teléfono debe incluir el prefijo');
        $this->persona->setTelefono('12345');
    }

    #[Test]
    public function testSetTelefonoConLetrasOSimbolosLanzaExcepcion(): void
    {
        $this->expectException(Exception::class);
        $this->persona->setTelefono('0414-abc-1234');
    }

    #[Test]
    public function testSetCorreoValidoEInvalido(): void
    {
        $this->persona->setCorreo('test.user@goodvibes.com');
        $this->assertEquals('test.user@goodvibes.com', $this->persona->getCorreo());

        $this->persona->setCorreo('');
        $this->assertNull($this->persona->getCorreo());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El formato del correo electrónico no es válido.');
        $this->persona->setCorreo('correo-invalido');
    }

    #[Test]
    public function testSetDireccionValidaEInvalida(): void
    {
        $this->persona->setDireccion('Av. Los Leones, Barquisimeto');
        $this->assertEquals('Av. Los Leones, Barquisimeto', $this->persona->getDireccion());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La dirección debe tener al menos 3 caracteres.');
        $this->persona->setDireccion('Ab');
    }

    #[Test]
    public function testSetSexoValidoEInvalido(): void
    {
        $this->persona->setSexo('M');
        $this->assertEquals('M', $this->persona->getSexo());

        $this->persona->setSexo('F');
        $this->assertEquals('F', $this->persona->getSexo());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El sexo debe ser M (Masculino) o F (Femenino).');
        $this->persona->setSexo('X');
    }
}

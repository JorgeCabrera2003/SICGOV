<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Producto;

class ProductoTest extends TestCase
{
    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->producto = new Producto();
    }

    #[Test]
    public function testSettersYPropiedades(): void
    {
        $this->producto->setIdProducto('PROD0012026010112');
        $this->producto->setNombre('Hamburguesa Good Vibes');
        $this->producto->setDescripcion('Carne Angus con queso cheddar y bacon');
        $this->producto->setPrecio(12.50);
        $this->producto->setcosto(6.20);
        $this->producto->setStock(50);
        $this->producto->setStockMinimo(10);
        $this->producto->setIdCategoria('CATG0012026010112');
        $this->producto->setImagen('hamburguesa.jpg');
        $this->producto->setEstatus(1);

        $this->assertInstanceOf(Producto::class, $this->producto);
    }

    #[Test]
    public function testTransaccionPeticionInvalida(): void
    {
        $res = $this->producto->Transaccion(['peticion' => 'desconocida']);
        $this->assertFalse($res);
    }
}

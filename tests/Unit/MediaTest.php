<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Security\Media;

class MediaTest extends TestCase
{
    #[Test]
    public function testInstanciacionYListarArchivos(): void
    {
        $media = new Media();
        $this->assertInstanceOf(Media::class, $media);

        // Listar archivos de carpeta noticias
        $archivos = $media->listarArchivos('noticias');
        $this->assertIsArray($archivos);
    }

    #[Test]
    public function testListarArchivosGeneral(): void
    {
        $media = new Media();
        $archivos = $media->listarArchivos(null);
        $this->assertIsArray($archivos);
    }
}

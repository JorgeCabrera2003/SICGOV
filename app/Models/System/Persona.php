<?php

namespace App\Models\System;

use Exception;

class Persona
{
    protected $cedula;
    protected $nombre;
    protected $apellido;
    protected $fecha_nacimiento;
    protected $telefono;
    protected $correo;
    protected $direccion;
    protected $sexo;

    public function __construct()
    {
        $this->cedula = "";
        $this->nombre = "";
        $this->apellido = "";
        $this->fecha_nacimiento = null;
        $this->telefono = null;
        $this->correo = null;
        $this->direccion = "";
        $this->sexo = "";
    }

    // Getters y Setters

    /**
     * Cédula: prefijo (V/E/J/P/G) + 7 a 9 dígitos.
     */
    public function setCedula(string $cedula)
    {
        $cedula = trim($cedula);
        if (!preg_match('/^[VEJPGvejpg]-\d{7,9}$/', $cedula)) {
            throw new Exception('La cédula debe tener un prefijo válido (V, E, J, P, G), un guion y 7 a 9 dígitos.');
        }
        
        // Guardamos con el formato Prefijo-Numeros (ej: V-12345678)
        $this->cedula = strtoupper($cedula);
    }

    /** Nombre: obligatorio, mínimo 2 caracteres, solo letras y espacios. */
    public function setNombre(string $nombre)
    {
        $nombre = trim($nombre);
        if (empty($nombre)) {
            throw new Exception('El nombre es obligatoria.');
        }
        if (mb_strlen($nombre) < 2) {
            throw new Exception('El nombre debe tener al menos 2 caracteres.');
        }
        if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ][a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ ]*$/', $nombre)) {
            throw new Exception('El nombre solo puede contener letras y espacios.');
        }
        $this->nombre = $nombre;
    }

    /** Apellido: obligatorio, mínimo 2 caracteres, solo letras y espacios. */
    public function setApellido(string $apellido)
    {
        $apellido = trim($apellido);
        if (empty($apellido)) {
            throw new Exception('El apellido es obligatorio.');
        }
        if (mb_strlen($apellido) < 2) {
            throw new Exception('El apellido debe tener al menos 2 caracteres.');
        }
        if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ][a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ ]*$/', $apellido)) {
            throw new Exception('El apellido solo puede contener letras y espacios.');
        }
        $this->apellido = $apellido;
    }

    /** Fecha de nacimiento: obligatoria, formato YYYY-MM-DD, no puede ser hoy ni futura. */
    public function setFechaNacimiento($fecha_nacimiento)
    {
        $fecha_nacimiento = trim($fecha_nacimiento ?? '');
        if (empty($fecha_nacimiento)) {
            throw new Exception('La fecha de nacimiento es obligatoria.');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nacimiento)) {
            throw new Exception('El formato de la fecha de nacimiento no es válido.');
        }
        if ($fecha_nacimiento >= date('Y-m-d')) {
            throw new Exception('La fecha de nacimiento debe ser anterior a hoy.');
        }
        $this->fecha_nacimiento = $fecha_nacimiento;
    }

    /**
     * Teléfono: opcional.
     * Si se ingresa, debe ser exactamente 11 dígitos (prefijo 4 + número 7).
     */
    public function setTelefono(string $telefono)
    {
        $telefono = trim($telefono);
        if ($telefono === '') {
            $this->telefono = null;
            return;
        }
        if (!preg_match('/^\d{4}-?\d{7}$/', $telefono)) {
            throw new Exception('El teléfono debe incluir el prefijo (4 dígitos) más 7 dígitos de número.');
        }
        
        // Asegurar que se guarde con el formato 0000-0000000
        if (strpos($telefono, '-') === false) {
            $telefono = substr($telefono, 0, 4) . '-' . substr($telefono, 4);
        }

        $this->telefono = $telefono;
    }

    /** Correo: opcional. Si se ingresa debe tener formato válido. */
    public function setCorreo(string $correo)
    {
        $correo = trim($correo);
        if ($correo === '') {
            $this->correo = null;
            return;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El formato del correo electrónico no es válido.');
        }
        $this->correo = $correo;
    }

    /** Dirección: obligatoria, mínimo 3 caracteres. */
    public function setDireccion(string $direccion)
    {
        $direccion = trim($direccion);
        if (empty($direccion)) {
            throw new Exception('La dirección es obligatoria.');
        }
        if (mb_strlen($direccion) < 3) {
            throw new Exception('La dirección debe tener al menos 3 caracteres.');
        }
        $this->direccion = $direccion;
    }

    /** Sexo: obligatorio, debe ser M o F. */
    public function setSexo(string $sexo)
    {
        $sexo = trim($sexo);
        if (!in_array($sexo, ['M', 'F'], true)) {
            throw new Exception('El sexo debe ser M (Masculino) o F (Femenino).');
        }
        $this->sexo = $sexo;
    }

    public function getCedula() { 
        return $this->cedula; 
    }

    public function getNombre() { 
        return $this->nombre; 
    }

    public function getApellido() { 
        return $this->apellido; 
    }

    public function getFechaNacimiento() {
         return $this->fecha_nacimiento;
    }

    public function getTelefono() {
         return $this->telefono; 
    }

    public function getCorreo() {
         return $this->correo; 
    }

    public function getDireccion() {
         return $this->direccion; 
    }
    
    public function getSexo() {
         return $this->sexo; 
    }
}
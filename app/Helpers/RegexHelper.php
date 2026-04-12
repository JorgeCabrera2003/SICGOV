<?php

namespace App\Helpers;

class RegexHelper{

    /**
     * Realiza una verificación utilizando una expresión regular.
     * Devuelve 1 o 0 dependiendo si el valor ingresado cumple 
     * o no la Expresión Regular (Regex)
     * 
     * @param string $valor Valor a comparar
     * @param string $config Indica que Expresión Regular se usara para la comparación
     * 
     * @return int $bool
     */
public static function ValidarFormatos(string $valor, string $config) {
    $bool = 0;

    $bool = match ($config) {
         "Cedula" => preg_match('/^[VE]{1}[0-9]{7,11}$/', $valor),
         "ID" => preg_match('/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/', $valor),
         "NombrePersona", "Persona" => preg_match('/^[0-9 a-zA-ZáéíóúüñÑçÇ]{3,65}$/', $valor),
         "NombreObjeto", "Objeto" => preg_match('/^[0-9 a-zA-ZáéíóúüñÑçÇ]{3,65}$/', $valor),
         "Telefono" => preg_match('/^[0-9]{4}[-]{1}[0-9]{7}$/', $valor),
         "Correo" => preg_match('/^[-0-9A-Za-zç_]{6,36}[@]{1}[0-9a-zA-Z]{5,25}[.]{1}[com]{3}$/', $valor),
         default => 0
    };

    return $bool;
}


}






?>
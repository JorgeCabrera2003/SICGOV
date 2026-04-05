<?php

namespace App\Helpers;

class RegexHelper{

    /**
     * Realiza una verificación utilizando una expresión regular.
     * Devuelve 1 o 0 dependiendo si el valor ingresado cumple 
     * o no la Expresión Regular (Regex)
     * 
     * 
     * 
     * @param string $valor Valor a comparar
     * @param string $config Indica que Expresión Regular se usara para la comparación \
     * \
     * Cedula => V00000000 (/^[VE]{1}[0-9]{7,11}$/) \
     * ID => EJEMUSA20260101240101000 (/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/) \
     * Persona => (/^[a-z A-ZáéíóúüñÑçÇ]{3,65}$/) \
     * Usuario => (/^[0-9a-zA-ZáéíóúüñÑçÇ_]{3,65}$/) \
     * Objeto => (/^[0-9 a-zA-ZáéíóúüñÑçÇ]{3,65}$/) \
     * ObjetoLargo => (/^[0-9 a-zA-ZáéíóúüñÑçÇ]{3,200}$/) \
     * Telefono => (/^[0-9]{4}[-][0-9]{3}[-][0-9]{4}$/) \
     * Correo => (/^[-0-9A-Za-zç_]{6,36}[@]{1}[0-9a-zA-Z]{5,25}[.]{1}[com]{3}$/) \
     * Sexo => (/^[MF]{1}$/) 
     * 
     * @return int $bool
     */
public static function ValidarFormatos(string $valor, string $config) {
    $bool = 0;

    $bool = match ($config) {
         "Cedula" => preg_match('/^[VE]{1}[0-9]{7,11}$/', $valor),
         "ID" => preg_match('/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/', $valor),
         "NombrePersona", "Persona" => preg_match('/^[a-z A-ZáéíóúüñÑçÇ]{3,65}$/', $valor),
         "NombreUsuario", "Usuario" => preg_match('/^[0-9 a-zA-ZáéíóúüñÑçÇ_]{3,65}$/', $valor),
         "NombreObjeto", "Objeto" => preg_match('/^[0-9 a-zA-ZáéíóúüñÑçÇ]{3,65}$/', $valor),
         "NombreObjetoLargo", "ObjetoLargo" => preg_match('/^[0-9 a-zA-ZáéíóúüñÑçÇ]{3,200}$/', $valor),
         "Telefono" => preg_match('/^[0-9]{4}[-][0-9]{3}[-][0-9]{4}$/', $valor),
         "Correo" => preg_match('/^[-0-9A-Za-zç_]{6,36}[@]{1}[0-9a-zA-Z]{5,25}[.]{1}[com]{3}$/', $valor),
         "Sexo" => preg_match('/^[MF]{1}$/', $valor),
         default => 0
    };

    return $bool;
}

}






?>
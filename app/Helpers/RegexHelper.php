<?php

namespace App\Helpers;

class RegexHelper{

    /**
     * Realiza una verificación utilizando una expresión regular.
     * Devuelve 1 o 0 dependiendo si el valor ingresado cumple 
     * o no la Expresión Regular (Regex)
     * 
     * @param string $valor Valor a comparar
     * @param string $config Indica que Expresión Regular se usara para la comparación \
     * \
     * Cedula => V00000000 (/^[VE]{1}[0-9]{7,15}$/) \
     * ID => EJEMUSA20260101240101000 (/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/) \
     * Persona => (/^[a-z A-ZáéíóúüñÑçÇ]{3,65}$/) \
     * Usuario => (/^[0-9a-zA-ZáéíóúüñÑçÇ_]{3,65}$/) \
     * Objeto => (/^[0-9 a-zA-ZáéíóúüñÑçÇ]{3,65}$/) \
     * ObjetoLargo => (/^[0-9 a-zA-ZáéíóúüñÑçÇ]{3,200}$/) \
     * Telefono => (/^[0-9]{4}[-][0-9]{3}[-][0-9]{4}$/) \
     * Correo => (/^[a-zA-Z0-9][a-zA-Z0-9._%+-]{1,63}@[a-zA-Z0-9][a-zA-Z0-9.-]{1,50}\.(com|es|mx|co\.uk|org|net)$/ \
     * Sexo => (/^[MF]{1}$/) 
     * 
     * @return int $bool
     */
public static function ValidarFormatos(string $valor, string $config) {
    $bool = 0;

    $bool = match ($config) {
         "Cedula" => preg_match('/^[VEJPGvejpg]{1}[0-9]{7,15}$/', $valor), // Sincronizado con utils.js (Guion opcional)
         "ID" => preg_match('/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/', $valor), // Sincronizado con id_generado de utils.js
         "NombrePersona", "Persona" => preg_match('/^[a-z A-ZáéíóúüñÑçÇ]{3,65}$/', $valor),
         "NombreUsuario", "Usuario" => preg_match('/^[0-9a-zA-Z_]{4,20}$/', $valor), // Sincronizado con utils.js
         "NombreObjeto", "Objeto" => preg_match('/^[0-9 a-zA-ZáéíóúüñÑçÇ]{3,65}$/', $valor),
         "NombreObjetoLargo", "ObjetoLargo" => preg_match('/^[0-9 a-zA-ZáéíóúüñÑçÇ\s\-.,()!?]{3,200}$/', $valor),
         "Titulo" => preg_match('/^[0-9a-zA-ZáéíóúüñÑçÇ\s\-.,()!?"\'%:;]{3,150}$/', $valor),
         "Telefono" => preg_match('/^[0-9]{4}[-][0-9]{3}[-][0-9]{4}$/', $valor),
         "Correo" => preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._%+-]{1,63}@[a-zA-Z0-9][a-zA-Z0-9.-]{1,50}\.(com|es|mx|co\.uk|org|net)$/', $valor),
         "Sexo" => preg_match('/^[MF]{1}$/', $valor),
         default => 0
    };

    return $bool;
}

}
?>
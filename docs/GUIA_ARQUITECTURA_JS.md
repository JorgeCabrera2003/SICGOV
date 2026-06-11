# Guía de la Arquitectura Aplicada para la Lógica de Archivos de Javascript - SICGOV

En está docmentación se dejarán las indicaciones y pautas sobre la arquitectura aplicada dentro del Proyecto **SICGOV**. En dicho documento se explicará un poco sobre la arquitectura, la cual estaba basado sobre los principios SOLID; haciendo especialmente enfásis en que cada archivo, elemento o funcion tengan "Una Responsabilidad Única", con la finalidad de establcer una jerarquía concreta, funcional y escalable; evitando problemas comunes que pueden ocasionar la redundancia de código.

---

### 0. Instalación y uso de Funciones con import y export.

## Inicialización:
En la Vista para poder utilizar los archivos de Javascript como móduios, se debe colocar la siguiente etiqueta en el HTML:
- <script type="module" src="/assets/js/Controllers/EjemploController.js" defer></script>

Se debe añadir el atributo "module" en el archivo de Javascript para poder usar las palabras reservadas import y export, las cuales funcionan de la siguiente manera.

## import:
Se usa para importar los archivos Javascript como módulos para poder acceder desde el mismo código fuente a funciones que están definidas en otro archivo diferente de Javascript, la cual tiene la siguiente sintaxis (hay varias maneras):

# 1 import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js"
# 2 import {GenerarMensaje, MostrarConfirmacion} from "../Helpers/MensajeriaHelper.js"

Usualmente, al importar un modulo, se menciona entre llaves **{}** a todas las funciones que necesites en el momento y evitar traer el archivo completamente, de no declarar las funciones que necesitas, no podrás acceder a ellas. En cualquier caso, si son demasiadas las funciones que quieres utilizar, usa la Forma 1, y accede a las funciones mediante a la variable definida como un alias ("**as**") usando el operador "**.**".

## export:
Se usa para exportar las funciones (o variables/constantes) definidas dentro de algún archivos Javascript para usarla desde otro punto difente, el uso de export se hace de la siguiente manera durante la defición de la función:

# 1 export function GenerarMensaje(icono, tiempo, titulo, mensaje) {}

Sin el export, no tendría sentido importar un modulo ya que es quien indica al motor del navegador que funciones o variables/constantes pueden utilizarse fuera de su código fuente de origen, puedes tener muchas funciones dentro de un mismo código fuente, pero no necesariamente exportar todas las funciones, por lo que puedes decidir cuales exportar y cuales no particularmente.

### 1. Nombre de Carpetas y Archivos
La distribución de esta arquitectura quedará de la siguiente manera basandose de los Principios SOLID.

- **Controllers**: En esta carpeta estarán todos los archivos controladores, los cuales se encargarán de procesar los eventos (En su mayoría eventos como "click", "change" o parecidos) de la interfaz de usuario, delegando así las funciones que estarán definidas dentro de los archivos handlers. El nombre del controlador deberá tener (comúnmente) el nombre de la variable Front-Controller, ya que debería ser un controlador único para cada vista del sistema. 

- **Handlers**: También llamados "Manejadores" es en donde se concentrará toda la lógica del negocio, es decir, el procesamiento de las validaciones del formulario, la lógica de Mostrar/Ocultar Modales (Dependiendo si se tiene dos o más modales para una misma sección del módulo), énvios de datos, renderizados (usando render como en algunos casos para ciertas celdas de la DataTable), entre otras funciones que se requieran en el momento. Por recomendación, se debe tener un Handler por cada modulo o funcionalidad concreta del sistema para evitar códigos fuentes demasiados extensos.

- **Helpers**: Son los archivos cuyas funciones reutilizables que se usan de manera flexible dentro de los demás códigos fuentes, de esta manera, se evita la duplicación/redundancia de código al tener adentro de una función una tarea que puede ser repetitiva en algunas ocasiones. Por lo que el uso de Helpers se hace técnicamente global dentro de los demás archivos del sistema

Como normativa para asignar nombres a los archivos se usara el Patrón Camel Case - La primera letra de cada palabra en mayúscula:
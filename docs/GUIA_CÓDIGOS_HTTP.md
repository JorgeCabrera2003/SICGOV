# Guía de la Implementación del uso de Códigos HTTP - SICGOV

Está guía rápida está orientada a explicar de manera breve y resumida los códigos HTTP que se van usar e implementar dentro de este sistema de información.

---

### Conceptualización Básica.

## ¿Qué es HTTP y Cómo Funciona?:
Normalmente cuando estamos navegando en la WEB, en cualquier dispositivo que se trate, entre el navegador y el servidor se utiliza el protocolo HTTP (o HTTPS para los sitios WEB que tengan mayor seguridad en el tráfico de la red) para establecer una comunicación entre ambas partes.

De una manera básica, funciona de la siguiente forma: El Cliente le envía una Petición (Que contiene datos de lo que el cliente necesita, o datos que se envían al Servidor por medio de un fórmulario) que cae en manos del Servidor, el Servidor lee la Petición y devuelve una respuesta. Dichas respuestas contienen lo que el cliente necesita (solicitó) en respuesta a su petición, dada una respuesta, el Servidor devuelve un código que define el estado de la petición (Pendiente, Exitosa o Rechazada).


## Códigos HTTP:
Como dicho anteriormente, son códigos que se usan para marcar en que estado queda la Petición realizada por el cliente, lo cuál sirve para entender que evento ocurrió después de ser procesada, dicho esto, los Códigos HTTP son los siguientes (Y los que se usarán comúnmente en este proyecto)


### Códigos 1XX

Son códigos informativos que sirven para indicar a manera de descripción que está haciendo el Servidor con una Petición actualmente, estos códigos no manifiestan ni Éxito ni Errores, por lo que solo actuan como mensajes nada más

### Códigos 2XX

Son que se usan para indicar que la Petición del Cliente fue procesada éxitosamente, por lo cual (Dependiendo) el servidor devolverá una Respuesta (Response) de vuelta a Cliente con la información que fue enviada/solicitada en los encabezados de la Petición. Usualmente se usan los siguientes:

- **200**: Es el código más genérico dentro de todo el conjunto, sirve para marcar que la estado de la petición fue exitosa sin decir mucho más, el mensaje de este código es "OK".

- **201**: Este código se usa para indicar que la petición fué exitosa y a su vez manifiesta que se creo un nuevo recurso en el servidor, como por ejemplo en la creación de un nuevo usuario/registro, subidas de imágenes, entre otros.

- **204**: El código es para indicar que la Petición fue exitosa, pero que no se devolverá un mensaje como respuesta, en algunas ocaciones se utiliza cuando se elimina algún recurso del servidor, sin embargo, cabe aclarar que esto hace que el servidor no devuelva una respuesta (response) al cliente, porque se asume que el Cliente no necesita nada de vuelta.

### Códigos 3XX

Son usados por el Servidor cuando hay un redireccionamiento, es decir, cuando el Cliente navega mediante enlaces o el mismo servidor redirige al Cliente a otra parte del Sitio Web.


### Códigos 4XX

Estos Códigos representan errores del Lado del Cliente, al marcar el Estado de la Petición con cualquiera de estos códigos, se da a entender que su estado es fallido o erróneo, comúnmente estos errores se usan para indicar que hay errores dentro de los encabezados (Payload) o se intentan acceder a funciones desde el Lado del Cliente que no son permitidas por el Servidor.

- **400**: Es el código más genérico dentro de todo el conjunto, indica que hay un error en la Petición HTTP del Cliente, pero no indica el qué exactamente.

- **403**: "Forbidden". Este código se usa para indicar que el Cliente envió una Petición intentando acceder a un recurso o funcionalidad de la aplicación, se conocen las credenciales de sesión del usuario, pero no tiene permisos suficientes para acceder.

- **404**: El código es para indicar que la Petición fue exitosa, pero que no se devolverá un mensaje como respuesta, en algunas ocaciones se utiliza cuando se elimina algún recurso del servidor, sin embargo, cabe aclarar que esto hace que el servidor no devuelva una respuesta (response) al cliente, porque se asume que el Cliente no necesita nada de vuelta.

- **409**: Se utiliza para indicar que existe un conflicto con algún recurso en específico, como por ejemplo la existencia de archivos o registros con el mismo nombre, en el mensaje del encabezado suele colocarlse "Conflict".


### Códigos 5XX

- **500**: Código genérico que indica que ha ocurrido un error sin revelar mayor detalle. No obstante, la información en el servidor suele guardarse en un log con los detalles del evento ocurrido.
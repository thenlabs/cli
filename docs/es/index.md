
# ThenLabs CLI.

Bienvenido a la documentación en español de la interfaz de línea de comandos de [ThenLabs][ThenLabs] para proyectos PHP.

Por lo general, esta herramienta solo se usará a la hora de trabajar con ciertos proyectos de [ThenLabs][ThenLabs] por lo que rara vez se necesitará instalarla manualmente. No obstante, en [este enlace](https://github.com/thenlabs/cli#manual-installation) se menciona como hacerlo.

Una vez que la instalación se haya llevado a cabo, se habrá creado un archivo de consola que podrá ser ejecutado de la siguiente manera:

    $ ./vendor/bin/then

Una vez que se ejecute dicho archivo se mostrará en pantalla todos los comandos disponibles así como cierta información sobre su forma de uso. Dicha consola está construida a partir del proyecto [Symfony Console](https://symfony.com/doc/current/components/console.html) por lo que la misma puede resultarle familiar.

Los comandos de esta consola realizarán determinadas tareas sobre paquetes [Composer][Composer] de tipo `then-package`. Tal y como se menciona en la documentación de [Composer][Composer] sobre [los tipos de los paquetes](https://getcomposer.org/doc/04-schema.md#type), hemos definido este tipo personalizado dado que estos paquetes requieren cierto tratamiento especial.

Por lo antes comentado, solemos usar la terminología *then package* para referirnos a los paquetes [Composer][Composer] de tipo `then-package` y que además cuenten con un archivo `then-package.json` donde se definirán las opciones de configuración que se tendrán en cuenta a la hora de procesar el paquete.

Dado que las operaciones que ciertos comandos realizan dependen de un directorio de destino, en esos casos se hace necesario que en el mismo exista un archivo de nombre `then.json` el cual servirá para que el respectivo comando tenga en cuenta cierta configuración a la hora de realizar su operación sobre dicho directorio.

Dicho de otra manera, el archivo `then.json` servirá para indicarle a la consola determinada configuración para tener en cuenta a la hora de operar sobre un directorio, mientras que el archivo `then-package.json` de un paquete contendrá determinada información sobre la forma de tratar e interpretar al mismo.

## 1. Trabajando con *assets*.

Una de las características que tienen los *then packages* es que los mismos pueden contener archivos de recursos web como es el caso de *scripts*, hojas de estilo, imágenes, etc.

>En el campo del desarrollo web a estos recursos se les define como *assets*.

El comando `install:assets` tiene por objetivo tomar todos los *assets* de los *then packages* presentes en el proyecto y copiarlos en un determinado directorio.

Para explicar el trabajo con *assets* y su forma de gestión, supongamos que existe una aplicación PHP gestionada por [Composer][Composer] donde existe instalado un *then package*. En dicho proyecto existirá un directorio `public` y un archivo `then.json` con el siguiente contenido:

```json
{
    "targetAssetsDir": "public"
}
```

Como usted podrá suponer, en dicho archivo se especifica que los *assets* de todos los *then packages* deberán ser instalados en el directorio `public` del proyecto.

Por otra parte, el *then package* se encontrará en el directorio `vendor/my-vendor/my-then-package`, donde además de los archivos que normalmente contienen los paquetes [Composer][Composer] existirá un archivo `then-package.json` y un directorio `res` con la siguiente estructura de archivos:

```
├── res/
│   ├── scripts.js
│   ├── styles.css
│   ├── img/
│   │   └── logo.png
│   └── package.json
```

El contenido del archivo `then-package.json` es el siguiente:

```json
{
    "assets": {
        "res/*": ""
    },
    "mergeJson": {
        "package.json": {
            "target": "package.json",
            "keys": ["dependencies", "devDependencies"]
        }
    }
}
```

Cuando se ejecute el comando `install:assets` sobre el proyecto todo el contenido del directorio `res` se copiará en un nuevo directorio localizado en `public/my-vendor/my-then-package`.

Además de esto, el archivo `package.json` del directorio `res` se habrá copiado en la raíz del directorio `public`. Esto ocurre, dado que en la configuración del archivo `then-package.json` se ha especificado que los datos `dependencies` y `devDependencies` de dicho archivo, deben ser mezclados con los de un archivo de igual nombre localizado en la raíz del directorio `public`.

Cuando durante la copia de los *assets* se indica la combinación de un determinado archivo `JSON`, si el archivo de destino no se encuentra, lo que se hace entonces es copiar íntegramente el respectivo archivo del *then package*. En cambio, si ya existe dicho archivo en el destino, solo se combinarán los datos especificados.

Esta manera de gestionar los *assets* está diseñada teniendo en cuenta que muchos son instalados mediante ciertos gestores siendo [NPM](https://www.npmjs.com/) y [Bower](https://bower.io/) los más populares. De esta forma, una vez que se haya terminado de ejecutar el comando `install:assets` se podrá acceder al directorio `public` y ejecutar la instalación manual de los mismos mediante los comandos `npm update` o `bower install` según sea el caso.

[Composer]: https://getcomposer.org
[ThenLabs]: http://thenlabs.org

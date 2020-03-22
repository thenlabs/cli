
# ThenLabs CLI.

Bienvenido a la documentación en español de la interfaz de línea de comandos de [ThenLabs][ThenLabs] para proyectos PHP.

Por lo general, esta herramienta solo se usará a la hora de trabajar con ciertos proyectos de [ThenLabs][ThenLabs] por lo que rara vez se necesitará instalarla manualmente. No obstante, en [este enlace](https://github.com/thenlabs/cli#manual-installation) se menciona como hacerlo.

Una vez que la instalación se ha llevado a cabo, se habrá creado un archivo de consola que podrá ser ejecutado de la siguiente manera:

    $ ./vendor/bin/then

Una vez que se ejecute dicho archivo se mostrará en pantalla todos los comandos disponibles así como cierta información sobre su forma de uso. Dicha consola está construida a partir del proyecto [Symfony Console](https://symfony.com/doc/current/components/console.html) por lo que la misma puede resultarle familiar.

Los comandos realizarán determinadas tareas sobre paquetes [Composer][Composer] de tipo `then-package`. Tal y como se menciona en la documentación de [Composer][Composer] sobre [los tipos de los paquetes](https://getcomposer.org/doc/04-schema.md#type), hemos definido este tipo personalizado dado que estos paquetes requieren cierto tratamiento especial.

Por lo antes comentado, solemos usar la terminología *then package* para referirnos a los paquetes [Composer][Composer] de tipo `then-package` y que además cuenten con un archivo `then-package.json` donde se definirán las opciones de configuración que se tendrán en cuenta a la hora de procesarlos.

Dado que las operaciones que ciertos comandos realizan dependen de un directorio de destino, en esos casos se hace necesario que en el mismo exista un archivo de nombre `then.json` el cual servirá para tener en cuenta cierta configuración a la hora de realizar la operación sobre el directorio.

Dicho de otra manera, el archivo `then.json` servirá para indicarle a la consola determinada configuración que se debe tener en cuenta a la hora de operar sobre un directorio, mientras que el archivo `then-package.json` de un paquete contendrá determinada información sobre la forma de tratar e interpretar al mismo.

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

Como usted podrá suponer, en dicho archivo se ha especificado que los *assets* de todos los *then packages* del proyecto deberán ser instalados en el directorio `public`. Recordemos que en nuestro ejemplo solo existe un único *then package*.

Por otra parte, el *then package* se encontrará en el directorio `vendor/my-vendor/my-then-package`, donde además de los archivos que normalmente contienen los paquetes [Composer][Composer], existirá además un archivo `then-package.json` y un directorio `res` con la siguiente estructura de archivos:

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

Cuando se ejecute el comando `install:assets` sobre el proyecto, todo el contenido del directorio `res` se copiará en un nuevo directorio localizado en `public/my-vendor/my-then-package`.

Además de esto, el archivo `package.json` del directorio `res` se habrá copiado en la raíz del directorio `public`. Esto ocurre, dado que en la configuración del archivo `then-package.json` se ha especificado que los datos `dependencies` y `devDependencies` de dicho archivo, deben ser mezclados con los de un archivo de igual nombre ubicado en la raíz del directorio `public`.

Dado que se indica la combinación de un archivo `JSON`, y teniendo en cuenta que el del destino no existe, lo que se hace en ese caso es copiar el del *then package*. Recordemos que en el caso del ejemplo nos referimos al archivo `package.json`. Por otra parte, si a la hora de realizar dicha combinación sí se encuentra el archivo del destino, este se modificará al llevarse a cabo la combinación de los datos indicados.

Esta manera de gestionar los *assets* está diseñada teniendo en cuenta que muchos son instalados mediante ciertos gestores donde [NPM](https://www.npmjs.com/) y [Bower](https://bower.io/) son los más populares. De esta forma, una vez que se haya terminado de ejecutar el comando `install:assets`, se deberá acceder al directorio `public` y ejecutar la instalación manual de los mismos a través los comandos `npm update` o `bower install` según sea el caso. Entre otras ventajas, de esta forma se garantiza que se descargarán las versiones más actualizadas de los *assets* que encuentren publicados en dichos repositorios.

[Composer]: https://getcomposer.org
[ThenLabs]: http://thenlabs.org

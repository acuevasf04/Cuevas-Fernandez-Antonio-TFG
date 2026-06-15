# Explicación del Script Bash: Monitor de Caducidad de Certificados SSL/TLS

Este script automatiza la búsqueda, verificación y alerta de certificados digitales (archivos `.pem` o `.crt`) alojados en el servidor. Si detecta certificados caducados o próximos a caducar (menos de 30 días), genera un reporte y envía un correo electrónico de alerta de forma automática utilizando **msmtp**.

---

## Estructura y Funcionamiento Paso a Paso

### 1. Definición de Objetivos y Variables Iniciales
El script comienza configurando los directorios que va a analizar y los datos de envío para las alertas de correo:

* **Destinos de búsqueda (`TARGETS`):** Revisa si se han pasado argumentos al ejecutar el script (utilizando `$#`). Si no se le pasa ningún directorio, toma por defecto tres rutas críticas del sistema: `/etc/`, `/home/` y `/root/`. Si el usuario introduce rutas al ejecutarlo (ej. `./script.sh /var/www/ /opt/`), utiliza esas en su lugar (`$@`).
* **Variables de Correo:** Define el remitente corporativo (`no-reply@aromaris.com`), el destinatario institucional y el asunto del correo dinámico, que incluye el nombre del servidor afectado mediante el comando `$(hostname)`.

### 2. Impresión del Encabezado de la Tabla
Antes de procesar la información, el script dibuja un formato de tabla limpio en la terminal mediante el comando `printf`, estableciendo columnas fijas para la **Ruta del Certificado**, los **Días** restantes y el **Estado**.

### 3. Búsqueda con Filtros Avanzados
La magia del escaneo ocurre en la condición de cierre del bucle `while`:

* Utiliza una **sustitución de procesos** (`< <(...)`) para alimentar el bucle en tiempo real sin perder variables internas.
* El comando `find` busca tanto archivos normales (`-type f`) como enlaces simbólicos (`-type l`).
* Filtra específicamente las extensiones de certificados más comunes: `*.pem` y `*.crt`.

### 4. Filtrado de Certificados Falsos Positivos (Exclusiones)

Para cada archivo encontrado, realiza varias comprobaciones antes de analizarlo:

* **Exclusión del sistema:** Resuelve la ruta real (`readlink -f`) y si pertenece al directorio interno de certificados raíz de la distribución (`/usr/share/ca-certificates/`), lo ignora (`continue`) para evitar alertas innecesarias sobre certificados del sistema operativo.
* **Exclusión de archivos auxiliares:** Salta los almacenes globales combinados (`ca-certificates.crt`) y los archivos de intercambio de claves Diffie-Hellman (`dhparam`).

### 5. Cálculo del Tiempo de Vida con OpenSSL

Para los certificados válidos, extrae criptográficamente su información interna:

1. **Fecha de expiración:** Ejecuta `openssl x509 -enddate -noout -in "$cert"` para obtener la fecha de caducidad exacta sin leer todo el contenido.
2. **Conversión a Epoch:** Pasa esa fecha de texto a segundos (*timestamp* Unix) con `date -d ... +%s`.
3. **Cálculo de días:** Resta los segundos de la fecha de caducidad menos los segundos del momento actual (`NOW`) y divide el resultado entre **86400** (los segundos que tiene un día) empleando la aritmética nativa de Bash `$((...))`.

### 6. Formateo y Control del Ancho de Vía

Si la ruta de un certificado es extremadamente larga (mayor a 55 caracteres), el script utiliza la manipulación de cadenas de Bash (`${cert:0:20}...${cert: -32}`) para recortarla y que no rompa visualmente la tabla de la consola, mostrando el inicio y el final de la ruta.

### 7. Evaluación de Estados y Construcción del Reporte (`ALERT_REPORT`)

Se evalúa el número de días restantes (`DAYS_LEFT`) mediante tres condicionales:

* **Menor a 0 días:** El certificado ya expiró. Se muestra en la terminal como `[CADUCADO]` en color rojo (`\e[31m`) y se añade al reporte del correo eliminando el signo negativo (`${DAYS_LEFT#-}`).
* **Menor a 30 días:** Alerta de vencimiento cercano. Se pinta en amarillo (`\e[33m`) y se añade al reporte del correo.
* **Cualquier otro caso:** El certificado es seguro, se muestra un `[OK]` verde (`\e[32m`) y **no** se añade al reporte de alertas.

### 8. Construcción del Correo Electrónico y Envío mediante `msmtp`

Al finalizar el bucle sobre todos los archivos, el script comprueba si la variable `$ALERT_REPORT` contiene texto:

* Si tiene alertas, redacta un bloque de texto que cumple estrictamente con el formato de cabeceras de correo estándar (añadiendo `Content-Type: text/plain; charset=utf-8` para soportar emojis y tildes).
* Inyecta este contenido directamente al comando `msmtp -t`, encargado de conectarse con el servidor SMTP corporativo para despachar el correo.
* Evalúa el código de salida del envío (`$?`). Si es `0`, confirma el éxito en la pantalla; de lo contrario, advierte de un error en el envío.

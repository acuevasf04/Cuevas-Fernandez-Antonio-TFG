# Aromaris – Panel web PHP
 
Aplicación web PHP para la empresa Aromaris (fábrica de jabones artesanales). Está compuesta por cinco archivos que cubren la configuración de base de datos, la página pública con login, el registro de usuarios, un panel de administración de datos y una utilidad de inicialización.
 
---
 
## Estructura de archivos
 
| Archivo | Función |
|---|---|
| `config.php` | Conexión PDO a MySQL compartida por todos los archivos |
| `index.php` | Página pública corporativa con formulario de login integrado |
| `registro.php` | Formulario de registro de nuevos usuarios |
| `admin.php` | Panel de administración de base de datos (tipo phpMyAdmin ligero) |
| `style.css` | Variables de color y utilidades CSS propias de Aromaris |
 
---
 
## `config.php` — Conexión a base de datos
 
Define las constantes de conexión (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET`) y expone la función `getDB()`, que implementa el patrón **singleton** mediante una variable estática: la conexión PDO se crea la primera vez y se reutiliza en las llamadas siguientes. PDO se configura con `ERRMODE_EXCEPTION` (lanza excepciones en vez de errores silenciosos), `FETCH_ASSOC` (arrays asociativos por defecto) y `EMULATE_PREPARES => false` (sentencias preparadas reales en el driver).
 
---
 
## `index.php` — Página pública + login
 
El archivo tiene dos partes diferenciadas que se ejecutan en el mismo request:
 
**Parte PHP (lógica de sesión):**
- Al recibir un `POST`, recupera `usuario` (email) y `password` del formulario.
- Busca el usuario en la base de datos con una sentencia preparada para evitar inyección SQL.
- Verifica la contraseña con `password_verify()` contra el hash almacenado.
- Si el login es correcto, guarda `nombre` e `id` en `$_SESSION`.
- Si la URL contiene `?logout=1`, destruye la sesión y redirige.
**Parte HTML (renderizado):**
- Navbar fija con Bootstrap 5. Si hay sesión activa muestra "Cerrar sesión"; si no, muestra los botones "Acceder" y "Registrarse".
- Secciones de contenido estático: Hero, Nosotros, Productos (tres líneas: Floral, Herbal, Terapéutica), Valores y Contacto.
- El formulario de login se integra dentro de la sección de Contacto. Muestra los errores o el mensaje de bienvenida según el estado.
- Un pequeño script JS gestiona el toggle de visibilidad de la contraseña (ojo/ojo-tachado) y el scroll suave al ancla `#login`.
---
 
## `registro.php` — Registro de usuarios
 
Procesa el formulario en el mismo archivo. Las validaciones se realizan en PHP (servidor):
 
- Campos obligatorios: nombre, email y contraseña.
- Formato de email con `filter_var(..., FILTER_VALIDATE_EMAIL)`.
- Longitud mínima de 8 caracteres para la contraseña.
- Confirmación: las dos contraseñas deben coincidir.
- Unicidad: comprueba si el email ya existe antes de insertar.
Si pasa todas las validaciones, hashea la contraseña con `password_hash(..., PASSWORD_DEFAULT)` e inserta el registro en la tabla `Usuarios` (los apellidos son opcionales y se guardan como `null` si se omiten).
 
El formulario tiene validación adicional en JS en tiempo real: mientras el usuario escribe en el segundo campo de contraseña, se muestra un indicador verde/rojo de si coinciden o no.
 
---
 
## `admin.php` — Panel de administración
 
Panel de administración de base de datos con autenticación requerida (redirige a `index.php#login` si no hay sesión). Opera sobre cuatro tablas fijas definidas en `$tablas_permitidas` (`Usuarios`, `Productos`, `Pedidos`, `Detalles_Pedidos`), rechazando cualquier nombre de tabla que no esté en esa lista.
 
La acción activa se determina por el parámetro GET `accion` y el método HTTP:
 
**Browse** (`accion=browse`) — Lista los registros de la tabla con paginación de 50 filas. Muestra el recuento total y botones de editar/eliminar por fila. La columna primaria se determina como el primer campo devuelto por `DESCRIBE`.
 
**Structure** (`accion=structure`) — Muestra el resultado de `DESCRIBE <tabla>` en formato de tabla, con badges visuales para los tipos de clave (PRI, UNI, MUL) y la nulabilidad.
 
**Insert** (`accion=insert`, POST) — Genera dinámicamente un formulario a partir de las columnas de la tabla. Los campos `auto_increment` se deshabilitan y se envían vacíos. Los campos de tipo `TEXT` se renderizan como `<textarea>`; los numéricos como `<input type="number">`. Al enviar, construye la sentencia `INSERT` excluyendo los campos vacíos.
 
**Edit** (`accion=edit`/`update`) — Carga la fila por clave primaria y muestra el mismo formulario dinámico con los valores actuales. Los campos PK se muestran como solo lectura. Al guardar, construye la sentencia `UPDATE` excluyendo la PK de los campos modificables.
 
**Delete** (`accion=delete`) — Elimina el registro por PK via GET, con confirmación en JS en el lado cliente antes de navegar a la URL destructiva.
 
**SQL libre** (`accion=sql`, POST) — Consola SQL que ejecuta cualquier sentencia directamente con `$db->query()`. Si la consulta devuelve columnas, muestra los resultados en tabla; si no (INSERT/UPDATE/DELETE), muestra el número de filas afectadas. Incluye botones de consultas de ejemplo predefinidas.
 
El sidebar muestra el recuento de filas de cada tabla en tiempo real consultando `COUNT(*)` para cada una al cargar la página.
 
---
 
## `style.css` — Estilos propios
 
Define cinco variables CSS de la paleta Aromaris (`--aromaris-green`, `--aromaris-green-dark`, `--aromaris-green-light`, `--aromaris-accent`, `--aromaris-beige`) y clases de utilidad que extienden Bootstrap: `.bg-aromaris`, `.btn-aromaris`, `.text-aromaris`, `.hero-section` (fondo degradado full-height), `.product-card` (hover con elevación), `.contact-icon-wrap` (icono circular), entre otras. El diseño es responsive con ajustes específicos para pantallas menores de 768px.

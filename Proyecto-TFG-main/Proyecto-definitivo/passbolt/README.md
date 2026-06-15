# ¿Qué es Passbolt y para qué sirve?

Passbolt es un gestor de contraseñas de código abierto (*open source*) diseñado específicamente para **equipos de trabajo y empresas**. A diferencia de los gestores de contraseñas tradicionales orientados a usuarios individuales, Passbolt nace con el objetivo de permitir que los miembros de una organización compartan credenciales de forma segura, colaborativa y bajo un control estricto de accesos.

---

## ¿Qué es Passbolt?
Es una plataforma de seguridad basada en el principio de **conocimiento cero (*Zero-knowledge*)** y en el cifrado de extremo a extremo utilizando el estándar **OpenPGP**. 

Esto significa que las contraseñas se cifran directamente en el dispositivo del usuario antes de ser enviadas al servidor. Ni los creadores de Passbolt, ni nadie que intercepte el servidor (en caso de un ataque), puede ver los datos en texto plano si no posee la clave privada del usuario.

Se compone principalmente de:
1. Una extensión para el navegador web (Chrome, Firefox, Edge) o aplicación móvil que se encarga de cifrar/descifrar las credenciales de forma local.
2. Un servidor centralizado que almacena los datos cifrados y gestiona los permisos.

---

## ¿Para qué sirve?

Passbolt sirve principalmente para resolver los problemas de seguridad y organización que surgen cuando varias personas necesitan acceder a las mismas cuentas o servidores dentro de una empresa:

### 1. Compartir credenciales en equipo de forma segura (Colaboración)
Evita la pésima práctica de enviar contraseñas por canales inseguros como Slack, WhatsApp, correos electrónicos o apuntarlas en notas adhesivas. 
* Permite crear carpetas compartidas con contraseñas de herramientas comunes (ej. la cuenta de redes sociales de la empresa, accesos a servidores de desarrollo, etc.).
* La compartición se realiza mediante claves criptográficas asimétricas, garantizando que solo los miembros autorizados del equipo puedan descifrar la información.

### 2. Gestión granular de permisos (Control de accesos)
Sirve para decidir con precisión quién puede ver o modificar cada contraseña. Un administrador puede definir si un usuario o grupo puede:
* **Solo ver** la contraseña para utilizarla.
* **Modificar** los datos de la credencial.
* **Administrar** la credencial (poder compartirla con otros compañeros).

### 3. Mantener la soberanía de los datos (Opción On-Premise)
Para empresas con políticas estrictas de privacidad o cumplimiento legal, Passbolt sirve como una solución perfecta porque permite el **autoalojamiento (*Self-hosted*)**. Puedes instalarlo en tus propios servidores locales o infraestructuras en la nube (por ejemplo, mediante Docker o scripts automatizados), garantizando que tus credenciales nunca salgan de la red corporativa.

### 4. Auditoría, Cumplimiento y Seguridad Corporativa
Sirve para elevar el estándar de seguridad y transparencia de la infraestructura de TI mediante:
* **Generador de contraseñas fuertes:** Ayuda a los usuarios a crear claves complejas y únicas para cada servicio.
* **Historial y Auditoría:** Registra detalladamente qué usuario ha accedido, modificado o compartido una contraseña específica, lo cual es vital para auditorías de seguridad.

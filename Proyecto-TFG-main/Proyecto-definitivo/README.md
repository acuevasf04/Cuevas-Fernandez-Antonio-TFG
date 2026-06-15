# Resumen: Proyecto Intermodular
**Autor:** Antonio Cuevas Fernández  
**Fecha:** 15/06/2026  
**Especialidad:** ASIR 2  

---

## 1. Introducción y Alcance
Este proyecto detalla el diseño, despliegue y gestión de una infraestructura informática centralizada en las oficinas de una organización. El entorno está estrictamente delimitado al **ámbito físico del edificio corporativo**, abarcando desde la capa física (cableado estructurado) hasta la lógica (contenedores Docker y segmentación de redes), priorizando la baja latencia y la alta disponibilidad local.

---

## 2. Descripción General de la Arquitectura
La red se divide lógicamente en dos segmentos principales interconectados mediante un router con acceso a Internet:

* **LAN 1 (Windows Server + Docker Corporativo):** **Un servidor Windows Server 2022** provee de forma nativa los servicios de **DNS, DHCP y FTP** para los empleados.
    * La gestión de usuarios en Active Directory se automatiza mediante una **aplicación Python con interfaz gráfica (Tkinter)** que invoca scripts en PowerShell (`crear_usuario.ps1`, etc.).
    * En el mismo host Linux de la LAN 1 se despliegan mediante Docker servicios internos no accesibles desde Internet: **GLPI** (gestión de activos y helpdesk con chatbot de IA integrado vía Ollama), **Passbolt** (gestor corporativo de contraseñas con cifrado OpenPGP en cliente) y un **Proxy Inverso** (`nginx-proxy`) para centralizar el acceso por nombres de dominio locales (`.local`).
* **LAN 2 (Linux - Aplicación Web / DMZ):**
    * Infraestructura montada enteramente sobre un host Docker en Linux Debian 13 para alojar **Aromaris**, una aplicación web de tienda de jabones artesanales en PHP.
    * Compuesta por un balanceador de carga Nginx, dos servidores backend PHP-FPM y un servidor de base de datos MySQL 8.0 aislado. Tiene acceso entrante controlado desde el exterior por los puertos 80 y 443.

---

## 3. Diseño de Red, Protocolos y Seguridad Perimetral

### Segmentación de Redes
1.  **LAN 1 (Servicios Internos):** Rango `192.168.2.0/24`. Solo salida a Internet.
2.  **LAN 2 (DMZ / Web):** Rango `192.168.3.0/24`. Acceso entrante HTTP/HTTPS.
3.  **red-pública (Docker Interna LAN 2):** Red tipo *bridge* que conecta el balanceador Nginx con los backends PHP (puerto interno 9000).
4.  **red-privada (Docker Interna LAN 2):** Red tipo *bridge* totalmente aislada que comunica los backends con la Base de Datos MySQL (puerto 3306), sin mapeo al host exterior.

### Políticas del Firewall
Implementadas a través del script `enrutamiento.sh` mediante **iptables** (política por defecto `FORWARD DROP`):
* **Windows Server:** Permite salida a Internet y conexión hacia LAN 2 para administrar la base de datos, pero bloquea el tráfico entrante desde Internet.
* **LAN 2:** Accesible desde el exterior únicamente por redirección de puertos (*Port Forwarding*) en el 80 y 443 hacia el balanceador Nginx. El servidor MySQL carece de *Default Gateway* al exterior para evitar filtraciones.

---

## 4. Servidor Web y Balanceador de Carga (LAN 2)
Se ha sustituido Apache por **Nginx** por su arquitectura basada en eventos (*event-driven*), óptima para gestionar un alto volumen de conexiones con baja memoria.

* **Configuración:** Procesos worker automatizados (`worker_processes auto`), optimización de timeouts y servicio directo de contenido estático (CSS, JS, imágenes) desde el volumen compartido `./html`.
* **VirtualHost:** Responde únicamente a peticiones dirigidas al dominio `aromaris.org` y `www.aromaris.org`.
* **Cifrado HTTPS:** Redirección forzosa de HTTP a HTTPS (puerto 443) empleando TLS 1.2 o superior. Utiliza certificados SSL guardados en `./certs`. El script `certificados.sh` monitoriza la caducidad y envía alertas por email si quedan menos de 30 días.
* **Balanceo de Carga:** El contenedor `nginx-balanceador` distribuye de manera secuencial el tráfico hacia `backend1` y `backend2` utilizando el algoritmo **Round Robin**. Implementa verificaciones pasivas de salud (*passive health checks*) para dejar de enviar tráfico a un nodo si este cae.

---

## 5. Base de Datos (MySQL 8.0)
* **Arquitectura:** Un único servidor de base de datos (`db-mysql`) priorizando la simplicidad y reducción de costes.
* **Seguridad y Permisos:** Aplica el *Principio de Menor Privilegio* (PoLP). Se utiliza un usuario específico de aplicación (`antonio`) limitado a instrucciones `SELECT`, `INSERT`, `UPDATE` y `DELETE`, denegando permisos estructurales como `DROP` o `ALTER`.
* **Aislamiento:** Confinado estrictamente a la `red-privada` de Docker; la comunicación con los backends se restringe a los nombres de contenedor `backend1` y `backend2`.

---

## 6. Respaldos y Continuidad de Negocio

### Estrategia de Copias de Seguridad (Capítulo 11)
Diseñada para minimizar el RPO (*Recovery Point Objective*):
* **Elementos respaldados:** Base de datos (vía `mysqldump`), contenidos web estáticos (`./html`), configuraciones del sistema (`docker-compose.yml`, `nginx.conf`, scripts, archivos `.env`) y volúmenes de GLPI y Passbolt.
* **Frecuencia:** Estrategia mixta con una **Copia Completa semanal** (domingos 00:00) y **Copias Incrementales diarias** (lunes a sábado).
* **Seguridad de las copias:** Cifrado en reposo mediante **AES-256**, transferencia segura por TLS/SSL a un almacenamiento remoto (*off-site*) inmutable durante 30 días.

### Alta Disponibilidad y Tolerancia a Fallos
* **Punto Único de Fallo (SPOF):** La base de datos, al ser de nodo único, es el elemento crítico. Se mitiga con políticas de reinicio automático (`restart: always`) en Docker y respaldos externos robustos.
* **Resiliencia Web:** Si uno de los backends PHP falla, Nginx lo detecta y el nodo hermano asume el 100% de la carga de trabajo de manera transparente para el usuario.

---

## 7. Monitorización y Mantenimiento
Se implementa un stack de visibilidad proactiva:
* **Herramientas:** Uso de `docker stats`.
* **KPIs supervisados:** Uso de hardware (CPU, RAM, Disco), rendimiento de red (ancho de banda y latencia) y estado de servicios (uptime y volumen de errores HTTP 4xx/5xx).
* **Parches de seguridad:** Configuración de `unattended-upgrades` en Debian 13 para parches del SO y actualizaciones programadas de contenedores mediante `docker compose pull`.

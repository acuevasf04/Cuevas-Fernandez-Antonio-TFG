# ¿Qué es un Proxy Inverso y para qué sirve en nuestra empresa?

En nuestra infraestructura, el **Proxy Inverso** es el servidor que actúa como intermediario y "punto de entrada único" para todo el tráfico web que viene desde el exterior (o desde la red de usuarios) hacia nuestros servicios internos clave: **GLPI** y **Passbolt**.

---

## ¿Qué es un Proxy Inverso?

Un proxy inverso (o *Reverse Proxy*) es un servidor que se coloca frente a las aplicaciones web (GLPI y Passbolt) y se encarga de recibir todas las peticiones entrantes de los usuarios. En lugar de que los usuarios se conecten directamente a los servidores o contenedores donde corren estas herramientas, le preguntan al Proxy Inverso, y este se encarga de redirigir la petición al destino correcto de forma totalmente transparente.

El flujo visual del tráfico funciona de la siguiente manera:

```

                  ┌───────────────────┐
                  │   Usuarios / Web  │
                  └─────────┬─────────┘
                            │
                            ▼ (Peticiones HTTP/HTTPS)
                  ┌───────────────────┐
                  │   PROXY INVERSO   │ (Punto de entrada único)
                  └────┬───────────┬──┘
                       │           │
     Si busca "glpi"   │           │   Si busca "passbolt"
                       ▼           ▼
                 ┌──────────┐ ┌───────────┐
                 │ Servidor │ │ Servidor  │
                 │   GLPI   │ │ Passbolt  │
                 └──────────┘ └───────────┘
```
---

## ¿Para qué sirve en nuestra empresa?

El uso de este proxy inverso para gestionar el tráfico de **GLPI** y **Passbolt** nos aporta cuatro beneficios fundamentales:

### 1. Centralización de Certificados SSL/TLS (Seguridad HTTPS)
En lugar de configurar la seguridad, el cifrado y los certificados SSL (como Let's Encrypt) en cada aplicación por separado, el Proxy Inverso se encarga de **"comprimir y descomprimir" el cifrado**. 
* Toda la comunicación entre los usuarios y el Proxy viaja de forma 100% segura por HTTPS.
* El proxy gestiona y renueva los certificados de seguridad de forma centralizada en un único punto.

### 2. Enrutamiento Inteligente por Nombre de Dominio
Sirve para redirigir al usuario a la aplicación correcta basándose en la dirección URL que escribe en su navegador, utilizando una sola dirección IP pública o de red:
* Si el usuario escribe `glpi.miempresa.local` (o su dominio correspondiente), el proxy detecta el nombre y lo redirige internamente al contenedor o servidor de **GLPI**.
* Si el usuario escribe `passbolt.miempresa.local`, el proxy lo identifica y lo desvía hacia el contenedor o servidor de **Passbolt**.

### 3. Ocultación y Aislamiento de la Infraestructura Interna
Por motivos de seguridad, **GLPI** y **Passbolt** nunca deben exponer sus puertos internos de forma directa a la red general o a Internet. 
* El Proxy Inverso actúa como un "escudo" o muro de contención.
* Los servidores reales de las aplicaciones permanecen ocultos en una red interna privada y segura. La única máquina que "habla" directamente con los clientes es el proxy.

### 4. Optimización de Recursos y Rendimiento
El proxy inverso es capaz de gestionar miles de conexiones simultáneas de forma mucho más eficiente que los servidores de aplicaciones nativos. Puede encargarse de tareas pesadas como la compresión de datos o la entrega de archivos estáticos (imágenes, hojas de estilo), liberando de carga de trabajo a las bases de datos y núcleos de GLPI y Passbolt.

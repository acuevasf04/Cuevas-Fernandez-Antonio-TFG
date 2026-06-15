# ¿Qué es un Proxy Inverso y para qué sirve en nuestra empresa?

En nuestra infraestructura basada en **Docker**, el **Proxy Inverso** es el componente crítico que actúa como intermediario y "punto de entrada único" para todo el tráfico web hacia nuestros servicios internos clave: **GLPI** y **Passbolt**.

---

## ¿Qué es un Proxy Inverso?

Un proxy inverso (o *Reverse Proxy*) es un servidor que se coloca frente a los contenedores de nuestras aplicaciones. Cuando un usuario intenta acceder a GLPI o Passbolt, su navegador no habla directamente con el contenedor de la aplicación, sino que le hace la petición al Proxy Inverso, y este se encarga de redirigir el tráfico al destino correcto de forma totalmente transparente.

El flujo del tráfico funciona de la siguiente manera:

```
                  ┌───────────────────┐
                  │   Usuarios / Web  │
                  └─────────┬─────────┘
                            │
                            ▼ (Peticiones por el puerto único 80/443)
                  ┌───────────────────┐
                  │   PROXY INVERSO   │ (Escucha en los puertos estándar)
                  └────┬───────────┬──┘
                       │           │
     Si busca "glpi"   │           │   Si busca "passbolt"
                       ▼           ▼
                 ┌──────────┐ ┌───────────┐
                 │Contenedor│ │Contenedor │
                 │   GLPI   │ │ Passbolt  │
                 └──────────┘ └───────────┘
```

---

## ¿Para qué sirve en nuestra empresa? (El motivo de Docker)

El uso del proxy inverso es indispensable en nuestro entorno por los siguientes motivos fundamentales:

### 1. Resolución del conflicto de puertos en Docker (El motivo principal)
Por defecto, tanto GLPI como Passbolt son aplicaciones web que necesitan atender peticiones en los puertos estándar de internet: el **puerto 80 (HTTP)** y el **puerto 443 (HTTPS)**. 

Dado que estamos utilizando **Docker**, el sistema operativo del servidor tiene una limitación física: **dos servicios o contenedores diferentes no pueden utilizar el mismo puerto al mismo tiempo**. Si intentáramos mapear el puerto 80 de GLPI y el puerto 80 de Passbolt directamente hacia el servidor, Docker daría un error de conflicto de puertos y el segundo servicio no podría iniciar.

El Proxy Inverso soluciona esto de forma elegante:
* El Proxy Inverso es **el único** que se adueña de los puertos 80 y 443 del servidor real.
* Los contenedores de GLPI y Passbolt se configuran en puertos internos expuestos de forma privada (en este caso, el puerto 8080 y el 8081).
* El proxy recibe todo en el puerto estándar y lo reparte internamente hacia el puerto específico de cada contenedor sin que el usuario lo note.

### 2. Enrutamiento por Nombre de Dominio
Como el proxy se encarga de recibir todo el tráfico en un único punto, sirve para analizar qué dirección ha escrito el usuario en el navegador y decidir el destino correcto:
* Si se solicita `glpi.aromaris.local`, el proxy lo desvía internamente hacia el contenedor de GLPI.
* Si se solicita `passbolt.aromaris.local`, lo desvía hacia el contenedor de Passbolt.

Esto evita que los usuarios tengan que escribir direcciones incómodas en el navegador con números de puertos (como `https://servidor:8080` o `https://servidor:8081`).

### 3. Centralización de la Seguridad y Certificados SSL/TLS
En lugar de configurar la seguridad y los certificados de cifrado dentro de cada contenedor por separado, el Proxy Inverso se encarga de gestionar la capa de seguridad (HTTPS) de manera unificada. Toda la comunicación externa viaja cifrada y segura hasta el proxy, simplificando la administración y renovación de certificados.

### 4. Aislamiento y Muro de Contención
Por seguridad, los contenedores con las bases de datos y los núcleos de GLPI y Passbolt permanecen en una red interna oculta y protegida dentro de Docker. El Proxy Inverso actúa como un escudo protector; es la única máquina expuesta que recibe las conexiones, reduciendo drásticamente la superficie de ataque frente a posibles amenazas.

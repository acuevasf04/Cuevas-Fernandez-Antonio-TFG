#  GLPI AI Chatbot API

API middleware que conecta un chatbot con **GLPI** (base de conocimientos y formularios) y un modelo de lenguaje local via **Ollama**, enriqueciendo las respuestas con contexto real antes de enviarlo al LLM.

---

## ¿Cómo funciona?

```
Cliente (chat)
     │
     ▼
POST /chat
     │
     ├─► GLPI: busca artículos en la base de conocimientos
     ├─► GLPI: carga formularios del catálogo (si es relevante)
     │
     └─► Ollama (LLM): genera respuesta enriquecida con el contexto
              │
              ▼
       Streaming SSE al cliente
```

1. El cliente envía un historial de mensajes al endpoint `/chat`.
2. El servidor consulta la **base de conocimientos de GLPI** buscando artículos relacionados con la última pregunta del usuario.
3. Si el usuario pregunta por formularios, solicitudes o servicios disponibles, también se cargan los **formularios activos del catálogo**.
4. Todo ese contexto se inyecta en el mensaje de sistema antes de enviarlo al LLM.
5. La respuesta del modelo se devuelve al cliente en **streaming** (Server-Sent Events).

---

## Gestión de sesión GLPI

Cada petición abre una sesión nueva en GLPI con `initSession` usando el `USER_TOKEN` y el `APP_TOKEN`, y la cierra con `killSession` al terminar (en el bloque `finally`, para garantizar que siempre se libera aunque haya errores).

---

## Búsqueda en la base de conocimientos

Para cada mensaje del usuario el servidor realiza hasta tres consultas a la API de GLPI:

1. **Por nombre** — busca artículos cuyo título coincida con la consulta.
2. **Por contenido** — busca artículos cuyo cuerpo coincida con la consulta.
3. **Fallback** — si ninguna búsqueda devuelve resultados, carga los 20 artículos más recientes.

Los resultados se deduplicán por `id` y se limitan a 5 artículos. Después se recupera el detalle completo de cada uno y el HTML del campo `answer` se limpia con `stripHtml` (elimina etiquetas, entidades HTML y espacios extra, truncando a 800 caracteres) antes de añadirlo al contexto.

---

## Carga de formularios del catálogo

El servidor detecta si el mensaje del usuario tiene intención de consultar servicios disponibles mediante una expresión regular sobre palabras clave:

```
formulari, form, solicitud, incidencia, abrir, crear, nuevo,
catálogo, servicio, disponible, qué puedo, que puedo
```

Si hay coincidencia, consulta el endpoint `Glpi\Form\Form` y filtra únicamente los formularios que estén **activos, no eliminados y no en borrador** (`is_active`, `is_deleted`, `is_draft`). El listado resultante se añade al contexto del sistema.

---

## Enriquecimiento del contexto

El contexto obtenido de GLPI (artículos + formularios) se inyecta al final del mensaje con rol `system` del historial, junto con la instrucción:

> *"Basa tus respuestas en la información anterior cuando sea relevante."*

El resto del historial de mensajes se envía sin modificar.

---

## Streaming al cliente

La petición al LLM (Ollama, compatible con la API de OpenAI) se hace con `stream: true`. La respuesta se hace pipe directamente al cliente con cabeceras `text/event-stream`, sin bufferizar en el servidor, lo que permite que el usuario vea la respuesta en tiempo real.

# 💬 GLPI Chat Widget

Widget de chat flotante embebible en cualquier página web que conecta a los usuarios con un asistente IA de soporte IT, usando streaming de respuestas en tiempo real.

---

## Estructura general

El código está envuelto en una **IIFE** (`(function(){ ... })()`) en modo estricto, lo que evita contaminar el scope global y protege las variables internas.

---

## Configuración (`CONFIG`)

Un objeto central define todo el comportamiento del widget sin necesidad de tocar el resto del código:

| Propiedad | Descripción |
|---|---|
| `endpoint` | Ruta de la API del chatbot (`/chat-api/chat`) |
| `apiKey` | Clave de autorización enviada en cada petición |
| `model` | Modelo LLM a usar (`gemma3:12b`) |
| `systemPrompt` | Instrucciones del asistente: qué puede y qué no puede responder |
| `title` / `subtitle` | Textos del encabezado del widget |
| `placeholder` | Texto del campo de entrada |
| `welcomeMessage` | Primer mensaje que aparece al abrir el chat |

El `systemPrompt` define explícitamente el rol del agente (soporte IT de Aromaris), las capacidades permitidas (KB de GLPI, procedimientos, formularios) y las prohibidas (configuraciones de red, credenciales, temas no relacionados).

---

## Estado interno

Cuatro variables de módulo controlan el ciclo de vida del widget:

- `isOpen` — si la ventana de chat está visible.
- `isStreaming` — si hay una respuesta en curso (bloquea el envío de nuevos mensajes).
- `unread` — contador de mensajes no leídos (mostrado en el badge del botón).
- `timerInterval` — referencia al intervalo del temporizador de espera.

El historial completo de la conversación se mantiene en `conversationHistory`, que se acumula durante la sesión y se envía íntegro en cada petición para que el modelo tenga contexto de los mensajes anteriores.

---

## Estilos (`STYLES`)

Todos los estilos están definidos como una cadena de CSS que se inyecta en el `<head>` del documento en tiempo de ejecución. El widget usa posicionamiento `fixed` para mantenerse visible al hacer scroll. La ventana de chat tiene una animación de apertura/cierre basada en `transform: scale` y `opacity`, con una curva `cubic-bezier` para efecto de rebote.

---

## Renderizado de Markdown (`parseMarkdown`)

Convierte un subconjunto de Markdown a HTML antes de insertar las respuestas del bot:

- `**texto**` → `<strong>`
- `` `código` `` → `<code>` con estilo inline
- Líneas que empiezan por `-` o `*` → `<li>` agrupados en `<ul>`
- Saltos de línea `\n` → `<br>`

Los mensajes del usuario se insertan como `textContent` (sin parsear) para evitar XSS.

---

## Flujo de envío (`sendMessage`)

1. Deshabilita el botón de envío y el campo de texto mientras dura el streaming.
2. Añade el mensaje del usuario a `conversationHistory`.
3. Muestra el indicador de escritura animado (tres puntos rebotando).
4. Activa un temporizador que muestra los segundos transcurridos si la espera supera 5 segundos.
5. Hace `fetch` al endpoint con el historial completo más el `systemPrompt` como primer mensaje de sistema.
6. Lee la respuesta como un **stream** con `ReadableStream` + `TextDecoder`, procesando línea a línea el formato `data: ...` de SSE.
7. En cuanto llega el primer chunk, oculta el indicador de escritura y crea la burbuja del bot, actualizando su contenido en tiempo real con cada fragmento recibido.
8. Al finalizar, reactiva la entrada y guarda la respuesta completa en `conversationHistory`.
9. En caso de error, muestra el mensaje de fallo en la burbuja del bot y un aviso en el área de error.

---

## Construcción del DOM (`init`)

Se ejecuta una sola vez al cargar (con guardia `if (document.getElementById("glpi-chat-btn")) return` para evitar duplicados). Crea e inyecta en el `<body>`:

- Un **botón flotante** circular con icono SVG de chat y un badge de notificaciones.
- Una **ventana de chat** con cabecera, área de mensajes, zona de error y pie de entrada.

Todo el HTML del widget se genera mediante JavaScript, sin depender de ningún fichero externo de plantillas ni de frameworks.

---

## Eventos

| Elemento | Evento | Acción |
|---|---|---|
| Botón flotante | `click` | Abre/cierra la ventana (`toggleChat`) |
| Botón cerrar (×) | `click` | Cierra la ventana |
| Botón enviar | `click` | Llama a `handleSend` |
| Campo de texto | `keydown Enter` | Envía el mensaje (Shift+Enter hace salto de línea) |
| Campo de texto | `input` | Ajusta la altura del textarea automáticamente (`autoResize`) |
| Documento | `keydown Escape` | Cierra la ventana si está abierta |

---

## Accesibilidad

El widget incluye atributos ARIA básicos: `role="dialog"` en la ventana, `role="log"` con `aria-live="polite"` en el área de mensajes (para que los lectores de pantalla anuncien los nuevos mensajes), y `role="alert"` en la zona de error. Los botones tienen `aria-label` descriptivos.

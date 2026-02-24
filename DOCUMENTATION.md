# Documentación – Agenda Virtual

> Fecha de generación: 24/02/2026

---

## Tabla de contenidos

1. [Descripción general](#1-descripción-general)
2. [Arquitectura y stack tecnológico](#2-arquitectura-y-stack-tecnológico)
3. [Variables de entorno](#3-variables-de-entorno)
4. [Estructura de archivos](#4-estructura-de-archivos)
5. [Módulo de autenticación](#5-módulo-de-autenticación)
6. [Seguridad](#6-seguridad)
7. [Panel de administración](#7-panel-de-administración)
8. [Sección de usuario](#8-sección-de-usuario)
9. [API JSON interna](#9-api-json-interna)
10. [Sistema de notificaciones](#10-sistema-de-notificaciones)
11. [Modelo de datos en Firebase](#11-modelo-de-datos-en-firebase)
12. [Estados de un turno](#12-estados-de-un-turno)
13. [Flujo completo de un turno](#13-flujo-completo-de-un-turno)
14. [Consideraciones de seguridad](#14-consideraciones-de-seguridad)
15. [Despliegue](#15-despliegue)

---

## 1. Descripción general

**Agenda Virtual** es una aplicación web PHP que permite gestionar turnos/citas entre un administrador y usuarios registrados. El administrador crea slots de tiempo disponibles; los usuarios los reservan; el administrador los confirma o cancela. Todas las transiciones de estado disparan notificaciones automáticas por **email** (SMTP/PHPMailer) y opcionalmente por **WhatsApp** (Twilio).

---

## 2. Arquitectura y stack tecnológico

| Capa | Tecnología |
|---|---|
| Lenguaje backend | PHP 8.x |
| Base de datos | Firebase Realtime Database (NoSQL en tiempo real) |
| SDK Firebase | `kreait/firebase-php` |
| Email | PHPMailer (SMTP, TLS/SSL) |
| WhatsApp | Twilio REST API (vía cURL) |
| Frontend | Bootstrap 5.3 + FullCalendar 6 |
| Sesiones | PHP sessions nativas |
| Contenedores | Docker (Dockerfile incluido) |
| PaaS | Render (render.yaml + Procfile) |

---

## 3. Variables de entorno

Todas las credenciales sensibles se proveen mediante variables de entorno (sin hardcoding).

### Firebase (obligatorias)

| Variable | Descripción |
|---|---|
| `FIREBASE_CREDENTIALS` | JSON completo de la cuenta de servicio de Firebase |
| `FIREBASE_URL` | URL de la base de datos Realtime Database |

### Email – PHPMailer (opcionales, necesarias para enviar correos)

| Variable | Descripción | Por defecto |
|---|---|---|
| `MAIL_HOST` | Servidor SMTP | — |
| `MAIL_PORT` | Puerto SMTP | `587` |
| `MAIL_USER` | Usuario SMTP | — |
| `MAIL_PASS` | Contraseña SMTP | — |
| `MAIL_FROM` | Dirección de origen | igual que `MAIL_USER` |
| `MAIL_FROM_NAME` | Nombre del remitente | `Agenda Virtual` |

### WhatsApp – Twilio (opcionales)

| Variable | Descripción |
|---|---|
| `TWILIO_SID` | Account SID de Twilio |
| `TWILIO_TOKEN` | Auth Token de Twilio |
| `TWILIO_WHATSAPP_FROM` | Número origen (ej: `whatsapp:+14155238886`) |

---

## 4. Estructura de archivos

```
/
├── config.php                  # Inicialización de Firebase
├── composer.json               # Dependencias PHP
├── Dockerfile                  # Imagen Docker
├── Procfile                    # Entrada para Render/Heroku
├── render.yaml                 # Configuración Render
└── public/
    ├── index.php               # Página de inicio / bienvenida
    ├── login.php               # Formulario e inicio de sesión
    ├── register.php            # Formulario de registro
    ├── logout.php              # Cierre de sesión
    ├── security_headers.php    # Cabeceras HTTP de seguridad
    ├── notificaciones.php      # Helper de emails y WhatsApp
    ├── sidebar.php             # Barra de navegación compartida
    ├── css/styles.css          # Estilos globales
    ├── admin/
    │   ├── panel.php           # Dashboard del administrador
    │   ├── turnos.php          # Calendario de gestión de turnos
    │   ├── usuarios.php        # CRUD de usuarios
    │   ├── get_turnos.php      # API JSON: turnos para el calendario admin
    │   ├── agregar_turno.php   # API JSON: crear turno disponible
    │   ├── confirmar_turno.php # API JSON: confirmar turno pendiente
    │   └── cancelar_turno.php  # API JSON: cancelar cualquier turno
    └── user/
        ├── agenda.php          # Vista de calendario del usuario
        ├── mis_turnos.php      # API JSON: turnos propios del usuario
        ├── get_turnos.php      # API JSON: turnos para el calendario usuario
        ├── reservar_turno.php  # API JSON: reservar un turno disponible
        └── cancelar_turno_user.php  # API JSON: cancelar turno propio
```

---

## 5. Módulo de autenticación

### `config.php`

Inicializa la conexión con Firebase:

1. Lee `FIREBASE_CREDENTIALS` (JSON de cuenta de servicio) desde el entorno.
2. Escribe el JSON en un archivo temporal en `/tmp/` con permisos `0600`.
3. Crea la instancia `$database` de Firebase Realtime Database con `kreait/firebase-php`.
4. **Elimina inmediatamente** el archivo temporal para no dejar credenciales en disco.

```php
$database = $factory->createDatabase(); // instancia disponible en todos los scripts que incluyan config.php
```

---

### `login.php`

**Método:** `GET` (muestra formulario) | `POST` (procesa login)

**Flujo POST:**

1. Valida token CSRF (`hash_equals`).
2. Sanitiza y valida `email` y `password`.
3. Recorre el nodo `usuarios` de Firebase buscando coincidencia de email.
4. Verifica la contraseña con `password_verify()`.
5. Si es válida:
   - Llama a `session_regenerate_id(true)` (previene session fixation).
   - Genera un nuevo token CSRF post-login.
   - Almacena en `$_SESSION['user']`: `id`, `rol`, `nombre`, `email`, `telefono`.
   - Redirige según rol: `ADMIN` → `admin/panel.php`, `USER` → `user/agenda.php`.

---

### `register.php`

**Método:** `GET` | `POST`

**Validaciones:**
- Campos `nombre`, `email`, `password` obligatorios.
- Email válido con `filter_var`.
- Contraseña mínimo 8 caracteres.
- Verificación de email duplicado (case-insensitive).

**Al registrar:**
- Hashea la contraseña con `password_hash($password, PASSWORD_DEFAULT)`.
- Limpia el teléfono (solo dígitos y `+`).
- Guarda el usuario en Firebase con rol `USER` (auto-registro siempre es USER).
- Redirige a `login.php?registro=ok`.

---

### `logout.php`

1. Vacía `$_SESSION`.
2. Expira la cookie de sesión (`setcookie` con tiempo pasado).
3. Destruye la sesión con `session_destroy()`.
4. Redirige a `index.php`.

---

## 6. Seguridad

### `security_headers.php`

Incluido al inicio de todas las páginas HTML (no en endpoints JSON).
Establece las siguientes cabeceras HTTP:

| Cabecera | Valor | Propósito |
|---|---|---|
| `X-Frame-Options` | `DENY` | Prevenir clickjacking |
| `X-Content-Type-Options` | `nosniff` | Prevenir MIME sniffing |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Controlar información del referrer |
| `Content-Security-Policy` | Ver detalle abajo | Restringir fuentes de recursos |

**CSP configurada:**
```
default-src 'self'
style-src   'self' https://cdn.jsdelivr.net
script-src  'self' https://cdn.jsdelivr.net
font-src    'self' data:
img-src     'self' data:
connect-src 'self'
```

Adicionalmente genera el token CSRF en sesión si no existe (requiere `session_start()` previo).

---

### `sidebar.php`

Barra de navegación responsive (Bootstrap). Renderiza links condicionalmente según `$_SESSION['user']['rol']`:
- `USER` → enlace a Mi Agenda.
- `ADMIN` → enlace al Panel Admin.
- Siempre muestra Inicio y Cerrar sesión.

---

## 7. Panel de administración

> Todos los archivos bajo `admin/` verifican `$_SESSION['user']['rol'] === 'ADMIN'` antes de ejecutar cualquier lógica. Si la comprobación falla, redirigen a `login.php` (páginas HTML) o devuelven HTTP 403 (endpoints JSON).

### `admin/panel.php`

Dashboard con tarjetas de resumen:

| Métrica | Fuente |
|---|---|
| Total usuarios | `count($usuarios)` |
| Turnos reservados | Filtro `estado === 'RESERVADO'` |
| Turnos disponibles | Filtro `estado === 'DISPONIBLE'` |

Incluye `sidebar.php`.

---

### `admin/turnos.php`

Vista principal de gestión de turnos. Muestra un **calendario interactivo** (FullCalendar 6) con todos los turnos.

**Funcionalidades:**
- **Vista adaptativa:** `listWeek` en móvil, `dayGridMonth` en escritorio.
- **Clic en slot vacío:** Abre modal "Agregar Turno" pre-rellenado con fecha.
- **Clic en evento existente:** Abre modal "Gestionar Turno" con botones contextuales según el estado del turno:
  - `PENDIENTE` → botones **Confirmar** + **Cancelar**.
  - `CONFIRMADO` → botón **Cancelar**.
  - `DISPONIBLE` → botón **Cancelar** / eliminar.
- Los eventos se cargan mediante `fetch('get_turnos.php')`.
- Las acciones llaman a `agregar_turno.php`, `confirmar_turno.php` o `cancelar_turno.php` via `fetch` POST con JSON.

---

### `admin/usuarios.php`

CRUD de usuarios con protección CSRF.

**Acciones disponibles (`POST['accion']`):**

| Acción | Descripción |
|---|---|
| `agregar` | Crea un usuario con nombre, email, contraseña (≥8 chars) y rol (`USER`/`ADMIN`). Verifica email duplicado. |
| `eliminar` | Elimina un usuario por ID. Valida el ID con regex `[\w-]+`. Impide que el admin se borre a sí mismo. |

---

## 8. Sección de usuario

> Todos los archivos bajo `user/` verifican `$_SESSION['user']['rol'] === 'USER'`.

### `user/agenda.php`

Vista principal del usuario. Contiene:

1. **Calendario FullCalendar** que carga eventos desde `get_turnos.php`:
   - Verde (DISPONIBLE): se puede reservar.
   - Amarillo (MI TURNO – pendiente): turno propio en espera.
   - Verde oscuro (MI TURNO – confirmado): turno propio confirmado.
2. **Tabla "Mis Turnos"** cargada por `fetch('mis_turnos.php')` con botón **Cancelar** por fila.

**JavaScript expuesto:**
- `cargarMisTurnos()`: recarga la tabla de turnos.
- `cancelarTurno(id)`: pide confirmación y llama a `cancelar_turno_user.php`.
- Al hacer clic en un slot **DISPONIBLE** del calendario, llama a `reservar_turno.php`.

---

## 9. API JSON interna

Todos los endpoints devuelven `Content-Type: application/json`.

### `admin/get_turnos.php`

**Método:** `GET`  
**Auth:** ADMIN

Devuelve todos los turnos como array de eventos de FullCalendar:

| Estado | Color | Título |
|---|---|---|
| `DISPONIBLE` | verde | `DISPONIBLE` |
| `PENDIENTE` | amarillo `#f59e0b` | `⏳ PENDIENTE – {nombre}` |
| `CONFIRMADO` | verde oscuro `#16a34a` | `✅ CONFIRMADO – {nombre}` |
| `RESERVADO` | rojo (legacy) | `RESERVADO – {nombre}` |
| `CANCELADO` | gris | `CANCELADO` |

---

### `admin/agregar_turno.php`

**Método:** `POST` (JSON body)  
**Auth:** ADMIN  
**Body:** `{ "fecha": "YYYY-MM-DD", "hora": "HH:MM" }`

Validaciones:
- Formato de fecha y hora con `DateTime::createFromFormat`.
- Fecha/hora no puede ser en el pasado.

Crea el turno en Firebase con `estado: DISPONIBLE`.

**Respuesta:**
```json
{ "success": true, "message": "Turno creado" }
```

---

### `admin/confirmar_turno.php`

**Método:** `POST` (JSON body)  
**Auth:** ADMIN  
**Body:** `{ "id": "<turnoId>" }`

- Verifica que el turno exista y esté en estado `PENDIENTE` (HTTP 409 si no).
- Actualiza a `CONFIRMADO` + registra `confirmadoPor` y `fechaConfirmacion`.
- Llama a `notificarCambioTurno(... 'CONFIRMADO' ...)`.

---

### `admin/cancelar_turno.php`

**Método:** `POST` (JSON body)  
**Auth:** ADMIN  
**Body:** `{ "id": "<turnoId>" }`

- Actualiza el turno a `CANCELADO`, limpia `usuarioId`, registra `canceladoPor` y `fechaCancelacion`.
- Si el turno tenía `usuarioId`, notifica al usuario (`notificarCambioTurno(... 'CANCELADO' ...)`).

---

### `user/get_turnos.php`

**Método:** `GET`  
**Auth:** USER

Devuelve eventos para el calendario del usuario. Solo muestra:
- Turnos `DISPONIBLE` (en verde, para todos).
- Turnos `PENDIENTE`/`CONFIRMADO`/`RESERVADO` **propios** del usuario.

---

### `user/mis_turnos.php`

**Método:** `GET`  
**Auth:** USER

Devuelve la lista de turnos propios (`PENDIENTE`, `CONFIRMADO`, `RESERVADO`) para la tabla de la agenda.

**Respuesta:**
```json
{
  "success": true,
  "turnos": [
    { "id": "...", "fecha": "YYYY-MM-DD", "hora": "HH:MM", "estado": "PENDIENTE" }
  ]
}
```

---

### `user/reservar_turno.php`

**Método:** `POST` (JSON body)  
**Auth:** USER  
**Body:** `{ "id": "<turnoId>" }`

- Verifica que el turno esté en estado `DISPONIBLE`.
- Actualiza a `PENDIENTE` con el `usuarioId` del usuario autenticado.
- Notifica al usuario (`notificarCambioTurno(... 'PENDIENTE' ...)`).

> **Nota de concurrencia:** La operación de verificar-y-actualizar no es atómica. Para entornos de alta concurrencia se debería usar Firebase Transactions (`runTransaction`).

---

### `user/cancelar_turno_user.php`

**Método:** `POST` (JSON body)  
**Auth:** USER  
**Body:** `{ "id": "<turnoId>" }`

Reglas de cancelación:
- El turno debe estar en estado `RESERVADO` y pertenecer al usuario.
- Solo se permite cancelar con al menos **48 horas** de anticipación (HTTP 403 si no se cumple).
- Al cancelar: estado vuelve a `DISPONIBLE`, `usuarioId` → `null`.
- Notifica al usuario (`notificarCambioTurno(... 'CANCELADO' ...)`).

---

## 10. Sistema de notificaciones

Archivo: `public/notificaciones.php`

### Función pública principal

```php
notificarCambioTurno(
    string $emailUsuario,
    string $nombreUsuario,
    string $evento,          // 'PENDIENTE' | 'CONFIRMADO' | 'CANCELADO'
    string $fecha,           // 'YYYY-MM-DD'
    string $hora,            // 'HH:MM'
    string $telefonoUsuario = ''
): void
```

Orquesta el envío de email (siempre) y WhatsApp (solo si `$telefonoUsuario` no está vacío). Los errores se logean con `error_log()` y nunca interrumpen el flujo principal.

---

### `enviarEmail()`

```php
enviarEmail(string $emailDestino, string $nombre, string $evento, string $fecha, string $hora): bool
```

- Usa **PHPMailer** con SMTP.
- TLS en puerto 465 (SMIME), STARTTLS en cualquier otro puerto.
- Si faltan las variables de entorno SMTP, retorna `false` sin lanzar excepción.
- Envía HTML + texto plano alternativo.

---

### `enviarWhatsApp()`

```php
enviarWhatsApp(string $telefono, string $nombre, string $evento, string $fecha, string $hora): bool
```

- Llama a la REST API de Twilio (`/2010-04-01/Accounts/{SID}/Messages.json`) mediante cURL.
- Normaliza el número agregando el prefijo `whatsapp:` si no lo tiene.
- Si las variables Twilio están ausentes, retorna `false` sin lanzar excepción.

---

### `_mensajeTurno()` (privada)

```php
_mensajeTurno(string $nombre, string $evento, string $fecha, string $hora): array
```

Genera los textos de notificación según el evento. Devuelve:
```php
[
  'asunto' => '...',  // Asunto del email
  'html'   => '...',  // Cuerpo HTML
  'texto'  => '...',  // Texto plano (para WhatsApp y AltBody)
]
```

| Evento | Asunto |
|---|---|
| `PENDIENTE` | ✅ Reserva recibida – pendiente de confirmación |
| `CONFIRMADO` | 🎉 Turno CONFIRMADO |
| `CANCELADO` | ❌ Turno cancelado |

---

## 11. Modelo de datos en Firebase

### Nodo `usuarios`

```json
{
  "<uid>": {
    "nombre":   "Juan Pérez",
    "email":    "juan@ejemplo.com",
    "password": "<bcrypt hash>",
    "telefono": "+5491123456789",
    "rol":      "USER" | "ADMIN"
  }
}
```

### Nodo `turnos`

```json
{
  "<turnoId>": {
    "fecha":             "YYYY-MM-DD",
    "hora":              "HH:MM",
    "estado":            "DISPONIBLE | PENDIENTE | CONFIRMADO | CANCELADO | RESERVADO",
    "adminId":           "<uid del admin que creó el turno>",
    "usuarioId":         "<uid del usuario> | null",
    "confirmadoPor":     "<uid admin>",
    "fechaConfirmacion": "YYYY-MM-DD HH:MM:SS",
    "canceladoPor":      "<uid admin>",
    "fechaCancelacion":  "YYYY-MM-DD HH:MM:SS"
  }
}
```

> `confirmadoPor`, `fechaConfirmacion`, `canceladoPor` y `fechaCancelacion` solo existen cuando aplica.

---

## 12. Estados de un turno

```
DISPONIBLE
    │
    │ (usuario reserva)
    ▼
 PENDIENTE ──────────────────────────────────────────────────┐
    │                                                         │
    │ (admin confirma)              (admin cancela)           │ (admin cancela)
    ▼                                     ▼                   ▼
CONFIRMADO ─────────────────────────► CANCELADO ◄───────── DISPONIBLE
    │
    │ (admin cancela)
    ▼
 CANCELADO
```

> `RESERVADO` es un estado **legacy** tratado como equivalente a `PENDIENTE`.

---

## 13. Flujo completo de un turno

```
Admin crea turno          → estado: DISPONIBLE
Usuario lo reserva        → estado: PENDIENTE  → notificación "PENDIENTE" al usuario
Admin confirma el turno   → estado: CONFIRMADO → notificación "CONFIRMADO" al usuario
   ó
Admin cancela el turno    → estado: CANCELADO  → notificación "CANCELADO" al usuario (si tenía usuario)
   ó
Usuario cancela (≥48h antes) → estado: DISPONIBLE → notificación "CANCELADO" al usuario
```

---

## 14. Consideraciones de seguridad

| Medida | Implementación |
|---|---|
| **CSRF** | Token aleatorio de 64 hex chars en sesión, validado con `hash_equals` en cada POST |
| **Session Fixation** | `session_regenerate_id(true)` al autenticar + nuevo token CSRF post-login |
| **XSS** | Toda salida de datos de usuario usa `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` |
| **Path Traversal** | IDs de turnos y usuarios validados con regex `^[\w-]+$` antes de usarlos como clave Firebase |
| **Contraseñas** | Almacenadas con `password_hash(PASSWORD_DEFAULT)` (bcrypt), verificadas con `password_verify` |
| **Credenciales Firebase** | Solo existen en memoria o en archivo `/tmp` con chmod 0600, eliminado inmediatamente |
| **Cabeceras HTTP** | `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Content-Security-Policy` |
| **Autorización** | Guard en cada archivo: comprueba sesión y rol antes de cualquier operación |
| **Formulario** | `autocomplete` apropiado en campos (`email`, `current-password`, `new-password`) |

---

## 15. Despliegue

### Docker

```dockerfile
# Dockerfile incluido en la raíz del proyecto
docker build -t agenda-virtual .
docker run -p 8080:8080 \
  -e FIREBASE_CREDENTIALS='...' \
  -e FIREBASE_URL='https://...' \
  agenda-virtual
```

### Render

El archivo `render.yaml` contiene la configuración del servicio. Las variables de entorno se configuran en el dashboard de Render.

El `Procfile` define el comando de inicio del servidor web.

### Dependencias PHP

```bash
composer install
```

Principales paquetes (`composer.json`):

| Paquete | Uso |
|---|---|
| `kreait/firebase-php` | SDK oficial Firebase para PHP |
| `phpmailer/phpmailer` | Envío de emails SMTP |
| `firebase/php-jwt` | Manejo de JWT (usado por Firebase SDK) |
| `guzzlehttp/guzzle` | Cliente HTTP (usado por Firebase SDK) |

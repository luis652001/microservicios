# 🧩 PRODUCTO 2: Arquitectura de Microservicios

## 📋 Descripción
Sistema distribuido implementado con arquitectura de microservicios, donde cada servicio es independiente, autónomo y se comunica a través de APIs REST. El API Gateway actúa como punto de entrada único para todas las solicitudes.

## 🏗️ Arquitectura del Sistema

### Diagrama de Arquitectura
```
┌─────────────────┐    HTTP/JSON    ┌─────────────────┐
│   Cliente Web   │ ──────────────→ │   API Gateway   │
│  (HTML/CSS/JS)  │ ←────────────── │   (Puerto 4000) │
└─────────────────┘                 └─────────────────┘
                                              │
                                              │ Enrutamiento
                                              ▼
                    ┌─────────────────────────────────────────────────┐
                    │                                                 │
        ┌───────────┴───────────┐  ┌───────────┴───────────┐  ┌──────┴──────┐
        │ Microservicio         │  │ Microservicio         │  │ Microservicio│
        │ Usuarios              │  │ Datos                 │  │ Notificaciones│
        │ (Puerto 5001)         │  │ (Puerto 5002)         │  │ (Puerto 5003)│
        └───────────┬───────────┘  └───────────┬───────────┘  └──────┬──────┘
                    │                           │                     │
                    │                           │                     │
        ┌───────────▼───────────┐  ┌───────────▼───────────┐  ┌──────▼──────┐
        │   MySQL Usuarios      │  │   MySQL Datos         │  │   Logs      │
        │   (BD: ms_users)      │  │   (BD: ms_data)       │  │   (Archivo) │
        └───────────────────────┘  └───────────────────────┘  └─────────────┘
```

### Componentes Principales
- **API Gateway**: Punto de entrada único que enruta solicitudes a los microservicios correspondientes
- **Microservicio Usuarios**: Maneja autenticación, registro y gestión de usuarios
- **Microservicio Datos**: Gestiona elementos/items del sistema
- **Microservicio Notificaciones**: Maneja envío de notificaciones y logs
- **Bases de Datos**: Cada microservicio puede tener su propia base de datos

## 🛠️ Tecnologías Utilizadas
- **Backend**: PHP 8.0+ con PDO
- **Base de Datos**: MySQL 8.0+ (separada por microservicio)
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Comunicación**: APIs REST con JSON
- **API Gateway**: PHP con enrutamiento inteligente
- **Autenticación**: JWT distribuido entre servicios

## 📁 Estructura de Archivos
```
producto-2-microservicios/
├── 📁 api-gateway/
│   ├── 📄 index.php              # Punto de entrada principal
│   ├── 📄 config/
│   │   ├── database.php          # Configuración de BD
│   │   └── services.php          # Configuración de microservicios
│   ├── 📄 middleware/
│   │   ├── AuthMiddleware.php    # Validación JWT
│   │   └── CorsMiddleware.php    # Configuración CORS
│   ├── 📄 routes/
│   │   └── gateway.php           # Enrutamiento principal
│   └── 📄 .htaccess              # Configuración Apache
├── 📁 microservicio-usuarios/
│   ├── 📄 index.php              # Servidor del microservicio
│   ├── 📄 config/
│   │   └── database.php          # Configuración BD usuarios
│   ├── 📄 controllers/
│   │   └── UserController.php    # Controlador de usuarios
│   ├── 📄 models/
│   │   └── User.php              # Modelo de usuario
│   ├── 📄 database/
│   │   └── schema.sql            # Esquema BD usuarios
│   └── 📄 .htaccess              # Configuración Apache
├── 📁 microservicio-datos/
│   ├── 📄 index.php              # Servidor del microservicio
│   ├── 📄 config/
│   │   └── database.php          # Configuración BD datos
│   ├── 📄 controllers/
│   │   └── ItemController.php    # Controlador de elementos
│   ├── 📄 models/
│   │   └── Item.php              # Modelo de elemento
│   ├── 📄 database/
│   │   └── schema.sql            # Esquema BD datos
│   └── 📄 .htaccess              # Configuración Apache
├── 📁 microservicio-notificaciones/
│   ├── 📄 index.php              # Servidor del microservicio
│   ├── 📄 config/
│   │   └── logger.php            # Configuración de logs
│   ├── 📄 controllers/
│   │   └── NotificationController.php # Controlador de notificaciones
│   ├── 📄 services/
│   │   └── EmailService.php      # Servicio de email (mock)
│   └── 📄 .htaccess              # Configuración Apache
├── 📁 frontend/
│   ├── 📄 index.html             # Página principal
│   ├── 📄 css/
│   │   └── styles.css            # Estilos principales
│   └── 📄 js/
│       ├── auth.js               # Lógica de autenticación
│       ├── items.js              # Lógica de elementos
│       └── notifications.js      # Lógica de notificaciones
├── 📄 docker-compose.yml         # Configuración Docker (opcional)
├── 📄 .env.example               # Variables de entorno de ejemplo
└── 📖 README.md                  # Este archivo
```

## 🚀 Instalación y Configuración

### 1. Requisitos Previos
- PHP 8.0 o superior
- MySQL 8.0 o superior
- Servidor web (Apache/Nginx)
- Extensión PHP PDO MySQL habilitada
- Múltiples puertos disponibles (4000, 5001, 5002, 5003)

### 2. Configuración de Bases de Datos
```sql
-- Base de datos para microservicio de usuarios
CREATE DATABASE ms_users;
USE ms_users;
-- Ejecutar microservicio-usuarios/database/schema.sql

-- Base de datos para microservicio de datos
CREATE DATABASE ms_data;
USE ms_data;
-- Ejecutar microservicio-datos/database/schema.sql
```

### 3. Configuración del Proyecto
1. Copiar `.env.example` a `.env` en cada microservicio
2. Configurar variables específicas por servicio:

**API Gateway (.env):**
```env
GATEWAY_PORT=4000
USERS_SERVICE_URL=http://localhost:5001
DATA_SERVICE_URL=http://localhost:5002
NOTIFICATIONS_SERVICE_URL=http://localhost:5003
JWT_SECRET=clave_secreta_compartida
```

**Microservicio Usuarios (.env):**
```env
SERVICE_PORT=5001
DB_HOST=localhost
DB_NAME=ms_users
DB_USER=tu_usuario
DB_PASS=tu_password
JWT_SECRET=clave_secreta_compartida
```

**Microservicio Datos (.env):**
```env
SERVICE_PORT=5002
DB_HOST=localhost
DB_NAME=ms_data
DB_USER=tu_usuario
DB_PASS=tu_password
JWT_SECRET=clave_secreta_compartida
```

**Microservicio Notificaciones (.env):**
```env
SERVICE_PORT=5003
JWT_SECRET=clave_secreta_compartida
LOG_FILE=logs/notifications.log
```

### 4. Instalación
1. Colocar cada microservicio en su propio directorio del servidor web
2. Configurar virtual hosts o subdirectorios para cada puerto
3. Configurar permisos de escritura en carpetas de logs
4. Acceder a `http://localhost:4000/` (API Gateway)

## 🔌 Endpoints de la API

### API Gateway (Puerto 4000)
- `POST /auth/register` → Enruta a Microservicio Usuarios
- `POST /auth/login` → Enruta a Microservicio Usuarios
- `GET /users/me` → Enruta a Microservicio Usuarios
- `GET /items` → Enruta a Microservicio Datos
- `POST /items` → Enruta a Microservicio Datos
- `PUT /items/{id}` → Enruta a Microservicio Datos
- `DELETE /items/{id}` → Enruta a Microservicio Datos
- `POST /notify` → Enruta a Microservicio Notificaciones

### Microservicio Usuarios (Puerto 5001)
- `POST /register` - Registro de usuario
- `POST /login` - Inicio de sesión
- `GET /me` - Obtener perfil del usuario autenticado

### Microservicio Datos (Puerto 5002)
- `GET /items` - Listar elementos del usuario
- `POST /items` - Crear nuevo elemento
- `PUT /items/{id}` - Actualizar elemento
- `DELETE /items/{id}` - Eliminar elemento

### Microservicio Notificaciones (Puerto 5003)
- `POST /notify` - Enviar notificación
- `GET /logs` - Obtener logs de notificaciones

## 📊 Esquemas de Base de Datos

### Microservicio Usuarios (ms_users)
```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Microservicio Datos (ms_data)
```sql
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    usuario_id INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🧪 Pruebas del Sistema

### Flujo de Pruebas Recomendado
1. **Registro de Usuario**: Crear cuenta a través del API Gateway
2. **Inicio de Sesión**: Autenticarse y obtener token JWT
3. **Crear Elemento**: Usar token para crear elemento a través del Gateway
4. **Listar Elementos**: Obtener lista de elementos del usuario
5. **Enviar Notificación**: Probar el servicio de notificaciones
6. **Verificar Comunicación**: Confirmar que los servicios se comunican correctamente

### Herramientas de Prueba
- **Postman**: Para probar endpoints individuales de cada microservicio
- **Navegador Web**: Para probar la interfaz completa
- **Consola del Navegador**: Para ver logs y errores
- **Logs de Servicios**: Para verificar comunicación entre servicios

## 🔒 Seguridad Implementada
- **JWT Distribuido**: Tokens compartidos entre microservicios
- **Validación Centralizada**: Middleware de autenticación en API Gateway
- **CORS Configurado**: Políticas de origen cruzado por servicio
- **Validación de Entrada**: Sanitización en cada microservicio
- **Logs de Auditoría**: Registro de todas las operaciones

## 📱 Características del Frontend
- **Comunicación Unificada**: Todas las peticiones van al API Gateway
- **Manejo de Estados**: Indicadores de carga y errores por servicio
- **Interfaz Responsiva**: Adaptable a diferentes dispositivos
- **Validación en Tiempo Real**: Feedback inmediato al usuario
- **Gestión de Tokens**: Manejo automático de autenticación

## 🚨 Solución de Problemas

### Error de Comunicación entre Microservicios
- Verificar que todos los servicios estén ejecutándose
- Confirmar URLs y puertos en archivos de configuración
- Verificar conectividad de red entre servicios

### Error de Autenticación JWT
- Confirmar que `JWT_SECRET` sea idéntico en todos los servicios
- Verificar que el token no haya expirado
- Confirmar formato del token en headers

### Problemas de Base de Datos
- Verificar credenciales en cada microservicio
- Confirmar que las bases de datos estén creadas
- Verificar permisos de usuario en cada base de datos

## 📈 Próximas Mejoras
- [ ] Implementar service discovery automático
- [ ] Agregar circuit breakers para resiliencia
- [ ] Implementar logging centralizado (ELK Stack)
- [ ] Agregar métricas y monitoreo (Prometheus)
- [ ] Implementar rate limiting por servicio
- [ ] Agregar versionado de APIs

## 🐳 Docker (Opcional)
```yaml
# docker-compose.yml
version: '3.8'
services:
  api-gateway:
    build: ./api-gateway
    ports:
      - "4000:80"
    depends_on:
      - users-service
      - data-service
      - notifications-service

  users-service:
    build: ./microservicio-usuarios
    ports:
      - "5001:80"
    environment:
      - DB_HOST=mysql-users

  data-service:
    build: ./microservicio-datos
    ports:
      - "5002:80"
    environment:
      - DB_HOST=mysql-data

  notifications-service:
    build: ./microservicio-notificaciones
    ports:
      - "5003:80"

  mysql-users:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: ms_users
      MYSQL_ROOT_PASSWORD: root

  mysql-data:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: ms_data
      MYSQL_ROOT_PASSWORD: root
```

---

**Desarrollado por**: Equipo de Arquitecturas 2025  
**Versión**: 1.0.0  
**Última actualización**: Diciembre 2025

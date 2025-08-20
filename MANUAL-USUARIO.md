# 🚀 MANUAL DE USUARIO - PROYECTO MICROSERVICIOS

## 📋 ÍNDICE
1. [Descripción del Proyecto](#descripción-del-proyecto)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Requisitos del Sistema](#requisitos-del-sistema)
4. [Instalación y Configuración](#instalación-y-configuración)
5. [Uso del Sistema](#uso-del-sistema)
6. [Funcionalidades](#funcionalidades)
7. [Solución de Problemas](#solución-de-problemas)
8. [Estructura del Proyecto](#estructura-del-proyecto)

---

## 🎯 DESCRIPCIÓN DEL PROYECTO

El **Sistema de Microservicios** es una aplicación web moderna que implementa una arquitectura de microservicios distribuidos con:

- **API Gateway**: Punto de entrada único para todas las peticiones
- **Microservicio de Usuarios**: Gestión de autenticación y perfiles
- **Microservicio de Datos**: Gestión de items y contenido
- **Microservicio de Notificaciones**: Sistema de mensajería
- **Dashboard Unificado**: Interfaz web para gestionar todo el sistema

---

## 🏗️ ARQUITECTURA DEL SISTEMA

### Componentes Principales:
```
┌─────────────────┐    ┌─────────────────────┐
│   Frontend      │    │   API Gateway       │
│   Dashboard     │◄──►│   (Puerto 80)       │
└─────────────────┘    └─────────────────────┘
                                │
                ┌───────────────┼───────────────┐
                │               │               │
        ┌───────▼──────┐ ┌──────▼──────┐ ┌─────▼──────┐
        │ Microservicio│ │ Microservicio│ │Microservicio│
        │   Usuarios   │ │    Datos     │ │Notificaciones│
        │   (Puerto 80)│ │   (Puerto 80)│ │  (Puerto 80)│
        └──────────────┘ └──────────────┘ └────────────┘
                │               │               │
        ┌───────▼──────┐ ┌──────▼──────┐ ┌─────▼──────┐
        │   Base de    │ │   Base de   │ │   Base de  │
        │   Datos      │ │   Datos     │ │   Datos    │
        │  Usuarios    │ │    Items    │ │Notificaciones│
        └──────────────┘ └──────────────┘ └────────────┘
```

---

## 💻 REQUISITOS DEL SISTEMA

### Software Requerido:
- **XAMPP** (versión 8.0 o superior)
- **Apache** (puerto 80)
- **MySQL** (puerto 3306)
- **PHP** (versión 8.0 o superior)
- **Navegador web** (Chrome, Firefox, Edge)

### Hardware Mínimo:
- **RAM**: 4GB
- **Espacio en disco**: 2GB libres
- **Procesador**: Intel/AMD de 2GHz o superior

---

## ⚙️ INSTALACIÓN Y CONFIGURACIÓN

### 1. Preparar XAMPP
```
1. Descargar e instalar XAMPP desde: https://www.apachefriends.org/
2. Iniciar XAMPP Control Panel
3. Iniciar Apache (Start)
4. Iniciar MySQL (Start)
5. Verificar que ambos servicios estén en verde
```

### 2. Ubicación del Proyecto
```
El proyecto debe estar en: C:\xampp\htdocs\microservicios\
```

### 3. Bases de Datos
```
1. Abrir phpMyAdmin: http://localhost/phpmyadmin
2. Crear las siguientes bases de datos:
   ✅ microservicios_usuarios_db
   ✅ microservicios_datos_db
   ✅ microservicios_notificaciones_db
3. Importar los archivos schema.sql correspondientes
4. Verificar que las tablas se crearon correctamente
```

### 4. Configuración de Archivos
```
Verificar que existan estos archivos:
✅ api-gateway/index.php
✅ api-gateway/.htaccess
✅ microservicio-usuarios/index.php
✅ microservicio-usuarios/.htaccess
✅ microservicio-datos/index.php
✅ microservicio-datos/.htaccess
✅ microservicio-notificaciones/index.php
✅ microservicio-notificaciones/.htaccess
✅ dashboard.html
```

---

## 🎮 USO DEL SISTEMA

### 1. Acceso al Sistema
```
URL Principal: http://localhost/microservicios/dashboard.html
```

### 2. Pantalla de Login
- **Email**: `demo@microservicios.com`
- **Password**: `password`

### 3. Funciones Disponibles
- ✅ **Registro de usuarios**
- ✅ **Inicio de sesión**
- ✅ **Gestión de items**
- ✅ **Envío de notificaciones**
- ✅ **Monitoreo del sistema**
- ✅ **Dashboard unificado**

---

## 🔧 FUNCIONALIDADES

### 🔐 AUTENTICACIÓN

#### Registro de Usuario
```
1. Hacer clic en "Crear Cuenta"
2. Completar formulario:
   - Nombre completo
   - Email válido
   - Password (mínimo 6 caracteres)
3. Hacer clic en "Crear Cuenta"
4. Confirmar mensaje de éxito
```

#### Inicio de Sesión
```
1. Ingresar email y password
2. Hacer clic en "Iniciar Sesión"
3. Verificar mensaje de login exitoso
4. Acceder al dashboard principal
```

### 📊 GESTIÓN DE ITEMS

#### Crear Item
```
1. En el dashboard, ir a "Gestión de Items"
2. Completar formulario:
   - Nombre del item
   - Descripción
   - Estado (activo/inactivo)
3. Hacer clic en "Crear Item"
4. Verificar que aparece en la lista
```

#### Ver Items
```
1. Los items se cargan automáticamente
2. Usar "Actualizar Lista" para refrescar
3. Cada item muestra:
   - Nombre
   - Descripción
   - Estado
   - Fecha de creación
```

#### Eliminar Item
```
1. Hacer clic en el botón "×" del item
2. Confirmar eliminación
3. El item desaparece de la lista
```

### 📧 SISTEMA DE NOTIFICACIONES

#### Enviar Notificación
```
1. En el dashboard, ir a "Enviar Notificación"
2. Completar formulario:
   - Destinatario (email)
   - Asunto
   - Mensaje
3. Hacer clic en "Enviar Notificación"
4. Confirmar mensaje de éxito
```

#### Ver Historial de Notificaciones
```
1. Hacer clic en "Ver Historial"
2. Se muestran todas las notificaciones enviadas
3. Cada notificación incluye:
   - Asunto
   - Destinatario
   - Mensaje
   - Estado
   - Fecha de envío
```

### 🏥 MONITOREO DEL SISTEMA

#### Verificar Estado
```
1. Hacer clic en "Verificar Estado"
2. Se muestra el estado de todos los microservicios:
   - API Gateway
   - Microservicio de Usuarios
   - Microservicio de Datos
   - Microservicio de Notificaciones
3. Se incluyen tiempos de respuesta y estado
```

---

## 🚨 SOLUCIÓN DE PROBLEMAS

### Error: "Error de conexión"
```
✅ Verificar que XAMPP esté ejecutándose
✅ Verificar que Apache y MySQL estén activos
✅ Verificar URL: http://localhost/microservicios/
✅ Limpiar caché del navegador (Ctrl+F5)
```

### Error: "Unexpected token '<'"
```
✅ Verificar que los microservicios estén funcionando
✅ Probar endpoints individuales
✅ Verificar archivos .htaccess
✅ Revisar consola del navegador (F12)
```

### Error: "Endpoint no encontrado"
```
✅ Verificar que los microservicios estén activos
✅ Verificar rutas en el API Gateway
✅ Verificar archivos .htaccess
✅ Revisar logs de Apache
```

### Base de Datos no Conecta
```
✅ Verificar que MySQL esté ejecutándose
✅ Verificar que las bases de datos existan
✅ Verificar credenciales en config/database.php
✅ Probar conexión en phpMyAdmin
```

### Items/Notificaciones no se Guardan
```
✅ Verificar conexión a la base de datos
✅ Verificar que las tablas existan
✅ Verificar permisos de MySQL
✅ Usar endpoints de debug para diagnosticar
```

---

## 📁 ESTRUCTURA DEL PROYECTO

```
microservicios/
├── api-gateway/
│   ├── index.php
│   └── .htaccess
├── microservicio-usuarios/
│   ├── index.php
│   ├── .htaccess
│   ├── config/
│   │   └── database.php
│   └── models/
│       └── User.php
├── microservicio-datos/
│   ├── index.php
│   ├── .htaccess
│   ├── config/
│   │   └── database.php
│   ├── models/
│   │   └── Item.php
│   ├── real-items.php
│   ├── debug-items.php
│   └── test-db.php
├── microservicio-notificaciones/
│   ├── index.php
│   ├── .htaccess
│   ├── config/
│   │   └── database.php
│   ├── models/
│   │   └── Notification.php
│   ├── real-notifications.php
│   ├── debug-notifications.php
│   └── test-db.php
├── dashboard.html
├── test-login.html
└── MANUAL-USUARIO.md
```

---

## 🔍 COMANDOS ÚTILES

### Verificar Servicios XAMPP
```
1. Abrir XAMPP Control Panel
2. Verificar estado de Apache (puerto 80)
3. Verificar estado de MySQL (puerto 3306)
4. Verificar logs si hay errores
```

### Probar Microservicios Individualmente
```
1. API Gateway: http://localhost/microservicios/api-gateway/health
2. Usuarios: http://localhost/microservicios/microservicio-usuarios/
3. Datos: http://localhost/microservicios/microservicio-datos/
4. Notificaciones: http://localhost/microservicios/microservicio-notificaciones/
```

### Limpiar Caché del Navegador
```
Chrome/Firefox: Ctrl + Shift + R
Edge: Ctrl + F5
```

### Abrir Herramientas de Desarrollador
```
F12 o Ctrl + Shift + I
```

---

## 📞 SOPORTE TÉCNICO

### Archivos de Log
```
XAMPP Logs: C:\xampp\apache\logs\
PHP Errors: C:\xampp\php\logs\
```

### Verificar Errores
```
1. Consola del navegador (F12)
2. Logs de Apache
3. Logs de PHP
4. Logs de MySQL
```

### Endpoints de Debug
```
1. Test DB Items: http://localhost/microservicios/microservicio-datos/test-db.php
2. Test DB Notifications: http://localhost/microservicios/microservicio-notificaciones/test-db.php
3. Debug Items: http://localhost/microservicios/microservicio-datos/debug-items.php
4. Debug Notifications: http://localhost/microservicios/microservicio-notificaciones/debug-notifications.php
```

---

## 🎉 ¡FELICITACIONES!

Has configurado exitosamente el **Sistema de Microservicios**. 

### Próximos Pasos:
1. ✅ **Probar todas las funcionalidades**
2. ✅ **Crear varios usuarios de prueba**
3. ✅ **Gestionar items de ejemplo**
4. ✅ **Enviar notificaciones de prueba**
5. ✅ **Monitorear el estado del sistema**
6. ✅ **Explorar el código para aprender**

### Recursos Adicionales:
- 📖 **Documentación PHP**: https://www.php.net/
- 📖 **Documentación MySQL**: https://dev.mysql.com/doc/
- 📖 **Documentación JavaScript**: https://developer.mozilla.org/es/docs/Web/JavaScript
- 📖 **Arquitectura de Microservicios**: https://microservices.io/

---

## 🔄 FLUJO DE TRABAJO TÍPICO

### 1. Inicio de Sesión
```
Usuario → Dashboard → Login → API Gateway → Microservicio Usuarios → Base de Datos
```

### 2. Creación de Item
```
Usuario → Dashboard → Crear Item → Microservicio Datos → Base de Datos
```

### 3. Envío de Notificación
```
Usuario → Dashboard → Enviar Notificación → Microservicio Notificaciones → Base de Datos
```

### 4. Monitoreo del Sistema
```
Usuario → Dashboard → Verificar Estado → API Gateway → Todos los Microservicios
```

---

**🔄 Última actualización**: Enero 2025  
**👨‍💻 Desarrollado por**: Sistema de Microservicios - Arquitecturas 2025  
**📧 Contacto**: Soporte técnico del proyecto



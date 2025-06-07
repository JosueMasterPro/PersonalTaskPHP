
---

# README para el **Backend Slim PHP (Railway)**

```markdown
# PersonalTaskPHP - Backend Slim PHP para TaskManager

Backend desarrollado en Slim PHP para gestionar tareas y autenticación.

---

## 🚀 Requisitos

- PHP 8.0+  
- Composer  
- Base de datos MySQL (usaremos Railway para el hosting)

---
## Dependencias
    -slim/slim: ^4.14,
    -slim/psr7: ^1.7,
    -symfony/polyfill-php80: ^1.32,
    -vlucas/phpdotenv: ^5.6,
    -phpmailer/phpmailer: ^6.10

## 📦 Instalación local

1. Clona el repositorio:

```sh
git clone https://github.com/JosueMasterPro/PersonalTaskPHP.git
```

```sh
cd PersonalTaskPHP
 ```

## Instala dependencias con Composer:
```sh
composer install
```

## crear un archio .env
```sh
MYSQL_URL=URL
MYSQLDATABASE=Nombre_base_de_datos
MYSQLHOST=host
MYSQLPASSWORD=contraseña
MYSQLPORT=puerto
MYSQLUSER=usuario
```
## 🗄️ Configuración de la base de datos
 Los archivos .sql para crear la estructura de la base están en la carpeta Database/

 Importarlos Usando Mysql Workbench o consola
```sh
mysql -u usuario -p nombre_base < Database/archivo.sql
```

🏗️ Despliegue en Railway
 Crea una cuenta y un proyecto en [Railway](https://railway.app/)
 Conecta tu repositorio de github con backend para deploy automatico en railway
 no subas la carpeta vendor al repositorio o fallara
 Configura las variables de entrono en Railway (settings > Variables):

```sh
DB_HOST=host_railway
DB_NAME=nombre_base_railway
DB_USER=usuario_railway
DB_PASS=contraseña_railway
JWT_SECRET=tu_clave_secreta

```
 posiblemnte te falten variables para la funcion enviar correo
## Configuración SMTP para envío de correos (PHPMailer)

```sh
SMTP_HOST=smtp.ejemplo.com // yo use smtp.gmail.com
SMTP_USER=tu_usuario_smtp
SMTP_PASS=tu_contraseña_smtp
SMTP_PORT=587
SMTP_FROM=correo@tu-dominio.com
SMTP_FROM_NAME="Nombre Remitente"
```

Tabla tarea: 

| id | id_usuario | titulo | tipo | descripcion | completado | fecha_final | fecha_creacion|
|----|------------|--------|------|-------------|------------|-------------|---------------|

Tabla usuarios:

| id | correo | password | rol |
|----|--------|----------|-----|

Tabla roles:

| id | id_usuarios | rol |
|----|-------------|-----|
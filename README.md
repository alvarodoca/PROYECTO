# Proyecto Final - Sistema Web para Despliegue Automatizado de Infraestructura FTP en AWS usando Terraform

## 📌 Información Básica del Proyecto

Este proyecto consiste en el despliegue de una infraestructura en AWS utilizando **Terraform** y **EKS (Elastic Kubernetes Service)**. Dentro del clúster de EKS se lanza un **Deployment** con una imagen de **nginx** configurada para servir una página en **PHP** que incluye autenticación. Esta página registra los inicios de sesión en una base de datos.

Además, al interactuar con la aplicación, se puede desplegar mediante Terraform una arquitectura compuesta por:

- Una **VPC** personalizada.
- Dos subredes: una **pública** y otra **privada**.
- Un **grupo de seguridad** configurado adecuadamente.
- Una **instancia EC2** que contiene un servidor **FTP con TLS**.
- Un **bucket de S3** que almacena los archivos subidos al servidor FTP.
- Un **segundo bucket de S3** destinado a almacenar el archivo `terraform.tfstate`.

Cada vez que se despliega o destruye la infraestructura, el archivo `terraform.tfstate` se guarda o actualiza automáticamente en el bucket correspondiente.

---

## 📁 Estructura del Repositorio

* ./backend/*
    * Todos los archivos terraform.

* ./config/*
    * Es el archivo de la configuración de la base de datos en Sqlite.

* ./frontend/*
    * Todos los archivos php que se van a mostrar en la página.

* ./k8s/*
    * Esta el deployment y el service de eks.

* ./Dockerfile

* ./nginx.conf

**El resto de archivos y directorios que no estan nombrados en la estructura fueron pruebas.**

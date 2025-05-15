FROM php:8.2-fpm-alpine3.18

# 1. Instala dependencias
RUN apk add --no-cache \
    nginx \
    aws-cli \
    wget \
    unzip \
    sudo && \
    wget https://releases.hashicorp.com/terraform/1.5.7/terraform_1.5.7_linux_amd64.zip && \
    unzip terraform_1.5.7_linux_amd64.zip -d /usr/local/bin && \
    rm terraform_1.5.7_linux_amd64.zip && \
    chmod +x /usr/local/bin/terraform

# 2. Configura usuarios y permisos (usando el www-data existente)
RUN mkdir -p /var/www/html /var/www/terraform && \
    chown -R www-data:www-data /var/www && \
    echo "www-data ALL=(ALL) NOPASSWD: /usr/local/bin/terraform" >> /etc/sudoers

# 3. Copia archivos de la aplicación
COPY --chown=www-data:www-data ./frontend /var/www/html/proyecto_ftp
COPY --chown=www-data:www-data ./backend /var/www/terraform

# 4. Configuración de Nginx
COPY ./nginx.conf /etc/nginx/nginx.conf

# 5. Puerto expuesto
EXPOSE 8080

# 6. Comando de inicio
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"
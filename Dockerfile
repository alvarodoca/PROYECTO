FROM php:8.2-fpm-alpine3.18

# 1. Instalación de dependencias optimizada
RUN apk add --no-cache \
    nginx \
    aws-cli \
    sudo \
    libcap \
    && apk add --no-cache --virtual .build-deps \
    wget \
    unzip

# 2. Instalar Terraform con permisos especiales
RUN wget -q https://releases.hashicorp.com/terraform/1.5.7/terraform_1.5.7_linux_amd64.zip -O /tmp/terraform.zip \
    && unzip /tmp/terraform.zip -d /usr/local/bin \
    && rm /tmp/terraform.zip \
    && chmod 0755 /usr/local/bin/terraform \
    && setcap 'cap_dac_override=eip' /usr/local/bin/terraform

# 3. Configuración del entorno
RUN mkdir -p /var/www/html/proyecto_ftp \
    /var/www/terraform \
    /var/www/.aws \
    && adduser www-data root \
    && chown -R www-data:www-data /var/www \
    && echo "www-data ALL=(ALL:ALL) NOPASSWD: /usr/bin/terraform" >> /etc/sudoers \
    && echo "alias terraform='/usr/local/bin/terraform'" >> /etc/profile.d/alias.sh

# 4. Variables de entorno esenciales
ENV PATH="/usr/local/bin:${PATH}" \
    TF_IN_AUTOMATION=true \
    TF_PLUGIN_CACHE_DIR="/var/www/.terraform.d/plugin-cache"

# 5. Copiar archivos de la aplicación
COPY --chown=www-data:www-data ./frontend /var/www/html/proyecto_ftp
COPY --chown=www-data:www-data ./backend /var/www/terraform
COPY --chown=www-data:www-data ./aws-config/credentials /var/www/.aws/

# 6. Configuración de Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# 7. Configuración del workspace
WORKDIR /var/www/terraform

# 8. Puerto expuesto
EXPOSE 8080

# 9. Comando de inicio optimizado
CMD ["sh", "-c", "chown -R www-data:www-data /var/www && php-fpm -D && exec nginx -g 'daemon off;'"]

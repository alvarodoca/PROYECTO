# Grupo de seguridad para la instancia en VPC 1
resource "aws_security_group" "sg_vpc1" {
  vpc_id = aws_vpc.vpc1.id

    # Regla de entrada para puerto SSH (22)
  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }


  # Reglas de entrada para puertos 20 y 21
  ingress {
    from_port   = 20
    to_port     = 21
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  # Reglas de entrada para puertos 1024 hasta 1048
  ingress {
    from_port   = 1024
    to_port     = 1048
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # Regla de salida para permitir todo el tráfico
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "sg_vpc1_instance"
  }
}



# Instancia EC2 en la subred pública de VPC 1
resource "aws_instance" "ftp_instancia" {
  ami                          = "ami-064519b8c76274859"
  instance_type                = "t2.micro"
  subnet_id                    = aws_subnet.public_subnet_vpc1.id
  associate_public_ip_address  = true
  vpc_security_group_ids       = [aws_security_group.sg_vpc1.id]
  key_name                     = "proyecto"
  tags = {
    Name = "ftp_instancia"
  }


    user_data = <<-EOF
    #!/bin/bash
    # Actualizar el repositorio e instalar Docker y S3FS
    sudo apt-get update
    sudo apt-get install -y ldap-utils
    sudo apt-get install -y docker.io s3fs
    

    # Iniciar servicio Docker
    sudo systemctl start docker
    sudo systemctl enable docker

    # Crear Dockerfile para ProFTP
    mkdir -p /home/admin/proftp
    cat << 'EOT' > /home/admin/proftp/Dockerfile
    FROM debian:latest
    RUN apt-get update && apt-get install -y proftpd openssl nano
    RUN apt-get install -y proftpd-mod-crypto
   
    # Generar el certificado SSL/TLS para ProFTPD
    RUN openssl req -x509 -newkey rsa:2048 -sha256 -keyout /etc/ssl/private/proftpd.key -out /etc/ssl/certs/proftpd.crt -nodes -days 365 -subj "/C=ES/ST=España/L=Atarfe/O=alvaro/OU=alvaro/CN=ftp.alvaro.com"


    # Configuración de ProFTPD
    RUN echo "Include /etc/proftpd/tls.conf" >> /etc/proftpd/proftpd.conf && \
    echo "LoadModule mod_ctrls_admin.c" >> /etc/proftpd/modules.conf && \
    echo "<IfModule mod_tls.c>" >> /etc/proftpd/tls.conf && \
    echo "  TLSEngine on" >> /etc/proftpd/tls.conf && \
    echo "  TLSLog /var/log/proftpd/tls.log" >> /etc/proftpd/tls.conf && \
    echo "  TLSProtocol SSLv23" >> /etc/proftpd/tls.conf && \
    echo "  TLSRSACertificateFile /etc/ssl/certs/proftpd.crt" >> /etc/proftpd/tls.conf && \
    echo "  TLSRSACertificateKeyFile /etc/ssl/private/proftpd.key" >> /etc/proftpd/tls.conf && \
    echo "</IfModule>" >> /etc/proftpd/tls.conf && \
    echo "LoadModule mod_tls.c" >> /etc/proftpd/modules.conf
    
    RUN echo "DefaultRoot /home/alvaro" >> /etc/proftpd/proftpd.conf && \
    echo "PassivePorts 1024 1048" >> /etc/proftpd/proftpd.conf && \
    echo "Port 21" >> /etc/proftpd/proftpd.conf && \
    echo "Port 20" >> /etc/proftpd/proftpd.conf && \
    echo "<Anonymous /home/alvaro>" >> /etc/proftpd/proftpd.conf && \
    echo "  RequireValidShell off" >> /etc/proftpd/proftpd.conf && \
    echo "  User ftp" >> /etc/proftpd/proftpd.conf && \
    echo "  Group nogroup" >> /etc/proftpd/proftpd.conf && \
    echo "  UserAlias anonymous ftp" >> /etc/proftpd/proftpd.conf && \
    echo "  <Limit WRITE>" >> /etc/proftpd/proftpd.conf && \
    echo "    DenyAll" >> /etc/proftpd/proftpd.conf && \
    echo "  </Limit>" >> /etc/proftpd/proftpd.conf && \
    echo "  <Limit READ>" >> /etc/proftpd/proftpd.conf && \
    echo "    AllowAll" >> /etc/proftpd/proftpd.conf && \
    echo "  </Limit>" >> /etc/proftpd/proftpd.conf && \
    echo "</Anonymous>" >> /etc/proftpd/proftpd.conf


    # Crear usuario FTP
    RUN adduser --disabled-password --gecos "" alvaro && \
    echo "alvaro:alvaro" | chpasswd
 
    CMD ["proftpd", "--nodaemon"]
    EXPOSE 20 21 1024-1048

    EOT

    # Crear archivo de credenciales de AWS
    sudo mkdir -p ~/.aws
    cat > ~/.aws/credentials <<-AWS_CREDENTIALS
    [default]
    aws_access_key_id=ASIAYS2NW4H6L24TS6JU
    aws_secret_access_key=nYSl3fMDlq54V9+pNgUuK4mL4cwOXt76Ud9I6iIv
    aws_session_token=IQoJb3JpZ2luX2VjENP//////////wEaCXVzLXdlc3QtMiJGMEQCIBCNubxoGpfi8UeQLr+xcTHwsZMSTBmXdYhGZT1B2Rh5AiBck6hSrni4VXnDwS+J8RwXuZvxQPe3PnOwiLPDLCdNliq8Agis//////////8BEAAaDDU5MDE4NDExMjYzNiIMQ67iPFjzicsNCYNiKpACda6mUEUkiC9vvJbAX1MQENK4S7EuT/+3m+5mxQp19p2egwRlJM8/mFxEubpgt3U7LUv3yh1jdBJempLcdAmlPuqq2rDgh0Pd1Sy1vY19EOGizcwP/Me2eE5YAY3FVPVug9qbaCwLDQM6VQVUn1H3V/a1dym8Mvm8Xovva64pwyMbdayt0oHzn12bAQGW+VDwiPUF3wlXTn8z0e7upqIe0prPDsqZZQlYT8VQtDraDqSnFgFqoo+IpoQjsBbGmecFW3HHqfZErjIXztb4ZINpodh9kiKdwoB3OwyG0SpNQfvOGXWgIVs81qSXS18e5+OndDsA5co7amrrZOD5twbtxxKNrFQOcjyuzfXg43+9wGYwsdOcwgY6ngEs4ZDSIth6NIaDvDs4qDXxM5V41eAsP+8R6u2d6jgeJFEBYcwGrrTyYP0cEwwdt5/AMKvq17OASU2LgcGTFFyroN+08SnJcOUXklHmbLM1CLctKGLKe79Acc418AsjSu0B8SYFGqQpp89CAGVo1lv89hFNk6yqrFZeDE3bXA3dQDw9jfdbUhDLaTOIboY1RIZ7nLvrF7ME12H7mnP1EA==
    AWS_CREDENTIALS

    # Crear directorio del bucket de S3
    sudo mkdir -p /home/admin/carpeta_bucket
    sudo chmod 755 /home/admin/carpeta_bucket
    
    # Montar el bucket S3
    sudo s3fs proyecto-alvaro /home/admin/carpeta_bucket -o allow_other

    # Crear y ejecutar el contenedor Docker
    cd /home/admin/proftp
    sudo docker build -t proftp .
    sudo docker run -d --name proftp -p 21:21 -p 20:20 -p 1024-1048:1024-1048 -v /home/admin/carpeta_bucket:/home/alvaro proftp
  EOF

}

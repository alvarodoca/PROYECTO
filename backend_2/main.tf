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
    sudo apt-get update -y
    sudo apt-get install -y ldap-utils docker.io s3fs

    sudo systemctl start docker
    sudo systemctl enable docker

    mkdir -p /home/admin/proftp
    cat << 'EOT' > /home/admin/proftp/Dockerfile
    FROM debian:latest
    RUN apt-get update && apt-get install -y proftpd openssl nano ldap-utils
    RUN apt-get install -y proftpd-mod-crypto proftpd-mod-ldap

    RUN openssl req -x509 -newkey rsa:2048 -sha256 -keyout /etc/ssl/private/proftpd.key -out /etc/ssl/certs/proftpd.crt -nodes -days 365 -subj "/C=ES/ST=España/L=Atarfe/O=alvaro/OU=alvaro/CN=ftp.alvaro.com"

    RUN adduser --disabled-password --gecos "" alvaro
    RUN echo "alvaro:alvaro" | chpasswd
    
    RUN sed -i '/<IfModule mod_quotatab.c>/,/<\/IfModule>/d' /etc/proftpd/proftpd.conf
    RUN echo "LoadModule mod_tls.c" >> /etc/proftpd/modules.conf
    RUN echo "LoadModule mod_ctrls_admin.c" >> /etc/proftpd/modules.conf
    RUN echo "LoadModule mod_ldap.c" >> /etc/proftpd/modules.conf
    RUN echo "<IfModule mod_tls.c>" > /etc/proftpd/tls.conf
    RUN echo "  TLSEngine on" >> /etc/proftpd/tls.conf
    RUN echo "  TLSLog /var/log/proftpd/tls.log" >> /etc/proftpd/tls.conf
    RUN echo "  TLSProtocol SSLv23" >> /etc/proftpd/tls.conf
    RUN echo "  TLSRSACertificateFile /etc/ssl/certs/proftpd.crt" >> /etc/proftpd/tls.conf
    RUN echo "  TLSRSACertificateKeyFile /etc/ssl/private/proftpd.key" >> /etc/proftpd/tls.conf
    RUN echo "</IfModule>" >> /etc/proftpd/tls.conf
    RUN mkdir -p /etc/proftpd/ssl
    
    RUN echo "Include /etc/proftpd/tls.conf" >> /etc/proftpd/proftpd.conf
    
    RUN mkdir /home/ftp
    RUN chmod 777 -R /home/ftp
    RUN echo "DefaultRoot /home/ftp" >> /etc/proftpd/proftpd.conf
    RUN echo "PassivePorts 1024 1048" >> /etc/proftpd/proftpd.conf
    RUN echo "<Anonymous /home/ftp>" >> /etc/proftpd/proftpd.conf
    RUN echo "  User ftp" >> /etc/proftpd/proftpd.conf
    RUN echo "  Group nogroup" >> /etc/proftpd/proftpd.conf
    RUN echo "  UserAlias anonymous ftp" >> /etc/proftpd/proftpd.conf
    RUN echo "  RequireValidShell off" >> /etc/proftpd/proftpd.conf
    RUN echo "  MaxClients 10" >> /etc/proftpd/proftpd.conf
    RUN echo "  <Directory *>" >> /etc/proftpd/proftpd.conf
    RUN echo "    <Limit WRITE>" >> /etc/proftpd/proftpd.conf
    RUN echo "      DenyAll" >> /etc/proftpd/proftpd.conf
    RUN echo "    </Limit>" >> /etc/proftpd/proftpd.conf
    RUN echo "  </Directory>" >> /etc/proftpd/proftpd.conf
    RUN echo "</Anonymous>" >> /etc/proftpd/proftpd.conf
    
    
    
    
    CMD ["proftpd", "--nodaemon", "--debug", "10"]
    EXPOSE 20 21 1024-1048
    EOT

    # Crear archivo de credenciales de AWS
    sudo mkdir -p ~/.aws
    cat > ~/.aws/credentials <<-AWS_CREDENTIALS
    [default]
    aws_access_key_id=ASIAYS2NW4H6LUEDVFRQ
    aws_secret_access_key=gHmCzzjxd3CizYpjArzf04Ba5dNBjUTSLuGQiOWV
    aws_session_token=IQoJb3JpZ2luX2VjEIf//////////wEaCXVzLXdlc3QtMiJIMEYCIQCy6SkvXB1cIcS1uHCeR1BhVpMPSFZAa0hBf61jiEaH0QIhAIWddb7Qtj4IrYED95KHdo02CcCt9FBG0VhDg/WjCZ87KrMCCDAQABoMNTkwMTg0MTEyNjM2IgxoWo/fei9gAw7YVJYqkAIhOWkG6OjvDvsEE6VFb3C65og3SS7WpTqGwCLWjMdiNiSap0TuRDfHKI+QdbF/tejmv+LFlRttMc7FiG6FOvuZW0ZNLeH1kpcGHErt1CqLc7SkWw0EUNsUXZTzFIlAeys80pRcDJG2SGJBsY+4J9FOqAIDHxOJCXptvEoNqzcpeyfgc7ZbZyYFtSwmPgqXu7vkBJthwgf/nFG0zE3pJyZu1W3cAOH36v9r9SmsGZaeLGFdMPrr0QzBIIP1tk0L9XAoB3KFMmkStCNp7gcR4Sj+OXxOEFRSxC/VlW45hZiS0mmMVsskDtDoqu6WdZmLUehljFwPB95rjmTSPoypYHqHerJdHbfp8td5PJv+L4A5KTCdk+PABjqcAXbvzBKzaaEUEXIl8uDL/RYwKeBpb+weHQcNkaXOpfu+qPCv9Q0QV3n/U6dRQraBFoxAuxTjuFKni4Xk+mj3GtA9nMlxiKr5zMkjL6R1yZge4+ry+lEUedaS9LQ6Ey638tw/Y3oftWdhRdzdPN/2jghQW93g1v8Ogkoxy5Kv7dtTh3ql9o4Np8jrvR5XQm+w2fAr/xYXnaEojnKxVg==
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



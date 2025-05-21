output "ftp_server_ip" {
  value = aws_instance.ftp_instancia.public_ip
}

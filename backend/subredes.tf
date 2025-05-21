# VPC 1 con CIDR 192.168.3.0/16
resource "aws_vpc" "vpc1" {
  cidr_block = "192.168.3.0/24"
  tags = {
    Name = "vpc1"
  }
}

# Subred pública en VPC 1 (dentro de 192.168.3.0/25)
resource "aws_subnet" "public_subnet_vpc1" {
  vpc_id            = aws_vpc.vpc1.id
  cidr_block        = "192.168.3.0/25"
  availability_zone = "us-east-1a"
  tags = {
    Name = "public_subnet_vpc1"
  }
}

# Subred privada en VPC 1 (dentro de 192.168.3.0/25)
resource "aws_subnet" "private_subnet_vpc1" {
  vpc_id            = aws_vpc.vpc1.id
  cidr_block        = "192.168.3.128/25"
  availability_zone = "us-east-1a"
  tags = {
    Name = "private_subnet_vpc1"
  }
}


resource "aws_internet_gateway" "igw" {
  vpc_id = aws_vpc.vpc1.id
  tags = {
    Name = "vpc1_igw"
  }
}


resource "aws_route_table" "public_rt" {
  vpc_id = aws_vpc.vpc1.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.igw.id
  }

  tags = {
    Name = "public_rt"
  }
}

resource "aws_route_table_association" "public_assoc" {
  subnet_id      = aws_subnet.public_subnet_vpc1.id
  route_table_id = aws_route_table.public_rt.id
}
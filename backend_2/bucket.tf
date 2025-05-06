resource "aws_s3_bucket" "bucket_del_ftp" { 
    bucket = "proyecto-alvaro"
    tags = { 
        Name = "bucket_ftp" 
        } 
}
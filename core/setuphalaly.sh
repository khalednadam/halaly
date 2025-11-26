#!/bin/bash

# ==============================
# Variables
# ==============================
PROJECT_PATH="$HOME/laravelProjects/halaly/core"
DOMAIN="halaly-dev.com"
SSL_PATH="/etc/ssl/halaly"
PHP_VERSION="8.1"  # عدّل حسب نسخة PHP عندك

# ==============================
# Step 0: تأكد من تحديث النظام
# ==============================
sudo apt update && sudo apt upgrade -y

# ==============================
# Step 1: تثبيت Nginx و PHP و الامتدادات اللازمة
# ==============================
sudo apt install -y nginx php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-mysql php${PHP_VERSION}-xml php${PHP_VERSION}-bcmath php${PHP_VERSION}-curl unzip curl

# ==============================
# Step 2: إنشاء شهادة SSL
# ==============================
sudo mkdir -p $SSL_PATH
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
-keyout $SSL_PATH/$DOMAIN.key -out $SSL_PATH/$DOMAIN.crt \
-subj "/C=US/ST=State/L=City/O=Local/OU=Dev/CN=$DOMAIN"

# ==============================
# Step 3: إعداد Nginx Virtual Host
# ==============================
NGINX_CONF="/etc/nginx/sites-available/$DOMAIN"
sudo tee $NGINX_CONF > /dev/null <<EOL
server {
    listen 80;
    server_name $DOMAIN;
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl;
    server_name $DOMAIN;

    root $PROJECT_PATH/public;
    index index.php index.html;

    ssl_certificate     $SSL_PATH/$DOMAIN.crt;
    ssl_certificate_key $SSL_PATH/$DOMAIN.key;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOL

# ==============================
# Step 4: تفعيل الموقع وإعادة تشغيل Nginx
# ==============================
sudo ln -sf $NGINX_CONF /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# ==============================
# Step 5: صلاحيات Laravel
# ==============================
sudo chown -R $USER:www-data $PROJECT_PATH
sudo chmod -R 775 $PROJECT_PATH/storage
sudo chmod -R 775 $PROJECT_PATH/bootstrap/cache

# ==============================
# Done
# ==============================
echo "-----------------------------------------"
echo "تم إعداد Laravel على: https://$DOMAIN"
echo "يمكنك الآن فتح المتصفح وتجاهل تحذير الشهادة self-signed"
echo "-----------------------------------------"

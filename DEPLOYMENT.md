# 部署指南 - IT 資產與報修管理系統

## 📋 部署前檢查清單

### 系統需求確認
- [ ] PHP 7.4+ (建議 8.0+)
- [ ] MySQL 5.7+ / MariaDB 10.3+
- [ ] Apache 2.4+ / Nginx 1.16+
- [ ] Node.js 16.0+ (建置前端)
- [ ] 至少 2GB RAM
- [ ] 至少 10GB 儲存空間
- [ ] SSL 憑證 (生產環境)

### 檔案準備
- [ ] 專案原始碼
- [ ] 資料庫結構檔案 (database.sql)
- [ ] 環境配置檔案
- [ ] SSL 憑證檔案

---

## 🚀 快速部署 (Ubuntu/Debian)

### 1. 系統更新與軟體安裝
```bash
# 更新系統
sudo apt update && sudo apt upgrade -y

# 安裝必要軟體
sudo apt install -y apache2 mysql-server php8.0 php8.0-mysql php8.0-gd \
    php8.0-mbstring php8.0-zip php8.0-curl php8.0-xml unzip curl

# 安裝 Node.js (用於建置前端)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# 啟用 Apache 模組
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers

# 重啟 Apache
sudo systemctl restart apache2
```

### 2. 資料庫設定
```bash
# 安全安裝 MySQL
sudo mysql_secure_installation

# 登入 MySQL
sudo mysql -u root -p

# 創建資料庫和用戶
CREATE DATABASE mis_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mis_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON mis_system.* TO 'mis_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# 匯入資料庫結構
mysql -u mis_user -p mis_system < database.sql
```

### 3. 部署後端
```bash
# 創建專案目錄
sudo mkdir -p /var/www/mis
sudo chown -R $USER:$USER /var/www/mis

# 上傳/複製專案檔案
# (使用 git clone, scp, 或直接複製)
git clone [your-repo] /var/www/mis
# 或
# scp -r ./MIS/* user@server:/var/www/mis/

# 設定後端環境
cd /var/www/mis/backend
cp .env.example .env
nano .env

# 設定檔案權限
sudo chown -R www-data:www-data /var/www/mis/backend
sudo chmod -R 755 /var/www/mis/backend
sudo chmod -R 777 /var/www/mis/backend/uploads
sudo chmod -R 777 /var/www/mis/backend/logs

# 創建必要目錄
mkdir -p /var/www/mis/backend/uploads/{images,equipment,announcements,thumbnails}
mkdir -p /var/www/mis/backend/logs
```

### 4. 建置與部署前端
```bash
# 建置前端
cd /var/www/mis/frontend
npm install
cp .env.example .env
nano .env  # 設定 API URL

# 生產建置
npm run build

# 複製建置檔案到 Apache 目錄
sudo cp -r dist/* /var/www/html/
# 或創建專用目錄
sudo mkdir -p /var/www/mis-frontend
sudo cp -r dist/* /var/www/mis-frontend/
```

### 5. Apache 虛擬主機設定
```bash
# 創建後端虛擬主機
sudo nano /etc/apache2/sites-available/mis-api.conf
```

```apache
<VirtualHost *:80>
    ServerName api.your-domain.com
    DocumentRoot /var/www/mis/backend/public

    <Directory /var/www/mis/backend/public>
        AllowOverride All
        Require all granted

        # URL 重寫
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>

    # PHP 設定
    php_admin_value upload_max_filesize 10M
    php_admin_value post_max_size 10M
    php_admin_value max_execution_time 300
    php_admin_value memory_limit 256M

    # 安全標頭
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"

    ErrorLog ${APACHE_LOG_DIR}/mis-api-error.log
    CustomLog ${APACHE_LOG_DIR}/mis-api-access.log combined
</VirtualHost>
```

```bash
# 創建前端虛擬主機
sudo nano /etc/apache2/sites-available/mis-frontend.conf
```

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/mis-frontend

    <Directory /var/www/mis-frontend>
        AllowOverride All
        Require all granted

        # SPA 路由支援
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.html [L]
    </Directory>

    # 快取設定
    <LocationMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg)$">
        ExpiresActive On
        ExpiresDefault "access plus 1 month"
    </LocationMatch>

    # Gzip 壓縮
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript application/json
    </IfModule>

    ErrorLog ${APACHE_LOG_DIR}/mis-frontend-error.log
    CustomLog ${APACHE_LOG_DIR}/mis-frontend-access.log combined
</VirtualHost>
```

```bash
# 啟用網站
sudo a2ensite mis-api.conf
sudo a2ensite mis-frontend.conf
sudo a2dissite 000-default.conf

# 重載 Apache 設定
sudo systemctl reload apache2
```

### 6. SSL 憑證設定 (Let's Encrypt)
```bash
# 安裝 Certbot
sudo apt install certbot python3-certbot-apache

# 取得 SSL 憑證
sudo certbot --apache -d your-domain.com -d api.your-domain.com

# 設定自動更新
sudo crontab -e
# 添加：0 12 * * * /usr/bin/certbot renew --quiet
```

---

## 🐳 Docker 部署

### 1. 創建 Dockerfile (後端)
```dockerfile
# backend/Dockerfile
FROM php:8.0-apache

# 安裝必要擴展
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# 啟用 Apache 模組
RUN a2enmod rewrite

# 複製應用程式檔案
COPY . /var/www/html/
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# 設定權限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/logs

EXPOSE 80
```

### 2. 創建 Docker Compose
```yaml
# docker-compose.yml
version: '3.8'

services:
  # 資料庫
  mysql:
    image: mysql:8.0
    container_name: mis-mysql
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: mis_system
      MYSQL_USER: mis_user
      MYSQL_PASSWORD: mis_password
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database.sql:/docker-entrypoint-initdb.d/database.sql
    ports:
      - "3306:3306"
    restart: unless-stopped

  # 後端 API
  backend:
    build: ./backend
    container_name: mis-backend
    depends_on:
      - mysql
    environment:
      DB_HOST: mysql
      DB_DATABASE: mis_system
      DB_USERNAME: mis_user
      DB_PASSWORD: mis_password
    volumes:
      - ./backend/uploads:/var/www/html/uploads
      - ./backend/logs:/var/www/html/logs
    ports:
      - "8000:80"
    restart: unless-stopped

  # 前端 (使用 Nginx)
  frontend:
    image: nginx:alpine
    container_name: mis-frontend
    volumes:
      - ./frontend/dist:/usr/share/nginx/html
      - ./nginx.conf:/etc/nginx/nginx.conf
    ports:
      - "3000:80"
    restart: unless-stopped

  # Redis (選用)
  redis:
    image: redis:alpine
    container_name: mis-redis
    ports:
      - "6379:6379"
    restart: unless-stopped

volumes:
  mysql_data:
```

### 3. 部署 Docker
```bash
# 建置前端
cd frontend
npm install
npm run build

# 啟動服務
docker-compose up -d

# 查看日誌
docker-compose logs -f

# 停止服務
docker-compose down
```

---

## 🔧 進階配置

### Nginx 配置 (替代 Apache)
```nginx
# /etc/nginx/sites-available/mis-api
server {
    listen 80;
    server_name api.your-domain.com;
    root /var/www/mis/backend/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 安全設定
    location ~ /\. {
        deny all;
    }

    # 檔案上傳大小
    client_max_body_size 10M;
}

# /etc/nginx/sites-available/mis-frontend
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/mis-frontend;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # 靜態檔案快取
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }

    # Gzip 壓縮
    gzip on;
    gzip_types text/css application/javascript application/json;
}
```

### 效能優化配置

#### PHP-FPM 優化
```ini
; /etc/php/8.0/fpm/pool.d/www.conf
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.0-fpm.sock
pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 10
pm.max_requests = 500
```

#### MySQL 優化
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 32M
query_cache_type = 1
max_connections = 200
```

---

## 📊 監控與維護

### 系統監控腳本
```bash
#!/bin/bash
# monitor.sh - 系統監控腳本

echo "=== MIS 系統狀態檢查 $(date) ==="

# 檢查服務狀態
echo "## 服務狀態"
systemctl is-active apache2 nginx mysql php8.0-fpm | paste <(echo -e "Apache\nNginx\nMySQL\nPHP-FPM") -

# 檢查磁碟空間
echo -e "\n## 磁碟空間"
df -h / /var/www

# 檢查記憶體使用
echo -e "\n## 記憶體使用"
free -h

# 檢查 MySQL 連線數
echo -e "\n## MySQL 連線數"
mysql -u root -p$MYSQL_ROOT_PASSWORD -e "SHOW STATUS LIKE 'Threads_connected';" 2>/dev/null

# 檢查日誌錯誤
echo -e "\n## 最近錯誤日誌"
tail -n 5 /var/log/apache2/error.log
tail -n 5 /var/www/mis/backend/logs/app.log
```

### 自動備份腳本
```bash
#!/bin/bash
# backup.sh - 自動備份腳本

BACKUP_DIR="/var/backups/mis"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="mis_system"
DB_USER="mis_user"
DB_PASS="mis_password"

# 創建備份目錄
mkdir -p $BACKUP_DIR/{database,files}

# 備份資料庫
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/database/db_$DATE.sql.gz

# 備份檔案
tar -czf $BACKUP_DIR/files/uploads_$DATE.tar.gz -C /var/www/mis/backend uploads/
tar -czf $BACKUP_DIR/files/config_$DATE.tar.gz -C /var/www/mis/backend .env

# 清理舊備份 (保留 30 天)
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "備份完成: $DATE"
```

### 設定自動化任務
```bash
# 編輯 crontab
sudo crontab -e

# 添加定時任務
# 每天 2:00 備份
0 2 * * * /usr/local/bin/backup.sh >> /var/log/backup.log 2>&1

# 每小時檢查系統狀態
0 * * * * /usr/local/bin/monitor.sh >> /var/log/monitor.log 2>&1

# 每週清理日誌
0 0 * * 0 find /var/log -name "*.log" -mtime +7 -delete

# SSL 憑證自動更新
0 12 * * * /usr/bin/certbot renew --quiet
```

---

## 🔒 安全加固

### 防火牆設定
```bash
# 安裝 UFW
sudo apt install ufw

# 設定基本規則
sudo ufw default deny incoming
sudo ufw default allow outgoing

# 允許必要端口
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# 限制 SSH 連線
sudo ufw limit ssh

# 啟用防火牆
sudo ufw enable
```

### Fail2Ban 設定
```bash
# 安裝 Fail2Ban
sudo apt install fail2ban

# 配置 Fail2Ban
sudo nano /etc/fail2ban/jail.local
```

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[apache-auth]
enabled = true
port = http,https
filter = apache-auth
logpath = /var/log/apache2/*error.log

[apache-badbots]
enabled = true
port = http,https
filter = apache-badbots
logpath = /var/log/apache2/*access.log
```

### 檔案權限安全
```bash
# 設定嚴格的檔案權限
sudo chown -R root:root /var/www/mis
sudo chown -R www-data:www-data /var/www/mis/backend/uploads
sudo chown -R www-data:www-data /var/www/mis/backend/logs

# 移除群組和其他用戶的寫入權限
sudo chmod -R o-w /var/www/mis
sudo chmod -R g-w /var/www/mis

# 設定上傳目錄權限
sudo chmod 755 /var/www/mis/backend/uploads
sudo chmod 644 /var/www/mis/backend/uploads/*

# 保護敏感檔案
sudo chmod 600 /var/www/mis/backend/.env
```

---

## 📈 效能監控工具

### 安裝 Netdata (即時監控)
```bash
# 安裝 Netdata
bash <(curl -Ss https://my-netdata.io/kickstart.sh)

# 配置 Netdata
sudo nano /etc/netdata/netdata.conf

# 設定存取控制
sudo nano /etc/netdata/netdata.conf
# 修改 bind socket to IP = 127.0.0.1

# 使用 Nginx 代理
sudo nano /etc/nginx/sites-available/netdata
```

```nginx
server {
    listen 80;
    server_name monitor.your-domain.com;

    location / {
        proxy_pass http://127.0.0.1:19999;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

---

## 🚨 故障排除指南

### 常見部署問題

#### 問題 1：Apache 無法啟動
```bash
# 檢查配置語法
sudo apache2ctl configtest

# 檢查錯誤日誌
sudo tail -f /var/log/apache2/error.log

# 檢查端口佔用
sudo netstat -tulpn | grep :80
```

#### 問題 2：PHP 錯誤
```bash
# 檢查 PHP 錯誤日誌
sudo tail -f /var/log/php8.0-fpm.log

# 檢查 PHP 模組
php -m | grep -E "(mysql|gd|mbstring)"

# 測試 PHP 配置
php -r "phpinfo();" | grep -E "(upload_max_filesize|post_max_size|memory_limit)"
```

#### 問題 3：資料庫連線失敗
```bash
# 檢查 MySQL 狀態
sudo systemctl status mysql

# 測試資料庫連線
mysql -u mis_user -p -h localhost mis_system

# 檢查資料庫權限
mysql -u root -p -e "SELECT User, Host FROM mysql.user WHERE User='mis_user';"
```

#### 問題 4：檔案權限問題
```bash
# 檢查檔案擁有者
ls -la /var/www/mis/backend/

# 重設權限
sudo chown -R www-data:www-data /var/www/mis/backend/uploads
sudo chmod -R 755 /var/www/mis/backend/uploads

# 檢查 SELinux (如果啟用)
sudo setsebool -P httpd_can_network_connect 1
sudo setsebool -P httpd_unified 1
```

---

## 📞 部署支援

### 檢查清單完成確認
- [ ] 系統服務全部運行正常
- [ ] 資料庫連接成功
- [ ] 前端頁面可正常載入
- [ ] API 端點回應正常
- [ ] 檔案上傳功能正常
- [ ] 郵件發送功能正常
- [ ] SSL 憑證安裝完成
- [ ] 備份腳本設定完成
- [ ] 監控系統安裝完成
- [ ] 防火牆規則設定完成

### 部署後測試
```bash
# 測試前端
curl -I http://localhost:40000

# 測試 API
curl -X POST http://localhost:9000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'

# 測試檔案上傳
curl -X POST http://localhost:9000/api/upload/test \
  -F "file=@test.jpg"
```

---

**🎉 部署完成！**

系統現在應該可以正常運行。請記得：
1. 修改預設密碼
2. 設定定期備份
3. 監控系統狀態
4. 定期更新系統和套件
5. 保留部署日誌以供參考

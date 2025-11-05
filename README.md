# NFC Business Card - Digital Business Card Platform

A modern, full-stack digital business card platform that allows users to create, customize, and share their professional profiles with NFC technology integration.

## 🚀 Features

### Core Features

- **Digital Business Cards**: Create and customize professional profiles
- **NFC Integration**: Tap NFC cards to instantly share contact information
- **Social Media Links**: Add and organize social media profiles
- **Analytics Dashboard**: Track profile views, NFC taps, and link clicks
- **Responsive Design**: Mobile-first approach for all devices
- **Custom Themes**: Multiple design themes and customization options

### User Tiers

- **Free Tier**: Basic profile creation and social links
- **Pro Tier**: Advanced analytics, NFC card management, custom domains
- **Enterprise Tier**: Team management, advanced features, priority support

### Admin Features

- **User Management**: View, create, edit, and delete users
- **NFC Card Management**: Register and track physical NFC cards
- **System Analytics**: Comprehensive system statistics and monitoring
- **Content Moderation**: Manage user content and profiles

## 🛠 Tech Stack

### Backend

- **Framework**: Laravel 11 (PHP 8.2+)
- **Database**: MySQL/PostgreSQL/SQLite
- **Authentication**: Laravel Sanctum
- **File Storage**: Laravel Storage with local/cloud support
- **API**: RESTful API with JSON responses
- **Testing**: PHPUnit with Pest support

### Frontend

- **Framework**: Nuxt.js 3 (Vue.js 3)
- **Styling**: Tailwind CSS
- **State Management**: Pinia stores
- **Authentication**: JWT tokens with Sanctum
- **UI Components**: Custom Vue components
- **Responsive**: Mobile-first design

### Infrastructure

- **Web Server**: Apache/Nginx
- **Database**: MySQL 8.0+ / PostgreSQL 13+
- **Cache**: Redis (optional)
- **Queue**: Laravel Queue with database driver
- **Storage**: Local storage with cloud migration support

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer 2.0+
- Node.js 18+ and npm
- MySQL 8.0+ or PostgreSQL 13+
- Web server (Apache/Nginx)

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd nfc-business-card
```

### 2. Backend Setup

```bash
cd backend
composer install
cp .env.example .env
```

Configure your `.env` file:

```env
APP_NAME="NFC Business Card"
APP_ENV=local
APP_KEY=base64:your-key-here
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nfc_business_card
DB_USERNAME=your_username
DB_PASSWORD=your_password

FRONTEND_URL=http://localhost:3000
```

### 3. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### 4. Storage Setup

```bash
php artisan storage:link
```

### 5. Frontend Setup

```bash
cd ../frontend
npm install
```

### 6. Start Development Servers

```bash
# Backend (Terminal 1)
cd backend
php artisan serve

# Frontend (Terminal 2)
cd frontend
npm run dev
```

## 🧪 Testing Accounts

After running the seeders, you'll have these test accounts:

### Admin Accounts

- **Super Admin**: `admin@nfcgo.com` / `admin123`

  - Full system access
  - User management
  - NFC card management
  - System analytics

- **Regular Admin**: `admin2@nfcgo.com` / `admin123`
  - Limited admin access
  - User management
  - NFC card management

### User Accounts

- **Free Tier User**: `john@example.com` / `password`

  - Basic profile features
  - Social media links
  - Basic analytics

- **Pro Tier User**: `test@example.com` / `password`
  - All free features
  - Advanced analytics
  - NFC card management
  - Custom themes

## 🔧 Configuration

### Environment Variables

#### Production Settings

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

#### Alpha/Beta Testing Settings

```env
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.yourdomain.com

# Use separate database for testing
DB_DATABASE=nfc_business_card_staging

# Enable detailed logging
LOG_LEVEL=debug
LOG_CHANNELS=daily,stack
```

## 🚀 Deployment

### Alpha Testing Deployment

1. **Server Setup**

   ```bash
   # Update system
   sudo apt update && sudo apt upgrade -y

   # Install required packages
   sudo apt install nginx mysql-server php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl composer
   ```

2. **Database Setup**

   ```bash
   sudo mysql_secure_installation
   mysql -u root -p
   CREATE DATABASE nfc_business_card_alpha;
   CREATE USER 'nfc_user'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON nfc_business_card_alpha.* TO 'nfc_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. **Application Deployment**

   ```bash
   cd /var/www
   sudo git clone <repository-url> nfc-business-card
   sudo chown -R www-data:www-data nfc-business-card
   cd nfc-business-card/backend

   composer install --optimize-autoloader --no-dev
   cp .env.example .env
   # Configure .env for alpha environment

   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Nginx Configuration**

   ```nginx
   server {
       listen 80;
       server_name alpha.yourdomain.com;
       root /var/www/nfc-business-card/backend/public;

       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";

       index index.php;

       charset utf-8;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location = /favicon.ico { access_log off; log_not_found off; }
       location = /robots.txt  { access_log off; log_not_found off; }

       error_page 404 /index.php;

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

### Beta Testing Deployment

1. **Production-like Environment**

   - Use production database
   - Enable caching and optimization
   - Set up monitoring and logging
   - Configure SSL certificates

2. **Load Testing**

   ```bash
   # Install Apache Bench
   sudo apt install apache2-utils

   # Test API endpoints
   ab -n 1000 -c 10 https://beta.yourdomain.com/api/test
   ```

3. **Monitoring Setup**
   - Set up Laravel Telescope for debugging
   - Configure error tracking (Sentry, Bugsnag)
   - Set up uptime monitoring

### Production Deployment

1. **Security Hardening**

   ```bash
   # Disable debug mode
   APP_DEBUG=false

   # Enable HTTPS only
   FORCE_HTTPS=true

   # Set secure headers
   SECURE_HEADERS=true
   ```

2. **Performance Optimization**

   ```bash
   # Enable all caches
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache

   # Optimize autoloader
   composer install --optimize-autoloader --no-dev

   # Set up queue workers
   php artisan queue:work --daemon
   ```

3. **Backup Strategy**

   ```bash
   # Database backup
   mysqldump -u username -p database_name > backup.sql

   # File backup
   tar -czf storage_backup.tar.gz storage/
   ```

## 🔍 Testing

### Backend Testing

```bash
cd backend
php artisan test
```

### Frontend Testing

```bash
cd frontend
npm run test
```

### API Testing

```bash
# Test endpoints
curl http://localhost:8000/api/test
curl -X POST http://localhost:8000/api/login -H "Content-Type: application/json" -d '{"email":"test@example.com","password":"password"}'
```

## 📁 Project Structure

```
nfc-business-card/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/Api/  # API Controllers
│   │   ├── Models/                # Eloquent Models
│   │   ├── Services/              # Business Logic
│   │   └── Middleware/            # Custom Middleware
│   ├── database/
│   │   ├── migrations/            # Database Schema
│   │   └── seeders/               # Test Data
│   ├── routes/api.php             # API Routes
│   └── config/                    # Configuration Files
├── frontend/               # Nuxt.js Application
│   ├── pages/              # Application Pages
│   ├── components/         # Vue Components
│   ├── stores/             # Pinia Stores
│   ├── middleware/         # Route Middleware
│   └── composables/        # Composables
└── README.md               # This file
```

## 🚨 Important Notes

### Security Considerations

- **CHANGE DEFAULT PASSWORDS** before production deployment
- Enable HTTPS in production
- Set up proper firewall rules
- Regular security updates
- Database access restrictions

### Performance Considerations

- Enable Redis for caching in production
- Set up database indexing
- Configure CDN for static assets
- Monitor database query performance

### Maintenance

- Regular database backups
- Monitor error logs
- Update dependencies regularly
- Performance monitoring

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## 📄 License

This project is proprietary software. All rights reserved.

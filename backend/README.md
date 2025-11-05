# NFC Business Card Backend API

A Laravel-based REST API for managing NFC business cards with social media links, analytics, and profile management.

## Features

- **User Authentication** - Registration, login, and token-based authentication
- **Profile Management** - Create and customize business card profiles
- **Social Links** - Add, edit, and reorder social media links
- **NFC Tag Management** - Activate and manage NFC tags
- **Analytics** - Track profile views, NFC taps, and link clicks
- **File Uploads** - Profile images and company logos
- **CORS Support** - Cross-origin resource sharing for frontend integration

## Tech Stack

- **Laravel 12** - PHP framework
- **Laravel Sanctum** - API authentication
- **MySQL/SQLite** - Database
- **Intervention Image** - Image processing (optional)

## Installation

1. **Clone the repository**
   ```bash
   cd backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   ```bash
   # Update .env with your database credentials
   php artisan migrate
   ```

5. **Create storage link**
   ```bash
   php artisan storage:link
   ```

6. **Seed database (optional)**
   ```bash
   php artisan db:seed
   ```

7. **Start the server**
   ```bash
   php artisan serve
   ```

## API Endpoints

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout (authenticated)
- `GET /api/me` - Get current user (authenticated)

### Profiles
- `GET /api/profiles/{slug}` - Get public profile
- `GET /api/user/profile` - Get user's profile (authenticated)
- `PUT /api/user/profile` - Update profile (authenticated)
- `POST /api/user/check-slug` - Check slug availability (authenticated)
- `PUT /api/user/slug` - Update profile slug (authenticated)

### Social Links
- `GET /api/links` - Get user's social links (authenticated)
- `POST /api/links` - Create new social link (authenticated)
- `PUT /api/links/{link}` - Update social link (authenticated)
- `DELETE /api/links/{link}` - Delete social link (authenticated)
- `POST /api/links/reorder` - Reorder social links (authenticated)
- `POST /api/links/{link}/track-click` - Track link click (authenticated)

### NFC Tags
- `GET /api/nfc/tags` - Get user's NFC tags (authenticated)
- `POST /api/nfc/activate` - Activate new NFC tag (authenticated)
- `PUT /api/nfc/tags/{tag}` - Update NFC tag (authenticated)
- `POST /api/nfc/tags/{tag}/deactivate` - Deactivate NFC tag (authenticated)
- `POST /api/nfc/tap/{nfcId}` - Handle NFC tap (public)

### Analytics
- `POST /api/analytics/track` - Track analytics event (public)
- `GET /api/analytics/overview` - Get analytics overview (authenticated)
- `GET /api/analytics/profile` - Get profile analytics (authenticated)
- `GET /api/analytics/nfc/{tag}` - Get NFC analytics (authenticated)

### File Uploads
- `POST /api/upload/profile-image` - Upload profile image (authenticated)
- `POST /api/upload/company-logo` - Upload company logo (authenticated)
- `DELETE /api/upload/profile-image` - Delete profile image (authenticated)
- `DELETE /api/upload/company-logo` - Delete company logo (authenticated)

## Database Schema

### Users
- `id`, `first_name`, `last_name`, `email`, `password`
- `phone`, `company`, `job_title`, `plan`
- `has_physical_card`, `last_login_at`
- `two_factor_enabled`, `two_factor_secret`

### Profiles
- `id`, `user_id`, `slug`, `name`, `title`, `company`
- `bio`, `email`, `phone`, `website`, `location`
- `profile_image`, `profile_image_path`, `company_logo`, `company_logo_path`
- `theme`, `background_color`, `text_color`, `font`, `button_style`
- `show_watermark`, `is_active`, `settings`

### Social Links
- `id`, `profile_id`, `platform`, `url`, `title`
- `order`, `is_active`, `click_count`

### NFC Tags
- `id`, `user_id`, `nfc_id`, `name`, `status`
- `tap_count`, `last_tapped_at`

### Analytics
- `id`, `trackable_type`, `trackable_id`, `action`
- `ip_address`, `user_agent`, `device_type`, `browser`, `platform`
- `country`, `city`, `referrer`, `data`

## Usage Examples

### Register a new user
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password",
    "company": "Tech Corp",
    "job_title": "Software Engineer"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password"
  }'
```

### Add a social link
```bash
curl -X POST http://localhost:8000/api/links \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "platform": "linkedin",
    "title": "LinkedIn",
    "url": "https://linkedin.com/in/johndoe"
  }'
```

### Activate NFC tag
```bash
curl -X POST http://localhost:8000/api/nfc/activate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nfc_id": "unique-nfc-id-123",
    "name": "Business Card"
  }'
```

## Testing

Run the test suite:
```bash
php artisan test
```

## Commands

### Create missing profiles
```bash
php artisan profiles:create-missing
```

### Clear cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Environment Variables

```env
APP_NAME="NFC Business Card"
APP_ENV=local
APP_KEY=your-app-key
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nfc_business_card
DB_USERNAME=root
DB_PASSWORD=

FRONTEND_URL=http://localhost:3000
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This project is licensed under the MIT License.

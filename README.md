# NFC Business Card Platform - Comprehensive Documentation

**Welcome to the NFC Business Card Platform!**

This document describes the entire system in detail. We have written it to be useful for **Developers** (Technical) and **Project Managers/Stakeholders** (Non-Technical).

---

## 📋 Table of Contents

1.  [Overview](#1-overview)
2.  [Key Features](#2-key-features)
3.  [Architecture & Technology](#3-architecture--technology)
4.  [Complete Project Structure](#4-complete-project-structure)
5.  [Installation & Setup Guide](#5-installation--setup-guide)
    - [Prerequisites](#prerequisites)
    - [Backend Setup](#step-1-backend-setup)
    - [Frontend Setup](#step-2-frontend-setup)
    - [Starting Servers](#step-3-start-development-servers)
6.  [Source Code Flows](#6-source-code-flows)
7.  [API Documentation](#7-api-documentation)
8.  [Important Notes](#8-important-notes)
9.  [Testing Accounts](#9-testing-accounts)
10. [Additional Documentation](#10-additional-documentation)

---

## 1. Overview

The **NFC Business Card Platform** is a digital solution that replaces traditional paper business cards with smart, digital profiles.

**How it works:**

1.  **Create**: A user signs up and builds a beautiful digital profile.
2.  **Connect**: They purchase a physical NFC card.
3.  **Share**: Tapping the card on a phone opens the digital profile instantly.

---

## 2. Key Features

### 👤 For Users

- **Digital Profile Builder**: Drag-and-drop editor to customize the look and feel.
- **NFC Card Integration**: Link physical cards to digital profiles.
- **Analytics**: Track profile views and card taps.

### 🏢 For Business Accounts

- **Employee Management**: Bulk create employee accounts via CSV.
- **Brand Control**: Enforce corporate branding on employee cards.
- **Unified Billing**: Centralized payment for all subscriptions.

### ⚙️ For Administrators

- **Dashboard**: System-wide analytics (users, revenue).
- **Card Inventory**: Manage physical card encoding and shipping.
- **Chatbot Management**: Train the AI support assistant.

---

## 3. Architecture & Technology

### 🎨 Frontend (The Face)

- **Framework**: Nuxt.js 3 (Vue 3)
- **Styling**: Tailwind CSS
- **State**: Pinia

### 🧠 Backend (The Brain)

- **Framework**: Laravel 11 (PHP 8.2+)
- **Auth**: Laravel Sanctum
- **Database**: MySQL 8.0+

---

## 4. Complete Project Structure

This section provides a detailed walk-through of the codebase.

### 📁 Frontend Structure (`frontend/`)

```
frontend/
├── assets/                  # CSS files, images, and fonts
├── components/              # Reusable UI building blocks
│   ├── ChatbotWidget.vue    # Floating AI help button
│   ├── Payment.vue          # Checkout form for buying cards
│   ├── ProfileBuilder.vue   # The drag-and-drop profile editor
│   └── ...
├── composables/             # Shared logic functions (Hooks)
│   ├── usePayment.js        # Handles Fiuu payment logic & fee calc
│   ├── useProfileData.js    # Manages state of the profile being edited
│   └── useAuth.js           # Handles login/logout state
├── layouts/                 # Page templates
│   ├── default.vue          # Standard layout with navbar/footer
│   └── auth.vue             # Clean layout for login pages
├── middleware/              # Route protection rules
│   ├── auth.js              # Redirects to login if not authenticated
│   └── admin.js             # Redirects if user is not an Admin
├── pages/                   # Application Screens (Routes)
│   ├── index.vue            # Homepage (Landing)
│   ├── login.vue            # Login Screen
│   ├── register.vue         # Registration Screen
│   ├── UserDashboard/       # User Area
│   │   ├── index.vue        # Main Dashboard
│   │   ├── Settings.vue     # Account Settings
│   │   └── ...
│   └── AdminManagement/     # Admin Area
│       ├── users.vue        # User List
│       ├── nfc-cards.vue    # Card Inventory
│       └── ...
├── stores/                  # Pinia State Management
│   └── auth.js              # Stores user token & profile data
└── nuxt.config.ts           # Main Nuxt Framework Configuration
```

### 📁 Backend Structure (`backend/`)

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/ # Request Handlers
│   │   │   ├── AuthController.php       # Login, Register, Password Reset
│   │   │   ├── NfcCardController.php    # Card Activation, Taps
│   │   │   ├── PaymentController.php    # Payment Processing
│   │   │   ├── ProfileController.php    # Saving Profile Changes
│   │   │   ├── AdminController.php      # Admin Stats & Management
│   │   │   └── ...
│   │   └── Middleware/      # Request Filters
│   │       └── AdminMiddleware.php      # Checks if user is Admin
│   ├── Models/              # Database Objects
│   │   ├── User.php         # Represents a User
│   │   ├── NfcCard.php      # Represents a Physical Card
│   │   ├── LandingPage.php  # Represents a Digital Profile
│   │   └── Transaction.php  # Represents a Payment
│   └── Services/            # Business Logic
│       ├── PaymentService.php       # Fiuu Calculation & Verification
│       └── NotificationService.php  # Email Sending Logic
├── config/                  # Configuration Files
│   ├── database.php         # DB Connection Settings
│   └── payment.php          # Payment Gateway Settings
├── database/
│   ├── migrations/          # Schema Definitions (Create Tables)
│   ├── seeders/             # Test Data Generators
│   └── factories/           # Model Factories
└── routes/
    └── api.php              # API Endpoint Definitions
```

---

## 5. Installation & Setup Guide

### Prerequisites

- **Node.js** v18+ (for Frontend)
- **PHP** v8.2+ (for Backend)
- **Composer** v2+ (PHP Dependency Manager)
- **MySQL** Database Server

### Step 1: Backend Setup

1.  **Navigate**: Open terminal in `backend/`.
2.  **Dependencies**: Run `composer install`.
3.  **Environment**:
    - Copy file: `cp .env.example .env`
    - Update `.env`:
      ```env
      DB_DATABASE=nfc_business_card
      DB_USERNAME=root
      DB_PASSWORD=your_password
      APP_URL=http://localhost:8000
      FRONTEND_URL=http://localhost:3000
      ```
4.  **Database Initialization**:
    ```bash
    php artisan key:generate       # Generate encryption key
    php artisan migrate            # Create tables
    php artisan db:seed            # Insert test data (Admin/Users)
    php artisan storage:link       # Enable public image access
    ```

### Step 2: Frontend Setup

1.  **Navigate**: Open new terminal in `frontend/`.
2.  **Dependencies**: Run `npm install`.
3.  **Environment**:
    - Copy file: `cp .env.example .env`
    - Update `.env`:
      ```env
      NUXT_PUBLIC_API_BASE_URL=http://localhost:8000/api
      ```

### Step 3: Start Development Servers

You must run **two** terminals simultaneously.

**Terminal 1 (Backend):**

```bash
cd backend
php artisan serve --host 0.0.0.0 --port 8000
```

> Server will start at `http://localhost:8000`

**Terminal 2 (Frontend):**

```bash
cd frontend
npm run dev
```

> Server will start at `http://localhost:3000`

---

## 6. Source Code Flows

### Registration Flow

1.  **User** submits form on `/register`.
2.  **Frontend** sends POST to `/api/register`.
3.  **Backend** creates User record and generates Sanctum Token.
4.  **Frontend** saves Token and redirects to Dashboard.

### Payment Flow (Fiuu)

1.  **User** initiates payment in Dashboard.
2.  **Backend** (`PaymentService`) generates secure `vcode` and returns Fiuu URL.
3.  **User** completes payment on Fiuu Gateway.
4.  **Fiuu** sends Webhook to Backend (`/api/webhooks/fiuu`).
5.  **Backend** verifies signature and marks Order as "Paid".

---

## 7. API Documentation

| Endpoint            | Method | Description            |
| :------------------ | :----- | :--------------------- |
| `/api/login`        | POST   | Authenticate user      |
| `/api/register`     | POST   | Create new account     |
| `/api/nfc-cards`    | GET    | List user's cards      |
| `/api/user/profile` | PUT    | Update profile details |

---

## 8. Important Notes

### 🔒 Security Considerations

- **Change Defaults**: Immediately change the default Admin password (`admin123`) and User passwords after deployment.
- **HTTPS**: Ensure HTTPS is enabled in production to protect payment data and auth tokens.
- **Permissions**: Files in `storage/` must be writable, but code directories should be read-only in production.

### 🚀 Performance

- **Caching**: In production, set `CACHE_DRIVER=redis` in `.env` for faster response times.
- **Optimization**: Run `php artisan config:cache` and `php artisan route:cache` when deploying to production.
- **Images**: Configure Cloudinary to offload image hosting from your main server.

### 🛠 Maintenance

- **Backups**: Regularly backup your MySQL database.
- **Logs**: Check `backend/storage/logs/laravel.log` if errors occur.
- **Updates**: Run `composer update` and `npm update` periodically to patch security vulnerabilities.

---

## 9. Testing Accounts

Use these credentials to log in immediately:

| Role            | Email              | Password   |
| :-------------- | :----------------- | :--------- |
| **Super Admin** | `admin@nfcgo.com`  | `admin123` |
| **Free User**   | `john@example.com` | `password` |
| **Pro User**    | `test@example.com` | `password` |

---

## 10. Additional Documentation

For deeper technical details, please refer to the documents in the `docs/` folder:

- [**Database Schema**](docs/database/DATABASE_SCHEMA.md): Complete breakdown of tables, columns, and relationships.

### 🔌 Integrations

**Fiuu (Payment Gateway)**

- [**Integration Guide**](docs/integrations/fiuu/README.md): Detailed setup and configuration.
- [**Verification Checklist**](docs/integrations/fiuu/VERIFICATION.md): Testing steps for go-live.

**OAuth (Social Login)**

- [**OAuth Guide**](docs/integrations/oauth/README.md): Google & Apple login configuration and flow.

_**End of Documentation**_

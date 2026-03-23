# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SMS sending web application built on Laravel 5.8 with OTP-based authentication. Two hardcoded users can send SMS via an external gateway and view their sending history.

## Commands

### Backend (PHP/Laravel)
```bash
# Install PHP dependencies
composer install

# Run database migrations
php artisan migrate

# Run development server (if not using Laragon)
php artisan serve

# Laravel Tinker (REPL)
php artisan tinker

# Run PHPUnit tests
./vendor/bin/phpunit

# Run a single test file
./vendor/bin/phpunit tests/Feature/ExampleTest.php
```

### Frontend (JavaScript/SCSS)
```bash
# Install JS dependencies
npm install

# Development build (with progress output)
npm run dev

# Watch for changes and rebuild
npm run watch

# Production build (minified)
npm run prod
```

## Architecture

### Authentication
This app uses **session-based OTP authentication**, not Laravel's standard auth system.

1. User submits mobile number → stored in session
2. User submits 4-digit OTP → validated against `config/settings.php` (hardcoded)
3. On success: `login_status = true` set in session
4. `OtpAuthentication` middleware protects routes by checking this session key

User credentials and OTP codes live in `config/settings.php`, not the database.

### Request Flow
- `routes/web.php` → Controllers → Blade views (`resources/views/admin/`)
- `LoginController` handles login/OTP/logout
- `HomeController` handles SMS sending and tracking
- SMS is dispatched via Guzzle to `https://fsonline.no/smsgateway.php`
- All sent SMS are logged to the `sms_logs` table via the `SmsLog` model

### Frontend
- Blade templates with inline jQuery for form handling and validation
- Vue.js is bootstrapped (`resources/js/app.js`) but minimally used
- Bootstrap 4 + jQuery served from `public/assets/`
- Compiled assets output to `public/js/` and `public/css/` via Laravel Mix (webpack)
- CSRF token injected into Axios headers in `resources/js/bootstrap.js`

### Key Files
| File | Purpose |
|------|---------|
| `config/settings.php` | Hardcoded user credentials and OTP codes |
| `app/Http/Middleware/OtpAuthentication.php` | Session auth guard |
| `app/Http/Controllers/HomeController.php` | SMS send + tracking logic |
| `app/Http/Controllers/LoginController.php` | OTP auth flow |
| `app/SmsLog.php` | Eloquent model for SMS log table |
| `webpack.mix.js` | Frontend build configuration |

### Routes Summary
| Route | Controller | Auth Required |
|-------|-----------|---------------|
| `GET /` | `LoginController@index` | No |
| `POST /login` | `LoginController@login` | No |
| `GET /home` | `HomeController@index` | Yes |
| `POST /home` | `HomeController@sendSms` | Yes |
| `GET /tracking` | `HomeController@getTrackingDetails` | Yes |
| `GET /logout` | `LoginController@logout` | No |

# Azure Deployment Configuration

This project is deployed on Azure App Service at:
**https://bnb-backend-fpe8ejhwhah5eubp.uaenorth-01.azurewebsites.net**

## Environment Variables for Azure

Use the following Azure App Service Application Settings JSON configuration:

```json
[
  { "name": "APP_NAME", "value": "BNB Management System", "slotSetting": false },
  { "name": "APP_ENV", "value": "production", "slotSetting": false },
  { "name": "APP_KEY", "value": "base64:PpBYGkqa89cR6bSrX51LV6qTLhcoW2zooEzrWZxm8qc=", "slotSetting": false },
  { "name": "APP_DEBUG", "value": "false", "slotSetting": false },
  { "name": "APP_URL", "value": "https://bnb-backend-fpe8ejhwhah5eubp.uaenorth-01.azurewebsites.net", "slotSetting": false },
  
  { "name": "APP_LOCALE", "value": "en", "slotSetting": false },
  { "name": "APP_FALLBACK_LOCALE", "value": "en", "slotSetting": false },
  { "name": "APP_FAKER_LOCALE", "value": "en_US", "slotSetting": false },
  
  { "name": "APP_MAINTENANCE_DRIVER", "value": "file", "slotSetting": false },
  { "name": "PHP_CLI_SERVER_WORKERS", "value": "4", "slotSetting": false },
  { "name": "BCRYPT_ROUNDS", "value": "12", "slotSetting": false },
  
  { "name": "LOG_CHANNEL", "value": "stack", "slotSetting": false },
  { "name": "LOG_STACK", "value": "single", "slotSetting": false },
  { "name": "LOG_DEPRECATIONS_CHANNEL", "value": "", "slotSetting": false },
  { "name": "LOG_LEVEL", "value": "debug", "slotSetting": false },
  
  { "name": "DB_CONNECTION", "value": "sqlite", "slotSetting": false },
  
  { "name": "SESSION_DRIVER", "value": "database", "slotSetting": false },
  { "name": "SESSION_LIFETIME", "value": "120", "slotSetting": false },
  { "name": "SESSION_ENCRYPT", "value": "false", "slotSetting": false },
  { "name": "SESSION_PATH", "value": "/", "slotSetting": false },
  { "name": "SESSION_DOMAIN", "value": "", "slotSetting": false },
  
  { "name": "BROADCAST_CONNECTION", "value": "log", "slotSetting": false },
  { "name": "FILESYSTEM_DISK", "value": "local", "slotSetting": false },
  { "name": "QUEUE_CONNECTION", "value": "database", "slotSetting": false },
  
  { "name": "CACHE_STORE", "value": "database", "slotSetting": false },
  
  { "name": "REDIS_CLIENT", "value": "phpredis", "slotSetting": false },
  { "name": "REDIS_HOST", "value": "127.0.0.1", "slotSetting": false },
  { "name": "REDIS_PASSWORD", "value": "", "slotSetting": false },
  { "name": "REDIS_PORT", "value": "6379", "slotSetting": false },
  
  { "name": "MAIL_MAILER", "value": "smtp", "slotSetting": false },
  { "name": "MAIL_HOST", "value": "smtp.gmail.com", "slotSetting": false },
  { "name": "MAIL_PORT", "value": "587", "slotSetting": false },
  { "name": "MAIL_USERNAME", "value": "abdulnassirbakari@gmail.com", "slotSetting": false },
  { "name": "MAIL_PASSWORD", "value": "biuo dxkf rhpp rnux", "slotSetting": false },
  { "name": "MAIL_ENCRYPTION", "value": "tls", "slotSetting": false },
  { "name": "MAIL_FROM_ADDRESS", "value": "brianeugene851@gmail.com", "slotSetting": false },
  { "name": "MAIL_FROM_NAME", "value": "BNB Management System", "slotSetting": false },
  
  { "name": "SANCTUM_STATEFUL_DOMAINS", "value": "localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1,bnb-backend-fpe8ejhwhah5eubp.uaenorth-01.azurewebsites.net", "slotSetting": false },
  
  { "name": "API_RATE_LIMIT", "value": "60", "slotSetting": false },
  
  { "name": "CORS_ALLOWED_ORIGINS", "value": "*", "slotSetting": false },
  { "name": "CORS_ALLOWED_METHODS", "value": "*", "slotSetting": false },
  { "name": "CORS_ALLOWED_HEADERS", "value": "*", "slotSetting": false },
  { "name": "CORS_EXPOSED_HEADERS", "value": "", "slotSetting": false },
  { "name": "CORS_MAX_AGE", "value": "0", "slotSetting": false },
  { "name": "CORS_SUPPORTS_CREDENTIALS", "value": "false", "slotSetting": false },
  
  { "name": "AWS_ACCESS_KEY_ID", "value": "", "slotSetting": false },
  { "name": "AWS_SECRET_ACCESS_KEY", "value": "", "slotSetting": false },
  { "name": "AWS_DEFAULT_REGION", "value": "us-east-1", "slotSetting": false },
  { "name": "AWS_BUCKET", "value": "", "slotSetting": false },
  { "name": "AWS_USE_PATH_STYLE_ENDPOINT", "value": "false", "slotSetting": false },
  
  { "name": "CLOUDINARY_CLOUD_NAME", "value": "dqotqwtlp", "slotSetting": false },
  { "name": "CLOUDINARY_API_KEY", "value": "388298685279568", "slotSetting": false },
  { "name": "CLOUDINARY_API_SECRET", "value": "2WSWmdRY5leTsMSDH_JwzKOvLP4", "slotSetting": false },
  { "name": "CLOUDINARY_URL", "value": "cloudinary://388298685279568:2WSWmdRY5leTsMSDH_JwzKOvLP4@dqotqwtlp", "slotSetting": false },
  
  { "name": "VITE_APP_NAME", "value": "BNB Management System", "slotSetting": false }
]
```

## API Endpoints

The following endpoints are available for frontend integration:

### Base URL
`https://bnb-backend-fpe8ejhwhah5eubp.uaenorth-01.azurewebsites.net/api/v1/`

### Key Endpoints
- **Health Check**: `GET /health`
- **Authentication**: `POST /auth/login`, `POST /auth/register`
- **BNBs**: `GET /bnbs`, `POST /bnbs`, `GET /bnbs/{id}`
- **Maps**: `GET /bnbs/search/map`
- **Reviews**: `GET /bnbs/{id}/reviews`, `POST /bnbs/{id}/reviews`
- **Support**: `GET /support/tickets`, `POST /support/tickets`
- **Billing**: `GET /user/bills`, `GET /bills/{id}`
- **Admin**: `GET /admin/users`, `GET /admin/stats`
- **Availability**: `GET /bnbs/{bnb}/availability`
- **Analytics**: `GET /dashboard/analytics`

### API Documentation
Swagger/OpenAPI documentation is available at:
`https://bnb-backend-fpe8ejhwhah5eubp.uaenorth-01.azurewebsites.net/api/documentation`

## Deployment Instructions

1. Set up Azure App Service with PHP 8.x runtime
2. Configure GitHub Actions for CI/CD deployment
3. Import the environment variables JSON above into Azure App Service Configuration
4. Set startup command: `php artisan migrate --force && php artisan storage:link && php artisan config:cache`
5. Ensure database is accessible (SQLite for development, MySQL/PostgreSQL for production)
# BNB Management System API

<p align="center">
<img src="https://img.shields.io/badge/Laravel-11.x-red.svg" alt="Laravel Version">
<img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
<img src="https://img.shields.io/badge/Tests-40%20Passing-green.svg" alt="Tests">
<img src="https://img.shields.io/badge/Coverage-100%25-brightgreen.svg" alt="Coverage">
<img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License">
</p>

A professional, production-ready Laravel API for managing Bed & Breakfast (BNB) properties. Built with modern Laravel practices, comprehensive testing, and designed for seamless integration with mobile and web applications.

## 🚀 Features

### Core API Features
- ✅ **Complete CRUD Operations** - Create, Read, Update, Delete BNB properties
- ✅ **Advanced Pagination** - Filtering, sorting, and navigation with metadata
- ✅ **Search & Filtering** - Filter by availability, location, price range, and name
- ✅ **Authentication & Authorization** - JWT tokens with role-based access control
- ✅ **Data Validation** - Comprehensive input validation and error handling
- ✅ **Soft Delete Support** - Safe deletion with recovery options

### Technical Features
- ✅ **Repository Pattern** - Clean architecture with dependency injection
- ✅ **Custom Exceptions** - Structured error handling with custom API exceptions
- ✅ **Response Caching** - Optimized performance with intelligent caching
- ✅ **Rate Limiting** - API throttling to prevent abuse
- ✅ **CORS Support** - Cross-origin resource sharing for web clients
- ✅ **API Versioning** - Future-proof with version-based routing

### Testing & Quality
- ✅ **100% Test Coverage** - 40 tests with 295 assertions
- ✅ **Unit Tests** - Models, repositories, and core logic testing
- ✅ **Feature Tests** - End-to-end API endpoint testing
- ✅ **CI/CD Pipeline** - Automated testing with GitHub Actions
- ✅ **Code Quality** - PSR standards and best practices

## 📋 Requirements

- **PHP** >= 8.2
- **Composer** >= 2.0
- **MySQL** >= 8.0 or **SQLite** (for development)
- **Node.js** >= 18.0 (for asset compilation)

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone https://github.com/ogemboeugene/BNB-backend.git
cd BNB-backend
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database
Edit `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bnb_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations and Seeders
```bash
php artisan migrate --seed
```

### 6. Generate JWT Secret
```bash
php artisan jwt:secret
```

### 7. Start Development Server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Unit tests only
php artisan test tests/Unit/

# Feature tests only
php artisan test tests/Feature/

# Specific test file
php artisan test tests/Feature/Feature/BNBApiTest.php
```

### Test Results
- **Total Tests**: 40
- **Total Assertions**: 295
- **Unit Tests**: 11 (Models, Repositories)
- **Feature Tests**: 29 (API Endpoints, Pagination, Error Handling)
- **Status**: ✅ All Passing

## 📡 API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/register` | User registration |
| POST | `/api/v1/auth/login` | User login |
| POST | `/api/v1/auth/logout` | User logout |
| GET | `/api/v1/auth/profile` | Get user profile |

### BNB Management
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/bnbs` | List all BNBs (paginated) | No |
| GET | `/api/v1/bnbs/{id}` | Get specific BNB | No |
| POST | `/api/v1/bnbs` | Create new BNB | Yes |
| PUT | `/api/v1/bnbs/{id}` | Update BNB | Yes |
| DELETE | `/api/v1/bnbs/{id}` | Delete BNB | Yes (Admin) |
| PATCH | `/api/v1/bnbs/{id}/availability` | Update availability | Yes |

### Health & Monitoring
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/health` | API health check |
| GET | `/api/v1/health/detailed` | Detailed health status |

### Admin (Admin Role Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/users` | List all users |
| PATCH | `/api/v1/admin/users/{id}/role` | Update user role |
| GET | `/api/v1/admin/stats` | System statistics |

## 🔧 Configuration

### Cache Configuration
The API uses Redis for caching (falls back to file cache):
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Rate Limiting
Default rate limits:
- **API Routes**: 60 requests per minute
- **Auth Routes**: 5 requests per minute

### CORS Settings
Configure allowed origins in `config/cors.php`:
```php
'allowed_origins' => ['http://localhost:3000', 'https://yourdomain.com'],
```

## 📱 Flutter Integration

For seamless integration with Flutter applications, see the comprehensive integration guide:

👉 **[FLUTTER_INTEGRATION.md](./FLUTTER_INTEGRATION.md)**

This guide includes:
- Complete endpoint documentation with request/response examples
- Flutter HTTP client setup and configuration
- Error handling patterns
- Authentication implementation
- Data models and serialization
- Best practices for mobile integration

## 🏗️ Architecture

### Project Structure
```
app/
├── Http/
│   ├── Controllers/Api/V1/     # API Controllers
│   ├── Requests/               # Form Request Validation
│   └── Resources/              # API Resources
├── Models/                     # Eloquent Models
├── Repositories/               # Repository Pattern
├── Exceptions/                 # Custom Exceptions
└── Traits/                     # Reusable Traits

database/
├── migrations/                 # Database Migrations
├── factories/                  # Model Factories
└── seeders/                    # Database Seeders

tests/
├── Unit/                       # Unit Tests
└── Feature/                    # Feature Tests
```

### Design Patterns
- **Repository Pattern** - Data access abstraction
- **Service Layer** - Business logic separation
- **Resource Pattern** - API response transformation
- **Form Requests** - Input validation and authorization

## 🔒 Security Features

- **JWT Authentication** - Secure token-based authentication
- **Role-Based Access Control** - Admin and user role separation
- **Input Validation** - Comprehensive request validation
- **SQL Injection Protection** - Eloquent ORM protection
- **CORS Configuration** - Cross-origin request security
- **Rate Limiting** - API abuse prevention

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure production database
- [ ] Set up Redis for caching and queues
- [ ] Configure proper CORS origins
- [ ] Set up SSL certificates
- [ ] Configure web server (Nginx/Apache)
- [ ] Set up monitoring and logging

### Docker Deployment
```bash
# Build and run with Docker Compose
docker-compose up -d --build

# Run migrations in container
docker-compose exec app php artisan migrate --seed
```

## 📊 Performance

### Optimization Features
- **Database Indexing** - Optimized queries
- **Response Caching** - Redis-backed caching
- **Eager Loading** - Reduced N+1 queries
- **Pagination** - Memory-efficient data loading
- **API Rate Limiting** - Resource protection

### Monitoring
- **Health Endpoints** - System status monitoring
- **Logging** - Comprehensive error and access logging
- **Metrics** - Performance and usage statistics

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Ensure all tests pass before submitting

## 📜 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## 📞 Support

For support, email [your-email@domain.com](mailto:your-email@domain.com) or create an issue on GitHub.

---

<p align="center">Built with ❤️ using Laravel 11.x</p>

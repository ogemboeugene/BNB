# BNB Management System API

<p align="center">
<img src="https://img.shields.io/badge/Laravel-11.x-red.svg" alt="Laravel Version">
<img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
<img src="https://img.shields.io/badge/Tests-40%20Passing-green.svg" alt="Tests">
<img src="https://img.shields.io/badge/Coverage-100%25-brightgreen.svg" alt="Coverage">
<img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License">
</p>

A professional, production-ready Laravel API for managing Bed & Breakfast (BNB) properties. Built with modern Laravel practices, comprehensive testing, and designed for seamless integration with mobile and web applications.

## 🌐 Live Deployment

**Production API:** https://bnb-backend-fpe8ejhwhah5eubp.uaenorth-01.azurewebsites.net/api/v1/

**API Documentation:** https://bnb-backend-fpe8ejhwhah5eubp.uaenorth-01.azurewebsites.net/api/documentation

The API is deployed on Azure App Service with automated CI/CD via GitHub Actions.

## 🚀 Features

### Core API Features
- ✅ **Complete CRUD Operations** - Create, Read, Update, Delete BNB properties
- ✅ **Image Upload & Management** - Cloudinary integration for BNB property images
- ✅ **Advanced Search & Filtering** - Price range, amenities, location radius, availability dates, ratings
- ✅ **Geolocation Support** - Proximity-based search with latitude/longitude
- ✅ **Review & Rating System** - User reviews with automatic rating calculations
- ✅ **Calendar & Scheduling** - Availability management with date ranges
- ✅ **Support Ticketing System** - Customer support with ticket management
- ✅ **Marketing & Analytics** - Comprehensive analytics dashboard
- ✅ **Notification System** - Email, in-app, and push notifications
- ✅ **Authentication & Authorization** - JWT tokens with role-based access control

### Advanced Features
- ✅ **Map Integration** - Flutter-friendly endpoints for map-based BNB discovery
- ✅ **Real-time Messaging** - Notification system with multiple channels
- ✅ **SMTP Email Integration** - Automated email workflows with Gmail
- ✅ **Analytics Dashboard** - Property performance metrics and insights
- ✅ **Multi-amenity Filtering** - Complex search with multiple amenity combinations
- ✅ **Rating Aggregation** - Automatic average rating calculation from reviews
- ✅ **Date-based Availability** - Smart availability checking for booking periods

### Technical Features
- ✅ **Repository Pattern** - Clean architecture with dependency injection
- ✅ **Custom Exceptions** - Structured error handling with custom API exceptions
- ✅ **Response Caching** - Optimized performance with intelligent caching
- ✅ **Rate Limiting** - API throttling to prevent abuse
- ✅ **CORS Support** - Cross-origin resource sharing for web clients
- ✅ **API Versioning** - Future-proof with version-based routing
- ✅ **SQLite Compatible** - Development-friendly database queries

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
- **Cloudinary Account** (for image upload functionality)

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

**For Development (SQLite):**
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### 5. Configure Cloudinary (Required for Image Upload)
Create a free account at [Cloudinary](https://cloudinary.com) and add your credentials to `.env`:
```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
```

### 5. Configure Email (Required for Notifications)
Add SMTP configuration to `.env` for email notifications:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Note**: For Gmail, use an App Password instead of your regular password. Enable 2FA and generate an App Password in your Google Account settings.

### 6. Run Migrations and Seeders
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

## � Image Upload Features

### Supported Image Formats
- **JPEG/JPG** - Standard photo format
- **PNG** - Lossless compression with transparency
- **WEBP** - Modern web-optimized format
- **GIF** - Animated and static images

### Image Upload Specifications
- **Maximum File Size**: 10MB per image
- **Automatic Optimization**: Quality and format optimization via Cloudinary
- **Cloud Storage**: Secure storage with CDN delivery
- **URL Generation**: Automatic secure URL generation for uploaded images

### Upload Methods
Images can be uploaded using:
- **Multipart Form Data** - Standard form upload
- **Base64 Encoding** - For mobile app integration
- **Direct URL** - Link to existing images

### API Endpoints for Image Upload

#### Create BNB with Image
```http
POST /api/v1/bnbs
Content-Type: multipart/form-data

{
  "name": "Cozy Downtown Apartment",
  "location": "New York, NY",
  "price_per_night": 150.00,
  "availability": true,
  "image": [binary file data]
}
```

#### Update BNB Image
```http
PUT /api/v1/bnbs/{id}
Content-Type: multipart/form-data

{
  "image": [binary file data]
}
```

### Response Format
```json
{
  "data": {
    "id": 1,
    "name": "Cozy Downtown Apartment",
    "location": "New York, NY",
    "price_per_night": "150.00",
    "availability": true,
    "image_url": "https://res.cloudinary.com/demo/image/upload/v123456789/bnb-images/sample.jpg",
    "created_at": "2025-09-08T10:00:00.000000Z",
    "updated_at": "2025-09-08T10:00:00.000000Z"
  }
}
```

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

### Advanced Search & Map Features
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/bnbs/search/nearby` | Search BNBs near location | No |
| GET | `/api/v1/bnbs/search/map` | Get BNBs for map display | No |

#### Search Parameters
**Basic Filters:**
- `name` - Search by property name
- `location` - Filter by location
- `min_price` / `max_price` - Price range filtering
- `availability` - Available properties only
- `min_rating` - Minimum rating filter

**Advanced Filters:**
- `amenities[]` - Multiple amenity filtering (e.g., `amenities[]=wifi&amenities[]=pool`)
- `check_in` / `check_out` - Date availability (YYYY-MM-DD format)
- `latitude` / `longitude` / `radius` - Geolocation search (radius in km)

**Examples:**
```http
# Basic search
GET /api/v1/bnbs?name=downtown&min_price=50&max_price=200

# Advanced search with amenities
GET /api/v1/bnbs?amenities[]=wifi&amenities[]=pool&min_rating=4.0

# Geolocation search
GET /api/v1/bnbs/search/nearby?latitude=40.7128&longitude=-74.0060&radius=5

# Date availability
GET /api/v1/bnbs?check_in=2025-12-01&check_out=2025-12-07
```

### Review & Rating System
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/bnbs/{id}/reviews` | Get BNB reviews | No |
| POST | `/api/v1/bnbs/{id}/reviews` | Create review | Yes |
| PUT | `/api/v1/reviews/{review}` | Update review | Yes (Owner) |
| DELETE | `/api/v1/reviews/{review}` | Delete review | Yes (Owner) |

#### Review Creation Example
```json
POST /api/v1/bnbs/1/reviews
{
  "rating": 5,
  "comment": "Amazing place! Clean, comfortable, and great location.",
  "stay_date": "2025-11-15"
}
```

### Support Ticketing System
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/support/tickets` | List user's tickets | Yes |
| POST | `/api/v1/support/tickets` | Create support ticket | Yes |
| GET | `/api/v1/support/tickets/{ticket}` | View specific ticket | Yes (Owner) |
| PATCH | `/api/v1/support/tickets/{ticket}` | Update ticket | Yes (Owner) |

#### Support Ticket Categories
- `technical` - Technical issues
- `billing` - Payment and billing
- `general` - General inquiries
- `booking` - Booking-related issues
- `account` - Account management

#### Priority Levels
- `low` - Non-urgent issues
- `medium` - Standard priority (default)
- `high` - Important issues
- `urgent` - Critical issues requiring immediate attention

#### Ticket Creation Example
```json
POST /api/v1/support/tickets
{
  "subject": "Login Issue",
  "message": "I'm having trouble accessing my account",
  "priority": "high",
  "category": "technical"
}
```

### Notifications
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/notifications` | List user notifications | Yes |
| PATCH | `/api/v1/notifications/{id}/mark-read` | Mark as read | Yes |
| DELETE | `/api/v1/notifications/{id}` | Delete notification | Yes |

### Analytics & Dashboard
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/dashboard/analytics` | User dashboard analytics | Yes |
| GET | `/api/v1/bnbs/{id}/analytics` | Specific BNB analytics | Yes (Owner) |

#### Analytics Data Includes
- Total views and bookings
- Revenue metrics
- Rating trends
- Popular amenities
- Geographic distribution
- Seasonal performance

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

### Cloudinary Configuration
Configure image upload settings in `config/cloudinary.php`:
```php
'upload' => [
    'folder' => 'bnb-images',
    'resource_type' => 'image',
    'allowed_formats' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
    'max_file_size' => 10485760, // 10MB in bytes
    'transformation' => [
        'quality' => 'auto',
        'fetch_format' => 'auto',
    ],
],
```

Environment variables for Cloudinary:
```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
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

## 🎯 Advanced Features Implemented

### 🔍 **Advanced Search & Filtering System**
Our BNB backend includes a sophisticated search system that goes beyond basic property listings:

#### **Multi-Parameter Search**
- **Price Range Filtering**: `min_price` and `max_price` parameters
- **Amenities Filtering**: Multiple amenity selection (wifi, pool, parking, kitchen, etc.)
- **Location-Based Search**: City, state, or address filtering
- **Rating-Based Filtering**: Minimum rating requirements
- **Availability Filtering**: Show only available properties

#### **Geolocation & Proximity Search**
- **Coordinate-Based Search**: Search within radius of specific coordinates
- **SQLite-Compatible Queries**: Optimized for both SQLite (development) and MySQL (production)
- **Map Integration Ready**: Endpoints specifically designed for Flutter map implementations
- **Distance Calculation**: Real-time distance calculation from search point

#### **Example Search Queries**
```http
# Find properties under $150/night with wifi and pool in New York
GET /api/v1/bnbs?max_price=150&amenities[]=wifi&amenities[]=pool&location=New York

# Search within 5km of specific coordinates
GET /api/v1/bnbs/search/nearby?latitude=40.7128&longitude=-74.0060&radius=5

# High-rated properties available for specific dates
GET /api/v1/bnbs?min_rating=4.0&check_in=2025-12-01&check_out=2025-12-07
```

### ⭐ **Complete Review & Rating System**
A full-featured review system that builds trust and provides valuable feedback:

#### **Review Features**
- **One Review Per User Per Property**: Prevents spam and duplicate reviews
- **1-5 Star Rating System**: Standard rating scale with validation
- **Detailed Comments**: Rich text feedback from guests
- **Stay Date Tracking**: Links reviews to actual stays
- **Verification System**: Ready for booking verification integration

#### **Automatic Rating Aggregation**
- **Average Rating Calculation**: Automatic calculation of property ratings
- **Review Count Tracking**: Total number of reviews per property
- **Real-time Updates**: Ratings update when reviews are added/modified
- **Performance Optimized**: Efficient database queries for rating calculations

#### **Review Management**
```http
# Get all reviews for a property (public)
GET /api/v1/bnbs/1/reviews

# Create a review (authenticated)
POST /api/v1/bnbs/1/reviews
{
  "rating": 5,
  "comment": "Amazing property!",
  "stay_date": "2025-11-15"
}

# Update your own review
PUT /api/v1/reviews/1
{
  "rating": 4,
  "comment": "Updated review"
}
```

### 🎫 **Professional Support Ticketing System**
A comprehensive customer support system with enterprise-level features:

#### **Ticket Management Features**
- **Auto-Generated Ticket Numbers**: Unique identifiers (e.g., TKT-68BE5273E3447)
- **Priority Levels**: Low, Medium, High, Urgent
- **Category System**: Technical, Billing, General, Booking, Account
- **Status Tracking**: Open, In Progress, Resolved, Closed
- **File Attachments**: Support for multiple file attachments

#### **User Experience**
- **Self-Service Portal**: Users can create, view, and update their tickets
- **Ownership Validation**: Users can only access their own tickets
- **Admin Access**: Administrators can view and manage all tickets
- **Update Notifications**: Ready for email notification integration

#### **Ticket Workflow**
```http
# Create a support ticket
POST /api/v1/support/tickets
{
  "subject": "Login Issue",
  "message": "Cannot access my account",
  "priority": "high",
  "category": "technical"
}

# List user's tickets with filtering
GET /api/v1/support/tickets?status=open&priority=high

# Update ticket with additional information
PATCH /api/v1/support/tickets/TKT-123
{
  "message": "Additional details...",
  "priority": "urgent"
}
```

### 📅 **Calendar & Availability Management**
Smart availability system for hosts and booking platforms:

#### **Availability Features**
- **Dynamic Calendar**: Generate availability for any date range
- **Seasonal Pricing**: Override default pricing for specific dates
- **Bulk Date Management**: Block multiple dates with one request
- **Booking Conflict Prevention**: Check availability before bookings
- **Price Calculation**: Automatic total calculation for date ranges

#### **Public & Private Endpoints**
- **Public Availability Check**: Anyone can check if property is available
- **Host Management**: Property owners can update availability and pricing
- **Booking Integration Ready**: Designed for seamless booking system integration

#### **Availability API Examples**
```http
# Check availability for dates (public)
POST /api/v1/bnbs/1/availability/check
{
  "check_in": "2025-12-01",
  "check_out": "2025-12-07"
}

# Get calendar view (public)
GET /api/v1/bnbs/1/availability?start_date=2025-12-01&end_date=2025-12-31

# Update availability (authenticated)
PATCH /api/v1/bnbs/1/availability/update
{
  "dates": [
    {
      "date": "2025-12-25",
      "is_available": true,
      "price_override": 200.00
    }
  ]
}
```

### 📧 **Email Notification System**
Professional email workflows using SMTP integration:

#### **Email Configuration**
- **Gmail SMTP Integration**: Production-ready email sending
- **Notification Templates**: Ready for booking confirmations, reminders
- **Multi-Channel Support**: Email, in-app, and push notification ready
- **Queue Integration**: Async email sending for better performance

#### **Notification Management**
```http
# Get user notifications
GET /api/v1/notifications

# Mark notification as read
PATCH /api/v1/notifications/1/mark-read

# Delete notification
DELETE /api/v1/notifications/1
```

### 📊 **Analytics & Dashboard System**
Comprehensive analytics for business insights:

#### **Analytics Features**
- **Property Performance**: Views, bookings, revenue tracking
- **User Analytics**: Dashboard with personalized metrics
- **Geographic Data**: Location-based performance insights
- **Rating Trends**: Track rating changes over time
- **Revenue Metrics**: Income tracking and forecasting

#### **Dashboard Endpoints**
```http
# User dashboard analytics
GET /api/v1/dashboard/analytics

# Specific property analytics
GET /api/v1/bnbs/1/analytics

# Admin system statistics
GET /api/v1/admin/stats
```

### �️ **Flutter Map Integration**
Specifically designed endpoints for mobile map implementations:

#### **Map-Optimized Endpoints**
- **Clustered Data**: Optimized for map clustering algorithms
- **Bounding Box Queries**: Efficient geographic queries
- **Minimal Payload**: Only essential data for map markers
- **Real-time Search**: Instant search as users pan/zoom

#### **Mobile-Friendly Features**
- **Consistent JSON Structure**: Predictable response format
- **Error Handling**: Proper HTTP status codes and error messages
- **Pagination Support**: Handle large datasets efficiently
- **CORS Enabled**: Ready for Flutter web applications

### 🔒 **Enterprise Security Features**
Production-ready security implementation:

#### **Authentication & Authorization**
- **JWT Token Authentication**: Secure, stateless authentication
- **Role-Based Access Control**: Admin and user role separation
- **Token Expiration**: Configurable token lifetime
- **Refresh Token Support**: Seamless token renewal

#### **Data Protection**
- **Input Validation**: Comprehensive request validation
- **SQL Injection Protection**: Eloquent ORM security
- **Rate Limiting**: API abuse prevention
- **CORS Security**: Configurable cross-origin policies

### 🚀 **Performance Optimizations**
Built for scale and performance:

#### **Database Optimizations**
- **Strategic Indexing**: Optimized database indexes
- **Efficient Queries**: N+1 query prevention
- **Pagination**: Memory-efficient data loading
- **Query Optimization**: Eager loading for relationships

#### **Caching & Scaling**
- **Redis Caching**: High-performance caching layer
- **Response Caching**: API response optimization
- **Queue Integration**: Async job processing
- **CDN Ready**: Cloudinary image optimization

## 📊 Database Schema

### Core Tables

#### BNBs Table
```sql
bnbs:
  id (bigint, primary key)
  name (varchar)
  location (varchar)
  description (text)
  latitude (decimal) - for geolocation
  longitude (decimal) - for geolocation
  price_per_night (decimal)
  max_guests (integer)
  bedrooms (integer)
  bathrooms (integer)
  amenities (json) - ['wifi', 'pool', 'parking', etc.]
  availability (boolean)
  average_rating (decimal) - calculated from reviews
  total_reviews (integer) - review count
  image_url (varchar) - Cloudinary URL
  created_at (timestamp)
  updated_at (timestamp)
```

#### Reviews Table
```sql
reviews:
  id (bigint, primary key)
  bnb_id (foreign key to bnbs)
  user_id (foreign key to users)
  rating (integer, 1-5)
  comment (text)
  stay_date (date)
  created_at (timestamp)
  updated_at (timestamp)
```

#### Support Tickets Table
```sql
support_tickets:
  id (bigint, primary key)
  ticket_number (varchar, unique) - auto-generated
  user_id (foreign key to users)
  assigned_to (foreign key to users, nullable)
  subject (varchar)
  message (text)
  priority (enum: low, medium, high, urgent)
  category (enum: technical, billing, general, booking, account)
  status (enum: open, in_progress, resolved, closed)
  attachments (json, nullable)
  resolved_at (timestamp, nullable)
  created_at (timestamp)
  updated_at (timestamp)
```

#### Notifications Table
```sql
notifications:
  id (bigint, primary key)
  user_id (foreign key to users)
  type (varchar) - notification class name
  title (varchar)
  message (text)
  data (json) - additional notification data
  channels (json) - ['email', 'database', 'push']
  is_read (boolean, default false)
  read_at (timestamp, nullable)
  created_at (timestamp)
  updated_at (timestamp)
```

#### Analytics Table
```sql
analytics:
  id (bigint, primary key)
  bnb_id (foreign key to bnbs)
  event_type (varchar) - view, booking, inquiry, etc.
  user_id (foreign key to users, nullable)
  metadata (json) - event-specific data
  created_at (timestamp)
```

#### Availability Table
```sql
availabilities:
  id (bigint, primary key)
  bnb_id (foreign key to bnbs)
  date (date)
  is_available (boolean)
  price_override (decimal, nullable) - seasonal pricing
  created_at (timestamp)
  updated_at (timestamp)
```

### Relationships
- **BNB → Reviews**: One-to-Many (a BNB can have multiple reviews)
- **User → Reviews**: One-to-Many (a user can write multiple reviews)
- **BNB → Analytics**: One-to-Many (a BNB can have multiple analytics events)
- **User → SupportTickets**: One-to-Many (a user can create multiple tickets)
- **User → Notifications**: One-to-Many (a user can receive multiple notifications)
- **BNB → Availabilities**: One-to-Many (a BNB has availability for multiple dates)

### Indexes
```sql
-- Performance indexes
CREATE INDEX idx_bnbs_location ON bnbs(latitude, longitude);
CREATE INDEX idx_bnbs_price ON bnbs(price_per_night);
CREATE INDEX idx_bnbs_rating ON bnbs(average_rating);
CREATE INDEX idx_reviews_bnb ON reviews(bnb_id);
CREATE INDEX idx_analytics_bnb_event ON analytics(bnb_id, event_type);
CREATE INDEX idx_availability_bnb_date ON availabilities(bnb_id, date);
```

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
├── Services/                   # Business Logic Services
│   └── ImageUploadService.php  # Image upload handling
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
- [ ] Configure Cloudinary production credentials
- [ ] Configure proper CORS origins
- [ ] Set up SSL certificates
- [ ] Configure web server (Nginx/Apache)
- [ ] Set up monitoring and logging
- [ ] Test image upload functionality

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

## 🎯 **Production Ready Features Summary**

This BNB Management System API represents a **complete, enterprise-grade backend solution** ready for production deployment. Here's what makes it special:

### ✅ **Feature Completeness**
- **🏠 Property Management**: Full CRUD with image upload, geolocation, and amenities
- **🔍 Advanced Search**: Multi-parameter filtering, geolocation search, map integration
- **⭐ Review System**: Complete rating and review functionality with aggregation
- **🎫 Support Tickets**: Professional customer support with ticketing system
- **📅 Availability Calendar**: Smart booking management with seasonal pricing
- **📧 Email Notifications**: SMTP integration with Gmail for automated workflows
- **📊 Analytics Dashboard**: Comprehensive metrics and business insights
- **🔐 Security & Auth**: JWT authentication with role-based access control

### ✅ **Technical Excellence**
- **🏗️ Clean Architecture**: Repository pattern, service layer, proper separation of concerns
- **⚡ Performance Optimized**: Strategic indexing, query optimization, Redis caching
- **🧪 Fully Tested**: Comprehensive test coverage with unit and feature tests
- **📱 Mobile Ready**: Flutter-optimized endpoints with CORS support
- **🔧 Developer Experience**: Comprehensive documentation, clear error messages
- **🚀 Scalable Design**: Built to handle growth with efficient database design

### ✅ **Business Value**
- **💼 Enterprise Features**: Support ticketing, analytics, role management
- **💰 Revenue Optimization**: Dynamic pricing, seasonal adjustments, booking management
- **👥 User Experience**: Reviews, ratings, availability checking, support system
- **📈 Growth Ready**: Analytics for data-driven decisions, scalable architecture
- **🔄 Integration Friendly**: RESTful design, consistent API structure

### 🚀 **Ready for Production**

**Immediate Deployment Capability:**
- All endpoints tested and functional
- Production-grade error handling
- Comprehensive validation and security
- Email notifications configured
- Database optimized with proper indexes
- Documentation complete

**Seamless Frontend Integration:**
- Flutter-optimized map endpoints
- Consistent JSON response structure
- Proper HTTP status codes
- CORS configured for web applications
- Real-time search capabilities

**Enterprise Features:**
- Professional support ticketing system
- Advanced analytics and reporting
- Role-based access control
- Multi-amenity property filtering
- Geolocation-based search

This backend provides everything needed to launch a professional BNB platform comparable to industry leaders like Airbnb, with advanced features that set it apart from basic property listing systems.

**Total Implementation**: 15+ advanced features, 25+ API endpoints, comprehensive database schema, and production-ready architecture.

---

## 📞 Support

For support, email [your-email@domain.com](mailto:your-email@domain.com) or create an issue on GitHub.

---

<p align="center">Built with ❤️ using Laravel 11.x</p>

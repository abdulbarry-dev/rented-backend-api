# Rented Backend API

A comprehensive Laravel-based REST API for a rental marketplace platform with dual authentication systems, user verification workflows, and product management capabilities.

## 🚀 Overview

This API provides a complete backend solution for a rental marketplace where users can browse, rent, and list products for rental. The system includes robust authentication, admin approval workflows, user verification processes, and comprehensive product management.

## 🏗️ Architecture

### Core Components

- **Framework**: Laravel 11+ with modern PHP 8.3 features
- **Authentication**: Laravel Sanctum for API token management
- **Database**: PostgreSQL with comprehensive relationships
- **Storage**: Laravel Filesystem for image/document handling
- **Service Layer**: Clean architecture with dedicated service classes

### System Architecture Patterns

- **Service Layer Pattern**: Business logic separated from controllers
- **Repository Pattern**: Data access abstraction through Eloquent models
- **Resource Pattern**: Consistent API response formatting
- **Request Validation**: Form request classes for input validation
- **Exception Handling**: Custom exception classes for specific error scenarios

## 🛠️ Technology Stack

### Backend Technologies
- **Laravel 11+**: Core framework with latest features
- **PHP 8.3**: Modern PHP with typed properties and attributes
- **PostgreSQL**: Primary database with advanced features
- **Laravel Sanctum**: API authentication and token management
- **Composer**: Dependency management

### Development Tools
- **Artisan CLI**: Laravel's command-line interface
- **Migration System**: Database version control
- **Seeders**: Database population for testing
- **Factory Classes**: Test data generation

## 📊 Database Schema

### Core Tables

#### Users System
```sql
users (id, first_name, last_name, email, phone, password_hash, role, created_at, updated_at)
user_verifications (id, user_id, national_id, full_name, address, phone_number, image_path, verification_status, notes, submitted_at, reviewed_at, admin_id)
```

#### Admin System
```sql
admins (id, name, email, password_hash, role, status, approved_by, approved_at, rejection_reason, created_at, updated_at)
admin_actions (id, admin_id, action, target_type, target_id, created_at)
```

#### Product System
```sql
products (id, owner_id, status, created_at, updated_at)
product_descriptions (id, product_id, title, description, product_images, categories)
product_verifications (id, product_id, verification_status, notes, submitted_at, reviewed_at, reviewed_by)
reviews (id, product_id, reviewer_id, rating, comment, created_at)
```

### Relationships
- **User → UserVerification**: One-to-One
- **User → Products**: One-to-Many (as owner)
- **Admin → AdminActions**: One-to-Many
- **Admin → ApprovedAdmins**: One-to-Many (self-referential)
- **Product → ProductDescription**: One-to-One
- **Product → ProductVerification**: One-to-One
- **Product → Reviews**: One-to-Many

## 🔐 Authentication System

### Dual Authentication Architecture

#### User Authentication
- **Registration**: Open registration with email/phone support
- **Login**: Flexible login with email or phone number
- **Roles**: Customer (buyer) and Seller (product owner)
- **Verification Required**: Identity verification for product transactions

#### Admin Authentication
- **Registration**: Public registration with pending approval
- **Approval Workflow**: Super admin approval required for activation
- **Roles**: Super Admin (full access) and Moderator (limited access)
- **Status Management**: Pending → Active → Banned workflow

### Token Management
- **Laravel Sanctum**: Secure API token generation
- **Token Abilities**: Role-based permissions
- **Multi-Device**: Support for multiple active sessions
- **Token Revocation**: Individual and bulk token invalidation

## 🎯 API Structure

### Public Endpoints (No Authentication)
```
POST /api/register              # User registration
POST /api/login                 # User login
GET  /api/products              # Browse products (public)
GET  /api/products/{id}         # View product details
POST /api/admin/register        # Admin registration (pending)
POST /api/admin/login           # Admin login
```

### User Protected Endpoints (Requires User Token)
```
GET    /api/profile             # User profile
POST   /api/logout              # Logout current session
GET    /api/tokens              # List active tokens
DELETE /api/tokens/{id}         # Revoke specific token

# Product Management
GET    /api/my-products         # User's products
POST   /api/products            # Create product (verified users only)
PUT    /api/products/{id}       # Update product (verified owners only)
DELETE /api/products/{id}       # Delete product (owners only)

# User Verification
GET    /api/verification/status        # Check verification status
POST   /api/verification/submit        # Submit verification documents
POST   /api/verification/resubmit      # Resubmit after rejection
GET    /api/verification/requirements  # Get verification requirements
```

### Admin Protected Endpoints (Requires Admin Token + Super Admin Role)
```
# Admin Management
GET    /api/admin/profile           # Admin profile
GET    /api/admin/admins            # List all admins
GET    /api/admin/statistics        # Admin statistics
GET    /api/admin/admins/pending    # Pending admin registrations
PATCH  /api/admin/admins/{id}/approve  # Approve pending admin
PATCH  /api/admin/admins/{id}/reject   # Reject pending admin
PATCH  /api/admin/admins/{id}/ban      # Ban moderator admin
PATCH  /api/admin/admins/{id}/unban    # Unban moderator admin
DELETE /api/admin/admins/{id}          # Delete moderator admin

# User Management
Resource: /api/admin/users         # Full CRUD operations
GET    /api/admin/users/role/{role}    # Filter users by role
PATCH  /api/admin/users/{id}/activate   # Activate user account
PATCH  /api/admin/users/{id}/deactivate # Deactivate user account
PATCH  /api/admin/users/{id}/role       # Change user role

# Product Management
GET    /api/admin/products/pending     # Products awaiting review
GET    /api/admin/products/all         # All products with filters
PATCH  /api/admin/products/{id}/review # Approve/reject products

# User Verification Management  
GET    /api/admin/verifications/pending     # Pending user verifications
GET    /api/admin/verifications/all         # All verifications with filters
GET    /api/admin/verifications/statistics  # Verification statistics
PATCH  /api/admin/verifications/{id}/review # Approve/reject user verification
```

## 🔄 Business Logic Workflows

### User Verification Workflow
1. **Registration**: User registers with basic information
2. **Browse Mode**: Unverified users can only browse products
3. **Verification Submission**: User submits national ID and details
4. **Admin Review**: Super admin reviews and approves/rejects
5. **Access Granted**: Verified users can create/rent products
6. **Resubmission**: Rejected users can resubmit with corrections

### Product Management Workflow
1. **Creation**: Verified sellers can create products
2. **Admin Review**: Products require admin verification
3. **Approval**: Approved products become publicly visible
4. **Rejection**: Rejected products return to seller with notes
5. **Updates**: Product changes reset verification status

### Admin Approval Workflow
1. **Registration**: Anyone can register as admin
2. **Pending Status**: New admins cannot login
3. **Super Admin Review**: Review pending registrations
4. **Approval**: Pending → Active (can login and work)
5. **Rejection**: Pending → Banned (cannot login)
6. **Management**: Super admins can ban/unban/delete moderators

## 📦 Service Layer Architecture

### BaseService
- **Foundation**: Common service functionality
- **Error Handling**: Centralized exception management
- **Validation**: Shared validation methods

### AuthService
- **User Authentication**: Login/logout/token management
- **Flexible Login**: Email or phone number detection
- **Session Management**: Multi-device support

### UserService
- **User Management**: Profile and account operations
- **Role Management**: Customer/seller role handling
- **Account Status**: Activation/deactivation logic

### AdminService
- **Admin Authentication**: Admin login/logout
- **Approval System**: Pending admin management
- **Administrative Actions**: Ban/unban/delete operations
- **Statistics**: Dashboard metrics and insights

### ProductService
- **Product CRUD**: Create/read/update/delete operations
- **Ownership Validation**: User permission checking
- **Verification Integration**: Product approval workflow
- **Rental Logic**: Rental eligibility checking

### UserVerificationService
- **Document Submission**: Handle verification documents
- **Status Management**: Track verification progress
- **Admin Review**: Process approval/rejection decisions
- **Resubmission**: Handle rejected verification resubmission

## 🛡️ Security Features

### Authentication Security
- **Password Hashing**: Bcrypt with proper salting
- **Token Security**: Sanctum secure token generation
- **Rate Limiting**: API endpoint protection
- **Input Validation**: Comprehensive request validation

### Authorization System
- **Role-Based Access**: User/Admin role separation
- **Permission Gates**: Fine-grained access control
- **Owner Validation**: Resource ownership checking
- **Status Verification**: Account status enforcement

### Data Protection
- **Input Sanitization**: XSS protection
- **SQL Injection Prevention**: Eloquent ORM protection
- **File Upload Security**: Validated image uploads
- **Sensitive Data**: Hidden password fields

## 📝 API Response Format

### Success Response
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response data
    },
    "pagination": {  // When applicable
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "errors": {  // Validation errors when applicable
        "field": ["Error message"]
    }
}
```

## 🚀 Getting Started

### Prerequisites
- PHP 8.3+
- Composer
- PostgreSQL
- Node.js (for asset compilation)

### Installation
```bash
# Clone repository
git clone https://github.com/abdulbarry-dev/rented-backend-api.git
cd rented-backend-api

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Create storage link
php artisan storage:link

# Start development server
php artisan serve
```

### Initial Setup
1. **Create Super Admin**: Use seeder to create first super admin
2. **Configure Storage**: Set up file storage for images
3. **Configure Mail**: Set up email for notifications
4. **API Testing**: Test endpoints with Postman/Insomnia

## 🔧 Management & Monitoring

### Admin Dashboard Capabilities
- **User Management**: View, activate, deactivate users
- **Admin Oversight**: Approve, reject, ban admin registrations
- **Product Review**: Approve/reject product listings
- **Verification Processing**: Review user identity documents
- **Statistics Dashboard**: Monitor platform metrics

### Logging & Auditing
- **Admin Actions**: Complete audit trail of admin activities
- **Authentication Events**: Login/logout tracking
- **Error Logging**: Comprehensive error tracking
- **Database Activity**: Migration and seeding logs

### Performance Monitoring
- **Database Indexing**: Optimized queries with proper indexes
- **Pagination**: Efficient data loading
- **Caching Strategy**: Laravel cache system integration
- **Query Optimization**: Eager loading and relationship optimization

## 📚 Development Guidelines

### Code Organization
- **Controllers**: Thin controllers, business logic in services
- **Models**: Eloquent models with relationships and scopes
- **Services**: Business logic and complex operations
- **Requests**: Input validation and sanitization
- **Resources**: API response formatting

### Best Practices
- **SOLID Principles**: Clean, maintainable code structure
- **DRY Principle**: Avoid code duplication
- **Error Handling**: Comprehensive exception management
- **Documentation**: Clear code documentation and comments
- **Testing**: Unit and feature test coverage

This API provides a robust, scalable foundation for a rental marketplace with comprehensive user management, product oversight, and administrative controls.

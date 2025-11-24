# Database Seeding Documentation

## Overview
This Laravel application includes a comprehensive database seeding system that creates realistic test data for a rental marketplace API.

## Quick Start

### Seed the Entire Database
```bash
php artisan migrate:fresh --seed
```

### Seed Specific Tables
```bash
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=ReviewSeeder
php artisan db:seed --class=UserVerificationSeeder
```

## Test Credentials

### Admin Accounts
| Role | Email | Password |
|------|-------|----------|
| Super Admin | super@admin.com | password123 |
| Super Admin | alice@admin.com | password123 |
| Moderator | bob@admin.com | password123 |
| Moderator | carol@admin.com | password123 |
| Pending Admin | emma@admin.com | password123 |

### User Accounts
| Role | Email | Password |
|------|-------|----------|
| Customer | customer@test.com | password123 |
| Seller | seller@test.com | password123 |
| Unverified | unverified@test.com | password123 |

## Seeder Classes

### 1. AdminSeeder
Creates admin accounts with different roles and statuses:
- **Super Admins**: Full platform access
- **Moderators**: Limited administrative access
- **Pending Admins**: Awaiting approval
- **Banned Admins**: For testing ban functionality

**Count**: ~8 admins

### 2. UserSeeder
Creates regular platform users:
- **Customers**: 15 users (can rent products)
- **Sellers**: 10 users (can list products)
- **Test Users**: 3 users with known credentials
- **Inactive Users**: 3 users for testing activation/deactivation

**Count**: ~28 users

### 3. ProductSeeder
Creates products with full details:
- Distributed across all sellers (2-8 products per seller)
- Includes ProductDescription for each product
- Includes ProductVerification for each product
- **Statuses**: available (50%), rented (25%), unavailable (25%)
- **Verification**: verified (70%), pending (20%), rejected (10%)

**Count**: ~30-50 products

### 4. ReviewSeeder
Creates product reviews with realistic ratings:
- 60% of verified products have reviews
- 1-5 reviews per product
- **Rating Distribution**:
  - 5 stars: 50%
  - 4 stars: 30%
  - 3 stars: 10%
  - 2 stars: 5%
  - 1 star: 5%

**Count**: ~50-100 reviews

### 5. UserVerificationSeeder
Creates user identity/business/address verifications:
- 80% of users have at least one verification
- **Types**: identity, business, address
- **Statuses**: verified (60%), pending (30%), rejected (10%)

**Count**: ~30-60 verifications

## Factory Classes

All models have comprehensive factories with helpful state methods:

### AdminFactory
```php
Admin::factory()->super()->create();
Admin::factory()->moderator()->active()->create();
Admin::factory()->pending()->create();
Admin::factory()->banned()->create();
```

### UserFactory
```php
User::factory()->customer()->create();
User::factory()->seller()->create();
User::factory()->inactive()->create();
User::factory()->withAvatar()->create();
```

### ProductFactory
```php
Product::factory()->available()->create();
Product::factory()->rented()->create();
Product::factory()->ownedBy($seller)->create();
```

### ProductDescriptionFactory
```php
ProductDescription::factory()->forProduct($product)->create();
ProductDescription::factory()->category('Electronics')->create();
```

### ReviewFactory
```php
Review::factory()->rating(5)->create();
Review::factory()->positive()->create();
Review::factory()->negative()->create();
Review::factory()->forProduct($product)->byUser($user)->create();
```

### ProductVerificationFactory
```php
ProductVerification::factory()->verified()->create();
ProductVerification::factory()->pending()->create();
ProductVerification::factory()->rejected()->create();
```

### UserVerificationFactory
```php
UserVerification::factory()->identity()->verified()->create();
UserVerification::factory()->business()->pending()->create();
UserVerification::factory()->address()->rejected()->create();
```

## Data Relationships

The seeders maintain proper relationships:

```
Admin
├── AdminAction (logs)
└── approvedAdmins (admins they approved)

User
├── ownedProducts (as seller)
├── reviews (as customer)
└── verifications (identity/business/address)

Product
├── owner (User)
├── description (ProductDescription)
├── verification (ProductVerification)
└── reviews (Review)

Review
├── user (reviewer)
└── product (reviewed product)

UserVerification
├── user
└── reviewer (Admin)

ProductVerification
├── product
└── reviewer (Admin)
```

## Customization

### Modify Seeding Counts
Edit the seeder classes to adjust quantities:

```php
// In UserSeeder.php
$customerCount = 15; // Change this
$sellerCount = 10;   // Change this

// In ProductSeeder.php
$productCount = fake()->numberBetween(2, 8); // Adjust range
```

### Add Custom Test Data
Add specific scenarios in individual seeders:

```php
// In ProductSeeder.php
$highRatedProduct = Product::factory()->available()->create();
ProductDescription::factory()->forProduct($highRatedProduct)->create([
    'title' => 'My Custom Product',
]);
```

## Best Practices

1. **Always use factories** for creating test data
2. **Use relationship methods** instead of hardcoded IDs
3. **Create test users** with known credentials for API testing
4. **Seed in order**: Admins → Users → Products → Verifications → Reviews
5. **Check for existing data** to prevent duplicates
6. **Use realistic data** for meaningful testing

## Troubleshooting

### Foreign Key Constraints
If you get foreign key errors:
```bash
php artisan migrate:fresh --seed
```

### Memory Issues
For large datasets, increase memory limit:
```bash
php -d memory_limit=512M artisan db:seed
```

### Specific Seeder Fails
Run seeders individually to isolate issues:
```bash
php artisan db:seed --class=ProductSeeder
```

## Notes

- All passwords are `password123` for easy testing
- Image paths are generated but files don't exist (use mock storage)
- Dates are realistic (within last 6 months)
- Reviews and verifications reference actual users/admins
- Products are distributed realistically across sellers

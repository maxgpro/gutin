# Blog Tests Structure

This directory contains all tests related to the blog functionality.

## Directory Structure

```
tests/Feature/Blog/
├── Controllers/           # Controller integration tests
│   ├── BlogPostControllerTest.php
│   └── BlogCategoryControllerTest.php
├── Policies/             # Authorization policy tests
│   ├── BlogPostPolicyTest.php
│   └── BlogCategoryPolicyTest.php
└── Middleware/           # Middleware tests
    └── AdminMiddlewareTest.php
```

## Test Categories

### Controllers (`/Controllers/`)
HTTP integration tests for blog controllers:
- Route access permissions
- Request validation
- Response status codes
- Database operations
- Authentication requirements

### Policies (`/Policies/`)
Authorization logic tests:
- User permission checks
- Role-based access control
- Author vs Admin permissions
- Guest access rules

### Middleware (`/Middleware/`)
Middleware functionality tests:
- Admin access control
- Authentication redirects
- Route protection

## Running Tests

```bash
# Run all blog tests
php artisan test tests/Feature/Blog/

# Run specific test category
php artisan test tests/Feature/Blog/Controllers/
php artisan test tests/Feature/Blog/Policies/
php artisan test tests/Feature/Blog/Middleware/

# Run specific test file
php artisan test tests/Feature/Blog/Controllers/BlogPostControllerTest.php
```

## Test Coverage

- ✅ **46 tests** with **71 assertions**
- ✅ All CRUD operations for posts and categories
- ✅ Complete authorization matrix (Guest/User/Author/Admin)
- ✅ All middleware protection scenarios
- ✅ Policy edge cases and permissions
# Blog Tests Structure

This directory contains all tests related to the blog functionality including rich text editing, syntax highlighting, and permission-based UI components.

## Directory Structure

```
tests/Feature/Blog/
├── Controllers/                    # Controller integration tests
│   ├── BlogPostControllerTest.php
│   └── BlogCategoryControllerTest.php
├── Policies/                      # Authorization policy tests
│   ├── BlogPostPolicyTest.php
│   └── BlogCategoryPolicyTest.php
├── Middleware/                    # Middleware tests
│   └── AdminMiddlewareTest.php
├── PermissionBasedUITest.php     # UI permission visibility tests
├── TiptapEditorIntegrationTest.php # Rich text editor tests
└── SyntaxHighlightingTest.php    # Code highlighting tests
```

## Test Categories

### Controllers (`/Controllers/`)
HTTP integration tests for blog controllers:
- Route access permissions
- Request validation
- Response status codes
- Database operations
- Authentication requirements
- Permission data passing to frontend

### Policies (`/Policies/`)
Authorization logic tests:
- User permission checks
- Role-based access control
- Author vs Admin permissions
- Guest access rules
- Create/Update/Delete permissions
- View permissions for published/draft content

### Middleware (`/Middleware/`)
Middleware functionality tests:
- Admin access control
- Authentication redirects
- Route protection
- Policy integration

### Permission-Based UI (`PermissionBasedUITest.php`)
Frontend permission visibility tests:
- Create button visibility for admins
- Create button hiding for regular users
- Create button hiding for guests
- Inertia.js props validation
- TypeScript interface compliance

### Rich Text Editor (`TiptapEditorIntegrationTest.php`)
Tiptap editor functionality tests:
- HTML content creation and updates
- Content formatting preservation
- Editor integration with Laravel forms
- Database storage of rich content

### Syntax Highlighting (`SyntaxHighlightingTest.php`)
Code block highlighting tests:
- Multiple programming languages support
- Language attribute preservation
- Proper HTML rendering with highlighting
- Integration with Tiptap editor

## Running Tests

```bash
# Run all blog tests
php artisan test tests/Feature/Blog/

# Run specific test categories
php artisan test tests/Feature/Blog/Controllers/
php artisan test tests/Feature/Blog/Policies/
php artisan test tests/Feature/Blog/Middleware/

# Run specific functionality tests
php artisan test --filter="PermissionBasedUITest"
php artisan test --filter="TiptapEditorIntegrationTest" 
php artisan test --filter="SyntaxHighlightingTest"

# Run specific test file
php artisan test tests/Feature/Blog/Controllers/BlogPostControllerTest.php

# Run with filter
php artisan test --filter="blog"
```

## Test Coverage

- ✅ **64 tests** with **272+ assertions**
- ✅ All CRUD operations for posts and categories
- ✅ Complete authorization matrix (Guest/User/Author/Admin)  
- ✅ All middleware protection scenarios
- ✅ Policy edge cases and permissions
- ✅ Permission-based UI visibility controls
- ✅ Rich text editor (Tiptap) integration
- ✅ Syntax highlighting for code blocks
- ✅ TypeScript interface validation
- ✅ Inertia.js props testing

## Key Features Tested

### Authentication & Authorization
- Multi-level permission system (Guest → User → Author → Admin)
- Policy-based access control for all operations
- Middleware protection for admin-only routes
- Frontend permission visibility (UI buttons)

### Rich Content Management  
- Tiptap WYSIWYG editor integration
- HTML content preservation and validation
- Syntax highlighting for code blocks (PHP, JavaScript, etc.)
- Dynamic content rendering

### User Interface
- Permission-based button visibility
- Type-safe Inertia.js props
- Vue 3 + TypeScript integration
- Responsive design compliance

### Data Integrity
- Complete CRUD operation validation
- Database constraint testing
- Form validation and error handling
- Cross-model relationship testing
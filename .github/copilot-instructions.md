# AI Agent Instructions for Laravel 12 + Vue + Inertia.js Project

## Humanize
User speak Russian. Respond in Russian.

## Architecture Overview

This is a **Laravel 12 + Vue 3 + Inertia.js** full-stack application. It uses:

- **Backend**: Laravel 12 with encrypted token storage
- **Frontend**: Vue 3 with TypeScript, Inertia.js for SPA behavior
- **UI**: Reka UI components (Radix Vue) with Tailwind CSS v4
- **Type-safe routing**: Laravel Wayfinder generates TypeScript route definitions
- **Testing**: Pest PHP for backend tests

## Key Patterns & Conventions

### Frontend Architecture

- **Route organization**: `resources/js/routes/` mirrors Laravel route structure
- **Type-safe forms**: Wayfinder generates `RouteFormDefinition` types for form validation
- **Component structure**: Shadcn-style UI components in `resources/js/components/ui/`
- **Inertia pages**: Located in `resources/js/pages/` with `.vue` extension

### Database Patterns

- Encrypted fields use Laravel's `encrypted` cast (e.g., `access_token`, `refresh_token`)
- JSON storage for complex data: `profile` and `token_payload` use `array` cast
- Foreign key constraints with cascade deletes

## Development Workflows

### Build & Dev Commands

```bash
# Backend development
php artisan serve                # Laravel dev server
php artisan migrate:fresh --seed # Reset database

# Frontend development
npm run dev                      # Vite dev server with HMR
npm run build:ssr               # Build with SSR support

# Testing
php artisan test                 # Run Pest tests
npm run lint                     # ESLint + Prettier check
```

### Component Development

- Use Reka UI components from `@/components/ui/`
- Follow the `buttonVariants` CVA pattern for styling variants
- Import icons from `lucide-vue-next`

### Route Development

- Add Laravel routes normally, Wayfinder auto-generates TypeScript definitions
- Use generated types: `import { routes } from '@/routes'`
- Form actions use `RouteFormDefinition` for type-safe submissions

## Critical Files & Dependencies

- `resources/js/wayfinder/` - Auto-generated route definitions (don't edit)
- `components.json` - Shadcn Vue configuration for UI components
- `vite.config.ts` - Includes Wayfinder plugin for route generation
- `config/inertia.php` - SSR enabled on port 13714

## External Integrations

- **HeadHunter API**: OAuth2 flow with automatic token refresh
- **Reka UI**: Unstyled accessible components with CVA variants
- **Laravel Wayfinder**: Type-safe routing between Laravel and Vue

When working with this codebase, prioritize type safety, follow the established OAuth patterns, and use the Wayfinder-generated route definitions for all navigation and form submissions.

## User Instructions

Use shandcn-iu firstly then create custom components. Use Tailwind CSS v4 for styling. Use TypeScript for all new code. Follow existing code patterns for consistency.

Всегда проверяйте готовые возможности фреймворка перед написанием кастомной логики
Request authorization - правильное место для проверки прав доступа к параметрам
Разделение ответственности между слоями архитектуры критически важно

# Tests
This project includes a comprehensive suite of tests to ensure the reliability and correctness of the blog features, including controllers, policies, middleware, and integrations. Below is an overview of the test structure, how to run them, and key features covered. Make tests for new features.

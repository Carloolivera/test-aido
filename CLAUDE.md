# CLAUDE.md - test-aido

## Session Init

Al inicio de cada sesion automaticamente:
1. Lee el ultimo session log en `C:\DEV\.aido-system\context\active\`
2. Lee este CLAUDE.md completo
3. Reporta: cantidad de tests, coverage, y ultima feature completada
4. Pregunta en que quiero trabajar hoy

## Proyecto

Proyecto de prueba del sistema AIDO. Valida que workflows, skills y agents funcionen correctamente.
Repo: https://github.com/Carloolivera/test-aido

## Stack

- Laravel 12 + PHP 8.2+
- Livewire 3.x (downgraded por Breeze, que requiere v3)
- Laravel Breeze (auth: login, register, password reset, profile)
- Tailwind CSS 4 (ya incluido en Laravel 12, no necesita tailwind.config.js)
- Alpine.js (incluido con Livewire)
- SQLite (desarrollo) / PostgreSQL (producción)
- Vite (assets)

## Sistema AIDO

El sistema AIDO está en `C:\DEV\.aido-system\` con esta estructura:
- `skills/` - Guías paso a paso reutilizables (CRUD, auth, API, deploy, testing)
- `workflows/` - Procedimientos multi-paso (nuevo proyecto, migración DB, handoff IA)
- `agents/` - Roles especializados (laravel-expert, database-architect, devops, frontend)
- `templates/` - .env, Dockerfile, docker-compose
- `context/active/` - Session logs de la sesión actual
- `context/archive/` - Historial de sesiones anteriores

Siempre leer el session log activo en `C:\DEV\.aido-system\context\active\` al inicio de cada sesión.

## Convenciones

- Modelos: singular PascalCase (`Product`, `Category`)
- Tablas: plural snake_case (`products`, `categories`)
- Componentes Livewire: PascalCase (`ProductManager`)
- Services: `XyzService` para lógica compleja
- Form Requests para validaciones
- Factories + Seeders para datos de prueba

## Lecciones importantes

- Breeze hizo downgrade de Livewire 4 → 3 (Breeze v2.x requiere Livewire 3)
- Layout está en `resources/views/layouts/app.blade.php` (Breeze standard)
- Componentes Livewire full-page usan `#[Layout('layouts.app')]` attribute
- Vistas Livewire usan `<x-slot name="header">` para el header de Breeze
- Vistas deben soportar dark mode (clases `dark:` de Tailwind)
- Tailwind CSS 4 usa `@import 'tailwindcss'` en app.css, no `@tailwind base/components/utilities`
- SQLite viene preconfigurado en Laravel 12
- Timezone: `America/Argentina/Buenos_Aires`

## Comandos

```bash
php artisan serve          # Servidor dev (puerto 8000)
npm run dev                # Vite watch
php artisan migrate:fresh --seed  # Reset DB con datos
php artisan test           # Tests
```

## Estado actual

- Auth completa (Breeze + Livewire): login, register, password reset, profile, logout
- Roles y permisos: admin/user con middleware `EnsureUserIsAdmin`
- Dashboard dinámico (Livewire): stats, actividad reciente, acciones rápidas (rol-based)
- Product CRUD completo (Livewire): crear, leer, editar, eliminar, buscar, paginar, filtros avanzados
- Category CRUD completo (Livewire): con relación a Products, filtros por estado (admin only)
- API REST (Sanctum): /api/products (read: all auth, write: admin), /api/register, /api/login, /api/logout, /api/user
- Exportación CSV/Excel: productos y categorías con filtros (admin only)
- Rutas Web: `/` (welcome), `/login`, `/register`, `/dashboard` (auth), `/products` (auth), `/categories` (admin), `/profile` (auth)
- DB: users (con role), products, categories, cache, jobs, sessions
- Tests: 191 pasando (Pest + PHPUnit)
- Testing framework: Pest con plugin Laravel
- Cobertura de código: 100% total

## Roles

- `admin`: acceso completo (categories, export, API write)
- `user`: acceso a dashboard, products, profile
- Middleware: `admin` alias → `EnsureUserIsAdmin`
- Helper: `$user->isAdmin()` en el modelo User
- Seeders: admin@example.com (admin), test@example.com (user)

## Tests

```bash
php artisan test                                    # Todos los tests (191)
php artisan test --filter="DashboardTest"           # Tests de Dashboard (13)
php artisan test --filter="RoleMiddlewareTest"      # Tests de Roles (20)
php artisan test --filter="ProductManagerTest"      # Tests de ProductManager (39)
php artisan test --filter="ProductApiTest"          # Tests de API Products (23)
php artisan test --filter="AuthApiTest"             # Tests de API Auth (21)
php artisan test --filter="ExportControllerTest"    # Tests de Export (28)
php artisan test --filter="CategoryManagerTest"     # Tests de CategoryManager (17)
php artisan test --coverage                         # Con cobertura (requiere Xdebug)
```

## Extensiones PHP instaladas

- Xdebug 3.3.1 (modo coverage) - `C:\xampp\php\ext\php_xdebug.dll`

## Skills AIDO disponibles

Usar cuando se necesite seguir un proceso paso a paso:
- **CRUD**: `C:\DEV\.aido-system\skills\laravel-crud-generator.md`
- **Auth**: `C:\DEV\.aido-system\skills\laravel-auth-setup.md`
- **API REST**: `C:\DEV\.aido-system\skills\laravel-api-rest.md`
- **Testing**: `C:\DEV\.aido-system\skills\laravel-testing.md`
- **Deploy Hostinger**: `C:\DEV\.aido-system\skills\laravel-deploy-hostinger.md`
- **Export CSV/Excel**: `C:\DEV\.aido-system\skills\laravel-export-csv-excel.md`
- **Roles/Permisos**: `C:\DEV\.aido-system\skills\laravel-roles-permissions.md`

## Slash Commands

- `/crud [Modelo campo1:tipo campo2:tipo]` - Genera CRUD completo siguiendo skill AIDO
- `/test-coverage` - Analiza coverage y sugiere tests faltantes
- `/handoff` - Genera documento de handoff para cambiar de IA
- `/deploy-check` - Checklist pre-deploy para Hostinger

## Próximos pasos sugeridos

- [ ] Tests E2E con Laravel Dusk
- [ ] Más roles (editor, viewer) con permisos granulares
- [ ] Notificaciones (email on CRUD actions)
- [ ] Dashboard charts con Chart.js o similar

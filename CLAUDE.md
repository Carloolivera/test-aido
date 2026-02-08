# CLAUDE.md - test-aido

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
- Product CRUD completo (Livewire): crear, leer, editar, eliminar, buscar, paginar
- Ruta `/products` protegida con middleware `auth`
- Rutas: `/` (welcome), `/login`, `/register`, `/dashboard` (auth), `/products` (auth), `/profile` (auth)
- DB: users, products (20 registros seed), cache, jobs, sessions
- Tests: 26 pasando (auth + base)
- API: NO configurada aún

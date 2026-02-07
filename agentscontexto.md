# Contexto de Proyecto - test-aido

## Información General

- **Cliente**: Interno AIDO (proyecto de prueba)
- **Fecha inicio**: 2026-02-06
- **Estado**: En desarrollo
- **Repositorio**: https://github.com/Carloolivera/test-aido
- **URL Producción**: N/A (proyecto de prueba)

## Stack Tecnológico

### Backend

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Base de datos (dev)**: SQLite
- **Base de datos (prod)**: PostgreSQL

### Frontend

- **UI Framework**: Livewire 4.x
- **CSS**: Tailwind CSS 4.x
- **JavaScript**: Alpine.js (incluido con Livewire)

### Infraestructura

- **Servidor**: Hostinger (si se despliega)
- **Docker**: No
- **CI/CD**: Manual

## Integraciones Externas

- Ninguna (proyecto de prueba)

## Estructura de Base de Datos

### Tablas Principales

- `users` - Usuarios del sistema (default Laravel)
- `cache` - Cache del sistema
- `jobs` - Cola de trabajos

### Relaciones Importantes

- Default Laravel (users, sessions, cache, jobs)

## Variables de Entorno Críticas

### Desarrollo (.env.local)

```env
APP_ENV=local
DB_CONNECTION=sqlite
APP_TIMEZONE=America/Argentina/Buenos_Aires
```

### Producción (.env.production)

```env
APP_ENV=production
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_DATABASE=test_aido

# Credenciales de producción (NO commitear)
```

## Comandos Comunes

### Desarrollo

```bash
# Iniciar proyecto
php artisan serve
npm run dev

# Migraciones
php artisan migrate:fresh --seed

# Testing
php artisan test
```

### Deploy

```bash
# Ver workflow: deploy-to-hostinger.md
```

## Archivos Importantes

- `app/Services/` - Lógica de negocio
- `app/Livewire/` - Componentes Livewire
- `routes/web.php` - Rutas principales
- `routes/api.php` - API endpoints

## Convenciones del Proyecto

- Modelos en singular: `User`, `Product`
- Tablas en plural: `users`, `products`
- Service classes para lógica compleja
- Form Requests para validaciones

## Testing

- **Cobertura actual**: Default Laravel
- **Framework**: PHPUnit
- **Comandos**: `php artisan test`

## Notas Importantes

- Este es un **proyecto de prueba** del sistema AIDO
- Objetivo: Validar workflows, skills y agents funcionan correctamente
- No tiene funcionalidad de negocio real

---

**Última actualización**: 2026-02-06
**Actualizado por**: Claude Code (handoff desde Gemini)

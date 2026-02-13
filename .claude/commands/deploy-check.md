Lee el skill de deploy en C:\DEV\.aido-system\skills\laravel-deploy-hostinger.md y ejecuta el checklist de verificacion pre-deploy.

Verifica cada uno de estos puntos contra el estado actual del proyecto:

1. **Tests**: Ejecuta `php artisan test` - todos deben pasar
2. **Env production**: Verifica que existe template en C:\DEV\.aido-system\templates\laravel-env-production.env
3. **Debug mode**: Verifica que APP_DEBUG=false en config de produccion
4. **APP_KEY**: Verifica que existe
5. **Database**: Verifica que las migraciones estan listas para PostgreSQL
6. **Assets**: Ejecuta `npm run build` y verifica que compila sin errores
7. **Cache**: Verifica config de cache para produccion
8. **CORS**: Verifica configuracion si hay API
9. **Rutas**: Ejecuta `php artisan route:list` y verifica que no hay rutas expuestas innecesariamente

Reporta un resumen con estado PASS/FAIL para cada punto y recomendaciones para los que fallen.

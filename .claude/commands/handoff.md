Lee el workflow de handoff en C:\DEV\.aido-system\workflows\ia-handoff-workflow.md y genera un documento de handoff completo para la sesion actual.

Pasos:
1. Ejecuta `git status` para ver el estado actual
2. Ejecuta `git log --oneline -10` para los ultimos commits
3. Lee el CLAUDE.md del proyecto para el contexto actual
4. Lee el ultimo session log en C:\DEV\.aido-system\context\active\

Genera el handoff con estas secciones:
- **IA origen**: Claude Code (Opus 4.6)
- **Proyecto**: nombre y stack
- **Estado actual**: que funciona, que tests pasan, coverage
- **Trabajo completado esta sesion**: lista detallada
- **Trabajo pendiente**: next steps
- **Archivos criticos modificados**: con rutas
- **Decisiones de arquitectura**: las que se tomaron
- **Comandos para arrancar**: como levantar el proyecto
- **Errores conocidos**: si los hay

Guarda el handoff en C:\DEV\.aido-system\context\active\ con formato YYYY-MM-DD-handoff.md

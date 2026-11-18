# Casos de uso

Contrato humano de cada clase en `app/Application`. Un archivo por caso de uso, mismo nombre de clase (`GenerateDietPlan` → `GenerateDietPlan.md`).

Los tests en `tests/Unit/Application` son la spec ejecutable. Este catálogo no duplica ramas de `if` ni el cuerpo de `handle()`.

## Plantilla

```markdown
# NombreDelCasoDeUso
- Actor:
- Trigger:
- Input:
- Output:
- Puertos:
- Errores:
- Notas:
```

Actualizar el markdown en el mismo cambio que crea o modifica la clase (firma, puertos, excepciones).

Se documenta a partir del primer caso de uso de producto (Fase 1+).

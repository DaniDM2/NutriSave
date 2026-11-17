# NutriSave

Aplicación Laravel 12 con arquitectura onion, Livewire 4, Neuron AI, DOMPDF, Redis, Horizon, PostgreSQL y Docker Compose.

## Stack

| Capa | Tecnología |
| --- | --- |
| Framework | Laravel 12 |
| Arquitectura | Onion (Domain → Application → Infrastructure → Presentation) |
| IA | Neuron AI (`neuron-core/neuron-laravel`) |
| PDF | DOMPDF (`barryvdh/laravel-dompdf`) |
| Colas | Redis + Laravel Horizon |
| Persistencia | PostgreSQL 16 |
| Frontend | Livewire v4 y Alpine.js (incluido en Livewire) |
| Runtime | Docker Compose (PHP-FPM, Nginx, Postgres, Redis, Horizon, scheduler, Vite) |

## Arquitectura onion

Las dependencias apuntan hacia adentro. El dominio no conoce Laravel, Neuron ni DOMPDF.

```
app/
  Domain/            Contratos, value objects y excepciones de negocio
  Application/       Casos de uso (orquestación)
  Infrastructure/    Adaptadores: Neuron, DOMPDF, Eloquent, jobs de cola
  Http/ y Livewire/  Presentación
```

Los puertos se enlazan en `app/Providers/DomainServiceProvider.php`:

- `AiAgent` → `NeuronAiAgent`
- `PdfGenerator` → `DomPdfGenerator`

Los modelos Eloquent siguen en `app/Models` (infraestructura de persistencia de Laravel). Los repositorios de dominio se añaden en `app/Infrastructure/Persistence/Eloquent` a medida que crezcan los bounded contexts.

## Arranque con Docker

Requisitos: Docker Desktop y Compose.

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Si ya existe un `.env` con `APP_KEY`, no hace falta regenerarla.

Servicios:

- App: http://localhost:8080
- Horizon: http://localhost:8080/horizon
- Vite (HMR): http://localhost:5173
- PostgreSQL: `localhost:5432` (usuario/clave `nutrisave` / `secret`)
- Redis: `localhost:6379`

Comandos útiles:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose exec app php artisan horizon:status
docker compose logs -f horizon
```

## Neuron AI

Configura el proveedor en `.env`:

```dotenv
NEURON_AI_PROVIDER=openai
OPENAI_KEY=sk-...
OPENAI_MODEL=gpt-5-mini
```

Proveedores soportados: `anthropic`, `gemini`, `openai`, `openai-responses`, `mistral`, `ollama`, `huggingface`, `deepseek`.

Nuevos agentes: `php artisan neuron:agent NombreDelAgente`. En este proyecto los agentes viven en `app/Infrastructure/Ai/Neuron` para no filtrar el SDK hacia el dominio.

## Tests

Los tests de PHPUnit usan SQLite en memoria. No requieren Docker:

```bash
php artisan test
```

Horizon necesita las extensiones `pcntl` y `posix`, disponibles en el contenedor `app`, no en PHP de Windows.

# ShowLatestBodySnapshot
- Actor: usuario autenticado
- Trigger: Livewire mount de dashboard / peso
- Input: UserId del actor
- Output: BodySnapshotView o null si no hay registros
- Puertos: BodySnapshotRepository
- Errores: ninguno
- Notas: el más reciente por recorded_at. No calcula caducidad.

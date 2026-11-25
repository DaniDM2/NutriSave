# ShowSnapshotStatus
- Actor: usuario autenticado
- Trigger: Livewire mount de dashboard / peso
- Input: UserId del actor, now opcional
- Output: SnapshotStatusView (perfil, snapshot, caducado, cadencia, días desde el último)
- Puertos: ProfileRepository, BodySnapshotRepository
- Errores: ninguno
- Notas: sin snapshot no es caducado. Caducado = recordedAt + cadencia días ≤ now. Sin scheduler ni email.

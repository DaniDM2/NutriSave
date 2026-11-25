# RecordBodySnapshot
- Actor: usuario autenticado con perfil
- Trigger: Livewire “Registrar peso”
- Input: UserId del actor, peso kg, % grasa/músculo/agua opcionales
- Output: BodySnapshot persistido con id, origen manual
- Puertos: ProfileRepository, BodySnapshotRepository
- Errores: ProfileNotFound, InvalidWeight, InvalidCompositionPercent
- Notas: IMC desde la altura del perfil. Sin fotos (eso es visión de báscula). recordedAt = ahora.

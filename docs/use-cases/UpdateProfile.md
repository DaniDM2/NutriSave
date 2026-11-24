# UpdateProfile
- Actor: usuario autenticado con perfil
- Trigger: Livewire “Guardar perfil” cuando el perfil ya existe
- Input: UserId del actor, sexo, fecha de nacimiento, altura cm, actividad, deportes, objetivo, cadencia
- Output: Profile persistido con el mismo id
- Puertos: ProfileRepository
- Errores: ProfileNotFound, InvalidHeight, InvalidBirthDate, InvalidSportName, DuplicateSport
- Notas: reconstruye el agregado con el id existente. Sin peso.

# CreateProfile
- Actor: usuario autenticado sin perfil
- Trigger: Livewire “Guardar perfil”
- Input: UserId del actor, sexo, fecha de nacimiento, altura cm, actividad, deportes, objetivo, cadencia
- Output: Profile persistido con id
- Puertos: ProfileRepository
- Errores: UserAlreadyHasProfile, InvalidHeight, InvalidBirthDate, InvalidSportName, DuplicateSport
- Notas: un usuario, un perfil en v1. Sin peso (eso es BodySnapshot).

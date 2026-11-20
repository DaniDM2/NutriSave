# CreateHousehold
- Actor: usuario autenticado sin hogar
- Trigger: Livewire “Crear hogar”
- Input: UserId del actor, nombre del hogar
- Output: Household persistido con id
- Puertos: HouseholdRepository
- Errores: UserAlreadyInHousehold, InvalidHouseholdName
- Notas: un usuario, un hogar en v1

# ShowHousehold
- Actor: miembro del hogar (o usuario sin hogar)
- Trigger: Livewire pantalla “Hogar”
- Input: UserId del actor
- Output: HouseholdView o null si no pertenece a ningún hogar
- Puertos: HouseholdRepository, HouseholdInvitationRepository, UserDirectory
- Errores: ninguno (ausencia = null)
- Notas: las invitaciones pendientes solo se incluyen si el actor es owner

# AcceptHouseholdInvitation
- Actor: usuario autenticado cuyo email coincide con la invitación
- Trigger: Livewire “Unirme al hogar”
- Input: UserId de la sesión, token plano de la URL
- Output: void (miembro añadido, invitación borrada)
- Puertos: HouseholdRepository, HouseholdInvitationRepository, UserDirectory
- Errores: InvitationNotFound, InvitationExpired, InvitationEmailMismatch, UserAlreadyInHousehold, DuplicateHouseholdMember
- Notas: el email de `UserDirectory` debe coincidir (case-insensitive) con el de la invitación

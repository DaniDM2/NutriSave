# InviteHouseholdMember
- Actor: owner del hogar
- Trigger: Livewire “Invitar”
- Input: UserId del owner, email del invitado
- Output: void (invitación persistida y correo enviado)
- Puertos: HouseholdRepository, HouseholdInvitationRepository, UserDirectory, HouseholdInvitationNotifier
- Errores: NotHouseholdOwner, CannotInviteSelf, DuplicateHouseholdMember, UserAlreadyInHousehold, InvalidInvitationEmail
- Notas: el invitado puede no tener cuenta; reenviar a un email pendiente rota el token; caduca a 7 días

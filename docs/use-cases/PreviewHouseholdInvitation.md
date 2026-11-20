# PreviewHouseholdInvitation
- Actor: visitante del enlace (guest o autenticado)
- Trigger: GET `/invitations/{token}`
- Input: token plano de la URL
- Output: InvitationPreview (nombre del hogar, email, caducada)
- Puertos: HouseholdRepository, HouseholdInvitationRepository
- Errores: InvitationNotFound
- Notas: no consume la invitación; Livewire decide si mostrar login, registro o “Unirme”

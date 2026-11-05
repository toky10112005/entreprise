
export function formaterDate(date) {
  if (!date) {
    return '-'
  }

  const dateFormatee = new Date(date.replace(' ', 'T'))
  if (Number.isNaN(dateFormatee.getTime())) {
    return date
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(dateFormatee)
}

export function nomManager(besoin) {
  const nom = [besoin.manager_prenom, besoin.manager_nom].filter(Boolean).join(' ')
  return nom || '-'
}

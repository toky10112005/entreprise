export function afficher(value) {
  return value === null || value === undefined || value === '' ? '-' : value
}

export function formaterDate(date) {
  if (!date) {
    return '-'
  }

  const dateFormatee = new Date(String(date).replace(' ', 'T'))
  if (Number.isNaN(dateFormatee.getTime())) {
    return date
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(dateFormatee)
}
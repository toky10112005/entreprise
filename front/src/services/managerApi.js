const API_BASE_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8081'

async function request(path, options = {}) {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      ...(options.body ? { 'Content-Type': 'application/json' } : {}),
      ...options.headers,
    },
    ...options,
  })

  const payload = await response.json().catch(() => ({}))

  if (!response.ok) {
    const messages = payload.messages
    const message = typeof messages === 'string'
      ? messages
      : messages && typeof messages === 'object'
        ? Object.values(messages).join(' ')
        : payload.error ?? payload.message ?? 'Une erreur est survenue.'

    throw new Error(message)
  }

  return payload
}

export function getBesoins(statut = '') {
  const query = statut ? `?statut=${encodeURIComponent(statut)}` : ''
  return request(`/manager/besoins${query}`)
}

export function getBesoin(id) {
  return request(`/manager/besoins/${encodeURIComponent(id)}`)
}

export function getFormulaireData() {
  return request('/manager/formulaire')
}

export function createBesoin(data) {
  return request('/manager/besoins', {
    method: 'POST',
    body: JSON.stringify(data),
  })
}

export function envoyerBesoin(id) {
  return request(`/manager/besoins/${encodeURIComponent(id)}/envoyer`, {
    method: 'POST',
  })
}

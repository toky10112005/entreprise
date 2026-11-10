const API_BASE_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8081/rh'
const CANDIDATURE_API_URL = import.meta.env.VITE_CANDIDATURE_API_URL ?? 'http://localhost:8081/candidature/postuler'

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

export function getListOffre() {
  return request('/offres')
}

export function getDetailsOffre(idOffre) {
  return request(`/offres/${encodeURIComponent(idOffre)}`)
}

export async function postulerOffre(formData) {
  const response = await fetch(CANDIDATURE_API_URL, {
    method: 'POST',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
    },
    body: formData,
  })

  const payload = await response.json().catch(() => ({}))

  if (!response.ok) {
    const messages = payload.messages
    const message = typeof messages === 'string'
      ? messages
      : messages && typeof messages === 'object'
        ? Object.values(messages).join(' ')
        : payload.error ?? payload.message ?? 'Impossible d’envoyer la candidature.'

    throw new Error(message)
  }

  return payload
}
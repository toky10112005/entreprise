import { ref, computed } from 'vue'

const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
const token = ref(localStorage.getItem('token') || null)

const API_URL = '/api';

export function useAuthStore() {
  const login = async (email, motDePasse) => {
    const response = await fetch(`${API_URL}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email, mot_de_passe: motDePasse }),
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.error || 'Erreur de connexion')
    }

    user.value = data.user
    token.value = data.token
    localStorage.setItem('user', JSON.stringify(data.user))
    localStorage.setItem('token', data.token)

    return data.user
  }

  const register = async (nom, prenom, email, motDePasse, departmentId, telephone = '') => {
    const response = await fetch(`${API_URL}/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ 
        nom,
        prenom,
        email,
        mot_de_passe: motDePasse,
        departement_id: departmentId,
        telephone,
      }),
    }) 

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.error || 'Erreur d’inscription')
    }

    user.value = data.user
    token.value = data.token || null

    if (data.user) {
      localStorage.setItem('user', JSON.stringify(data.user))
    }
    if (data.token) {
      localStorage.setItem('token', data.token)
    }

    return data.user
  }

  const logout = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => user.value ? user.value.role : null)

  return {
    user,
    token,
    login,
    register,
    logout,
    isAuthenticated,
    userRole,
  }
}
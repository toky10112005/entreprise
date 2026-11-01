
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/AuthStore'

const router = useRouter()
const authStore = useAuthStore()

const isLoginTab = ref(true)
const showPassword = ref(false)
const showRegisterPassword = ref(false)

const email = ref('')
const password = ref('')

const firstName = ref('')
const lastName = ref('')
const departmentId = ref('')
const telephone = ref('')
const registerEmail = ref('')
const registerPassword = ref('')

const error = ref('')
const registerError = ref('')
const isLoading = ref(false)

const validateEmail = (value) =>
  /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)

const validatePhone = (value) =>
  /^\+?[0-9\s.-]{8,15}$/.test(value)

const redirectByRole = (role) => {
  const destinations = {
    manager: 'manager-dashboard',
    rh: 'rh-dashboard',
    finance: 'finance-dashboard',
    direction: 'direction-dashboard',
    candidat: 'candidat-dashboard',
  }

  const destination = destinations[role]

  if (destination) {
    router.push({ name: destination })
  } else {
    router.push({ name: 'login' })
  }
}

const handleLogin = async () => {
  error.value = ''

  if (!validateEmail(email.value.trim())) {
    error.value = 'Veuillez entrer une adresse email valide.'
    return
  }

  if (!password.value || password.value.length < 6) {
    error.value = 'Le mot de passe doit contenir au moins 6 caractères.'
    return
  }

  isLoading.value = true

  try {
    const user = await authStore.login(
      email.value.trim(),
      password.value,
    )

    redirectByRole(user.role)
  } catch (err) {
    error.value = err.message || 'Erreur de connexion.'
  } finally {
    isLoading.value = false
  }
}

const handleRegister = async () => {
  registerError.value = ''

  if (!firstName.value.trim() || !lastName.value.trim()) {
    registerError.value = 'Le prénom et le nom sont obligatoires.'
    return
  }

  if (!departmentId.value) {
    registerError.value = 'Veuillez choisir un département.'
    return
  }

  if (!validatePhone(telephone.value)) {
    registerError.value = 'Veuillez saisir un numéro de téléphone valide.'
    return
  }

  if (!validateEmail(registerEmail.value.trim())) {
    registerError.value = 'Veuillez entrer une adresse email valide.'
    return
  }

  if (!registerPassword.value || registerPassword.value.length < 6) {
    registerError.value = 'Le mot de passe doit contenir au moins 6 caractères.'
    return
  }

  isLoading.value = true

  try {
    const user = await authStore.register(
      lastName.value.trim(),
      firstName.value.trim(),
      registerEmail.value.trim(),
      registerPassword.value,
      Number(departmentId.value),
      telephone.value.trim(),
    )

    redirectByRole(user.role)
  } catch (err) {
    registerError.value = err.message || 'Erreur d’inscription.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="bg-body-tertiary min-vh-100 d-flex align-items-center py-4">
    <CContainer>
      <CRow class="justify-content-center">
        <CCol :xs="12" :sm="10" :md="8" :lg="6" :xl="5">
          <CCard class="shadow-sm border-0">
            <CCardBody class="p-4 p-md-5">

              <div class="text-center mb-4">
                <h1 class="h3 fw-bold mb-2">
                  Gestion RH
                </h1>
                <p class="text-body-secondary mb-0">
                  Bienvenue sur votre espace professionnel
                </p>
              </div>

              <CRow class="g-2 mb-4">
                <CCol :xs="6">
                  <CButton
                    type="button"
                    class="w-100"
                    :color="isLoginTab ? 'primary' : 'secondary'"
                    :variant="isLoginTab ? undefined : 'outline'"
                    @click="isLoginTab = true; error = ''"
                  >
                    Connexion
                  </CButton>
                </CCol>

                <CCol :xs="6">
                  <CButton
                    type="button"
                    class="w-100"
                    :color="!isLoginTab ? 'primary' : 'secondary'"
                    :variant="!isLoginTab ? undefined : 'outline'"
                    @click="isLoginTab = false; registerError = ''"
                  >
                    Créer un compte
                  </CButton>
                </CCol>
              </CRow>

              <template v-if="isLoginTab">
                <h2 class="h5 fw-semibold mb-4">
                  Connectez-vous à votre compte
                </h2>

                <CForm @submit.prevent="handleLogin" novalidate>
                  <div class="mb-3">
                    <CFormLabel for="loginEmail">
                      Adresse email
                    </CFormLabel>
                    <CFormInput
                      id="loginEmail"
                      v-model="email"
                      type="email"
                      placeholder="vous@exemple.com"
                      autocomplete="email"
                      required
                    />
                  </div>

                  <div class="mb-3">
                    <CFormLabel for="loginPassword">
                      Mot de passe
                    </CFormLabel>

                    <CInputGroup>
                      <CFormInput
                        id="loginPassword"
                        v-model="password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Votre mot de passe"
                        autocomplete="current-password"
                        required
                      />
                      <CButton
                        type="button"
                        color="secondary"
                        variant="outline"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                      >
                        {{ showPassword ? 'Masquer' : 'Afficher' }}
                      </CButton>
                    </CInputGroup>
                  </div>

                  <CAlert v-if="error" color="danger" class="mb-3">
                    {{ error }}
                  </CAlert>

                  <CButton
                    type="submit"
                    color="primary"
                    class="w-100 mt-2"
                    :disabled="isLoading"
                  >
                    {{ isLoading ? 'Connexion en cours...' : 'Se connecter' }}
                  </CButton>
                </CForm>
              </template>

              <template v-else>
                <h2 class="h5 fw-semibold mb-4">
                  Inscription candidat
                </h2>

                <CForm @submit.prevent="handleRegister" novalidate>
                  <CRow>
                    <CCol :md="6" class="mb-3">
                      <CFormLabel for="firstName">Prénom</CFormLabel>
                      <CFormInput
                        id="firstName"
                        v-model="firstName"
                        autocomplete="given-name"
                        required
                      />
                    </CCol>

                    <CCol :md="6" class="mb-3">
                      <CFormLabel for="lastName">Nom</CFormLabel>
                      <CFormInput
                        id="lastName"
                        v-model="lastName"
                        autocomplete="family-name"
                        required
                      />
                    </CCol>
                  </CRow>

                  <div class="mb-3">
                    <CFormLabel for="department">
                      Département souhaité
                    </CFormLabel>

                    <select
                      id="department"
                      v-model="departmentId"
                      class="form-select"
                      required
                    >
                      <option value="">Choisir un département</option>
                      <option value="1">Informatique</option>
                      <option value="2">Finance</option>
                      <option value="3">RH</option>
                      <option value="4">Marketing</option>
                      <option value="5">Administration</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <CFormLabel for="telephone">
                      Numéro de téléphone
                    </CFormLabel>
                    <CFormInput
                      id="telephone"
                      v-model="telephone"
                      type="tel"
                      autocomplete="tel"
                      placeholder="0601020304"
                      required
                    />
                  </div>

                  <div class="mb-3">
                    <CFormLabel for="registerEmail">
                      Adresse email
                    </CFormLabel>
                    <CFormInput
                      id="registerEmail"
                      v-model="registerEmail"
                      type="email"
                      autocomplete="email"
                      placeholder="vous@exemple.com"
                      required
                    />
                  </div>

                  <div class="mb-3">
                    <CFormLabel for="registerPassword">
                      Mot de passe
                    </CFormLabel>

                    <CInputGroup>
                      <CFormInput
                        id="registerPassword"
                        v-model="registerPassword"
                        :type="showRegisterPassword ? 'text' : 'password'"
                        autocomplete="new-password"
                        required
                      />
                      <CButton
                        type="button"
                        color="secondary"
                        variant="outline"
                        @click="showRegisterPassword = !showRegisterPassword"
                      >
                        {{ showRegisterPassword ? 'Masquer' : 'Afficher' }}
                      </CButton>
                    </CInputGroup>
                  </div>

                  <CAlert
                    v-if="registerError"
                    color="danger"
                    class="mb-3"
                  >
                    {{ registerError }}
                  </CAlert>

                  <CButton
                    type="submit"
                    color="primary"
                    class="w-100 mt-2"
                    :disabled="isLoading"
                  >
                    {{ isLoading ? 'Création en cours...' : 'Créer mon compte' }}
                  </CButton>
                </CForm>
              </template>

              <div class="text-center mt-4">
                <small class="text-body-secondary">
                  Application de gestion d'entreprise
                </small>
              </div>

            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
    </CContainer>
  </div>
</template>

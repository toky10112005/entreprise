<script setup>
import { onMounted, ref } from 'vue'
import { BesoinToOffre, getBesoinValides } from '@/services/rhApi'
import { nomManager, formaterDate } from '@/utils/format'
const besoins = ref([])
const loading = ref(false)
const error = ref('')
const annonceEnCours = ref(null)
const annonceCreee = ref(null)


async function chargerBesoins() {
  loading.value = true
  error.value = ''

  try {
    const response = await getBesoinValides()
    besoins.value = Array.isArray(response.besoins) ? response.besoins : []
  } catch (exception) {
    error.value = exception instanceof Error
      ? exception.message
      : 'Impossible de charger les besoins valides.'
  } finally {
    loading.value = false
  }
}

async function creerAnnonce(besoin) {
  annonceEnCours.value = besoin.id
  error.value = ''
  annonceCreee.value = null

  try {
    await BesoinToOffre(besoin.id)
    annonceCreee.value = besoin.id
  } catch (exception) {
    error.value = exception instanceof Error
      ? exception.message
      : 'Impossible de créer l’annonce.'
  } finally {
    annonceEnCours.value = null
  }
}

onMounted(chargerBesoins)
</script>

<template>
  <section>
    <header>
      <h1>Besoins valides</h1>
      <button type="button" :disabled="loading" @click="chargerBesoins">
        {{ loading ? 'Actualisation...' : 'Actualiser' }}
      </button>
    </header>

    <p v-if="loading" role="status">Chargement des besoins valides...</p>
    <p v-else-if="error" role="alert">
      {{ error }}
      <button type="button" @click="chargerBesoins">Réessayer</button>
    </p>
    <p v-else-if="besoins.length === 0">Aucun besoin valide pour le moment.</p>

    <table v-else>
      <caption>Liste des besoins dont le statut est valide</caption>
      <thead>
        <tr>
          <th scope="col">ID</th>
          <th scope="col">Manager</th>
          <th scope="col">Date de soumission</th>
          <th scope="col">Statut</th>
          <th scope="col">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="besoin in besoins" :key="besoin.id">
          <td>{{ besoin.id }}</td>
          <td>{{ nomManager(besoin) }}</td>
          <td>{{ formaterDate(besoin.date_soumission_demande) }}</td>
          <td>{{ besoin.statut ?? 'Valide' }}</td>
          <td>
            <button
              type="button"
              :disabled="annonceEnCours === besoin.id || annonceCreee === besoin.id"
              @click="creerAnnonce(besoin)"
            >
              {{
                annonceEnCours === besoin.id
                  ? 'Création...'
                  : annonceCreee === besoin.id
                    ? 'Annonce créée'
                    : 'Créer une annonce'
              }}
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </section>
</template>
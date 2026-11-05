<script setup>
import { onMounted, ref } from 'vue'
import { getBesoinValides } from '@/services/rhApi'

const besoins = ref([])
const loading = ref(false)
const error = ref('')

function formaterDate(date) {
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

function nomManager(besoin) {
  const nom = [besoin.manager_prenom, besoin.manager_nom].filter(Boolean).join(' ')
  return nom || '-'
}

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
        </tr>
      </thead>
      <tbody>
        <tr v-for="besoin in besoins" :key="besoin.id">
          <td>{{ besoin.id }}</td>
          <td>{{ nomManager(besoin) }}</td>
          <td>{{ formaterDate(besoin.date_soumission_demande) }}</td>
          <td>{{ besoin.statut ?? 'Valide' }}</td>
        </tr>
      </tbody>
    </table>
  </section>
</template>
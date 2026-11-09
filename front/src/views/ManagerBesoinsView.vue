<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { getBesoins } from '@/services/managerApi'

const besoins = ref([])
const statut = ref('')
const loading = ref(false)
const error = ref('')

async function chargerBesoins() {
  loading.value = true
  error.value = ''

  try {
    const response = await getBesoins(statut.value)
    besoins.value = response.besoins ?? []
  } catch (exception) {
    error.value = exception.message
    console.error(error);
  } finally {
    loading.value = false
  }
}

onMounted(chargerBesoins)
</script>

<template>
  <section>
    <header>
      <h1>Mes besoins</h1>
      <RouterLink to="/manager/formulaire">Créer un besoin</RouterLink>
    </header>

    <form @submit.prevent="chargerBesoins">
      <label for="statut">Filtrer par statut</label>
      <input id="statut" v-model="statut" type="text" placeholder="Ex. Soumise">
      <button type="submit">Rechercher</button>
      <button type="button" @click="statut = ''; chargerBesoins()">Réinitialiser</button>
    </form>

    <p v-if="loading">Chargement...</p>
    <p v-else-if="error" role="alert">{{ error }}</p>
    <p v-else-if="besoins.length === 0">Aucun besoin trouvé.</p>

    <table v-else>
      <thead>
        <tr>
          <th>ID</th>
          <th>Date de soumission</th>
          <th>Statut</th>
          <th>Département</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="besoin in besoins" :key="besoin.id">
          <td>{{ besoin.id }}</td>
          <td>{{ besoin.date_soumission_demande }}</td>
          <td>{{ besoin.statut }}</td>
          <td>{{ besoin.departement ?? besoin.departement_libelle ?? '-' }}</td>
          <td>
            <RouterLink :to="`/manager/besoins/${besoin.id}`">Voir le détail</RouterLink>
          </td>
        </tr>
      </tbody>
    </table>
  </section>
</template>

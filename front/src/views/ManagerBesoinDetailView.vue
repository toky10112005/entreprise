<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { getBesoin } from '@/services/managerApi'

const route = useRoute()
const besoin = ref(null)
const loading = ref(false)
const error = ref('')

async function chargerBesoin() {
  loading.value = true
  error.value = ''

  try {
    const response = await getBesoin(route.params.id)
    besoin.value = response.besoin
  } catch (exception) {
    error.value = exception.message
  } finally {
    loading.value = false
  }
}

onMounted(chargerBesoin)
</script>

<template>
  <section>
    <RouterLink to="/manager/besoins">Retour à mes besoins</RouterLink>
    <h1>Détail du besoin</h1>

    <p v-if="loading">Chargement...</p>
    <p v-else-if="error" role="alert">{{ error }}</p>

    <template v-else-if="besoin">
      <dl>
        <dt>ID</dt>
        <dd>{{ besoin.id }}</dd>
        <dt>Manager</dt>
        <dd>{{ besoin.manager_nom }} {{ besoin.manager_prenom }}</dd>
        <dt>Statut</dt>
        <dd>{{ besoin.statut }}</dd>
        <dt>Date de soumission</dt>
        <dd>{{ besoin.date_soumission_demande }}</dd>
      </dl>

      <h2>Détails</h2>
      <ul v-if="besoin.details?.length">
        <li v-for="detail in besoin.details" :key="detail.id">
          Poste : {{ detail.poste_libelle ?? detail.poste_id }},
          effectif : {{ detail.effectif }},
          motif : {{ detail.motif_libelle ?? detail.motif_recrutement_id }},
          budget : {{ detail.budget ?? '-' }}
        </li>
      </ul>
      <p v-else>Aucun détail disponible.</p>

      <h2>Historique</h2>
      <ul v-if="besoin.historique?.length">
        <li v-for="ligne in besoin.historique" :key="ligne.id">
          {{ ligne.date_insertion }} - {{ ligne.statut_libelle ?? ligne.statut_id }}
          <span v-if="ligne.commentaire"> : {{ ligne.commentaire }}</span>
        </li>
      </ul>
      <p v-else>Aucun historique disponible.</p>
    </template>
  </section>
</template>

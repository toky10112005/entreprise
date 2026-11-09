<script setup>
import { onMounted, ref } from 'vue'
import { getDetailsOffre, getListOffre } from '@/services/api'
import {afficher,formaterDate} from '@/utils/format'
const offres = ref([])
const loading = ref(false)
const error = ref('')

async function chargerOffres() {
  loading.value = true
  error.value = ''

  try {
    const response = await getListOffre()
    const liste = Array.isArray(response.offres) ? response.offres : []
    offres.value = await Promise.all(
      liste.map(async (offre) => {
        const details = await getDetailsOffre(offre.id_offre)
        return details.offre ?? offre
      }),
    )
  } catch (exception) {
    offres.value = []
    error.value = exception instanceof Error
      ? exception.message
      : 'Impossible de charger les offres d’emploi.'
  } finally {
    loading.value = false
  }
}

onMounted(chargerOffres)
</script>

<template>
  <main>
    <header>
      <p>Opportunités professionnelles</p>
      <h1>Nos offres d’emploi</h1>
      <button type="button" :disabled="loading" @click="chargerOffres">
        {{ loading ? 'Actualisation...' : 'Actualiser' }}
      </button>
    </header>

    <p v-if="loading" role="status">Chargement des offres...</p>
    <section v-else-if="error" role="alert">
      <p>{{ error }}</p>
      <button type="button" @click="chargerOffres">Réessayer</button>
    </section>
    <p v-else-if="offres.length === 0">Aucune offre d’emploi disponible.</p>

    <section v-else aria-label="Liste des offres d’emploi">
      <article v-for="offre in offres" :key="offre.id_offre">
        <header>
          <p>Offre #{{ offre.id_offre }}</p>
          <h2>{{ offre.poste_libelle }}</h2>
        </header>

        <dl>
        <dt>Salaire propose:</dt>
        <dd>{{ offre.salaire_propose }}</dd>
        <dt>Mode de travail:</dt>
        <dd>{{ offre.mode_de_travail }}</dd>
        <dt>formation requise : </dt>
        <dd>{{ offre.formation }}</dd>
        <dt>Competences requises :</dt>
        <dd>{{ offre.competences_techniques }}</dd>
        <dt>Contact:</dt>
        <dd>{{ offre.rh_email }}</dd>
        </dl>
      </article>
    </section>
  </main>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { getDetailsOffre, getListOffre } from '@/services/api'
import {afficher,titreOffre,formaterDate,detailsPoste} from '@/utils/format'
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
          <h2>{{ titreOffre(offre) }}</h2>
          <p>{{ afficher(detailsPoste(offre)?.departement) }}</p>
        </header>

        <dl>
          <dt>Statut</dt>
          <dd>{{ afficher(offre.statut_offre) }}</dd>
          <dt>Date de publication</dt>
          <dd>{{ formaterDate(offre.date_insertion) }}</dd>
          <dt>Responsable RH</dt>
          <dd>{{ afficher(`${offre.rh_prenom ?? ''} ${offre.rh_nom ?? ''}`.trim()) }}</dd>
          <dt>Effectif recherché</dt>
          <dd>{{ afficher(detailsPoste(offre)?.effectif) }}</dd>
          <dt>Motif</dt>
          <dd>{{ afficher(detailsPoste(offre)?.motif) }}</dd>
          <dt>Date souhaitée</dt>
          <dd>{{ afficher(detailsPoste(offre)?.date_delai_recrutement) }}</dd>
          <dt>Budget</dt>
          <dd>{{ afficher(detailsPoste(offre)?.budget) }}</dd>
          <dt>Description</dt>
          <dd>{{ afficher(detailsPoste(offre)?.commentaire) }}</dd>
        </dl>
      </article>
    </section>
  </main>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { getDetailsOffre, getListOffre, postulerOffre } from '@/services/api'
import {afficher,formaterDate} from '@/utils/format'
const offres = ref([])
const loading = ref(false)
const error = ref('')
const candidatures = ref({})

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

function etatCandidature(idOffre) {
  return candidatures.value[idOffre] ?? { loading: false, error: '', success: '', visible: false }
}

function basculerFormulaire(idOffre) {
  const etat = etatCandidature(idOffre)
  candidatures.value[idOffre] = { ...etat, visible: !etat.visible }
}

async function soumettreCandidature(event, idOffre) {
  candidatures.value[idOffre] = { ...etatCandidature(idOffre), loading: true, error: '', success: '' }

  const formulaire = event.currentTarget
  const donnees = new FormData(formulaire)
  donnees.set('offre_id', idOffre)

  try {
    const response = await postulerOffre(donnees)
    candidatures.value[idOffre] = {
      loading: false,
      error: '',
      success: response.message ?? 'Candidature envoyée avec succès.',
      visible: true,
    }
    formulaire.reset()
  } catch (exception) {
    candidatures.value[idOffre] = {
      loading: false,
      error: exception instanceof Error
        ? exception.message
        : 'Impossible d’envoyer la candidature.',
      success: '',
      visible: true,
    }
  }
}
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

        <button type="button" @click="basculerFormulaire(offre.id_offre)">
          Postuler
        </button>

        <form
          v-if="etatCandidature(offre.id_offre).visible"
          enctype="multipart/form-data"
          @submit.prevent="soumettreCandidature($event, offre.id_offre)"
        >
          <h3>Déposer votre candidature</h3>
          <label>
            Prénom
            <input name="prenom" type="text" required>
          </label>
          <label>
            Nom
            <input name="nom" type="text" required>
          </label>
          <label>
            Adresse e-mail
            <input name="email" type="email" required>
          </label>
          <label>
            CV
            <input name="cv" type="file" required>
          </label>
          <label>
            Lettre de motivation (facultatif)
            <input name="lettre_motivation" type="file">
          </label>
          <button type="submit" :disabled="etatCandidature(offre.id_offre).loading">
            {{ etatCandidature(offre.id_offre).loading ? 'Envoi en cours...' : 'Envoyer ma candidature' }}
          </button>
          <p v-if="etatCandidature(offre.id_offre).error" role="alert">
            {{ etatCandidature(offre.id_offre).error }}
          </p>
          <p v-if="etatCandidature(offre.id_offre).success" role="status">
            {{ etatCandidature(offre.id_offre).success }}
          </p>
        </form>
      </article>
    </section>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { createBesoin, getFormulaireData } from '@/services/managerApi'

const router = useRouter()
const postes = ref([])
const motifs = ref([])
const departementId = ref(null)
const loading = ref(true)
const submitting = ref(false)
const error = ref('')

const form = reactive({
  poste_id: '',
  effectif: 1,
  motif_recrutement_id: '',
  date_delai_recrutement: '',
  budget: '',
  commentaire: '',
})

async function chargerDonnees() {
  try {
    const response = await getFormulaireData()
    departementId.value = response.departement_id
    postes.value = response.postes ?? []
    motifs.value = response.motifs ?? []
  } catch (exception) {
    error.value = exception.message
  } finally {
    loading.value = false
  }
}

async function soumettre() {
  submitting.value = true
  error.value = ''

  const data = {
    ...form,
    poste_id: Number(form.poste_id),
    effectif: Number(form.effectif),
    motif_recrutement_id: Number(form.motif_recrutement_id),
  }

  if (data.date_delai_recrutement === '') delete data.date_delai_recrutement
  if (data.budget === '') delete data.budget
  if (data.commentaire === '') delete data.commentaire

  try {
    const response = await createBesoin(data)
    await router.push(`/manager/besoins/${response.id}`)
  } catch (exception) {
    error.value = exception.message
  } finally {
    submitting.value = false
  }
}

onMounted(chargerDonnees)
</script>

<template>
  <section>
    <h1>Nouveau besoin</h1>
    <p><RouterLink to="/manager/besoins">Retour à mes besoins</RouterLink></p>

    <p v-if="loading">Chargement du formulaire...</p>
    <p v-if="error" role="alert">{{ error }}</p>

    <form v-if="!loading" @submit.prevent="soumettre">
      <p>Département : {{ departementId ?? '-' }}</p>

      <label for="poste">Poste</label>
      <select id="poste" v-model="form.poste_id" required>
        <option value="" disabled>Choisir un poste</option>
        <option v-for="poste in postes" :key="poste.id" :value="poste.id">
          {{ poste.libelle }}
        </option>
      </select>

      <label for="effectif">Effectif</label>
      <input id="effectif" v-model.number="form.effectif" type="number" min="1" required>

      <label for="motif">Motif</label>
      <select id="motif" v-model="form.motif_recrutement_id" required>
        <option value="" disabled>Choisir un motif</option>
        <option v-for="motif in motifs" :key="motif.id" :value="motif.id">
          {{ motif.libelle }}
        </option>
      </select>

      <label for="date">Date limite</label>
      <input id="date" v-model="form.date_delai_recrutement" type="date">

      <label for="budget">Budget</label>
      <input id="budget" v-model="form.budget" type="number" min="0" step="0.01">

      <label for="commentaire">Commentaire</label>
      <textarea id="commentaire" v-model="form.commentaire" rows="4"></textarea>

      <button type="submit" :disabled="submitting">
        {{ submitting ? 'Enregistrement...' : 'Enregistrer en brouillon' }}
      </button>
    </form>
  </section>
</template>

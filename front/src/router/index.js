import { createRouter, createWebHistory } from 'vue-router'
import ManagerBesoinsView from '@/views/ManagerBesoinsView.vue'
import ManagerBesoinDetailView from '@/views/ManagerBesoinDetailView.vue'
import ManagerFormulaireView from '@/views/ManagerFormulaireView.vue'
import BesoinsValidesView from '@/views/Rh/BesoinsValidesView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/manager/besoins',
    },
    {
      path: '/manager/besoins',
      name: 'manager-besoins',
      component: ManagerBesoinsView,
    },
    {
      path: '/manager/besoins/:id',
      name: 'manager-besoin-detail',
      component: ManagerBesoinDetailView,
    },
    {
      path: '/manager/formulaire',
      name: 'manager-formulaire',
      component: ManagerFormulaireView,
    },
    {
      path: '/rh/besoins-valides',
      name: 'rh-besoins-valides',
      component: BesoinsValidesView,
    },
  ],
})

export default router

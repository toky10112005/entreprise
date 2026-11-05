import { createRouter, createWebHistory } from 'vue-router'
import ManagerBesoinsView from '@/views/ManagerBesoinsView.vue'
import ManagerBesoinDetailView from '@/views/ManagerBesoinDetailView.vue'
import ManagerFormulaireView from '@/views/ManagerFormulaireView.vue'

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
  ],
})

export default router

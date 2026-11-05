import { createRouter, createWebHistory } from 'vue-router'
import OffreDetailsView from '@/views/OffreDetailsView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/offres',
    },
    {
      path: '/offres',
      name: 'offres',
      component: OffreDetailsView,
    },
  ],
})

export default router

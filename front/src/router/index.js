
import { createRouter, createWebHistory } from 'vue-router'
import DefaultLayout from '../layouts/DefaultLayout.vue'
import LoginView from '../view/LoginView.vue'
import testconnex from '../view/testconnex.vue'

const routes = [
  {
    path: '/',
    redirect: '/login',
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
  },
  {
    path: '/',
    component: DefaultLayout,
    children: [
      {
        path: 'rh-dashboard',
        name: 'rh-dashboard',
        component: testconnex,
      },
      {
        path: 'manager-dashboard',
        name: 'manager-dashboard',
        component: testconnex,
      },
      {
        path: 'nouvelle',
        name: 'nouvelle',
        component: testconnex,
      },
      {
        path: 'finance-dashboard',
        name: 'finance-dashboard',
        component: testconnex
      },
      {
        path: 'direction-dashboard',
        name: 'direction-dashboard',
        component: testconnex,
      },
      {
        path: 'candidat-dashboard',
        name: 'candidat-dashboard',
        component: testconnex,
      },
      {
        path: 'candidatures',
        name: 'candidatures',
        component: testconnex,
      },
    ],
  },
]

export default createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})


import { createApp } from 'vue'
import { createPinia } from 'pinia'
import CoreuiVue from '@coreui/vue'
import CIcon from '@coreui/icons-vue'

import App from './App.vue'
import router from './router'
import { iconsSet } from './assets/icons'

import './styles/style.scss'
import 'simplebar-vue/dist/simplebar.min.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(CoreuiVue)
app.component('CIcon', CIcon)
app.provide('icons', iconsSet)

app.mount('#app')

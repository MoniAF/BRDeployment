import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'
import './assets/scss/main.scss'
import SaveRecipeButton from './components/SaveRecipeButton.vue'
import LikeRecipeButton from './components/LikeRecipeButton.vue'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.component('SaveRecipeButton', SaveRecipeButton)
app.component('LikeRecipeButton', LikeRecipeButton)

app.mount('#app')

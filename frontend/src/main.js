import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { showFlash } from './utils/flash'

window.showFlash = showFlash

const app = createApp(App)
app.config.globalProperties.$showFlash = showFlash
app.use(router)
app.mount('#app')
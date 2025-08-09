import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './css/app.css'
import MessageContainer from './components/MessageContainer.vue'

const app = createApp(App);
app.component('MessageContainer', MessageContainer);
app.use(router);
app.mount('#app');
import './bootstrap'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './components/App.vue'

import { clerkPlugin } from '@clerk/vue'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(clerkPlugin, {
  publishableKey: import.meta.env.VITE_CLERK_PUBLISHABLE_KEY,
  localization: {
    signIn: {
      start: {
        title: 'Welcome to Alvo',
        subtitle: 'Sign in to manage your finances',
      }
    },
    signUp: {
      start: {
        title: 'Create your account',
        subtitle: 'Join Alvo to get started',
      }
    }
  }
})

app.mount('#app')

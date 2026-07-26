import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';

import { clerkPlugin } from '@clerk/vue';

const app = createApp(App);

app.use(clerkPlugin, {
  publishableKey: import.meta.env.VITE_CLERK_PUBLISHABLE_KEY,
});

app.mount('#app');

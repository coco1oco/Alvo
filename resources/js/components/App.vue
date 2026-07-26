<template>
  <div id="alvo-app" :class="isDark ? 'dark' : ''">
    <div class="min-h-screen font-sans antialiased transition-colors duration-200 app-bg">

      <!-- Auth Screen -->
      <AuthView v-if="!isLoaded || !isSignedIn" />

      <!-- Main App Shell -->
      <div v-else class="flex h-screen overflow-hidden">

        <!-- ── Sidebar ─────────────────────────────────────────── -->
        <aside class="w-60 flex flex-col flex-shrink-0 transition-colors duration-200 app-sidebar">

          <!-- Logo -->
          <div class="p-5 flex items-center gap-3 sidebar-header">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0 app-logo">
              <!-- Alvo logo mark: target/crosshair -->
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <h1 class="text-sm font-bold tracking-tight text-primary-color">Alvo</h1>
              <p class="text-xs text-muted-color">Finance Manager</p>
            </div>
          </div>

          <!-- Nav -->
          <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
            <button
              v-for="item in navItems" :key="item.view"
              @click="currentView = item.view"
              class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
              :style="currentView === item.view ? activeNavStyle : inactiveNavStyle"
            >
              <!-- Active indicator bar -->
              <span v-if="currentView === item.view"
                    class="absolute left-3 w-0.5 h-4 rounded-full nav-indicator">
              </span>
              <component :is="item.icon" class="w-[18px] h-[18px] flex-shrink-0" />
              {{ item.label }}
            </button>
          </nav>

          <!-- Sidebar Footer: Theme Toggle + User + Logout -->
          <div class="p-3 space-y-1 sidebar-footer">
            <!-- User Info Row -->
            <div class="flex items-center gap-2.5 px-2 py-2 rounded-xl text-primary-color">
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0 user-avatar">
                {{ user?.firstName?.charAt(0) || user?.primaryEmailAddress?.emailAddress?.charAt(0).toUpperCase() || 'A' }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold truncate text-primary-color">{{ user?.fullName || 'User' }}</p>
                <p class="text-xs truncate text-muted-color">{{ user?.primaryEmailAddress?.emailAddress }}</p>
              </div>
              <!-- Theme Toggle -->
              <button
                @click="toggleTheme"
                :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                class="w-7 h-7 rounded-lg flex items-center justify-center transition-all text-secondary-color theme-toggle"
              >
                <!-- Sun icon (shown in dark mode) -->
                <svg v-if="isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
                <!-- Moon icon (shown in light mode) -->
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
              </button>
            </div>

            <!-- Sign Out -->
            <button @click="logout"
              class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition-all text-secondary-color logout-btn"
            >
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Sign Out
            </button>
          </div>
        </aside>

        <!-- ── Main Content ───────────────────────────────────── -->
        <main class="flex-1 overflow-y-auto transition-colors duration-200 app-main">
          <Transition name="page" mode="out-in">
            <DashboardView   v-if="currentView === 'dashboard'"    :key="'dashboard-'   + refreshKey" :is-dark="isDark" />
            <TransactionsView v-else-if="currentView === 'transactions'" :key="'transactions-' + refreshKey" @refresh="refresh" />
            <AccountsView    v-else-if="currentView === 'accounts'"    :key="'accounts-'    + refreshKey" @refresh="refresh" />
            <CategoriesView  v-else-if="currentView === 'categories'"  :key="'categories-'  + refreshKey" />
            <BudgetsView     v-else-if="currentView === 'budgets'"     :key="'budgets-'     + refreshKey" />
          </Transition>
        </main>
      </div>

      <!-- ── Toast Notifications ───────────────────────────────── -->
      <transition-group name="toast" tag="div" class="fixed bottom-6 right-6 z-50 space-y-2">
        <div
          v-for="toast in toasts" :key="toast.id"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium border toast-item"
          :style="toast.type === 'success'
            ? { background: 'var(--success-light)', borderColor: 'var(--success)', color: 'var(--success)' }
            : { background: 'var(--danger-light)',  borderColor: 'var(--danger)',  color: 'var(--danger)' }"
        >
          <!-- Success icon -->
          <svg v-if="toast.type === 'success'" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
          <!-- Error icon -->
          <svg v-else class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
          {{ toast.message }}
        </div>
      </transition-group>
    </div>
  </div>
</template>

<script setup>
import { ref, provide, onMounted, watch, defineComponent, h, onUnmounted } from 'vue'
import axios from 'axios'
import { useAuth, useUser } from '@clerk/vue'
import AuthView from './AuthView.vue'
import DashboardView from './DashboardView.vue'
import TransactionsView from './TransactionsView.vue'
import AccountsView from './AccountsView.vue'
import CategoriesView from './CategoriesView.vue'
import BudgetsView from './BudgetsView.vue'

// ── Inline SVG Icon Components ────────────────────────────────
const IconDashboard = defineComponent({
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' })
  ])
})

const IconTransactions = defineComponent({
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4' })
  ])
})

const IconAccounts = defineComponent({
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' })
  ])
})

const IconCategories = defineComponent({
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z' })
  ])
})

const IconBudgets = defineComponent({
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
  ])
})

// ── State ─────────────────────────────────────────────────────
const currentView = ref('dashboard')
const toasts      = ref([])
const refreshKey  = ref(0)

// ── Clerk Auth ────────────────────────────────────────────────
const { isLoaded, isSignedIn, signOut, getToken } = useAuth()
const { user } = useUser()

// Axios Interceptor for Clerk JWT
const axiosInterceptor = axios.interceptors.request.use(async (config) => {
  if (isSignedIn.value) {
    const token = await getToken.value()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
  }
  return config
})

onUnmounted(() => {
  axios.interceptors.request.eject(axiosInterceptor)
})

// ── Dark Mode ─────────────────────────────────────────────────
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)')
const savedTheme  = localStorage.getItem('alvo-theme')
const isDark      = ref(savedTheme ? savedTheme === 'dark' : prefersDark.matches)

watch(isDark, val => {
  document.documentElement.classList.toggle('dark', val)
  localStorage.setItem('alvo-theme', val ? 'dark' : 'light')
}, { immediate: true })

if (!savedTheme) {
  prefersDark.addEventListener('change', e => { isDark.value = e.matches })
}

function toggleTheme() {
  isDark.value = !isDark.value
}

// ── Nav ───────────────────────────────────────────────────────
const navItems = [
  { view: 'dashboard',    label: 'Dashboard',    icon: IconDashboard },
  { view: 'transactions', label: 'Transactions', icon: IconTransactions },
  { view: 'accounts',     label: 'Accounts',     icon: IconAccounts },
  { view: 'categories',   label: 'Categories',   icon: IconCategories },
  { view: 'budgets',      label: 'Budgets',      icon: IconBudgets },
]

const activeNavStyle = {
  backgroundColor: 'var(--primary-glass)',
  color: 'var(--primary)',
  borderLeft: '2px solid var(--primary)',
  paddingLeft: '0.875rem',
}

const inactiveNavStyle = {
  color: 'var(--text-secondary)',
  borderLeft: '2px solid transparent',
  paddingLeft: '0.875rem',
}

// ── Toasts ────────────────────────────────────────────────────
function showToast(message, type = 'success') {
  const id = Date.now()
  toasts.value.push({ id, message, type })
  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }, 3500)
}

// ── Auth Methods ──────────────────────────────────────────────
function refresh() {
  refreshKey.value++
}

async function logout() {
  await signOut.value()
  currentView.value = 'dashboard'
}

// ── Provide to children ───────────────────────────────────────
provide('toast', showToast)
provide('refresh', refresh)
provide('isDark', isDark)
</script>

<style scoped>
.app-bg { background-color: var(--bg-base); color: var(--text-primary); }
.app-sidebar { background-color: var(--bg-surface); border-right: 1px solid var(--border); }
.sidebar-header { border-bottom: 1px solid var(--border); }
.app-logo { background: linear-gradient(135deg, #1A56DB, #4B8EF8); box-shadow: 0 4px 12px rgba(26,86,219,0.35); }
.text-primary-color { color: var(--text-primary); }
.text-muted-color { color: var(--text-muted); }
.text-secondary-color { color: var(--text-secondary); }
.nav-indicator { background-color: var(--primary); }
.sidebar-footer { border-top: 1px solid var(--border); }
.user-avatar { background: linear-gradient(135deg, #1A56DB, #4B8EF8); }
.theme-toggle:hover { background-color: var(--bg-surface-2); color: var(--text-primary); }
.logout-btn:hover { background-color: var(--danger-light); color: var(--danger); }
.app-main { background-color: var(--bg-base); }
.toast-item { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
</style>

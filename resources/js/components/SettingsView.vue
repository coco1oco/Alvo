<template>
  <div class="view">
    <!-- Header -->
    <div class="view-header">
      <div>
        <h1 class="view-title">Settings &amp; Profile</h1>
        <p class="view-subtitle">Manage your account preferences, theme, and data</p>
      </div>
    </div>

    <div class="settings-grid">
      <!-- ── User Profile Section ──────────────────────────────── -->
      <section class="glass-card settings-section">
        <h2 class="section-title flex items-center gap-2">
          <UserIcon class="w-5 h-5 text-primary" />
          User Profile
        </h2>

        <div class="profile-card">
          <div class="profile-avatar">
            <img v-if="user?.imageUrl" :src="user.imageUrl" alt="User Avatar" class="w-full h-full object-cover rounded-full" />
            <span v-else class="avatar-initials">
              {{ user?.firstName?.charAt(0) || user?.primaryEmailAddress?.emailAddress?.charAt(0).toUpperCase() || 'A' }}
            </span>
          </div>

          <div class="profile-details">
            <h3 class="profile-name">{{ user?.fullName || 'Alvo User' }}</h3>
            <p class="profile-email">{{ user?.primaryEmailAddress?.emailAddress || 'user@example.com' }}</p>
            <span class="badge badge-success mt-2">Verified Account</span>
          </div>
        </div>
      </section>

      <!-- ── Preferences Section ──────────────────────────────── -->
      <section class="glass-card settings-section">
        <h2 class="section-title flex items-center gap-2">
          <AdjustmentsHorizontalIcon class="w-5 h-5 text-primary" />
          App Preferences
        </h2>

        <div class="form-group">
          <label class="label">Default Currency</label>
          <select v-model="preferences.currency" @change="savePreferences" class="input-field">
            <option value="PHP">PHP (₱) - Philippine Peso</option>
            <option value="USD">USD ($) - US Dollar</option>
            <option value="EUR">EUR (€) - Euro</option>
            <option value="GBP">GBP (£) - British Pound</option>
            <option value="JPY">JPY (¥) - Japanese Yen</option>
          </select>
        </div>

        <div class="form-group">
          <label class="label">Default Account</label>
          <select v-model="preferences.defaultAccountId" @change="savePreferences" class="input-field">
            <option value="">No default (Prompt on transaction creation)</option>
            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
              {{ acc.name }} ({{ acc.type }})
            </option>
          </select>
        </div>
      </section>

      <!-- ── Appearance / Theme Selector ──────────────────────── -->
      <section class="glass-card settings-section">
        <h2 class="section-title flex items-center gap-2">
          <SwatchIcon class="w-5 h-5 text-primary" />
          Appearance
        </h2>
        <p class="text-xs text-muted mb-4">Choose how Alvo looks on your screen</p>

        <div class="theme-options-grid">
          <!-- Light Swatch -->
          <div
            @click="setTheme('light')"
            class="theme-card"
            :class="{ 'theme-card--active': currentTheme === 'light' }"
          >
            <div class="theme-preview theme-preview--light">
              <div class="preview-sidebar"></div>
              <div class="preview-content">
                <div class="preview-bar"></div>
                <div class="preview-cards">
                  <div class="preview-card-item"></div>
                  <div class="preview-card-item"></div>
                </div>
              </div>
            </div>
            <div class="theme-card-footer">
              <span class="theme-name">Light Mode</span>
              <span v-if="currentTheme === 'light'" class="badge badge-primary">Active</span>
            </div>
          </div>

          <!-- Dark Swatch -->
          <div
            @click="setTheme('dark')"
            class="theme-card"
            :class="{ 'theme-card--active': currentTheme === 'dark' }"
          >
            <div class="theme-preview theme-preview--dark">
              <div class="preview-sidebar"></div>
              <div class="preview-content">
                <div class="preview-bar"></div>
                <div class="preview-cards">
                  <div class="preview-card-item"></div>
                  <div class="preview-card-item"></div>
                </div>
              </div>
            </div>
            <div class="theme-card-footer">
              <span class="theme-name">Dark Mode</span>
              <span v-if="currentTheme === 'dark'" class="badge badge-primary">Active</span>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Data Management Section ────────────────────────────── -->
      <section class="glass-card settings-section">
        <h2 class="section-title flex items-center gap-2">
          <CircleStackIcon class="w-5 h-5 text-primary" />
          Data &amp; Exports
        </h2>

        <div class="data-actions-list space-y-3">
          <div class="flex items-center justify-between p-3 rounded-xl bg-surface-2">
            <div>
              <p class="text-sm font-semibold text-primary-color">Export Transactions</p>
              <p class="text-xs text-muted">Download all transaction data as CSV format</p>
            </div>
            <button @click="exportCsv" class="btn-ghost text-xs">
              <ArrowDownTrayIcon class="w-4 h-4 mr-1 inline" />
              Export
            </button>
          </div>

          <div class="flex items-center justify-between p-3 rounded-xl bg-surface-2">
            <div>
              <p class="text-sm font-semibold text-primary-color">Local Cache</p>
              <p class="text-xs text-muted">Stored theme &amp; app configuration</p>
            </div>
            <button @click="clearCache" class="btn-ghost text-xs text-muted">
              Clear Cache
            </button>
          </div>
        </div>
      </section>

      <!-- ── App Metadata Section ───────────────────────────────── -->
      <section class="glass-card settings-section text-center">
        <div class="flex items-center justify-center gap-2 mb-2">
          <img :src="isDark ? '/logo-dark.svg' : '/logo.svg'" alt="Alvo Logo" class="w-6 h-6" />
          <h3 class="font-bold text-primary-color">Alvo Finance Manager</h3>
        </div>
        <p class="text-xs text-muted">Version 1.2.0 (Fintech Dark Core Edition)</p>
        <p class="text-xs text-muted mt-1">Built with Laravel 11 + Vue 3 + Tailwind v4 + Clerk</p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, inject, computed } from 'vue'
import axios from 'axios'
import { useUser } from '@clerk/vue'
import { 
  UserIcon, AdjustmentsHorizontalIcon, SwatchIcon, CircleStackIcon, ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'
import { currentCurrency, setGlobalCurrency } from '../utils/currency'

const { user } = useUser()
const toast = inject('toast')

const props = defineProps({
  isDark: { type: Boolean, default: false }
})

const emit = defineEmits(['toggle-theme'])

const accounts = ref([])
const currentTheme = computed(() => props.isDark ? 'dark' : 'light')

const preferences = reactive({
  currency: currentCurrency.value || 'PHP',
  defaultAccountId: localStorage.getItem('alvo_pref_default_account') || ''
})

async function fetchAccounts() {
  try {
    const { data } = await axios.get('/api/accounts')
    accounts.value = data
  } catch (e) {
    console.error('Failed to load accounts for settings', e)
  }
}

function savePreferences() {
  setGlobalCurrency(preferences.currency)
  localStorage.setItem('alvo_pref_default_account', preferences.defaultAccountId)
  toast(`Preferences saved`)
}

function setTheme(mode) {
  if ((mode === 'dark' && !props.isDark) || (mode === 'light' && props.isDark)) {
    emit('toggle-theme')
  }
}

async function exportCsv() {
  try {
    const response = await axios.get('/api/transactions/export', { responseType: 'blob' })
    const url = URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'alvo-transactions.csv')
    document.body.appendChild(link)
    link.click()
    link.remove()
    toast('CSV exported')
  } catch (e) {
    toast('Export failed', 'error')
  }
}

function clearCache() {
  localStorage.removeItem('alvo_pref_currency')
  localStorage.removeItem('alvo_pref_default_account')
  toast('Cache cleared')
}

onMounted(fetchAccounts)
</script>

<style scoped>
.view {
  padding: 2rem;
  max-width: 960px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.view-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.view-subtitle {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-top: 0.25rem;
}

.settings-grid {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.settings-section {
  padding: 1.5rem;
  border-radius: 1.25rem;
}

.section-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 1.25rem;
}

/* Profile Card */
.profile-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.profile-avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar-initials {
  font-size: 1.5rem;
  font-weight: 700;
  color: #ffffff;
}

.profile-name {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.profile-email {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-top: 0.125rem;
}

/* Forms */
.form-group {
  margin-bottom: 1rem;
}
.form-group:last-child {
  margin-bottom: 0;
}

/* Theme Swatches */
.theme-options-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.theme-card {
  border: 2px solid var(--border);
  border-radius: 1rem;
  padding: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.theme-card:hover {
  border-color: var(--border-strong);
}

.theme-card--active {
  border-color: var(--primary) !important;
  box-shadow: 0 0 0 2px var(--primary-glass);
}

.theme-preview {
  height: 90px;
  border-radius: 0.625rem;
  display: flex;
  overflow: hidden;
  border: 1px solid var(--border);
}

.theme-preview--light {
  background: #FAF9F5;
}

.theme-preview--light .preview-sidebar {
  width: 25%;
  background: #FFFFFF;
  border-right: 1px solid rgba(0,0,0,0.08);
}

.theme-preview--light .preview-content {
  flex: 1;
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.theme-preview--light .preview-bar {
  height: 12px;
  background: #E8E6DC;
  border-radius: 4px;
}

.theme-preview--light .preview-card-item {
  height: 24px;
  background: #FFFFFF;
  border-radius: 6px;
  border: 1px solid rgba(0,0,0,0.06);
}

.theme-preview--dark {
  background: #141413;
}

.theme-preview--dark .preview-sidebar {
  width: 25%;
  background: #1E1E1C;
  border-right: 1px solid rgba(255,255,255,0.08);
}

.theme-preview--dark .preview-content {
  flex: 1;
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.theme-preview--dark .preview-bar {
  height: 12px;
  background: #2A2A28;
  border-radius: 4px;
}

.theme-preview--dark .preview-card-item {
  height: 24px;
  background: #1E1E1C;
  border-radius: 6px;
  border: 1px solid rgba(255,255,255,0.06);
}

.theme-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 0.75rem;
}

.theme-name {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-primary);
}
</style>

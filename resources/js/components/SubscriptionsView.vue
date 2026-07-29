<template>
  <div class="view">
    <!-- Header -->
    <div class="view-header">
      <div>
        <h1 class="view-title">Subscription Manager</h1>
        <p class="view-subtitle">Track recurring SaaS, streaming services, and membership renewals</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
        Add Subscription
      </button>
    </div>

    <!-- Skeleton Loading -->
    <template v-if="loading">
      <div class="glass-card p-6 sk-card mb-6">
        <div class="skeleton" style="width:10rem;height:1.25rem;border-radius:0.375rem;margin-bottom:1rem"></div>
        <div class="skeleton" style="width:100%;height:3.5rem;border-radius:0.75rem"></div>
      </div>
    </template>

    <template v-else>
      <!-- Summary Hero Card -->
      <div class="glass-card summary-hero-card mb-6">
        <div class="summary-hero-content">
          <div>
            <span class="summary-label">Monthly Subscription Spend</span>
            <h2 class="summary-value tabular-nums amount-negative">{{ formatCurrency(totalMonthlySpend) }}</h2>
            <p class="summary-subtext">Yearly projection: {{ formatCurrency(totalMonthlySpend * 12) }}</p>
          </div>

          <div class="summary-breakdown">
            <div class="summary-box">
              <span class="box-label">Active Subscriptions</span>
              <span class="box-value tabular-nums text-primary-color">{{ activeSubs.length }}</span>
            </div>
            <div class="summary-box">
              <span class="box-label">Renewing Soon (7 days)</span>
              <span class="box-value tabular-nums" :class="renewingSoon.length ? 'text-warning font-bold' : 'text-primary-color'">
                {{ renewingSoon.length }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Preset Selector Bar -->
      <div class="glass-card p-4 mb-6">
        <span class="text-xs font-bold uppercase tracking-wider text-muted block mb-2.5">Quick Add Popular Services</span>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="preset in presets"
            :key="preset.name"
            @click="openModalWithPreset(preset)"
            class="preset-btn flex items-center gap-2"
            :style="{ borderColor: preset.color + '40', background: preset.color + '12' }"
          >
            <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: preset.color }"></span>
            <span class="font-semibold text-xs text-primary-color">{{ preset.name }}</span>
            <span class="text-[11px] text-muted">{{ formatCurrency(preset.amount) }}</span>
          </button>
        </div>
      </div>

      <!-- Subscriptions Grid -->
      <div class="mb-8">
        <div v-if="!subscriptions.length" class="empty-state glass-card">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <p class="empty-text">No active subscriptions tracked yet.</p>
          <button @click="openModal()" class="btn-primary mt-3">Add Your First Subscription</button>
        </div>

        <div v-else class="subs-grid">
          <div
            v-for="sub in subscriptions"
            :key="sub.id"
            class="glass-card sub-card flex flex-col justify-between"
            :style="{
              borderColor: `color-mix(in srgb, ${sub.color || '#6366f1'} 35%, transparent)`
            }"
            :class="{ 'opacity-60': !sub.is_active }"
          >
            <!-- Glow background -->
            <div class="sub-glow" :style="{ backgroundColor: sub.color || '#6366f1' }"></div>

            <div>
              <!-- Top Row: Icon, Name + Options -->
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm shadow-sm"
                       :style="{ backgroundColor: (sub.color || '#6366f1') + '25', color: sub.color || '#6366f1' }">
                    <span class="font-bold text-xs uppercase">{{ sub.name.substring(0, 2) }}</span>
                  </div>
                  <div>
                    <h3 class="font-bold text-sm text-primary-color">{{ sub.name }}</h3>
                    <p class="text-xs text-muted">{{ sub.account?.name || 'Manual Payment' }}</p>
                  </div>
                </div>

                <div class="flex items-center gap-1">
                  <button @click="openModal(sub)" class="action-btn action-btn--edit" title="Edit">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                  </button>
                  <button @click="deleteSub(sub)" class="action-btn action-btn--delete" title="Cancel/Delete">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </div>
              </div>

              <!-- Price + Cycle -->
              <div class="mt-4 flex items-baseline justify-between">
                <div>
                  <span class="text-2xl font-extrabold tabular-nums amount-negative">
                    {{ formatCurrency(sub.amount) }}
                  </span>
                  <span class="text-xs text-muted">/{{ sub.billing_cycle === 'yearly' ? 'yr' : 'mo' }}</span>
                </div>
                <span class="badge badge-primary uppercase text-[10px]">{{ sub.billing_cycle }}</span>
              </div>
            </div>

            <!-- Renewal Alert Footer -->
            <div class="mt-4 pt-3 border-t border-border/40 flex items-center justify-between">
              <span class="text-xs text-muted">Renews: {{ formatDate(sub.next_renewal_date) }}</span>
              <span :class="getRenewalBadgeClass(sub)">{{ getRenewalText(sub) }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Add / Edit Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-panel">
        <div class="modal-header">
          <h3 class="modal-title">{{ editingSub ? 'Edit Subscription' : 'Add Subscription' }}</h3>
          <button @click="showModal = false" class="modal-close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveSub" class="modal-form">
          <div>
            <label class="label">Service Name</label>
            <input v-model="form.name" required placeholder="e.g. Netflix, Spotify, ChatGPT Plus" class="input-field" />
          </div>

          <div class="form-row-2">
            <div>
              <label class="label">Amount ({{ getCurrencySymbol() }})</label>
              <input v-model="form.amount" type="number" step="0.01" min="0.01" required placeholder="549.00" class="input-field" />
            </div>
            <div>
              <label class="label">Billing Cycle</label>
              <select v-model="form.billing_cycle" class="input-field">
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
                <option value="weekly">Weekly</option>
              </select>
            </div>
          </div>

          <div class="form-row-2">
            <div>
              <label class="label">Next Renewal Date</label>
              <input v-model="form.next_renewal_date" type="date" required class="input-field" />
            </div>
            <div>
              <label class="label">Payment Account</label>
              <select v-model="form.account_id" class="input-field">
                <option value="">Select account...</option>
                <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
              </select>
            </div>
          </div>

          <div>
            <label class="label">Brand Accent Color</label>
            <input v-model="form.color" type="color" class="input-field h-10 p-1 cursor-pointer" />
          </div>

          <div v-if="formError" class="alert-danger">{{ formError }}</div>

          <div class="modal-footer">
            <button type="button" @click="showModal = false" class="btn-ghost modal-btn">Cancel</button>
            <button type="submit" :disabled="saving" class="btn-primary modal-btn">
              <span v-if="saving" class="btn-spinner"></span>
              {{ saving ? 'Saving...' : (editingSub ? 'Update Subscription' : 'Create Subscription') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, inject } from 'vue'
import axios from 'axios'
import { formatCurrency, getCurrencySymbol } from '../utils/currency'

const emit = defineEmits(['refresh'])
const toast = inject('toast')

const loading = ref(true)
const subscriptions = ref([])
const accounts = ref([])
const showModal = ref(false)
const editingSub = ref(null)
const saving = ref(false)
const formError = ref('')

const presets = [
  { name: 'Netflix', amount: 549, cycle: 'monthly', color: '#E50914' },
  { name: 'Spotify', amount: 149, cycle: 'monthly', color: '#1DB954' },
  { name: 'ChatGPT Plus', amount: 1150, cycle: 'monthly', color: '#10A37F' },
  { name: 'YouTube Premium', amount: 159, cycle: 'monthly', color: '#FF0000' },
  { name: 'GitHub Copilot', amount: 580, cycle: 'monthly', color: '#6e40c9' },
  { name: 'iCloud 200GB', amount: 149, cycle: 'monthly', color: '#007AFF' },
  { name: 'Adobe Creative Cloud', amount: 2800, cycle: 'monthly', color: '#FF0000' },
  { name: 'Disney+', amount: 369, cycle: 'monthly', color: '#113CCF' },
]

const form = reactive({
  name: '',
  amount: '',
  billing_cycle: 'monthly',
  next_renewal_date: new Date().toISOString().substring(0, 10),
  account_id: '',
  color: '#6366f1',
  is_active: true,
})

const activeSubs = computed(() => subscriptions.value.filter(s => s.is_active))
const totalMonthlySpend = computed(() => activeSubs.value.reduce((s, sub) => s + (parseFloat(sub.monthly_amount) || 0), 0))

const renewingSoon = computed(() => {
  const now = new Date()
  const sevenDays = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000)
  return activeSubs.value.filter(s => new Date(s.next_renewal_date) <= sevenDays)
})

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function getRenewalText(sub) {
  const due = new Date(sub.next_renewal_date)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  due.setHours(0, 0, 0, 0)
  const diffDays = Math.round((due - today) / (1000 * 60 * 60 * 24))

  if (diffDays < 0) return `${Math.abs(diffDays)}d overdue`
  if (diffDays === 0) return 'Renews today'
  if (diffDays === 1) return 'Renews tomorrow'
  return `In ${diffDays} days`
}

function getRenewalBadgeClass(sub) {
  const due = new Date(sub.next_renewal_date)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  due.setHours(0, 0, 0, 0)
  const diffDays = Math.round((due - today) / (1000 * 60 * 60 * 24))

  if (diffDays <= 0) return 'text-xs text-danger font-bold'
  if (diffDays <= 7) return 'text-xs text-warning font-bold'
  return 'text-xs text-muted'
}

async function fetchSubs() {
  loading.value = true
  try {
    const [subRes, accRes] = await Promise.all([
      axios.get('/api/subscriptions'),
      axios.get('/api/accounts'),
    ])
    subscriptions.value = subRes.data
    accounts.value = accRes.data
  } catch (e) {
    toast('Failed to load subscriptions', 'error')
  } finally {
    loading.value = false
  }
}

function openModal(sub = null) {
  editingSub.value = sub
  formError.value = ''
  if (sub) {
    Object.assign(form, {
      name: sub.name,
      amount: sub.amount,
      billing_cycle: sub.billing_cycle,
      next_renewal_date: sub.next_renewal_date ? sub.next_renewal_date.substring(0, 10) : new Date().toISOString().substring(0, 10),
      account_id: sub.account_id ?? '',
      color: sub.color || '#6366f1',
      is_active: sub.is_active,
    })
  } else {
    Object.assign(form, {
      name: '',
      amount: '',
      billing_cycle: 'monthly',
      next_renewal_date: new Date().toISOString().substring(0, 10),
      account_id: '',
      color: '#6366f1',
      is_active: true,
    })
  }
  showModal.value = true
}

function openModalWithPreset(preset) {
  openModal()
  form.name = preset.name
  form.amount = preset.amount
  form.billing_cycle = preset.cycle
  form.color = preset.color
}

async function saveSub() {
  formError.value = ''
  saving.value = true
  try {
    const payload = {
      ...form,
      amount: parseFloat(form.amount) || 0,
      account_id: form.account_id || null,
    }
    if (editingSub.value) {
      await axios.put(`/api/subscriptions/${editingSub.value.id}`, payload)
      toast('Subscription updated')
    } else {
      await axios.post('/api/subscriptions', payload)
      toast('Subscription added')
    }
    showModal.value = false
    fetchSubs()
    emit('refresh')
  } catch (e) {
    const errors = e.response?.data?.errors
    formError.value = errors
      ? Object.values(errors).flat().join(' ')
      : (e.response?.data?.message || 'Failed to save subscription')
  } finally {
    saving.value = false
  }
}

async function deleteSub(sub) {
  if (!confirm(`Cancel/Delete subscription "${sub.name}"?`)) return
  try {
    await axios.delete(`/api/subscriptions/${sub.id}`)
    toast('Subscription removed')
    fetchSubs()
    emit('refresh')
  } catch (e) {
    toast('Failed to delete subscription', 'error')
  }
}

onMounted(fetchSubs)
</script>

<style scoped>
.view { padding: 2rem; max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem; }
.view-header { display: flex; align-items: center; justify-content: space-between; }
.view-title { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 0; }
.view-subtitle { font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem; }

/* ── Hero Summary Card ────────────────────────────────────── */
.summary-hero-card { padding: 1.5rem; display: flex; flex-direction: column; }
.summary-hero-content { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem; }
.summary-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.summary-value { font-size: 2.25rem; font-weight: 800; line-height: 1.1; margin: 0.25rem 0; }
.summary-subtext { font-size: 0.75rem; color: var(--text-muted); }
.summary-breakdown { display: flex; gap: 1.25rem; flex-wrap: wrap; }
.summary-box { background: var(--bg-surface-2); padding: 0.75rem 1rem; border-radius: 0.875rem; display: flex; flex-direction: column; min-width: 130px; }
.box-label { font-size: 0.6875rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }
.box-value { font-size: 1.125rem; font-weight: 700; margin-top: 0.25rem; }

/* ── Presets Selector ─────────────────────────────────────── */
.preset-btn { padding: 0.4rem 0.75rem; border-radius: 0.625rem; border: 1px solid; cursor: pointer; transition: transform 0.15s ease; }
.preset-btn:hover { transform: translateY(-1px); }

/* ── Empty State ─────────────────────────────────────────── */
.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3.5rem 1.5rem; text-align: center; border-radius: 1.25rem; }
.empty-icon { width: 3rem; height: 3rem; color: var(--text-muted); opacity: 0.5; margin-bottom: 0.75rem; }
.empty-text { font-size: 0.875rem; color: var(--text-muted); margin: 0; }

/* ── Subscriptions Grid ───────────────────────────────────── */
.subs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; }
.sub-card { padding: 1.25rem; border-radius: 1.25rem; position: relative; overflow: hidden; min-height: 180px; }
.sub-glow { position: absolute; top: -35px; right: -35px; width: 100px; height: 100px; border-radius: 50%; filter: blur(45px); opacity: 0.2; pointer-events: none; }

/* ── Action Buttons ───────────────────────────────────────── */
.action-btn { width: 28px; height: 28px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: transparent; border: none; cursor: pointer; color: var(--text-muted); transition: background-color 0.15s, color 0.15s; }
.action-btn--edit:hover { background-color: var(--primary-light); color: var(--primary); }
.action-btn--delete:hover { background-color: var(--danger-light); color: var(--danger); }

/* ── Modal Specs ──────────────────────────────────────────── */
.modal-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 50; }
.modal-panel { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 1.5rem; box-shadow: var(--shadow-md); padding: 1.75rem; width: 100%; max-width: 480px; }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
.modal-title { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0; }
.modal-close { background: transparent; border: none; color: var(--text-muted); cursor: pointer; border-radius: 0.5rem; padding: 0.25rem; }
.modal-close:hover { background-color: var(--bg-surface-2); color: var(--text-primary); }

.modal-form { display: flex; flex-direction: column; gap: 1rem; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

.modal-footer { display: flex; gap: 0.75rem; padding-top: 0.5rem; }
.modal-btn { flex: 1; justify-content: center; }
</style>

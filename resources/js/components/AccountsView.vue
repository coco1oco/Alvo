<template>
  <div class="view">
    <!-- Header -->
    <div class="view-header">
      <div>
        <h1 class="view-title">Accounts</h1>
        <p class="view-subtitle">Manage your wallets and bank accounts</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
        Add Account
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
    </div>

    <template v-else>
      <!-- Account Cards -->
      <div class="accounts-grid">
        <div
          v-for="acc in accounts"
          :key="acc.id"
          class="glass-card account-card group"
        >
          <!-- Color glow -->
          <div class="account-glow" :style="{ backgroundColor: acc.color }"></div>

          <div class="account-top">
            <div class="account-identity">
              <div class="account-icon" :style="{ backgroundColor: acc.color + '20', color: acc.color }">
                <component :is="accountIcon(acc.type)" class="w-5 h-5" />
              </div>
              <div>
                <h3 class="account-name">{{ acc.name }}</h3>
                <p class="account-type">{{ acc.type.replace('_', ' ') }}</p>
              </div>
            </div>
            <div class="account-actions">
              <button @click="openModal(acc)" class="action-btn action-btn--edit" title="Edit">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button @click="deleteAccount(acc)" class="action-btn action-btn--delete" title="Delete">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>

          <div class="account-balance-section">
            <p class="balance-label">Current Balance</p>
            <p class="balance-value tabular-nums" :class="acc.balance >= 0 ? 'amount-positive' : 'amount-negative'">
              {{ formatCurrency(acc.balance) }}
            </p>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!accounts.length" class="empty-state col-span-full">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
          <p class="empty-text">No accounts yet. Add one to get started!</p>
        </div>
      </div>

      <!-- Net Worth Footer -->
      <div v-if="accounts.length" class="glass-card net-worth-card">
        <div>
          <p class="net-worth-label">Total Net Worth</p>
          <p class="net-worth-note">Sum of all account balances</p>
        </div>
        <p class="net-worth-value tabular-nums" :class="totalBalance >= 0 ? 'amount-positive' : 'amount-negative'">
          {{ formatCurrency(totalBalance) }}
        </p>
      </div>
    </template>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-panel">
        <div class="modal-header">
          <h3 class="modal-title">{{ editingAcc ? 'Edit Account' : 'Add Account' }}</h3>
          <button @click="showModal = false" class="modal-close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveAccount" class="modal-form">
          <div>
            <label class="label">Account Name</label>
            <input v-model="form.name" required placeholder="e.g. BDO Savings" class="input-field" />
          </div>
          <div>
            <label class="label">Type</label>
            <select v-model="form.type" class="input-field">
              <option value="cash">Cash / Wallet</option>
              <option value="bank">Bank Account</option>
              <option value="savings">Savings Account</option>
              <option value="credit_card">Credit Card</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div v-if="!editingAcc">
            <label class="label">Starting Balance (₱)</label>
            <input v-model="form.balance" type="number" step="0.01" placeholder="0.00" class="input-field" />
          </div>
          <div>
            <label class="label">Color</label>
            <div class="color-picker">
              <button
                type="button"
                v-for="c in colorPalette"
                :key="c"
                @click="form.color = c"
                :class="['color-swatch', form.color === c ? 'color-swatch--active' : '']"
                :style="{ backgroundColor: c }"
              ></button>
            </div>
          </div>
          <div v-if="formError" class="alert-danger">{{ formError }}</div>
          <div class="modal-footer">
            <button type="button" @click="showModal = false" class="btn-ghost modal-btn">Cancel</button>
            <button type="submit" :disabled="saving" class="btn-primary modal-btn">
              <span v-if="saving" class="btn-spinner"></span>
              {{ saving ? 'Saving...' : (editingAcc ? 'Update' : 'Add Account') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, inject, defineComponent, h } from 'vue'
import axios from 'axios'

const emit       = defineEmits(['refresh'])
const toast      = inject('toast')
const loading    = ref(true)
const accounts   = ref([])
const showModal  = ref(false)
const editingAcc = ref(null)
const saving     = ref(false)
const formError  = ref('')

const colorPalette = ['#6366f1','#8b5cf6','#ec4899','#ef4444','#f97316','#f59e0b','#22c55e','#10b981','#06b6d4','#3b82f6','#64748b','#0ea5e9']
const form         = reactive({ name: '', type: 'cash', balance: '', color: '#6366f1' })
const totalBalance = computed(() => accounts.value.reduce((s, a) => s + parseFloat(a.balance || 0), 0))

// ── Account type icons (inline SVGs) ──────────────────────────
const IconWallet = defineComponent({
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' })
  ])
})

const IconBank = defineComponent({
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z' })
  ])
})

const IconCreditCard = defineComponent({
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' })
  ])
})

const IconFolder = defineComponent({
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z' })
  ])
})

function accountIcon(type) {
  const map = { cash: IconWallet, bank: IconBank, savings: IconBank, credit_card: IconCreditCard, other: IconFolder }
  return map[type] || IconWallet
}

function formatCurrency(v) {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 }).format(v || 0)
}

async function fetchAccounts() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/accounts')
    accounts.value = data
  } finally {
    loading.value = false
  }
}

function openModal(acc = null) {
  editingAcc.value = acc
  formError.value  = ''
  if (acc) {
    Object.assign(form, { name: acc.name, type: acc.type, balance: '', color: acc.color })
  } else {
    Object.assign(form, { name: '', type: 'cash', balance: '', color: '#6366f1' })
  }
  showModal.value = true
}

async function saveAccount() {
  formError.value = ''
  saving.value    = true
  try {
    const payload = { name: form.name, type: form.type, color: form.color }
    if (!editingAcc.value) payload.balance = parseFloat(form.balance) || 0

    if (editingAcc.value) {
      await axios.put(`/api/accounts/${editingAcc.value.id}`, payload)
      toast('Account updated')
    } else {
      await axios.post('/api/accounts', payload)
      toast('Account added')
    }
    showModal.value = false
    fetchAccounts()
    emit('refresh')
  } catch (e) {
    formError.value = e.response?.data?.message || 'Failed to save'
  } finally {
    saving.value = false
  }
}

async function deleteAccount(acc) {
  if (!confirm(`Delete "${acc.name}"? This will also delete all related transactions.`)) return
  try {
    await axios.delete(`/api/accounts/${acc.id}`)
    toast('Account deleted')
    fetchAccounts()
    emit('refresh')
  } catch (e) {
    toast('Delete failed', 'error')
  }
}

onMounted(fetchAccounts)
</script>

<style scoped>
.view {
  padding: 2rem;
  max-width: 1280px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.view-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.view-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
  letter-spacing: -0.01em;
}

.view-subtitle {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0.25rem 0 0;
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 12rem;
}

/* ── Accounts Grid ────────────────────────────────────────── */
.accounts-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 640px)  { .accounts-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1280px) { .accounts-grid { grid-template-columns: repeat(3, 1fr); } }

.account-card {
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.account-glow {
  position: absolute;
  top: -20px;
  right: -20px;
  width: 100px;
  height: 100px;
  border-radius: 50%;
  filter: blur(32px);
  opacity: 0.18;
  pointer-events: none;
}

.account-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.account-identity {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.account-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.account-name {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.account-type {
  font-size: 0.75rem;
  color: var(--text-muted);
  margin: 0;
  text-transform: capitalize;
}

.account-actions {
  display: flex;
  gap: 0.25rem;
  opacity: 0;
  transition: opacity 0.15s;
}

.account-card:hover .account-actions { opacity: 1; }

.action-btn {
  width: 28px;
  height: 28px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background-color 0.15s, color 0.15s;
  color: var(--text-muted);
}

.action-btn--edit:hover  { background-color: var(--primary-light); color: var(--primary); }
.action-btn--delete:hover { background-color: var(--danger-light);  color: var(--danger); }

.account-balance-section { margin-top: auto; }

.balance-label {
  font-size: 0.6875rem;
  color: var(--text-muted);
  margin: 0 0 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.balance-value {
  font-size: 1.625rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.2;
}

/* ── Empty State ─────────────────────────────────────────── */
.col-span-full { grid-column: 1 / -1; }

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 1rem;
  gap: 0.75rem;
  color: var(--text-muted);
}

.empty-icon { width: 48px; height: 48px; opacity: 0.4; }

.empty-text { font-size: 0.9375rem; color: var(--text-muted); margin: 0; text-align: center; }

/* ── Net Worth ────────────────────────────────────────────── */
.net-worth-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.net-worth-card:hover { transform: none; }

.net-worth-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin: 0;
}

.net-worth-note { font-size: 0.6875rem; color: var(--text-muted); margin: 0.25rem 0 0; }

.net-worth-value { font-size: 1.75rem; font-weight: 700; margin: 0; }

/* ── Color Picker ─────────────────────────────────────────── */
.color-picker { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.25rem; }

.color-swatch {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 2px solid transparent;
  cursor: pointer;
  transition: transform 0.15s, border-color 0.15s;
}

.color-swatch--active { border-color: var(--text-primary); transform: scale(1.2); }

/* ── Modal ────────────────────────────────────────────────── */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.modal-title { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0; }

.modal-close {
  width: 32px;
  height: 32px;
  border-radius: 0.5rem;
  background: transparent;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  transition: background-color 0.15s, color 0.15s;
}

.modal-close:hover { background-color: var(--bg-surface-2); color: var(--text-primary); }

.modal-form { display: flex; flex-direction: column; gap: 1rem; }

.modal-footer { display: flex; gap: 0.75rem; padding-top: 0.25rem; }

.modal-btn { flex: 1; justify-content: center; }

.btn-spinner {
  width: 12px;
  height: 12px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}
</style>

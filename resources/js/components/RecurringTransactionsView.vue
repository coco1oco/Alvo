<template>
  <div class="view">
    <!-- Header -->
    <div class="view-header">
      <div>
        <h1 class="view-title">Recurring Transactions</h1>
        <p class="view-subtitle">Manage automated bills, subscriptions, and scheduled income</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
        Add Recurring Bill
      </button>
    </div>

    <!-- Skeleton Loading -->
    <template v-if="loading">
      <div class="glass-card p-6 sk-card">
        <div class="skeleton" style="width:10rem;height:1.25rem;border-radius:0.375rem;margin-bottom:1rem"></div>
        <div v-for="i in 4" :key="i" class="skeleton mb-3" style="width:100%;height:3.5rem;border-radius:0.75rem"></div>
      </div>
    </template>

    <template v-else>
      <!-- Summary Hero Card -->
      <div class="glass-card summary-hero-card mb-6">
        <div class="summary-hero-content">
          <div>
            <span class="summary-label">Monthly Recurring Bills</span>
            <h2 class="summary-value tabular-nums amount-negative">{{ formatCurrency(totalMonthlyRecurringExpenses) }}</h2>
            <p class="summary-subtext">{{ activeRecurring.length }} active recurring schedule{{ activeRecurring.length === 1 ? '' : 's' }}</p>
          </div>

          <div class="summary-breakdown">
            <div class="summary-box">
              <span class="box-label">Monthly Income</span>
              <span class="box-value amount-positive tabular-nums">{{ formatCurrency(totalMonthlyRecurringIncome) }}</span>
            </div>
            <div class="summary-box">
              <span class="box-label">Due Next 7 Days</span>
              <span class="box-value tabular-nums" :class="dueSoonItems.length ? 'text-warning font-bold' : 'text-primary-color'">
                {{ dueSoonItems.length }} bill{{ dueSoonItems.length === 1 ? '' : 's' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Recurring Schedules Table / Cards -->
      <div class="table-card glass-card overflow-hidden">
        <div class="p-4 border-b border-border flex items-center justify-between">
          <h2 class="font-bold text-sm text-primary-color">Scheduled Bills & Subscriptions</h2>
          <span class="text-xs text-muted">{{ items.length }} total</span>
        </div>

        <table v-if="items.length" class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-border/60 text-[11px] font-bold text-muted uppercase tracking-wider">
              <th class="p-3.5">Schedule / Description</th>
              <th class="p-3.5">Account</th>
              <th class="p-3.5">Frequency</th>
              <th class="p-3.5">Next Due Date</th>
              <th class="p-3.5 text-right">Amount</th>
              <th class="p-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/40 text-xs">
            <tr v-for="item in items" :key="item.id" class="hover:bg-bg-surface-2/50 transition-colors" :class="{ 'opacity-60': !item.is_active }">
              <!-- Description + Category -->
              <td class="p-3.5">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0"
                       :style="{ backgroundColor: (item.category?.color || '#6366f1') + '25', color: item.category?.color || '#6366f1' }">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-bold text-primary-color text-xs">{{ item.description || item.category?.name || 'Recurring Item' }}</h4>
                    <p class="text-[11px] text-muted">{{ item.category?.name || 'Uncategorized' }}</p>
                  </div>
                </div>
              </td>

              <!-- Account -->
              <td class="p-3.5 text-secondary-color">
                {{ item.account?.name }}
                <span v-if="item.to_account" class="text-muted"> → {{ item.to_account?.name }}</span>
              </td>

              <!-- Frequency -->
              <td class="p-3.5">
                <span class="badge badge-primary uppercase text-[10px]">{{ item.frequency }}</span>
              </td>

              <!-- Next Due Date & Status -->
              <td class="p-3.5">
                <div>
                  <span class="font-semibold text-primary-color block">{{ formatDate(item.next_due_date) }}</span>
                  <span :class="getDueDaysBadgeClass(item)">{{ getDueDaysText(item) }}</span>
                </div>
              </td>

              <!-- Amount -->
              <td class="p-3.5 text-right font-bold tabular-nums text-sm"
                  :class="item.type === 'income' ? 'amount-positive' : 'amount-negative'">
                {{ item.type === 'income' ? '+' : '−' }}{{ formatCurrency(item.amount) }}
              </td>

              <!-- Actions -->
              <td class="p-3.5 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button @click="processNow(item)" :disabled="processingId === item.id" class="btn-primary py-1 px-2.5 text-xs" title="Log transaction now">
                    <span v-if="processingId === item.id">...</span>
                    <span v-else>Log Now</span>
                  </button>
                  <button @click="openModal(item)" class="action-btn action-btn--edit" title="Edit">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                  </button>
                  <button @click="deleteItem(item)" class="action-btn action-btn--delete" title="Delete">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="p-8 text-center text-xs text-muted">
          No recurring transactions set up yet. Click "Add Recurring Bill" to create your first schedule.
        </div>
      </div>
    </template>

    <!-- Modal for Adding / Editing Recurring Schedule -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-panel">
        <div class="modal-header">
          <h3 class="modal-title">{{ editingItem ? 'Edit Recurring Schedule' : 'Add Recurring Schedule' }}</h3>
          <button @click="showModal = false" class="modal-close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveItem" class="modal-form">
          <!-- Type -->
          <div>
            <label class="label">Type</label>
            <div class="type-toggle">
              <button
                type="button"
                v-for="t in ['expense', 'income', 'transfer']"
                :key="t"
                @click="form.type = t"
                :class="['type-btn', typeClass(t)]"
              >{{ t }}</button>
            </div>
          </div>

          <!-- Account -->
          <div>
            <label class="label">{{ form.type === 'transfer' ? 'From Account' : 'Account' }}</label>
            <select v-model="form.account_id" required class="input-field">
              <option value="">Select account...</option>
              <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
            </select>
          </div>

          <!-- To Account (transfer) -->
          <div v-if="form.type === 'transfer'">
            <label class="label">To Account</label>
            <select v-model="form.to_account_id" required class="input-field">
              <option value="">Select destination...</option>
              <option v-for="acc in accounts.filter(a => a.id !== form.account_id)" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
            </select>
          </div>

          <!-- Category -->
          <div v-if="form.type !== 'transfer'">
            <label class="label">Category</label>
            <select v-model="form.category_id" class="input-field">
              <option value="">Select category...</option>
              <option v-for="cat in categories.filter(c => c.type === form.type)" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>

          <!-- Amount + Frequency -->
          <div class="form-row-2">
            <div>
              <label class="label">Amount (₱)</label>
              <input v-model="form.amount" type="number" step="0.01" min="0.01" required placeholder="0.00" class="input-field" />
            </div>
            <div>
              <label class="label">Frequency</label>
              <select v-model="form.frequency" class="input-field">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="bi-weekly">Bi-weekly (2 weeks)</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly (3 months)</option>
                <option value="yearly">Yearly</option>
              </select>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="label">Description / Title</label>
            <input v-model="form.description" required placeholder="e.g. Netflix, Electricity, Rent" class="input-field" />
          </div>

          <!-- Start Date + Next Due Date -->
          <div class="form-row-2">
            <div>
              <label class="label">Start Date</label>
              <input v-model="form.start_date" type="date" required class="input-field" />
            </div>
            <div>
              <label class="label">Next Due Date</label>
              <input v-model="form.next_due_date" type="date" required class="input-field" />
            </div>
          </div>

          <div v-if="formError" class="alert-danger">{{ formError }}</div>

          <div class="modal-footer">
            <button type="button" @click="showModal = false" class="btn-ghost modal-btn">Cancel</button>
            <button type="submit" :disabled="saving" class="btn-primary modal-btn">
              <span v-if="saving" class="btn-spinner"></span>
              {{ saving ? 'Saving...' : (editingItem ? 'Update Schedule' : 'Create Schedule') }}
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

const emit = defineEmits(['refresh'])
const toast = inject('toast')

const loading = ref(true)
const items = ref([])
const accounts = ref([])
const categories = ref([])
const showModal = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const formError = ref('')
const processingId = ref(null)

const form = reactive({
  type: 'expense',
  account_id: '',
  to_account_id: '',
  category_id: '',
  amount: '',
  description: '',
  frequency: 'monthly',
  start_date: new Date().toISOString().substring(0, 10),
  next_due_date: new Date().toISOString().substring(0, 10),
})

const activeRecurring = computed(() => items.value.filter(i => i.is_active))

const totalMonthlyRecurringExpenses = computed(() => {
  return activeRecurring.value
    .filter(i => i.type === 'expense')
    .reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0)
})

const totalMonthlyRecurringIncome = computed(() => {
  return activeRecurring.value
    .filter(i => i.type === 'income')
    .reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0)
})

const dueSoonItems = computed(() => {
  const now = new Date()
  const sevenDays = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000)
  return activeRecurring.value.filter(i => {
    const due = new Date(i.next_due_date)
    return due <= sevenDays
  })
})

function formatCurrency(val) {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 }).format(val || 0)
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function typeClass(t) {
  if (form.type !== t) return ''
  if (t === 'income') return 'type-btn--income'
  if (t === 'expense') return 'type-btn--expense'
  return 'type-btn--transfer'
}

function getDueDaysText(item) {
  const due = new Date(item.next_due_date)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  due.setHours(0, 0, 0, 0)
  const diffDays = Math.round((due - today) / (1000 * 60 * 60 * 24))

  if (diffDays < 0) return `${Math.abs(diffDays)}d overdue`
  if (diffDays === 0) return 'Due today'
  if (diffDays === 1) return 'Due tomorrow'
  return `In ${diffDays} days`
}

function getDueDaysBadgeClass(item) {
  const due = new Date(item.next_due_date)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  due.setHours(0, 0, 0, 0)
  const diffDays = Math.round((due - today) / (1000 * 60 * 60 * 24))

  if (diffDays <= 0) return 'text-xs text-danger font-bold block'
  if (diffDays <= 7) return 'text-xs text-warning font-semibold block'
  return 'text-xs text-muted block'
}

async function fetchData() {
  loading.value = true
  try {
    const [recRes, accRes, catRes] = await Promise.all([
      axios.get('/api/recurring-transactions'),
      axios.get('/api/accounts'),
      axios.get('/api/categories'),
    ])
    items.value = recRes.data
    accounts.value = accRes.data
    categories.value = catRes.data
  } catch (e) {
    toast('Failed to load recurring data', 'error')
  } finally {
    loading.value = false
  }
}

function openModal(item = null) {
  editingItem.value = item
  formError.value = ''
  if (item) {
    Object.assign(form, {
      type: item.type,
      account_id: item.account_id,
      to_account_id: item.to_account_id ?? '',
      category_id: item.category_id ?? '',
      amount: item.amount,
      description: item.description ?? '',
      frequency: item.frequency,
      start_date: item.start_date ? item.start_date.substring(0, 10) : new Date().toISOString().substring(0, 10),
      next_due_date: item.next_due_date ? item.next_due_date.substring(0, 10) : new Date().toISOString().substring(0, 10),
    })
  } else {
    Object.assign(form, {
      type: 'expense',
      account_id: '',
      to_account_id: '',
      category_id: '',
      amount: '',
      description: '',
      frequency: 'monthly',
      start_date: new Date().toISOString().substring(0, 10),
      next_due_date: new Date().toISOString().substring(0, 10),
    })
  }
  showModal.value = true
}

async function saveItem() {
  formError.value = ''
  saving.value = true
  try {
    const payload = { ...form }
    if (editingItem.value) {
      await axios.put(`/api/recurring-transactions/${editingItem.value.id}`, payload)
      toast('Schedule updated')
    } else {
      await axios.post('/api/recurring-transactions', payload)
      toast('Recurring schedule created')
    }
    showModal.value = false
    fetchData()
    emit('refresh')
  } catch (e) {
    const errors = e.response?.data?.errors
    formError.value = errors
      ? Object.values(errors).flat().join(' ')
      : (e.response?.data?.message || 'Failed to save schedule')
  } finally {
    saving.value = false
  }
}

async function processNow(item) {
  processingId.value = item.id
  try {
    await axios.post(`/api/recurring-transactions/${item.id}/process`)
    toast('Transaction logged and balance updated')
    fetchData()
    emit('refresh')
  } catch (e) {
    toast(e.response?.data?.message || 'Failed to process transaction', 'error')
  } finally {
    processingId.value = null
  }
}

async function deleteItem(item) {
  if (!confirm(`Delete recurring schedule "${item.description || 'Item'}"?`)) return
  try {
    await axios.delete(`/api/recurring-transactions/${item.id}`)
    toast('Schedule deleted')
    fetchData()
    emit('refresh')
  } catch (e) {
    toast('Failed to delete schedule', 'error')
  }
}

onMounted(fetchData)
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

.view-header { display: flex; align-items: center; justify-content: space-between; }
.view-title { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 0; }
.view-subtitle { font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem; }

/* ── Hero Summary Card ────────────────────────────────────── */
.summary-hero-card { padding: 1.5rem; }
.summary-hero-content { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem; }
.summary-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.summary-value { font-size: 2.25rem; font-weight: 800; line-height: 1.1; margin: 0.25rem 0; }
.summary-subtext { font-size: 0.75rem; color: var(--text-muted); }
.summary-breakdown { display: flex; gap: 1.25rem; flex-wrap: wrap; }
.summary-box { background: var(--bg-surface-2); padding: 0.75rem 1rem; border-radius: 0.875rem; display: flex; flex-direction: column; min-width: 130px; }
.box-label { font-size: 0.6875rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }
.box-value { font-size: 1.125rem; font-weight: 700; margin-top: 0.25rem; }

/* ── Action Buttons ───────────────────────────────────────── */
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
  color: var(--text-muted);
  transition: background-color 0.15s, color 0.15s;
}
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

.type-toggle { display: flex; gap: 0.5rem; }
.type-btn { flex: 1; padding: 0.5rem; border-radius: 0.625rem; font-size: 0.8125rem; font-weight: 600; border: 1px solid var(--border-strong); background: transparent; color: var(--text-secondary); cursor: pointer; transition: all 0.15s; text-transform: capitalize; }
.type-btn--income { background-color: var(--success-light); border-color: var(--success); color: var(--success); }
.type-btn--expense { background-color: var(--danger-light); border-color: var(--danger); color: var(--danger); }
.type-btn--transfer { background-color: var(--primary-light); border-color: var(--primary); color: var(--primary); }

.modal-footer { display: flex; gap: 0.75rem; padding-top: 0.5rem; }
.modal-btn { flex: 1; justify-content: center; }
</style>

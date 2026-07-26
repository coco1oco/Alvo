<template>
  <div class="view">
    <!-- Header -->
    <div class="view-header">
      <div>
        <h1 class="view-title">Budgets</h1>
        <p class="view-subtitle">Set monthly spending limits per category</p>
      </div>
      <div class="header-actions">
        <input v-model="selectedMonth" type="month" @change="fetchBudgets" class="input-field month-input" />
        <button @click="showModal = true" class="btn-primary">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
          </svg>
          Set Budget
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
    </div>

    <!-- Budget Cards -->
    <div v-else-if="budgets.length" class="budgets-grid">
      <div
        v-for="b in budgets"
        :key="b.id"
        class="glass-card budget-card"
        :class="b.percentage > 100 ? 'budget-card--over' : b.percentage > 80 ? 'budget-card--warn' : ''"
      >
        <!-- Card Header -->
        <div class="budget-top">
          <div class="budget-identity">
            <div class="budget-icon" :style="{ backgroundColor: b.color + '20', color: b.color }">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
              </svg>
            </div>
            <span class="budget-category">{{ b.category }}</span>
          </div>
          <div class="budget-header-right">
            <span :class="b.percentage > 100 ? 'badge badge-danger' : b.percentage > 80 ? 'badge badge-warning' : 'badge badge-success'">
              {{ b.percentage }}%
            </span>
            <button @click="deleteBudget(b)" class="action-btn action-btn--delete" title="Delete">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="budget-bar-track">
          <div
            class="budget-bar-fill"
            :class="b.percentage > 100 ? 'bar-danger' : b.percentage > 80 ? 'bar-warning' : 'bar-success'"
            :style="{ width: Math.min(b.percentage, 100) + '%' }"
          ></div>
        </div>

        <!-- Amounts -->
        <div class="budget-amounts">
          <span class="tabular-nums">Spent: <strong class="amount-spent">{{ formatCurrency(b.spent) }}</strong></span>
          <span class="tabular-nums">Limit: <strong class="amount-limit">{{ formatCurrency(b.budget) }}</strong></span>
        </div>

        <!-- Status Note -->
        <div v-if="b.percentage > 100" class="budget-status budget-status--over">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          Over budget by {{ formatCurrency(b.spent - b.budget) }}
        </div>
        <div v-else class="budget-status budget-status--ok">
          {{ formatCurrency(b.remaining) }} remaining
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state">
      <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>
      <p class="empty-title">No budgets for {{ selectedMonth }}</p>
      <p class="empty-hint">Click "Set Budget" to add a spending limit.</p>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-panel">
        <div class="modal-header">
          <h3 class="modal-title">Set Budget</h3>
          <button @click="showModal = false" class="modal-close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="saveBudget" class="modal-form">
          <div>
            <label class="label">Category (Expense)</label>
            <select v-model="form.category_id" required class="input-field">
              <option value="">Select category...</option>
              <option v-for="cat in expenseCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Monthly Limit (₱)</label>
            <input v-model="form.amount" type="number" step="0.01" min="1" required placeholder="e.g. 5000" class="input-field" />
          </div>
          <div>
            <label class="label">Month</label>
            <input v-model="form.month" type="month" required class="input-field" />
          </div>
          <div v-if="formError" class="alert-danger">{{ formError }}</div>
          <div class="modal-footer">
            <button type="button" @click="showModal = false" class="btn-ghost modal-btn">Cancel</button>
            <button type="submit" :disabled="saving" class="btn-primary modal-btn">
              {{ saving ? 'Saving...' : 'Set Budget' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, inject } from 'vue'
import axios from 'axios'

const toast           = inject('toast')
const loading         = ref(true)
const budgets         = ref([])
const expenseCategories = ref([])
const showModal       = ref(false)
const saving          = ref(false)
const formError       = ref('')

const now           = new Date()
const selectedMonth = ref(`${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`)

const form = reactive({ category_id: '', amount: '', month: selectedMonth.value })

function formatCurrency(v) {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 }).format(v || 0)
}

async function fetchBudgets() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/budgets', { params: { month: selectedMonth.value } })
    budgets.value = data
  } finally {
    loading.value = false
  }
}

async function fetchCategories() {
  const { data } = await axios.get('/api/categories')
  expenseCategories.value = data.filter(c => c.type === 'expense')
}

async function saveBudget() {
  formError.value = ''
  saving.value    = true
  try {
    await axios.post('/api/budgets', form)
    toast('Budget set')
    showModal.value = false
    fetchBudgets()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Failed to save'
  } finally {
    saving.value = false
  }
}

async function deleteBudget(b) {
  if (!confirm('Delete this budget?')) return
  try {
    await axios.delete(`/api/budgets/${b.id}`)
    toast('Budget deleted')
    fetchBudgets()
  } catch (e) {
    toast('Delete failed', 'error')
  }
}

onMounted(() => {
  fetchBudgets()
  fetchCategories()
})
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
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 640px) {
  .view-header { flex-direction: row; align-items: center; justify-content: space-between; }
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

.header-actions { display: flex; align-items: center; gap: 0.625rem; flex-shrink: 0; }

.month-input { width: auto; }

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 12rem;
}

/* ── Budgets Grid ─────────────────────────────────────────── */
.budgets-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 640px)  { .budgets-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1280px) { .budgets-grid { grid-template-columns: repeat(3, 1fr); } }

/* ── Budget Card ──────────────────────────────────────────── */
.budget-card {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.budget-card--over { border-color: var(--danger) !important; }
.budget-card--warn { border-color: var(--warning) !important; }

.budget-card:hover { transform: none; }

.budget-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.budget-identity {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  min-width: 0;
}

.budget-icon {
  width: 32px;
  height: 32px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.budget-category {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.budget-header-right {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  flex-shrink: 0;
}

/* ── Progress Bar ─────────────────────────────────────────── */
.budget-bar-track {
  width: 100%;
  height: 8px;
  background-color: var(--bg-surface-2);
  border-radius: 9999px;
  overflow: hidden;
}

.budget-bar-fill {
  height: 100%;
  border-radius: 9999px;
  transition: width 0.5s ease;
}

.bar-success { background-color: var(--success); }
.bar-warning { background-color: var(--warning); }
.bar-danger  { background-color: var(--danger); }

/* ── Amounts ──────────────────────────────────────────────── */
.budget-amounts {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.amount-spent { color: var(--text-primary); }
.amount-limit { color: var(--text-primary); }

/* ── Status Notes ─────────────────────────────────────────── */
.budget-status {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.budget-status--over { color: var(--danger); }
.budget-status--ok   { color: var(--text-muted); }

/* ── Action Buttons ───────────────────────────────────────── */
.action-btn {
  width: 26px;
  height: 26px;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background-color 0.15s, color 0.15s;
  color: var(--text-muted);
}

.action-btn--delete:hover { background-color: var(--danger-light); color: var(--danger); }

/* ── Empty State ─────────────────────────────────────────── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 5rem 1rem;
  gap: 0.5rem;
  text-align: center;
}

.empty-icon { width: 48px; height: 48px; color: var(--text-muted); opacity: 0.4; }

.empty-title { font-size: 1rem; font-weight: 600; color: var(--text-secondary); margin: 0.5rem 0 0; }

.empty-hint { font-size: 0.8125rem; color: var(--text-muted); margin: 0; }

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
</style>

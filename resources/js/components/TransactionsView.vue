<template>
  <div class="view">
    <!-- Header -->
    <div class="view-header">
      <div>
        <h1 class="view-title">Transactions</h1>
        <p class="view-subtitle">Manage your income, expenses &amp; transfers</p>
      </div>
      <div class="header-actions">
        <button @click="showTrashModal = true" class="btn-ghost">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Trash
        </button>
        <button @click="exportCsv" class="btn-ghost">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Export CSV
        </button>
        <button @click="openModal()" class="btn-primary">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
          </svg>
          Add Transaction
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="filter-panel">
      <input
        v-model="filters.search"
        @input="debouncedFetch"
        type="text"
        placeholder="Search description..."
        class="input-field filter-search"
      />
      <select v-model="filters.type" @change="fetchTransactions" class="input-field filter-select">
        <option value="">All Types</option>
        <option value="income">Income</option>
        <option value="expense">Expense</option>
        <option value="transfer">Transfer</option>
      </select>
      <input v-model="filters.from" @change="fetchTransactions" type="date" class="input-field filter-date" />
      <input v-model="filters.to"   @change="fetchTransactions" type="date" class="input-field filter-date" />
    </div>

    <!-- Table -->
    <div class="table-card">
      <div v-if="loading" class="table-loading">
        <div class="spinner"></div>
      </div>

      <table v-else class="txn-table">
        <thead>
          <tr class="table-head-row">
            <th class="th">Date</th>
            <th class="th">Type</th>
            <th class="th">Account</th>
            <th class="th">Category</th>
            <th class="th">Description</th>
            <th class="th th--right">Amount</th>
            <th class="th"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="txn in transactions"
            :key="txn.id"
            class="table-row"
          >
            <td class="td td--muted">{{ formatDate(txn.date) }}</td>
            <td class="td">
              <span :class="txnBadgeClass(txn.type)">{{ txn.type }}</span>
            </td>
            <td class="td td--secondary">
              {{ txn.account?.name }}
              <span v-if="txn.to_account" class="td-arrow"> → {{ txn.to_account?.name }}</span>
            </td>
            <td class="td td--secondary">{{ txn.category?.name || '—' }}</td>
            <td class="td td--muted td--truncate">{{ txn.description || '—' }}</td>
            <td class="td td--right">
              <span class="txn-amount tabular-nums"
                :class="txn.type === 'income' ? 'amount-positive' : txn.type === 'expense' ? 'amount-negative' : 'amount-transfer'">
                {{ txn.type === 'income' ? '+' : txn.type === 'expense' ? '−' : '' }}{{ formatCurrency(txn.amount) }}
              </span>
            </td>
            <td class="td td--actions">
              <div class="row-actions">
                <button @click="openModal(txn)" class="action-btn action-btn--edit" title="Edit">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button @click="deleteTransaction(txn)" class="action-btn action-btn--delete" title="Delete">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!transactions.length">
            <td colspan="7" class="td td--empty">No transactions found.</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="pagination">
        <span class="pagination-info">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
        <div class="pagination-btns">
          <button
            :disabled="!pagination.prev_page_url"
            @click="goPage(pagination.current_page - 1)"
            class="btn-ghost pagination-btn"
          >← Prev</button>
          <button
            :disabled="!pagination.next_page_url"
            @click="goPage(pagination.current_page + 1)"
            class="btn-ghost pagination-btn"
          >Next →</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <TransactionModal
      v-if="showModal"
      :transaction="editingTxn"
      @close="showModal = false"
      @saved="onSaved"
    />
    
    <!-- Trash Modal -->
    <TrashModal
      v-if="showTrashModal"
      @close="showTrashModal = false"
      @restored="fetchTransactions"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, inject } from 'vue'
import axios from 'axios'
import TransactionModal from './TransactionModal.vue'
import TrashModal from './TrashModal.vue'

const emit         = defineEmits(['refresh'])
const toast        = inject('toast')
const loading      = ref(true)
const transactions = ref([])
const pagination   = ref({})
const showModal    = ref(false)
const showTrashModal = ref(false)
const editingTxn   = ref(null)
let searchTimeout  = null

const filters = reactive({ search: '', type: '', from: '', to: '', page: 1 })

function formatCurrency(v) {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
  }).format(v || 0)
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function txnBadgeClass(type) {
  if (type === 'income')   return 'badge badge-success'
  if (type === 'expense')  return 'badge badge-danger'
  return 'badge badge-primary'
}

async function fetchTransactions() {
  loading.value = true
  try {
    const params = {}
    if (filters.search)   params.search = filters.search
    if (filters.type)     params.type   = filters.type
    if (filters.from)     params.from   = filters.from
    if (filters.to)       params.to     = filters.to
    if (filters.page > 1) params.page   = filters.page

    const { data } = await axios.get('/api/transactions', { params })
    transactions.value = data.data
    pagination.value   = data
  } catch (e) {
    toast('Failed to load transactions', 'error')
  } finally {
    loading.value = false
  }
}

function debouncedFetch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchTransactions, 400)
}

function goPage(page) {
  filters.page = page
  fetchTransactions()
}

function openModal(txn = null) {
  editingTxn.value = txn
  showModal.value  = true
}

function onSaved() {
  showModal.value = false
  fetchTransactions()
  emit('refresh')
}

async function deleteTransaction(txn) {
  if (!confirm('Delete this transaction? This will reverse the balance change.')) return
  try {
    await axios.delete(`/api/transactions/${txn.id}`)
    toast('Transaction deleted')
    fetchTransactions()
    emit('refresh')
  } catch (e) {
    toast('Delete failed', 'error')
  }
}

async function exportCsv() {
  try {
    const response = await axios.get('/api/transactions/export', { responseType: 'blob' })
    const url  = URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href  = url
    link.setAttribute('download', 'transactions.csv')
    document.body.appendChild(link)
    link.click()
    link.remove()
    toast('CSV exported successfully')
  } catch (e) {
    toast('Export failed', 'error')
  }
}

onMounted(fetchTransactions)
</script>

<style scoped>
.view {
  padding: 2rem;
  max-width: 1280px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
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

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  flex-shrink: 0;
}

/* ── Filter Panel ─────────────────────────────────────────── */
.filter-panel {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 1rem;
}

@media (min-width: 640px)  { .filter-panel { grid-template-columns: 1fr 1fr; } }
@media (min-width: 1280px) { .filter-panel { grid-template-columns: 2fr 1fr 1fr 1fr; } }

/* ── Table Card ───────────────────────────────────────────── */
.table-card {
  background: var(--bg-glass);
  backdrop-filter: blur(16px) saturate(180%);
  -webkit-backdrop-filter: blur(16px) saturate(180%);
  border: 1px solid var(--border);
  border-radius: 1.25rem;
  box-shadow: var(--shadow-glass);
  overflow: hidden;
}

.table-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 12rem;
}

/* ── Table ────────────────────────────────────────────────── */
.txn-table {
  width: 100%;
  border-collapse: collapse;
}

.table-head-row {
  border-bottom: 1px solid var(--border);
}

.th {
  text-align: left;
  font-size: 0.6875rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0.875rem 1.25rem;
}

.th--right { text-align: right; }

.table-row {
  border-bottom: 1px solid var(--border);
  transition: background-color 0.15s;
}

.table-row:last-child { border-bottom: none; }

.table-row:hover { background-color: var(--bg-surface-2); }

.table-row:hover .row-actions { opacity: 1; }

.td {
  padding: 0.75rem 1.25rem;
  font-size: 0.8125rem;
  color: var(--text-primary);
  vertical-align: middle;
}

.td--muted     { color: var(--text-muted); }
.td--secondary { color: var(--text-secondary); }
.td--right     { text-align: right; }
.td--truncate  { max-width: 12rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.td--empty     { text-align: center; padding: 3rem; color: var(--text-muted); font-size: 0.875rem; }

.td-arrow { color: var(--text-muted); }

.txn-amount { font-weight: 700; font-size: 0.875rem; }

.amount-transfer { color: var(--primary); }

/* ── Row Actions ──────────────────────────────────────────── */
.td--actions { width: 5rem; }

.row-actions {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  opacity: 0;
  transition: opacity 0.15s;
}

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

/* ── Pagination ───────────────────────────────────────────── */
.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.25rem;
  border-top: 1px solid var(--border);
}

.pagination-info { font-size: 0.75rem; color: var(--text-muted); }

.pagination-btns { display: flex; gap: 0.5rem; }

.pagination-btn {
  padding: 0.375rem 0.75rem;
  font-size: 0.75rem;
}
</style>

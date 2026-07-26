<template>
  <div class="modal-overlay">
    <div class="modal-panel modal-panel--lg">
      <!-- Header -->
      <div class="modal-header">
        <div>
          <h3 class="modal-title">Recently Deleted</h3>
          <p class="modal-subtitle">Transactions will be permanently deleted after 30 days.</p>
        </div>
        <button @click="$emit('close')" class="modal-close">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="modal-content">
        <div v-if="loading" class="table-loading">
          <div class="spinner"></div>
        </div>

        <div v-else-if="!transactions.length" class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          <p class="empty-text">Trash is empty</p>
        </div>

        <div v-else class="trash-list">
          <div v-for="txn in transactions" :key="txn.id" class="trash-item">
            <div class="trash-info">
              <p class="trash-desc">{{ txn.description || txn.category?.name || 'Transfer' }}</p>
              <p class="trash-meta">
                {{ formatDate(txn.date) }} &bull; {{ txn.account?.name }}
                <span v-if="txn.to_account"> &rarr; {{ txn.to_account?.name }}</span>
              </p>
              <p class="trash-days">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ daysLeft(txn.deleted_at) }} days left
              </p>
            </div>
            
            <div class="trash-amount">
              <span class="tabular-nums" :class="txn.type === 'income' ? 'amount-positive' : txn.type === 'expense' ? 'amount-negative' : 'amount-transfer'">
                {{ txn.type === 'income' ? '+' : txn.type === 'expense' ? '−' : '' }}{{ formatCurrency(txn.amount) }}
              </span>
            </div>

            <div class="trash-actions">
              <button @click="restoreTxn(txn.id)" class="btn-ghost action-btn action-btn--restore" title="Restore">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
                Restore
              </button>
              <button @click="forceDelete(txn.id)" class="btn-ghost action-btn action-btn--delete" title="Delete Permanently">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import axios from 'axios'

const emit = defineEmits(['close', 'restored'])
const toast = inject('toast')
const loading = ref(true)
const transactions = ref([])

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

function daysLeft(deletedAt) {
  const deletedDate = new Date(deletedAt)
  const expirationDate = new Date(deletedDate.getTime() + 30 * 24 * 60 * 60 * 1000)
  const today = new Date()
  const diffTime = expirationDate - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return Math.max(0, diffDays)
}

async function fetchTrash() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/transactions/trashed')
    transactions.value = data
  } catch (e) {
    if (toast) toast('Failed to load trash', 'error')
  } finally {
    loading.value = false
  }
}

async function restoreTxn(id) {
  try {
    await axios.post(`/api/transactions/${id}/restore`)
    if (toast) toast('Transaction restored')
    emit('restored')
    fetchTrash() // refresh list
  } catch (e) {
    if (toast) toast('Restore failed', 'error')
  }
}

async function forceDelete(id) {
  if (!confirm('Permanently delete this transaction? This cannot be undone.')) return
  try {
    await axios.delete(`/api/transactions/${id}/force`)
    if (toast) toast('Transaction permanently deleted')
    fetchTrash() // refresh list
  } catch (e) {
    if (toast) toast('Delete failed', 'error')
  }
}

onMounted(fetchTrash)
</script>

<style scoped>
.modal-panel--lg {
  max-width: 640px;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.modal-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.modal-subtitle {
  font-size: 0.8125rem;
  color: var(--text-muted);
  margin: 0.25rem 0 0;
}

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

.modal-close:hover {
  background-color: var(--bg-surface-2);
  color: var(--text-primary);
}

.modal-content {
  max-height: 60vh;
  overflow-y: auto;
  margin: 0 -1.5rem;
  padding: 0 1.5rem;
}

.table-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 12rem;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 12rem;
  gap: 0.5rem;
  color: var(--text-muted);
}

.empty-icon {
  width: 40px;
  height: 40px;
  opacity: 0.5;
}

.empty-text {
  font-size: 0.8125rem;
  color: var(--text-muted);
  margin: 0;
}

.trash-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.trash-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background-color: var(--bg-surface-2);
  border-radius: 0.75rem;
  border: 1px solid var(--border);
}

.trash-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.trash-desc {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.trash-meta {
  font-size: 0.75rem;
  color: var(--text-secondary);
  margin: 0;
}

.trash-days {
  font-size: 0.6875rem;
  color: var(--warning);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: 500;
}

.trash-amount {
  font-size: 0.875rem;
  font-weight: 700;
  text-align: right;
  min-width: 80px;
}

.trash-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.625rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  transition: all 0.15s;
}

.action-btn--restore {
  background-color: var(--primary-light);
  color: var(--primary);
  border: 1px solid transparent;
}
.action-btn--restore:hover {
  background-color: var(--primary);
  color: #fff;
}

.action-btn--delete {
  padding: 0.375rem;
  color: var(--text-muted);
}
.action-btn--delete:hover {
  background-color: var(--danger-light);
  color: var(--danger);
}
</style>

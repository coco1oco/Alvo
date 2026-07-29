<template>
  <div class="view">
    <!-- Header -->
    <div class="view-header">
      <div>
        <h1 class="view-title">Splits &amp; Debts</h1>
        <p class="view-subtitle">Track shared expenses, owed amounts, and settle up with friends</p>
      </div>
      <button @click="openTransactionModal" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
        New Split Expense
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
            <span class="summary-label">Total Outstanding Owed to You</span>
            <h2 class="summary-value tabular-nums amount-positive">{{ formatCurrency(summary.total_owed || 0) }}</h2>
            <p class="summary-subtext">Across {{ summary.total_people || 0 }} people • {{ summary.pending_splits_count || 0 }} pending shares</p>
          </div>

          <div class="summary-breakdown">
            <div class="summary-box">
              <span class="box-label">Total Settled</span>
              <span class="box-value tabular-nums text-success font-bold">{{ formatCurrency(summary.total_settled || 0) }}</span>
            </div>
            <div class="summary-box">
              <span class="box-label">People Count</span>
              <span class="box-value tabular-nums text-primary-color">{{ summary.total_people || 0 }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs: People Ledger vs Transactions breakdown -->
      <div class="view-tabs mb-6">
        <button
          @click="activeTab = 'people'"
          :class="['tab-btn', activeTab === 'people' ? 'tab-btn--active' : '']"
        >
          People Ledger ({{ people.length }})
        </button>
        <button
          @click="activeTab = 'transactions'"
          :class="['tab-btn', activeTab === 'transactions' ? 'tab-btn--active' : '']"
        >
          All Split Expenses ({{ splitTransactions.length }})
        </button>
      </div>

      <!-- TAB 1: People Ledger Grid -->
      <div v-if="activeTab === 'people'" class="people-section">
        <div v-if="!people.length" class="empty-state glass-card">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <p class="empty-text">No split expenses logged yet.</p>
          <button @click="openTransactionModal" class="btn-primary mt-3">Split Your First Expense</button>
        </div>

        <div v-else class="people-grid">
          <div
            v-for="person in people"
            :key="person.name"
            class="glass-card person-card"
          >
            <div class="person-card-header">
              <div class="person-avatar">
                {{ person.name.charAt(0).toUpperCase() }}
              </div>
              <div class="person-info">
                <h3 class="person-name">{{ person.name }}</h3>
                <span class="person-subtext">
                  {{ person.pending_count }} pending • {{ person.settled_count }} paid
                </span>
              </div>
              <div class="person-amount-tag text-right">
                <span class="text-xs text-muted block">Owes You</span>
                <span class="person-total tabular-nums" :class="person.total_owed > 0 ? 'text-success' : 'text-muted'">
                  {{ formatCurrency(person.total_owed) }}
                </span>
              </div>
            </div>

            <!-- Person Splits List -->
            <div class="person-splits-list">
              <div
                v-for="item in person.splits"
                :key="item.transaction_id + '-' + item.participant_index"
                class="person-split-item"
              >
                <div class="split-item-info">
                  <span class="split-item-desc">{{ item.description }}</span>
                  <span class="split-item-meta">{{ item.date }} • Total: {{ formatCurrency(item.total_amount) }}</span>
                </div>
                <div class="split-item-action">
                  <span class="split-item-share tabular-nums">{{ formatCurrency(item.person_share) }}</span>
                  <button
                    @click="toggleSettle(item.transaction_id, item.participant_index)"
                    :class="['settle-btn', item.is_settled ? 'settle-btn--paid' : '']"
                  >
                    {{ item.is_settled ? 'Paid ✓' : 'Mark Settled' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: Transactions Table -->
      <div v-else-if="activeTab === 'transactions'" class="table-card">
        <table class="txn-table">
          <thead>
            <tr class="table-head-row">
              <th class="th">Date</th>
              <th class="th">Expense</th>
              <th class="th">Account</th>
              <th class="th">Total Amount</th>
              <th class="th">Participants Breakdown</th>
              <th class="th th--right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="txn in splitTransactions"
              :key="txn.id"
              class="table-row"
            >
              <td class="td td--muted">{{ formatDate(txn.date) }}</td>
              <td class="td font-medium">{{ txn.description || 'Expense' }}</td>
              <td class="td td--secondary">{{ txn.account?.name || '—' }}</td>
              <td class="td font-bold tabular-nums">{{ formatCurrency(txn.amount) }}</td>
              <td class="td">
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="(p, idx) in getParticipants(txn)"
                    :key="idx"
                    class="participant-tag"
                    :class="p.is_settled ? 'participant-tag--settled' : ''"
                  >
                    {{ p.name }} ({{ formatCurrency(p.amount) }})
                    <button
                      @click="toggleSettle(txn.id, idx)"
                      class="tag-settle-toggle"
                      :title="p.is_settled ? 'Mark Unpaid' : 'Mark Paid'"
                    >
                      {{ p.is_settled ? '✓' : '○' }}
                    </button>
                  </span>
                </div>
              </td>
              <td class="td td--right">
                <button @click="openModalForEdit(txn)" class="action-btn action-btn--edit" title="Edit">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <!-- Transaction Modal for adding/editing -->
    <TransactionModal
      v-if="showModal"
      :transaction="editingTxn"
      @close="showModal = false"
      @saved="onSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import axios from 'axios'
import { formatCurrency } from '../utils/currency'
import TransactionModal from './TransactionModal.vue'

const emit = defineEmits(['refresh'])
const toast = inject('toast')

const loading = ref(true)
const activeTab = ref('people')
const summary = ref({})
const people = ref([])
const splitTransactions = ref([])
const showModal = ref(false)
const editingTxn = ref(null)

function formatDate(d) {
  return new Date(d).toLocaleDateString('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

function getParticipants(txn) {
  return txn.split_data?.participants || []
}

async function fetchSplitsData() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/splits/summary')
    summary.value = data.summary
    people.value = data.people
    splitTransactions.value = data.transactions
  } catch (e) {
    toast('Failed to load splits summary', 'error')
  } finally {
    loading.value = false
  }
}

async function toggleSettle(transactionId, participantIndex) {
  try {
    await axios.patch(`/api/transactions/${transactionId}/split-participant/${participantIndex}/toggle-settle`)
    toast('Settlement updated')
    fetchSplitsData()
    emit('refresh')
  } catch (e) {
    toast('Failed to update settlement', 'error')
  }
}

function openTransactionModal() {
  editingTxn.value = null
  showModal.value = true
}

function openModalForEdit(txn) {
  editingTxn.value = txn
  showModal.value = true
}

function onSaved() {
  showModal.value = false
  fetchSplitsData()
  emit('refresh')
}

onMounted(() => {
  fetchSplitsData()
})
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
  .view-header {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
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

/* ── Hero Card ────────────────────────────────────────────── */
.summary-hero-card {
  padding: 1.5rem;
  border-radius: 1.25rem;
  background: var(--bg-glass);
  border: 1px solid var(--border);
}

.summary-hero-content {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
@media (min-width: 640px) {
  .summary-hero-content {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}

.summary-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}

.summary-value {
  font-size: 2rem;
  font-weight: 800;
  margin: 0.25rem 0 0;
  letter-spacing: -0.02em;
}

.summary-subtext {
  font-size: 0.75rem;
  color: var(--text-muted);
  margin: 0.25rem 0 0;
}

.summary-breakdown {
  display: flex;
  gap: 1rem;
}

.summary-box {
  background: var(--bg-surface-2);
  padding: 0.75rem 1rem;
  border-radius: 0.875rem;
  border: 1px solid var(--border);
  display: flex;
  flex-direction: column;
}

.box-label {
  font-size: 0.6875rem;
  color: var(--text-muted);
  font-weight: 500;
}

.box-value {
  font-size: 1.125rem;
  font-weight: 700;
  margin-top: 0.15rem;
}

/* ── View Tabs ────────────────────────────────────────────── */
.view-tabs {
  display: flex;
  gap: 0.5rem;
}

.tab-btn {
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  font-size: 0.8125rem;
  font-weight: 600;
  background: var(--bg-surface);
  border: 1px solid var(--border);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.15s;
}

.tab-btn--active {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}

/* ── People Grid ──────────────────────────────────────────── */
.people-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.25rem;
}
@media (min-width: 768px) {
  .people-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.person-card {
  padding: 1.25rem;
  border-radius: 1.25rem;
  background: var(--bg-surface);
  border: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.person-card-header {
  display: flex;
  align-items: center;
  gap: 0.875rem;
}

.person-avatar {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 999px;
  background: linear-gradient(135deg, var(--primary), #818cf8);
  color: #fff;
  font-weight: 700;
  font-size: 1.125rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.person-info {
  flex: 1;
  min-width: 0;
}

.person-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.person-subtext {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.person-total {
  font-size: 1.125rem;
  font-weight: 800;
  display: block;
}

.person-splits-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  border-top: 1px dashed var(--border);
  padding-top: 0.75rem;
}

.person-split-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 0.75rem;
  border-radius: 0.625rem;
  background: var(--bg-surface-2);
}

.split-item-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.split-item-desc {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-primary);
  truncate: true;
}

.split-item-meta {
  font-size: 0.6875rem;
  color: var(--text-muted);
}

.split-item-action {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.split-item-share {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--text-primary);
}

.settle-btn {
  font-size: 0.6875rem;
  font-weight: 700;
  padding: 0.25rem 0.625rem;
  border-radius: 0.5rem;
  border: 1px solid var(--primary);
  background: var(--primary-light);
  color: var(--primary);
  cursor: pointer;
  transition: all 0.15s;
}

.settle-btn--paid {
  border-color: var(--success);
  background: var(--success-light);
  color: var(--success);
}

/* ── Table Card ───────────────────────────────────────────── */
.table-card {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 1.25rem;
  overflow: hidden;
}

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
.th--right {
  text-align: right;
}

.table-row {
  border-bottom: 1px solid var(--border);
}
.table-row:last-child {
  border-bottom: none;
}

.td {
  padding: 0.75rem 1.25rem;
  font-size: 0.8125rem;
  color: var(--text-primary);
}

.participant-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.2rem 0.5rem;
  border-radius: 999px;
  font-size: 0.6875rem;
  font-weight: 600;
  background: var(--bg-surface-2);
  border: 1px solid var(--border-strong);
  color: var(--text-secondary);
}

.participant-tag--settled {
  background: var(--success-light);
  border-color: var(--success);
  color: var(--success);
}

.tag-settle-toggle {
  background: transparent;
  border: none;
  cursor: pointer;
  font-weight: 800;
  font-size: 0.75rem;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.empty-icon {
  width: 3rem;
  height: 3rem;
  color: var(--text-muted);
  margin-bottom: 1rem;
}

.empty-text {
  font-size: 0.875rem;
  color: var(--text-muted);
}
</style>

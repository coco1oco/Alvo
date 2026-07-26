<template>
  <div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Dashboard</h1>
        <p class="dashboard-subtitle">{{ currentMonth }} overview</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="dashboard-loading">
      <div class="spinner"></div>
    </div>

    <div v-else class="dashboard-body">

      <!-- ── Stat Cards ────────────────────────────────────────── -->
      <div class="stat-grid">

        <!-- Net Worth -->
        <div class="glass-card stat-card">
          <div class="stat-header">
            <span class="stat-label">Net Worth</span>
            <div class="stat-icon">
              <svg class="stat-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
          </div>
          <p class="stat-value tabular-nums" :class="data.total_balance >= 0 ? 'amount-positive' : 'amount-negative'">
            {{ formatCurrency(data.total_balance) }}
          </p>
          <p class="stat-note">Across all accounts</p>
        </div>

        <!-- Income -->
        <div class="glass-card stat-card">
          <div class="stat-header">
            <span class="stat-label">Income</span>
            <div class="stat-icon stat-icon--success">
              <svg class="stat-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
          <p class="stat-value amount-positive tabular-nums">{{ formatCurrency(data.monthly_income) }}</p>
          <p class="stat-note">This month</p>
        </div>

        <!-- Expenses -->
        <div class="glass-card stat-card">
          <div class="stat-header">
            <span class="stat-label">Expenses</span>
            <div class="stat-icon stat-icon--danger">
              <svg class="stat-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 17H5m0 0V9m0 8l8-8 4 4 6-6" />
              </svg>
            </div>
          </div>
          <p class="stat-value amount-negative tabular-nums">{{ formatCurrency(data.monthly_expense) }}</p>
          <p class="stat-note">This month</p>
        </div>

        <!-- Net -->
        <div class="glass-card stat-card">
          <div class="stat-header">
            <span class="stat-label">Net</span>
            <div class="stat-icon">
              <svg class="stat-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
          <p class="stat-value tabular-nums" :class="data.net >= 0 ? 'amount-positive' : 'amount-negative'">
            {{ formatCurrency(data.net) }}
          </p>
          <p class="stat-note">Income − Expenses</p>
        </div>
      </div>

      <!-- ── Charts Row ────────────────────────────────────────── -->
      <div class="charts-grid">
        <!-- Cashflow Bar Chart -->
        <div class="glass-card chart-card chart-card--wide">
          <h2 class="panel-title">6-Month Cashflow</h2>
          <div class="chart-wrapper">
            <canvas ref="cashflowChart"></canvas>
          </div>
        </div>

        <!-- Expense Donut -->
        <div class="glass-card chart-card">
          <h2 class="panel-title">Expenses by Category</h2>
          <div v-if="data.expense_by_category?.length" class="chart-wrapper">
            <canvas ref="donutChart"></canvas>
          </div>
          <div v-else class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
            </svg>
            <p class="empty-text">No expenses this month</p>
          </div>
        </div>
      </div>

      <!-- ── Bottom Row ────────────────────────────────────────── -->
      <div class="bottom-grid">

        <!-- Accounts -->
        <div class="glass-card panel">
          <h2 class="panel-title">Accounts</h2>
          <div class="account-list">
            <div v-for="acc in data.accounts" :key="acc.id" class="account-row">
              <div class="account-icon" :style="{ backgroundColor: acc.color + '20', color: acc.color }">
                <img v-if="acc.icon && acc.icon !== 'wallet'" :src="`/bankIcons/${acc.icon}`" class="w-5 h-5 object-contain" />
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
              <div class="account-info">
                <p class="account-name">{{ acc.name }}</p>
                <p class="account-type">{{ acc.type.replace('_', ' ') }}</p>
              </div>
              <span class="account-balance tabular-nums"
                :class="acc.balance >= 0 ? 'amount-positive' : 'amount-negative'">
                {{ formatCurrency(acc.balance) }}
              </span>
            </div>
            <div v-if="!data.accounts?.length" class="empty-state empty-state--sm">
              <p class="empty-text">No accounts yet</p>
            </div>
          </div>
        </div>

        <!-- Budget Status -->
        <div class="glass-card panel">
          <h2 class="panel-title">Budget Status</h2>
          <div class="budget-list">
            <div v-for="b in data.budgets" :key="b.id" class="budget-item">
              <div class="budget-row">
                <span class="budget-name">{{ b.category }}</span>
                <span class="budget-pct"
                  :class="b.percentage > 100 ? 'badge badge-danger' : b.percentage > 80 ? 'badge badge-warning' : 'badge badge-success'">
                  {{ b.percentage }}%
                </span>
              </div>
              <div class="budget-bar-track">
                <div
                  class="budget-bar-fill"
                  :class="b.percentage > 100 ? 'bar-danger' : b.percentage > 80 ? 'bar-warning' : 'bar-success'"
                  :style="{ width: Math.min(b.percentage, 100) + '%' }"
                ></div>
              </div>
              <p class="budget-amounts tabular-nums">
                {{ formatCurrency(b.spent) }} / {{ formatCurrency(b.budget) }}
              </p>
            </div>
            <div v-if="!data.budgets?.length" class="empty-state empty-state--sm">
              <p class="empty-text">No budgets set this month</p>
            </div>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="glass-card panel">
          <h2 class="panel-title">Recent Transactions</h2>
          <div class="txn-list">
            <div v-for="txn in data.recent_transactions" :key="txn.id" class="txn-row">
              <div class="txn-icon"
                :class="txn.type === 'income' ? 'txn-icon--income' : txn.type === 'expense' ? 'txn-icon--expense' : 'txn-icon--transfer'">
                <!-- Income arrow up -->
                <svg v-if="txn.type === 'income'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
                <!-- Expense arrow down -->
                <svg v-else-if="txn.type === 'expense'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
                <!-- Transfer arrows -->
                <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
              </div>
              <div class="txn-info">
                <p class="txn-desc">{{ txn.description || txn.category?.name || 'Transfer' }}</p>
                <p class="txn-account">{{ txn.account?.name }}</p>
              </div>
              <span class="txn-amount tabular-nums"
                :class="txn.type === 'income' ? 'amount-positive' : txn.type === 'expense' ? 'amount-negative' : 'amount-transfer'">
                {{ txn.type === 'income' ? '+' : txn.type === 'expense' ? '−' : '' }}{{ formatCurrency(txn.amount) }}
              </span>
              <div class="row-actions">
                <button @click="deleteTransaction(txn)" class="action-btn action-btn--delete" title="Delete">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
            <div v-if="!data.recent_transactions?.length" class="empty-state empty-state--sm">
              <p class="empty-text">No transactions yet</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch, inject } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const toast = inject('toast')

const props = defineProps({
  isDark: {
    type: Boolean,
    default: false,
  },
})

const loading       = ref(true)
const data          = ref({})
const cashflowChart = ref(null)
const donutChart    = ref(null)
const currentMonth  = new Date().toLocaleString('default', { month: 'long', year: 'numeric' })

let cashflowInstance = null
let donutInstance    = null

function formatCurrency(val) {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
  }).format(val || 0)
}

function chartColors() {
  return props.isDark
    ? { tick: '#8896B0', grid: 'rgba(255,255,255,0.05)', legend: '#8896B0' }
    : { tick: '#5A6478', grid: 'rgba(0,0,0,0.05)',  legend: '#5A6478' }
}

async function fetchDashboard() {
  try {
    const { data: d } = await axios.get('/api/dashboard')
    data.value = d
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
  await nextTick()
  renderCharts()
}

async function deleteTransaction(txn) {
  if (!confirm('Delete this transaction? This will reverse the balance change.')) return
  try {
    await axios.delete(`/api/transactions/${txn.id}`)
    if (toast) toast('Transaction deleted')
    fetchDashboard()
  } catch (e) {
    if (toast) toast('Delete failed', 'error')
  }
}

function renderCharts() {
  const colors = chartColors()

  // Cashflow Bar Chart
  if (cashflowChart.value && data.value.cashflow?.length) {
    if (cashflowInstance) cashflowInstance.destroy()
    cashflowInstance = new Chart(cashflowChart.value, {
      type: 'bar',
      data: {
        labels: data.value.cashflow.map(c => c.label),
        datasets: [
          {
            label: 'Income',
            data: data.value.cashflow.map(c => c.income),
            backgroundColor: 'rgba(18, 161, 121, 0.7)',
            borderColor: '#12A179',
            borderWidth: 1,
            borderRadius: 5,
          },
          {
            label: 'Expenses',
            data: data.value.cashflow.map(c => c.expense),
            backgroundColor: 'rgba(224, 49, 49, 0.7)',
            borderColor: '#E03131',
            borderWidth: 1,
            borderRadius: 5,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: { color: colors.legend, font: { size: 11, family: 'Geist, Inter, sans-serif' } },
          },
        },
        scales: {
          x: {
            ticks: { color: colors.tick, font: { size: 10 } },
            grid: { color: colors.grid },
          },
          y: {
            ticks: {
              color: colors.tick,
              font: { size: 10 },
              callback: v => '₱' + v.toLocaleString(),
            },
            grid: { color: colors.grid },
          },
        },
      },
    })
  }

  // Expense Doughnut Chart
  if (donutChart.value && data.value.expense_by_category?.length) {
    if (donutInstance) donutInstance.destroy()
    donutInstance = new Chart(donutChart.value, {
      type: 'doughnut',
      data: {
        labels: data.value.expense_by_category.map(c => c.category),
        datasets: [{
          data: data.value.expense_by_category.map(c => c.total),
          backgroundColor: data.value.expense_by_category.map(c => c.color + 'cc'),
          borderColor: data.value.expense_by_category.map(c => c.color),
          borderWidth: 1,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: colors.legend,
              font: { size: 10, family: 'Geist, Inter, sans-serif' },
              padding: 10,
            },
          },
        },
      },
    })
  }
}

// Re-render charts when theme changes
watch(() => props.isDark, async () => {
  await nextTick()
  renderCharts()
})

onMounted(fetchDashboard)
</script>

<style scoped>
/* ── Layout ───────────────────────────────────────────────── */
.dashboard {
  padding: 2rem;
  max-width: 1280px;
  margin: 0 auto;
}

.dashboard-header {
  margin-bottom: 2rem;
}

.dashboard-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
  letter-spacing: -0.01em;
}

.dashboard-subtitle {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0.25rem 0 0;
}

.dashboard-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 16rem;
}

.dashboard-body {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ── Stat Cards ───────────────────────────────────────────── */
.stat-grid {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 1rem;
}

@media (min-width: 640px)  { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1280px) { .stat-grid { grid-template-columns: repeat(4, 1fr); } }

.stat-card {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.stat-icon {
  width: 36px;
  height: 36px;
  border-radius: 0.625rem;
  background-color: var(--primary-light);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon--success {
  background-color: var(--success-light);
  color: var(--success);
}

.stat-icon--danger {
  background-color: var(--danger-light);
  color: var(--danger);
}

.stat-icon-svg {
  width: 18px;
  height: 18px;
}

.stat-value {
  font-size: 1.625rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
  line-height: 1.2;
}

.stat-note {
  font-size: 0.6875rem;
  color: var(--text-muted);
  margin: 0;
}

/* ── Charts ───────────────────────────────────────────────── */
.charts-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 1280px) {
  .charts-grid { grid-template-columns: 2fr 1fr; }
}

.chart-card {
  display: flex;
  flex-direction: column;
}

.chart-wrapper {
  position: relative;
  height: 220px;
  margin-top: 0.5rem;
}

.chart-wrapper canvas {
  width: 100% !important;
}

/* ── Bottom Grid ──────────────────────────────────────────── */
.bottom-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 1280px) {
  .bottom-grid { grid-template-columns: repeat(3, 1fr); }
}

.panel {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* Override hover lift on data panels — not interactive */
.panel:hover {
  transform: none;
}

.panel-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 0.25rem;
}

/* ── Accounts ─────────────────────────────────────────────── */
.account-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.account-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.account-icon {
  width: 32px;
  height: 32px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.object-contain { object-fit: contain; }

.account-info {
  flex: 1;
  min-width: 0;
}

.account-name {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.account-type {
  font-size: 0.6875rem;
  color: var(--text-muted);
  margin: 0;
  text-transform: capitalize;
}

.account-balance {
  font-size: 0.8125rem;
  font-weight: 700;
  flex-shrink: 0;
}

/* ── Budgets ──────────────────────────────────────────────── */
.budget-list {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.budget-item { display: flex; flex-direction: column; gap: 0.25rem; }

.budget-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.budget-name {
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.budget-bar-track {
  width: 100%;
  height: 6px;
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

.budget-amounts {
  font-size: 0.6875rem;
  color: var(--text-muted);
  margin: 0;
}

/* ── Recent Transactions ──────────────────────────────────── */
.txn-list {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.txn-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.txn-row:hover .row-actions { opacity: 1; }

.row-actions {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  opacity: 0;
  transition: opacity 0.15s;
  margin-left: 0.25rem;
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
.action-btn--delete:hover { background-color: var(--danger-light); color: var(--danger); }

.txn-icon {
  width: 28px;
  height: 28px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.txn-icon--income   { background-color: var(--success-light); color: var(--success); }
.txn-icon--expense  { background-color: var(--danger-light);  color: var(--danger); }
.txn-icon--transfer { background-color: var(--primary-light); color: var(--primary); }

.txn-info {
  flex: 1;
  min-width: 0;
}

.txn-desc {
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-primary);
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.txn-account {
  font-size: 0.6875rem;
  color: var(--text-muted);
  margin: 0;
}

.txn-amount {
  font-size: 0.8125rem;
  font-weight: 700;
  flex-shrink: 0;
}

.amount-transfer {
  color: var(--primary);
}

/* ── Empty States ─────────────────────────────────────────── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 12rem;
  gap: 0.5rem;
  color: var(--text-muted);
}

.empty-state--sm {
  height: auto;
  padding: 1rem 0;
  text-align: center;
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
</style>

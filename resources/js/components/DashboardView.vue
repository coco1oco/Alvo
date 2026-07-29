<template>
  <div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Dashboard</h1>
        <p class="dashboard-subtitle">{{ currentMonth }} overview</p>
      </div>
      <button @click="showTransactionModal = true" class="btn-primary flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
        Add Transaction
      </button>
    </div>

    <!-- Skeleton Loading -->
    <div v-if="loading" class="dashboard-body">
      <!-- Stat Cards skeleton -->
      <div class="stat-grid">
        <div class="glass-card stat-card stat-card--hero sk-card">
          <div class="sk-row" style="margin-bottom:0.75rem">
            <div class="skeleton" style="width:5rem;height:0.75rem;border-radius:0.375rem"></div>
            <div class="skeleton" style="width:3.5rem;height:1.25rem;border-radius:999px"></div>
          </div>
          <div class="skeleton" style="width:70%;height:2.25rem;border-radius:0.5rem;margin-bottom:0.5rem"></div>
          <div class="skeleton" style="width:100%;height:1.75rem;border-radius:0.375rem;margin-bottom:0.5rem"></div>
          <div class="skeleton" style="width:8rem;height:0.75rem;border-radius:0.375rem"></div>
        </div>
        <div v-for="i in 3" :key="i" class="glass-card stat-card sk-card">
          <div class="sk-row" style="margin-bottom:0.75rem">
            <div class="skeleton" style="width:4rem;height:0.75rem;border-radius:0.375rem"></div>
            <div class="skeleton" style="width:2rem;height:2rem;border-radius:0.625rem"></div>
          </div>
          <div class="skeleton" style="width:65%;height:1.75rem;border-radius:0.5rem;margin-bottom:0.5rem"></div>
          <div class="skeleton" style="width:5rem;height:0.75rem;border-radius:0.375rem"></div>
        </div>
      </div>

      <!-- Charts skeleton -->
      <div class="charts-grid">
        <div class="glass-card chart-card chart-card--wide sk-card">
          <div class="skeleton" style="width:9rem;height:1rem;border-radius:0.375rem;margin-bottom:1.25rem"></div>
          <div class="skeleton" style="width:100%;height:10rem;border-radius:0.75rem"></div>
        </div>
        <div class="glass-card chart-card sk-card">
          <div class="skeleton" style="width:9rem;height:1rem;border-radius:0.375rem;margin-bottom:1.25rem"></div>
          <div class="skeleton" style="width:9rem;height:9rem;border-radius:50%;margin:0 auto"></div>
        </div>
      </div>

      <!-- Bottom panels skeleton -->
      <div class="bottom-grid">
        <div v-for="panel in 3" :key="panel" class="glass-card panel sk-card">
          <div class="skeleton" style="width:8rem;height:1rem;border-radius:0.375rem;margin-bottom:1.25rem"></div>
          <div v-for="row in 4" :key="row" class="sk-row" style="margin-bottom:0.875rem;align-items:center">
            <div class="skeleton" style="width:2rem;height:2rem;border-radius:0.625rem;flex-shrink:0"></div>
            <div style="flex:1;display:flex;flex-direction:column;gap:0.3rem">
              <div class="skeleton" style="width:70%;height:0.75rem;border-radius:0.375rem"></div>
              <div class="skeleton" style="width:45%;height:0.625rem;border-radius:0.375rem"></div>
            </div>
            <div class="skeleton" style="width:4rem;height:0.875rem;border-radius:0.375rem"></div>
          </div>
        </div>
      </div>
    </div>


    <div v-else class="dashboard-body">

      <!-- ── Stat Cards ────────────────────────────────────────── -->
      <div class="stat-grid">

        <!-- Net Worth (Hero Stat) -->
        <div class="glass-card stat-card stat-card--hero">
          <div class="stat-header">
            <span class="stat-label">Net Worth</span>
            <span class="badge badge-success">+8.3%</span>
          </div>
          <p class="stat-value tabular-nums" :class="data.total_balance >= 0 ? 'amount-positive' : 'amount-negative'">
            {{ formatCurrency(displayNetWorth) }}
          </p>
          <div class="hero-sparkline">
            <!-- Simple CSS sparkline representation or placeholder -->
            <svg viewBox="0 0 100 20" class="sparkline" preserveAspectRatio="none">
              <path d="M0,15 C20,10 30,20 40,5 C50,-5 70,15 80,5 C90,-5 100,5 100,5" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
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
          <p class="stat-value amount-positive tabular-nums">{{ formatCurrency(displayIncome) }}</p>
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
          <p class="stat-value amount-negative tabular-nums">{{ formatCurrency(displayExpenses) }}</p>
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
            {{ formatCurrency(displayNet) }}
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
                <span class="budget-name">{{ b.category?.name || b.category }}</span>
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
            <div v-for="txn in (data.recent_transactions || []).slice(0, 4)" :key="txn.id" class="txn-row">
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
            <div v-else-if="data.recent_transactions?.length > 4" class="mt-2 pt-2 border-t border-border flex justify-center">
              <button @click="emit('navigate', 'transactions')" class="btn-ghost text-xs w-full justify-center">
                View all transactions →
              </button>
            </div>
          </div>
        </div>
        <!-- Upcoming Bills Panel -->
        <div v-if="data.upcoming_bills?.length" class="glass-card card-panel col-span-full">
          <div class="card-panel-header">
            <div>
              <h2 class="card-panel-title flex items-center gap-2">
                <svg class="w-4 h-4 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Upcoming Bills (Next 7 Days)
              </h2>
            </div>
            <button @click="emit('navigate', 'recurring')" class="btn-ghost text-xs">Manage Schedules →</button>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
            <div v-for="bill in data.upcoming_bills" :key="bill.id" class="flex items-center justify-between p-3 rounded-xl bg-bg-surface-2/60 border border-border/50">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0"
                     :style="{ backgroundColor: (bill.category?.color || '#6366f1') + '25', color: bill.category?.color || '#6366f1' }">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                </div>
                <div>
                  <h4 class="font-bold text-primary-color text-xs">{{ bill.description || bill.category?.name || 'Upcoming Bill' }}</h4>
                  <p class="text-[11px] text-muted">Due {{ formatDate(bill.next_due_date) }} · {{ bill.account?.name }}</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <span class="font-bold text-xs amount-negative tabular-nums">−{{ formatCurrency(bill.amount) }}</span>
                <button @click="processRecurringBill(bill)" class="btn-primary text-xs py-1 px-2.5">Log Now</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Savings Goals Panel -->
        <div v-if="data.goals?.length" class="glass-card card-panel col-span-full">
          <div class="card-panel-header">
            <div>
              <h2 class="card-panel-title flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Savings Goals
              </h2>
            </div>
            <button @click="emit('navigate', 'goals')" class="btn-ghost text-xs">View all goals →</button>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mt-3">
            <div v-for="goal in data.goals" :key="goal.id" class="p-3 rounded-xl bg-bg-surface-2/60 border border-border/50 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-1.5">
                  <h4 class="font-bold text-xs text-primary-color truncate">{{ goal.name }}</h4>
                  <span class="text-[10px] font-bold" :class="goal.progress_percentage >= 100 ? 'text-success' : 'text-primary'">
                    {{ goal.progress_percentage }}%
                  </span>
                </div>
                <p class="text-[11px] text-muted mb-2">
                  <strong class="text-primary-color">{{ formatCurrency(goal.effective_current_amount) }}</strong> / {{ formatCurrency(goal.target_amount) }}
                </p>
                <div class="overall-progress-bar" style="height: 4px;">
                  <div class="overall-progress-fill" :style="{ width: goal.progress_percentage + '%', backgroundColor: goal.color || '#6366f1' }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Subscriptions Panel -->
        <div v-if="data.subscriptions?.length" class="glass-card card-panel col-span-full">
          <div class="card-panel-header">
            <div>
              <h2 class="card-panel-title flex items-center gap-2">
                <svg class="w-4 h-4 text-secondary-color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Active Subscriptions
              </h2>
            </div>
            <button @click="emit('navigate', 'subscriptions')" class="btn-ghost text-xs">Manage Subscriptions →</button>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 mt-3">
            <div v-for="sub in data.subscriptions" :key="sub.id" class="p-3 rounded-xl bg-bg-surface-2/60 border border-border/50 flex items-center justify-between">
              <div class="flex items-center gap-2.5 overflow-hidden">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0"
                     :style="{ backgroundColor: (sub.color || '#6366f1') + '25', color: sub.color || '#6366f1' }">
                  <span class="font-bold text-[10px] uppercase">{{ sub.name.substring(0, 2) }}</span>
                </div>
                <div class="truncate">
                  <h4 class="font-bold text-xs text-primary-color truncate">{{ sub.name }}</h4>
                  <p class="text-[10px] text-muted">Renews {{ formatDate(sub.next_renewal_date) }}</p>
                </div>
              </div>
              <span class="font-bold text-xs amount-negative tabular-nums flex-shrink-0 ml-2">−{{ formatCurrency(sub.amount) }}</span>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- Quick Add Transaction Modal -->
    <TransactionModal
      v-if="showTransactionModal"
      @close="showTransactionModal = false"
      @saved="onTransactionSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch, inject, readonly } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import TransactionModal from './TransactionModal.vue'
import { formatCurrency, getCurrencySymbol } from '../utils/currency'

Chart.register(...registerables)

const emit = defineEmits(['navigate', 'refresh'])
const toast = inject('toast')

const props = defineProps({
  isDark: {
    type: Boolean,
    default: false,
  },
})

const loading       = ref(true)
const data          = ref({})
const showTransactionModal = ref(false)
const cashflowChart = ref(null)
const donutChart    = ref(null)
const currentMonth  = new Date().toLocaleString('default', { month: 'long', year: 'numeric' })

let cashflowInstance = null
let donutInstance    = null

// ── Count-up animated display values ─────────────────────────
const displayNetWorth = ref(0)
const displayIncome   = ref(0)
const displayExpenses = ref(0)
const displayNet      = ref(0)

function easeOutQuart(t) {
  return 1 - Math.pow(1 - t, 4)
}

function animateTo(refVal, target, duration = 900) {
  const start     = performance.now()
  const from      = refVal.value
  const delta     = target - from
  function step(now) {
    const elapsed  = now - start
    const progress = Math.min(elapsed / duration, 1)
    refVal.value   = from + delta * easeOutQuart(progress)
    if (progress < 1) requestAnimationFrame(step)
    else refVal.value = target
  }
  requestAnimationFrame(step)
}

// Duration scales with magnitude: ₱100 → ~300ms, ₱10K → ~600ms, ₱1M → ~900ms
function durationFor(target) {
  const abs = Math.abs(target || 0)
  if (abs < 10) return 250
  const log = Math.log10(abs)
  return Math.round(Math.min(900, Math.max(250, log * 150)))
}

function triggerCountUp(d) {
  animateTo(displayNetWorth, d.total_balance  || 0, durationFor(d.total_balance))
  animateTo(displayIncome,   d.monthly_income  || 0, durationFor(d.monthly_income))
  animateTo(displayExpenses, d.monthly_expense || 0, durationFor(d.monthly_expense))
  animateTo(displayNet,      d.net             || 0, durationFor(d.net))
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
    await nextTick()
    triggerCountUp(d)
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
      type: 'line',
      data: {
        labels: data.value.cashflow.map(c => c.label),
        datasets: [
          {
            label: 'Income',
            data: data.value.cashflow.map(c => c.income),
            backgroundColor: (context) => {
              const ctx = context.chart.ctx;
              const gradient = ctx.createLinearGradient(0, 0, 0, 300);
              gradient.addColorStop(0, 'rgba(120, 140, 93, 0.4)');
              gradient.addColorStop(1, 'rgba(120, 140, 93, 0)');
              return gradient;
            },
            borderColor: 'var(--success)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointRadius: 0,
            pointHoverRadius: 4,
          },
          {
            label: 'Expenses',
            data: data.value.cashflow.map(c => c.expense),
            backgroundColor: (context) => {
              const ctx = context.chart.ctx;
              const gradient = ctx.createLinearGradient(0, 0, 0, 300);
              gradient.addColorStop(0, 'rgba(224, 49, 49, 0.4)');
              gradient.addColorStop(1, 'rgba(224, 49, 49, 0)');
              return gradient;
            },
            borderColor: 'var(--danger)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointRadius: 0,
            pointHoverRadius: 4,
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
            grid: { display: false },
          },
          y: {
            ticks: {
              color: colors.tick,
              font: { size: 10 },
              callback: v => getCurrencySymbol() + v.toLocaleString(),
            },
            grid: { color: colors.grid },
          },
        },
        animation: {
          y: {
            duration: 1000,
            easing: 'easeOutQuart',
          }
        },
        interaction: {
          mode: 'index',
          intersect: false,
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

// Update chart colors when theme changes without re-triggering animations
watch(() => props.isDark, async () => {
  await nextTick()
  const colors = chartColors()
  
  if (cashflowInstance) {
    cashflowInstance.options.plugins.legend.labels.color = colors.legend
    cashflowInstance.options.scales.x.ticks.color = colors.tick
    if (cashflowInstance.options.scales.x.grid) cashflowInstance.options.scales.x.grid.color = colors.grid
    cashflowInstance.options.scales.y.ticks.color = colors.tick
    if (cashflowInstance.options.scales.y.grid) cashflowInstance.options.scales.y.grid.color = colors.grid
    cashflowInstance.update('none')
  }
  
  if (donutInstance) {
    donutInstance.options.plugins.legend.labels.color = colors.legend
    donutInstance.update('none')
  }
})

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric' })
}

async function processRecurringBill(bill) {
  try {
    await axios.post(`/api/recurring-transactions/${bill.id}/process`)
    toast('Bill logged successfully')
    fetchDashboard()
    emit('refresh')
  } catch (e) {
    toast(e.response?.data?.message || 'Failed to log bill', 'error')
  }
}

function onTransactionSaved() {
  showTransactionModal.value = false
  fetchDashboard()
  emit('refresh')
}

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
  display: flex;
  align-items: center;
  justify-content: space-between;
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

.sk-card {
  pointer-events: none;
}
.sk-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
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
  gap: 1.5rem;
}

@media (min-width: 640px)  { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1280px) { .stat-grid { grid-template-columns: repeat(4, 1fr); } }

.stat-card {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stat-card--hero {
  grid-column: 1 / -1;
  background-color: var(--bg-surface);
  background: linear-gradient(145deg, var(--bg-surface-2) 0%, var(--bg-glass) 100%);
  border: 1px solid var(--border-strong);
  padding: 2rem;
  gap: 0.75rem;
  isolation: isolate;
  transform: translateZ(0);
}

.stat-card--hero .stat-value {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.hero-sparkline {
  height: 30px;
  width: 150px;
  margin: 0.5rem 0;
}

.sparkline {
  width: 100%;
  height: 100%;
  overflow: visible;
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

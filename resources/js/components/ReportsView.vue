<template>
  <div class="view">
    <!-- Header -->
    <div class="view-header">
      <div>
        <h1 class="view-title">Reports & Insights</h1>
        <p class="view-subtitle">In-depth financial analytics, spending heatmaps, and cashflow trends</p>
      </div>
      <button @click="exportCSV" class="btn-primary flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        Export CSV
      </button>
    </div>

    <!-- Date Range Filter Pills -->
    <div class="glass-card p-4 flex flex-wrap items-center justify-between gap-3">
      <div class="flex flex-wrap gap-2">
        <button
          v-for="p in periods"
          :key="p.value"
          @click="selectPeriod(p.value)"
          class="period-pill"
          :class="{ 'period-pill--active': currentPeriod === p.value }"
        >
          {{ p.label }}
        </button>
      </div>

      <!-- Custom Date Inputs -->
      <div v-if="currentPeriod === 'custom'" class="flex items-center gap-2">
        <input v-model="customFrom" type="date" @change="fetchReports" class="input-field py-1 text-xs" />
        <span class="text-xs text-muted">-</span>
        <input v-model="customTo" type="date" @change="fetchReports" class="input-field py-1 text-xs" />
      </div>
    </div>

    <!-- Skeleton Loading -->
    <template v-if="loading">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div v-for="i in 4" :key="i" class="glass-card p-5 sk-card">
          <div class="skeleton mb-2" style="width:6rem;height:0.75rem;border-radius:0.375rem"></div>
          <div class="skeleton" style="width:70%;height:1.75rem;border-radius:0.5rem"></div>
        </div>
      </div>
    </template>

    <template v-else>
      <!-- KPI Metric Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Net Cashflow -->
        <div class="glass-card stat-card">
          <span class="stat-label">Net Cashflow</span>
          <h2 class="stat-value tabular-nums" :class="kpis.net_cashflow >= 0 ? 'amount-positive' : 'amount-negative'">
            {{ formatCurrency(kpis.net_cashflow) }}
          </h2>
          <p class="stat-subtext">Income − Expenses</p>
        </div>

        <!-- Savings Rate -->
        <div class="glass-card stat-card">
          <div class="flex justify-between items-start">
            <span class="stat-label">Savings Rate</span>
            <span class="badge" :class="savingsRateBadgeClass(kpis.savings_rate)">
              {{ savingsRateText(kpis.savings_rate) }}
            </span>
          </div>
          <h2 class="stat-value tabular-nums text-primary-color">{{ kpis.savings_rate }}%</h2>
          <p class="stat-subtext">Percentage of income saved</p>
        </div>

        <!-- Avg Daily Spend -->
        <div class="glass-card stat-card">
          <span class="stat-label">Avg. Daily Spend</span>
          <h2 class="stat-value tabular-nums amount-negative">{{ formatCurrency(kpis.avg_daily_spend) }}</h2>
          <p class="stat-subtext">Over {{ kpis.days_count }} day{{ kpis.days_count === 1 ? '' : 's' }}</p>
        </div>

        <!-- Income vs Expenses Total -->
        <div class="glass-card stat-card">
          <span class="stat-label">Total Outflow</span>
          <h2 class="stat-value tabular-nums amount-negative">{{ formatCurrency(kpis.total_expense) }}</h2>
          <p class="stat-subtext">Inflow: <span class="amount-positive font-bold">{{ formatCurrency(kpis.total_income) }}</span></p>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Cashflow Trend Chart (2 cols) -->
        <div class="glass-card chart-card lg:col-span-2">
          <div class="chart-header">
            <div>
              <h3 class="chart-title">Income vs Expense Multi-Month Trend</h3>
              <p class="chart-subtitle">Historical breakdown across last 6 months</p>
            </div>
          </div>
          <div class="chart-wrapper">
            <canvas ref="trendChartCanvas"></canvas>
          </div>
        </div>

        <!-- Category Distribution Donut Chart (1 col) -->
        <div class="glass-card chart-card">
          <div class="chart-header">
            <div>
              <h3 class="chart-title">Expense Allocation</h3>
              <p class="chart-subtitle">By spending category</p>
            </div>
          </div>
          <div class="chart-wrapper flex items-center justify-center">
            <canvas ref="categoryChartCanvas"></canvas>
          </div>
        </div>
      </div>

      <!-- Category Ranked Breakdown Table & Daily Heatmap Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Category Ranked Table -->
        <div class="glass-card p-5">
          <h3 class="font-bold text-sm text-primary-color mb-3">Top Spending Categories</h3>
          <div v-if="categoryReport.length" class="space-y-3">
            <div v-for="cat in categoryReport" :key="cat.id" class="flex flex-col gap-1">
              <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                  <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: cat.color }"></span>
                  <span class="font-bold text-primary-color">{{ cat.name }}</span>
                  <span class="text-muted text-[11px]">({{ cat.count }} txns)</span>
                </div>
                <span class="font-bold tabular-nums text-primary-color">{{ formatCurrency(cat.amount) }} ({{ cat.percentage }}%)</span>
              </div>
              <div class="overall-progress-bar" style="height: 6px;">
                <div class="overall-progress-fill" :style="{ width: cat.percentage + '%', backgroundColor: cat.color }"></div>
              </div>
            </div>
          </div>
          <div v-else class="text-xs text-muted py-6 text-center">No expense transactions recorded in this period.</div>
        </div>

        <!-- Daily Spending Heatmap -->
        <div class="glass-card p-5">
          <h3 class="font-bold text-sm text-primary-color mb-1">Daily Spending Intensity</h3>
          <p class="text-xs text-muted mb-4">Calendar heat mapping for daily outflows</p>

          <div class="heatmap-grid">
            <div
              v-for="day in heatmapDays"
              :key="day.dateStr"
              class="heatmap-cell group relative"
              :style="{ backgroundColor: getHeatmapColor(day.amount) }"
            >
              <!-- Tooltip -->
              <div class="heatmap-tooltip">
                <span class="font-bold block">{{ day.dateStr }}</span>
                <span>{{ day.amount > 0 ? formatCurrency(day.amount) : 'No spend' }}</span>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-between text-[11px] text-muted mt-4">
            <span>Less spend</span>
            <div class="flex items-center gap-1">
              <span class="w-3 h-3 rounded" style="background-color: var(--bg-surface-2)"></span>
              <span class="w-3 h-3 rounded" style="background-color: rgba(99, 102, 241, 0.25)"></span>
              <span class="w-3 h-3 rounded" style="background-color: rgba(99, 102, 241, 0.55)"></span>
              <span class="w-3 h-3 rounded" style="background-color: rgba(239, 68, 68, 0.85)"></span>
            </div>
            <span>More spend</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick, inject } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
  isDark: { type: Boolean, default: false }
})

const toast = inject('toast')
const loading = ref(true)
const currentPeriod = ref('this_month')
const customFrom = ref('')
const customTo = ref('')

const kpis = ref({})
const cashflowTrend = ref([])
const categoryReport = ref([])
const dailyExpenses = ref({})

const trendChartCanvas = ref(null)
const categoryChartCanvas = ref(null)
let trendChartInstance = null
let categoryChartInstance = null

const periods = [
  { label: 'This Month', value: 'this_month' },
  { label: 'Last Month', value: 'last_month' },
  { label: 'Last 3 Months', value: 'last_3_months' },
  { label: 'This Year', value: 'this_year' },
  { label: 'Custom Range', value: 'custom' },
]

function formatCurrency(val) {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 }).format(val || 0)
}

function savingsRateText(rate) {
  if (rate >= 30) return 'Excellent'
  if (rate >= 15) return 'Healthy'
  if (rate > 0) return 'Needs Focus'
  return 'Negative'
}

function savingsRateBadgeClass(rate) {
  if (rate >= 30) return 'badge-success'
  if (rate >= 15) return 'badge-primary'
  return 'badge-danger'
}

function selectPeriod(p) {
  currentPeriod.value = p
  fetchReports()
}

const maxHeatmapAmount = computed(() => {
  const values = Object.values(dailyExpenses.value)
  return values.length ? Math.max(...values, 1) : 1
})

const heatmapDays = computed(() => {
  const days = []
  const now = new Date()
  const year = now.getFullYear()
  const month = now.getMonth()
  const daysInMonth = new Date(year, month + 1, 0).getDate()

  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
    days.push({
      dateStr,
      amount: dailyExpenses.value[dateStr] ? parseFloat(dailyExpenses.value[dateStr]) : 0
    })
  }
  return days
})

function getHeatmapColor(amount) {
  if (!amount || amount <= 0) return 'var(--bg-surface-2)'
  const ratio = amount / maxHeatmapAmount.value
  if (ratio < 0.25) return 'rgba(99, 102, 241, 0.25)'
  if (ratio < 0.6) return 'rgba(99, 102, 241, 0.55)'
  return 'rgba(239, 68, 68, 0.85)'
}

async function fetchReports() {
  loading.value = true
  try {
    const params = { period: currentPeriod.value }
    if (currentPeriod.value === 'custom') {
      params.from = customFrom.value
      params.to = customTo.value
    }
    const res = await axios.get('/api/reports', { params })
    kpis.value = res.data.kpis
    cashflowTrend.value = res.data.cashflow_trend
    categoryReport.value = res.data.category_report
    dailyExpenses.value = res.data.daily_expenses

    await nextTick()
    renderCharts()
  } catch (e) {
    toast('Failed to load reports data', 'error')
  } finally {
    loading.value = false
  }
}

function renderCharts() {
  const isDark = props.isDark
  const textColor = isDark ? '#94a3b8' : '#64748b'
  const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)'

  // Trend Chart
  if (trendChartCanvas.value) {
    if (trendChartInstance) trendChartInstance.destroy()

    const labels = cashflowTrend.value.map(item => item.month)
    const incomeData = cashflowTrend.value.map(item => item.income)
    const expenseData = cashflowTrend.value.map(item => item.expense)

    trendChartInstance = new Chart(trendChartCanvas.value, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label: 'Income', data: incomeData, backgroundColor: '#10b981', borderRadius: 6 },
          { label: 'Expenses', data: expenseData, backgroundColor: '#ef4444', borderRadius: 6 },
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { labels: { color: textColor } }
        },
        scales: {
          x: { ticks: { color: textColor }, grid: { color: gridColor } },
          y: { ticks: { color: textColor }, grid: { color: gridColor } }
        }
      }
    })
  }

  // Category Donut Chart
  if (categoryChartCanvas.value && categoryReport.value.length) {
    if (categoryChartInstance) categoryChartInstance.destroy()

    const labels = categoryReport.value.map(c => c.name)
    const data = categoryReport.value.map(c => c.amount)
    const colors = categoryReport.value.map(c => c.color)

    categoryChartInstance = new Chart(categoryChartCanvas.value, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{ data, backgroundColor: colors, borderWidth: 0 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom', labels: { color: textColor, boxWidth: 10 } }
        },
        cutout: '70%'
      }
    })
  }
}

function exportCSV() {
  let url = '/api/transactions/export'
  if (currentPeriod.value === 'custom' && customFrom.value && customTo.value) {
    url += `?from=${customFrom.value}&to=${customTo.value}`
  }
  window.open(url, '_blank')
}

watch(() => props.isDark, () => renderCharts())

onMounted(fetchReports)
</script>

<style scoped>
.view { padding: 2rem; max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem; }
.view-header { display: flex; align-items: center; justify-content: space-between; }
.view-title { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 0; }
.view-subtitle { font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem; }

/* ── Period Pills ─────────────────────────────────────────── */
.period-pill { padding: 0.4rem 0.875rem; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 600; background: var(--bg-surface-2); color: var(--text-muted); border: 1px solid transparent; cursor: pointer; transition: all 0.15s; }
.period-pill:hover { color: var(--text-primary); }
.period-pill--active { background: var(--primary); color: #ffffff; border-color: var(--primary); }

/* ── Stat & Chart Cards ───────────────────────────────────── */
.stat-card { padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between; }
.stat-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.stat-value { font-size: 1.75rem; font-weight: 800; margin: 0.25rem 0; line-height: 1.1; }
.stat-subtext { font-size: 0.75rem; color: var(--text-muted); }

.chart-card { padding: 1.25rem; display: flex; flex-direction: column; height: 320px; }
.chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.chart-title { font-size: 0.9375rem; font-weight: 700; color: var(--text-primary); margin: 0; }
.chart-subtitle { font-size: 0.75rem; color: var(--text-muted); margin: 0; }
.chart-wrapper { flex: 1; position: relative; min-height: 0; }

.overall-progress-bar { width: 100%; background-color: var(--bg-surface-2); border-radius: 999px; overflow: hidden; }
.overall-progress-fill { height: 100%; border-radius: 999px; transition: width 0.6s ease-out; }

/* ── Daily Heatmap Grid ───────────────────────────────────── */
.heatmap-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.375rem; }
.heatmap-cell { aspect-ratio: 1; border-radius: 0.375rem; cursor: pointer; transition: transform 0.15s; }
.heatmap-cell:hover { transform: scale(1.15); z-index: 10; }

.heatmap-tooltip { display: none; position: absolute; bottom: 125%; left: 50%; transform: translateX(-50%); background: var(--bg-surface); border: 1px solid var(--border-strong); padding: 0.375rem 0.5rem; border-radius: 0.5rem; font-size: 0.6875rem; color: var(--text-primary); white-space: nowrap; box-shadow: var(--shadow-md); pointer-events: none; }
.heatmap-cell:hover .heatmap-tooltip { display: block; }
</style>

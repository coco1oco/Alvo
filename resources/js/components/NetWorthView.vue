<template>
  <div class="view print:p-0">
    <!-- Header -->
    <div class="view-header print:hidden">
      <div>
        <h1 class="view-title flex items-center gap-2">
          <BuildingLibraryIcon class="w-6 h-6 text-primary" />
          Net Worth Statement
        </h1>
        <p class="view-subtitle">Official balance sheet breakdown of your assets and liabilities</p>
      </div>

      <div class="flex items-center gap-2">
        <button @click="printStatement" class="btn-secondary text-xs">
          <PrinterIcon class="w-4 h-4 mr-1 inline" />
          Print Statement
        </button>
      </div>
    </div>

    <!-- Printable Header (Visible only when printing) -->
    <div class="hidden print:block mb-6 border-b pb-4">
      <h1 class="text-2xl font-bold text-black">Alvo Finance Manager — Net Worth Statement</h1>
      <p class="text-xs text-gray-500">Generated on {{ currentDate }}</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state glass-card">
      <div class="spinner"></div>
      <p class="text-xs text-muted mt-2">Computing balance sheet metrics...</p>
    </div>

    <template v-else>
      <!-- Hero Solvency Summary Card -->
      <section class="glass-card hero-card">
        <div class="hero-header">
          <div>
            <span class="hero-label">Total Net Worth</span>
            <h2 class="hero-value tabular-nums" :class="data.net_worth >= 0 ? 'text-primary-color' : 'amount-negative'">
              {{ formatCurrency(data.net_worth) }}
            </h2>
          </div>

          <div class="hero-badge-container">
            <span class="solvency-badge" :class="solvencyBadgeClass">
              <ShieldCheckIcon class="w-4 h-4 mr-1 inline" />
              {{ solvencyStatusText }}
            </span>
            <span v-if="data.total_liabilities > 0" class="solvency-ratio-text text-xs text-muted mt-1 block">
              Assets cover liabilities {{ data.solvency_ratio }}x
            </span>
          </div>
        </div>

        <!-- Asset vs Liability Dual Meter -->
        <div class="meter-container">
          <div class="meter-labels">
            <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-500">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
              Assets: {{ formatCurrency(data.total_assets) }} ({{ assetsPercentage }}%)
            </div>
            <div class="flex items-center gap-1.5 text-xs font-semibold text-rose-500">
              <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
              Liabilities: {{ formatCurrency(data.total_liabilities) }} ({{ liabilitiesPercentage }}%)
            </div>
          </div>
          <div class="meter-bar-track">
            <div class="meter-bar-fill bg-emerald-500" :style="{ width: assetsPercentage + '%' }"></div>
            <div class="meter-bar-fill bg-rose-500" :style="{ width: liabilitiesPercentage + '%' }"></div>
          </div>
        </div>
      </section>

      <!-- Historical Net Worth Trend Chart -->
      <section class="glass-card chart-card">
        <div class="card-header flex items-center justify-between mb-4">
          <h3 class="card-title flex items-center gap-2">
            <ArrowTrendingUpIcon class="w-5 h-5 text-primary" />
            Historical Net Worth Trajectory (6 Months)
          </h3>
        </div>
        <div class="chart-container">
          <canvas ref="chartCanvas"></canvas>
        </div>
      </section>

      <!-- Balance Sheet 2-Column Ledger Grid -->
      <div class="balance-sheet-grid">
        <!-- ── ASSETS COLUMN ──────────────────────────────────────── -->
        <section class="glass-card ledger-section">
          <div class="ledger-header text-emerald-500 border-emerald-500/20">
            <h3 class="font-bold flex items-center gap-2">
              <WalletIcon class="w-5 h-5" />
              Assets (What You Own)
            </h3>
            <span class="font-extrabold text-lg tabular-nums">{{ formatCurrency(data.total_assets) }}</span>
          </div>

          <div v-if="!data.assets.length" class="empty-substate">
            <p class="text-xs text-muted">No asset accounts recorded.</p>
          </div>

          <div v-else class="ledger-list space-y-3">
            <div v-for="asset in data.assets" :key="asset.id" class="ledger-item">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs flex-shrink-0"
                       :style="{ backgroundColor: (asset.color || '#10b981') + '20', color: asset.color || '#10b981' }">
                    <WalletIcon class="w-4 h-4" />
                  </div>
                  <div>
                    <h4 class="text-sm font-semibold text-primary-color">{{ asset.name }}</h4>
                    <span class="text-[11px] text-muted capitalize">{{ asset.type }} Account</span>
                  </div>
                </div>
                <div class="text-right">
                  <span class="text-sm font-bold amount-positive tabular-nums">{{ formatCurrency(asset.balance) }}</span>
                  <p class="text-[11px] text-muted">
                    {{ data.total_assets > 0 ? Math.round((asset.balance / data.total_assets) * 100) : 0 }}% of assets
                  </p>
                </div>
              </div>
              <div class="proportion-track">
                <div class="proportion-fill bg-emerald-500"
                     :style="{ width: (data.total_assets > 0 ? (asset.balance / data.total_assets) * 100 : 0) + '%' }">
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- ── LIABILITIES COLUMN ─────────────────────────────────── -->
        <section class="glass-card ledger-section">
          <div class="ledger-header text-rose-500 border-rose-500/20">
            <h3 class="font-bold flex items-center gap-2">
              <CreditCardIcon class="w-5 h-5" />
              Liabilities (What You Owe)
            </h3>
            <span class="font-extrabold text-lg tabular-nums">{{ formatCurrency(data.total_liabilities) }}</span>
          </div>

          <div v-if="!data.liabilities.length" class="empty-substate">
            <ShieldCheckIcon class="w-8 h-8 text-emerald-500/60 mb-1" />
            <p class="text-xs text-muted font-semibold">Zero Outstanding Liabilities!</p>
            <p class="text-[11px] text-muted">You currently have no credit card debt.</p>
          </div>

          <div v-else class="ledger-list space-y-3">
            <div v-for="liability in data.liabilities" :key="liability.id" class="ledger-item">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs flex-shrink-0"
                       :style="{ backgroundColor: (liability.color || '#ef4444') + '20', color: liability.color || '#ef4444' }">
                    <CreditCardIcon class="w-4 h-4" />
                  </div>
                  <div>
                    <h4 class="text-sm font-semibold text-primary-color">{{ liability.name }}</h4>
                    <span class="text-[11px] text-muted">Limit: {{ formatCurrency(liability.credit_limit) }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <span class="text-sm font-bold amount-negative tabular-nums">−{{ formatCurrency(liability.owed) }}</span>
                  <p class="text-[11px] text-muted">{{ liability.utilization }}% limit used</p>
                </div>
              </div>
              <div class="proportion-track">
                <div class="proportion-fill bg-rose-500" :style="{ width: Math.min(liability.utilization, 100) + '%' }"></div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick, inject } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import { 
  BuildingLibraryIcon, PrinterIcon, ShieldCheckIcon, ArrowTrendingUpIcon, WalletIcon, CreditCardIcon
} from '@heroicons/vue/24/outline'
import { formatCurrency, getCurrencySymbol } from '../utils/currency'

Chart.register(...registerables)

const props = defineProps({
  isDark: { type: Boolean, default: false }
})

const loading = ref(true)
const data = ref({
  net_worth: 0,
  total_assets: 0,
  total_liabilities: 0,
  solvency_ratio: 0,
  assets: [],
  liabilities: [],
  historical_trend: []
})

const chartCanvas = ref(null)
let chartInstance = null

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
})

const totalVolume = computed(() => data.value.total_assets + data.value.total_liabilities)

const assetsPercentage = computed(() => {
  if (!totalVolume.value) return 100
  return Math.round((data.value.total_assets / totalVolume.value) * 100)
})

const liabilitiesPercentage = computed(() => {
  if (!totalVolume.value) return 0
  return 100 - assetsPercentage.value
})

const solvencyStatusText = computed(() => {
  if (data.value.total_liabilities === 0 && data.value.total_assets > 0) return 'Debt Free'
  if (data.value.net_worth < 0) return 'Negative Net Worth'
  if (data.value.solvency_ratio >= 3) return 'Strong Solvency'
  if (data.value.solvency_ratio >= 1.5) return 'Healthy Solvency'
  return 'Debt Heavy'
})

const solvencyBadgeClass = computed(() => {
  if (data.value.total_liabilities === 0 || data.value.solvency_ratio >= 3) return 'badge-success'
  if (data.value.solvency_ratio >= 1.5) return 'badge-info'
  return 'badge-warning'
})

async function fetchNetWorth() {
  loading.value = true
  try {
    const res = await axios.get('/api/net-worth')
    data.value = res.data
    await nextTick()
    renderTrendChart()
  } catch (e) {
    console.error('Failed to load Net Worth Statement', e)
  } finally {
    loading.value = false
  }
}

function renderTrendChart() {
  if (!chartCanvas.value || !data.value.historical_trend.length) return

  if (chartInstance) {
    chartInstance.destroy()
  }

  const ctx = chartCanvas.value.getContext('2d')
  const labels = data.value.historical_trend.map(t => t.month)
  const values = data.value.historical_trend.map(t => t.net_worth)

  const isDark = props.isDark
  const primaryColor = isDark ? '#6366f1' : '#4f46e5'
  const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)'
  const textColor = isDark ? '#8896B0' : '#5A6478'

  const gradient = ctx.createLinearGradient(0, 0, 0, 240)
  gradient.addColorStop(0, isDark ? 'rgba(99, 102, 241, 0.35)' : 'rgba(79, 70, 229, 0.25)')
  gradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)')

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Net Worth',
          data: values,
          borderColor: primaryColor,
          borderWidth: 3,
          backgroundColor: gradient,
          fill: true,
          tension: 0.35,
          pointBackgroundColor: primaryColor,
          pointRadius: 4,
          pointHoverRadius: 6,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: context => ` Net Worth: ${formatCurrency(context.raw)}`
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: textColor, font: { size: 11 } }
        },
        y: {
          grid: { color: gridColor },
          ticks: {
            color: textColor,
            font: { size: 10 },
            callback: v => getCurrencySymbol() + v.toLocaleString()
          }
        }
      }
    }
  })
}

function printStatement() {
  window.print()
}

watch(() => props.isDark, () => {
  renderTrendChart()
})

onMounted(fetchNetWorth)
</script>

<style scoped>
.view {
  padding: 2rem;
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.view-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.view-subtitle {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-top: 0.25rem;
}

/* Loading */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
}

/* Hero Card */
.hero-card {
  padding: 1.75rem;
  border-radius: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.hero-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.hero-label {
  font-size: 0.8125rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}

.hero-value {
  font-size: 2.5rem;
  font-weight: 800;
  margin-top: 0.25rem;
  line-height: 1.1;
}

.solvency-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.375rem 0.875rem;
  border-radius: 9999px;
  font-size: 0.8125rem;
  font-weight: 700;
}

/* Meter */
.meter-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.meter-labels {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.meter-bar-track {
  height: 10px;
  border-radius: 9999px;
  background: var(--surface-2);
  display: flex;
  overflow: hidden;
}

.meter-bar-fill {
  height: 100%;
  transition: width 0.5s ease;
}

/* Chart Card */
.chart-card {
  padding: 1.5rem;
  border-radius: 1.25rem;
}

.chart-container {
  position: relative;
  height: 240px;
  width: 100%;
}

.card-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

/* Balance Sheet Grid */
.balance-sheet-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .balance-sheet-grid {
    grid-template-columns: 1fr;
  }
}

.ledger-section {
  padding: 1.5rem;
  border-radius: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.ledger-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid;
}

.empty-substate {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2.5rem 1rem;
  text-align: center;
}

.ledger-item {
  padding: 0.875rem;
  border-radius: 0.875rem;
  background: var(--surface-2);
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.proportion-track {
  height: 4px;
  border-radius: 9999px;
  background: var(--border);
  overflow: hidden;
}

.proportion-fill {
  height: 100%;
  border-radius: 9999px;
  transition: width 0.4s ease;
}
</style>

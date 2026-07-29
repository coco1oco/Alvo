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

    <!-- Skeleton Loading -->
    <template v-if="loading">
      <!-- Summary Hero skeleton -->
      <div class="glass-card summary-hero-card mb-6 sk-card">
        <div class="summary-hero-content">
          <div style="display:flex;flex-direction:column;gap:0.5rem">
            <div class="skeleton" style="width:6rem;height:0.75rem;border-radius:0.375rem"></div>
            <div class="skeleton" style="width:11rem;height:2rem;border-radius:0.5rem"></div>
            <div class="skeleton" style="width:9rem;height:0.75rem;border-radius:0.375rem"></div>
          </div>
          <div class="summary-breakdown">
            <div class="summary-box" style="gap:0.4rem;display:flex;flex-direction:column">
              <div class="skeleton" style="width:3.5rem;height:0.75rem;border-radius:0.375rem"></div>
              <div class="skeleton" style="width:6rem;height:1.125rem;border-radius:0.375rem"></div>
            </div>
            <div class="summary-box" style="gap:0.4rem;display:flex;flex-direction:column">
              <div class="skeleton" style="width:4.5rem;height:0.75rem;border-radius:0.375rem"></div>
              <div class="skeleton" style="width:6rem;height:1.125rem;border-radius:0.375rem"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Account groups skeleton -->
      <div class="account-groups-container sk-card">
        <div v-for="group in 2" :key="group" class="account-group">
          <div class="skeleton" style="width:7rem;height:0.875rem;border-radius:0.375rem;margin-bottom:0.75rem"></div>
          <div class="accounts-grid">
            <div v-for="card in 2" :key="card" class="glass-card account-card" style="display:flex;flex-direction:column;gap:0.875rem">
              <!-- Top row: icon + name -->
              <div style="display:flex;align-items:center;gap:0.75rem">
                <div class="skeleton" style="width:2.5rem;height:2.5rem;border-radius:0.75rem;flex-shrink:0"></div>
                <div style="flex:1;display:flex;flex-direction:column;gap:0.375rem">
                  <div class="skeleton" style="width:60%;height:0.875rem;border-radius:0.375rem"></div>
                  <div class="skeleton" style="width:40%;height:0.75rem;border-radius:0.375rem"></div>
                </div>
              </div>
              <!-- Balance line -->
              <div class="skeleton" style="width:55%;height:1.5rem;border-radius:0.5rem"></div>
              <!-- Bar -->
              <div class="skeleton" style="width:100%;height:0.375rem;border-radius:999px"></div>
            </div>
          </div>
        </div>
      </div>
    </template>


    <template v-else>
      <!-- Total Liquid Balance Summary Banner -->
      <div class="glass-card summary-hero-card mb-6">
        <div class="summary-hero-content">
          <div>
            <span class="summary-label">Total Liquid Balance</span>
            <h2 class="summary-value tabular-nums">{{ formatCurrency(totalAssets) }}</h2>
            <p class="summary-subtext">Across {{ activeAccounts.length }} active account{{ activeAccounts.length === 1 ? '' : 's' }}</p>
          </div>
          <div class="summary-breakdown">
            <div class="summary-box">
              <span class="box-label">Bank & Savings</span>
              <span class="box-value amount-positive tabular-nums">{{ formatCurrency(totalBankBalance) }}</span>
            </div>
            <div class="summary-box">
              <span class="box-label">Cash & Wallets</span>
              <span class="box-value amount-positive tabular-nums">{{ formatCurrency(totalCashBalance) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Account Category Filter Pills -->
      <div class="flex items-center gap-2 mb-6 flex-wrap">
        <button
          v-for="filter in accountFilterOptions"
          :key="filter.value"
          @click="selectedAccountFilter = filter.value"
          class="account-filter-pill"
          :class="{ 'account-filter-pill--active': selectedAccountFilter === filter.value }"
        >
          {{ filter.label }}
        </button>
      </div>

      <div class="account-groups-container">
        <!-- Grouped Accounts -->
        <div v-for="(groupAccounts, groupName) in filteredGroupedAccounts" :key="groupName" class="account-group" v-show="groupAccounts.length">
          <h2 class="group-title">{{ groupName }}</h2>
          <div class="accounts-grid">
            <div
              v-for="acc in groupAccounts"
              :key="acc.id"
              class="glass-card account-card account-brand-card group relative"
              :style="{
                background: `linear-gradient(140deg, color-mix(in srgb, ${acc.color} 24%, var(--bg-surface)) 0%, var(--bg-surface) 100%)`,
                borderColor: `color-mix(in srgb, ${acc.color} 40%, transparent)`
              }"
            >
              <div class="account-glow" :style="{ backgroundColor: acc.color }"></div>

              <!-- Top Row: Identity + Menu -->
              <div class="account-top flex items-center justify-between gap-2">
                <div class="account-identity flex items-center gap-2.5 min-w-0 flex-1">
                  <div class="account-icon shadow-sm flex-shrink-0" :style="{ backgroundColor: acc.color + '30', color: acc.color }">
                    <img v-if="acc.icon && acc.icon !== 'wallet'" :src="`/bankIcons/${acc.icon}`" class="w-6 h-6 object-contain" />
                    <component v-else :is="accountIcon(acc.type)" class="w-5 h-5" />
                  </div>
                  <div class="min-w-0 flex-1">
                    <h3 class="account-name font-bold text-sm text-primary-color truncate">{{ acc.name }}</h3>
                    <p class="account-subtag text-xs text-muted font-medium truncate">{{ formatAccountSubtag(acc) }}</p>
                  </div>
                </div>

                <!-- ••• Menu Button Dropdown -->
                <div class="relative flex-shrink-0">
                  <button @click.stop="toggleAccountMenu(acc.id)" class="menu-dots-btn" title="Account Options">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                  </button>

                  <div v-if="activeMenuId === acc.id" class="account-dropdown-menu" @click.stop>
                    <button @click="openTransaction(acc); activeMenuId = null" class="dropdown-item">
                      <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      Add Transaction
                    </button>
                    <!-- Pay Bill shortcut — credit cards only -->
                    <button v-if="acc.type === 'credit_card'" @click="openPayBill(acc); activeMenuId = null" class="dropdown-item dropdown-item--paybill">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                      </svg>
                      Pay Bill
                    </button>
                    <button @click="openModal(acc); activeMenuId = null" class="dropdown-item">
                      <svg class="w-4 h-4 text-secondary-color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Edit Account
                    </button>
                    <div class="dropdown-divider"></div>
                    <button @click="toggleArchive(acc); activeMenuId = null" class="dropdown-item dropdown-item--danger">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                      </svg>
                      Archive Account
                    </button>
                  </div>
                </div>
              </div>

              <!-- Balance Section -->
              <div class="account-balance-section mt-4">
                <p class="balance-label uppercase text-[10px] tracking-wider font-bold text-muted">
                  {{ acc.type === 'credit_card' ? 'Used Credit' : 'Balance' }}
                </p>
                <p class="balance-value tabular-nums font-extrabold text-2xl" :class="acc.balance >= 0 ? 'amount-positive' : 'amount-negative'">
                  {{ formatCurrency(acc.balance) }}
                </p>
              </div>

              <!-- Credit Card Gauge OR Proportion Bar -->
              <div v-if="acc.type === 'credit_card'" class="credit-gauge-section mt-3">
                <div class="flex justify-between text-xs font-semibold mb-1"
                     :class="getCreditUtilizationClass(acc)">
                  <span>{{ getCreditUsedPct(acc) }}% used</span>
                  <span>{{ formatCurrency(getCreditAvailable(acc)) }} left</span>
                </div>
                <div class="proportion-bar-wrapper">
                  <div class="proportion-bar"
                       :class="getCreditBarClass(acc)"
                       :style="{ width: getCreditUsedPct(acc) + '%' }"></div>
                </div>
                <!-- High utilization warning -->
                <div v-if="getCreditUsedPct(acc) >= 75" class="credit-warning-badge mt-1.5">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                  </svg>
                  {{ getCreditUsedPct(acc) >= 100 ? 'Limit Reached' : 'High Utilization' }}
                </div>
                <!-- Billing info -->
                <div v-if="acc.credit_limit" class="credit-limit-display mt-1">
                  Limit: {{ formatCurrency(acc.credit_limit) }}
                  <template v-if="acc.billing_cycle_day"> &nbsp;·&nbsp; Cuts on the {{ ordinal(acc.billing_cycle_day) }}</template>
                  <template v-if="acc.due_date_day"> &nbsp;·&nbsp; Due on the {{ ordinal(acc.due_date_day) }}</template>
                </div>
              </div>
              <div v-else class="proportion-bar-wrapper mt-4">
                <div class="proportion-bar" :style="{ width: getProportion(acc) + '%', backgroundColor: acc.color }"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!activeAccounts.length" class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
          <p class="empty-text">No accounts yet. Add one to get started!</p>
        </div>

        <!-- Archived Section -->
        <div v-if="archivedAccounts.length" class="archived-section mt-6 archived-section-mt">
          <button @click="showArchived = !showArchived" class="toggle-archived-btn">
            {{ showArchived ? 'Hide' : 'Show' }} Archived Accounts ({{ archivedAccounts.length }})
          </button>
          
          <div v-if="showArchived" class="accounts-grid archived-grid-wrap">
            <div v-for="acc in archivedAccounts" :key="acc.id" class="glass-card account-card group archived-card">
              <div class="account-top">
                <div class="account-identity">
                  <div class="account-icon archived-icon">
                    <svg class="w-5 h-5 text-muted-color" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                  </div>
                  <div>
                    <h3 class="account-name text-secondary-color">{{ acc.name }}</h3>
                    <p class="account-type">{{ acc.type.replace('_', ' ') }} (Archived)</p>
                  </div>
                </div>
                <div class="account-actions">
                  <button @click="toggleArchive(acc)" class="action-btn action-btn--add" title="Restore">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                  </button>
                  <button @click="deleteAccount(acc)" class="action-btn action-btn--delete" title="Delete Permanently">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Liquid Balance Footer -->
      <div v-if="activeAccounts.length" class="glass-card net-worth-card">
        <div class="nw-breakdown">
          <div class="nw-item">
            <p class="nw-label">Bank & Savings</p>
            <p class="nw-value amount-positive">{{ formatCurrency(totalBankBalance) }}</p>
          </div>
          <div class="nw-operator">+</div>
          <div class="nw-item">
            <p class="nw-label">Cash & Wallets</p>
            <p class="nw-value amount-positive">{{ formatCurrency(totalCashBalance) }}</p>
          </div>
          <div class="nw-operator">=</div>
          <div class="nw-item nw-total">
            <p class="nw-label text-primary-color">Total Liquid Balance</p>
            <p class="nw-value text-primary">
              {{ formatCurrency(totalAssets) }}
            </p>
          </div>
        </div>
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
              <option value="other">Other</option>
            </select>
          </div>
          <div v-if="!editingAcc">
            <label class="label">Starting Balance</label>
            <div class="input-with-prefix">
              <span class="input-prefix">{{ getCurrencySymbol() }}</span>
              <input v-model="form.balance" type="number" step="0.01" min="0" placeholder="0.00" class="input-field input-field--prefix" />
            </div>
          </div>
          <div>
            <div class="color-picker-header">
              <label class="label label-no-mb">Color</label>
              <button type="button" @click="showColors = !showColors" class="color-toggle-btn">
                {{ showColors ? 'Hide' : 'Customize' }}
              </button>
            </div>
            <div v-show="showColors" class="color-picker">
              <button
                type="button"
                v-for="c in displayPalette"
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

    <!-- Quick Add Transaction Modal -->
    <TransactionModal 
      v-if="showTransactionModal" 
      :defaultAccountId="quickTransactionAccountId" 
      @close="showTransactionModal = false" 
      @saved="onTransactionSaved" 
    />

    <!-- Pay Bill Modal — pre-filled transfer TO the credit card -->
    <TransactionModal
      v-if="showPayBillModal"
      :payBillTargetId="payBillTargetId"
      :payBillAmount="payBillAmount"
      @close="showPayBillModal = false"
      @saved="onPayBillSaved"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, inject, defineComponent, h, watch } from 'vue'
import axios from 'axios'
import TransactionModal from './TransactionModal.vue'
import { formatCurrency, getCurrencySymbol } from '../utils/currency'

const emit       = defineEmits(['refresh'])
const toast      = inject('toast')
const loading    = ref(true)
const accounts   = ref([])
const showModal  = ref(false)
const editingAcc = ref(null)
const saving     = ref(false)
const formError  = ref('')
const showColors = ref(false)
const showArchived = ref(false)

const showTransactionModal = ref(false)
const quickTransactionAccountId = ref('')
const activeMenuId = ref(null)

// Pay Bill modal state
const showPayBillModal = ref(false)
const payBillTargetId  = ref(null)
const payBillAmount    = ref(0)

function toggleAccountMenu(id) {
  activeMenuId.value = activeMenuId.value === id ? null : id
}

function formatAccountSubtag(acc) {
  const typeMap = {
    bank: 'Debit • PHP',
    savings: 'Savings • PHP',
    credit_card: 'Credit • PHP',
    cash: 'Cash • PHP',
    other: 'Wallet • PHP'
  }
  return typeMap[acc.type] || `${acc.type.replace('_', ' ')} • PHP`
}

function getCreditLimit(acc) {
  return parseFloat(acc.credit_limit) || 0
}

function getCreditUsedPct(acc) {
  const limit = getCreditLimit(acc)
  if (!limit) return 0
  const used = Math.max(parseFloat(acc.balance) || 0, 0)
  return Math.min(Math.round((used / limit) * 100), 100)
}

function getCreditAvailable(acc) {
  const limit = getCreditLimit(acc)
  const used  = Math.max(parseFloat(acc.balance) || 0, 0)
  return Math.max(limit - used, 0)
}

function getCreditUtilizationClass(acc) {
  const pct = getCreditUsedPct(acc)
  if (pct >= 75) return 'text-danger'
  if (pct >= 30) return 'text-warning'
  return 'text-muted'
}

function getCreditBarClass(acc) {
  const pct = getCreditUsedPct(acc)
  if (pct >= 75) return 'proportion-bar--danger'
  if (pct >= 30) return 'proportion-bar--warning'
  return 'proportion-bar--safe'
}

function ordinal(n) {
  const s = ['th','st','nd','rd'], v = n % 100
  return n + (s[(v - 20) % 10] || s[v] || s[0])
}

const colorPalette = ['#6366f1','#8b5cf6','#ec4899','#ef4444','#f97316','#f59e0b','#22c55e','#10b981','#06b6d4','#3b82f6','#64748b','#0ea5e9']
const bankKeywords = [
  { keys: ['asia united bank', 'asia united', 'aub'], brand: 'AUB', icon: 'asia-united-bank.png', color: '#00478f' },
  { keys: ['banco de oro', 'bdo'], brand: 'BDO', icon: 'bdo-unibank.svg', color: '#002C77' },
  { keys: ['bank of the philippine islands', 'bank of the philippine', 'bpi'], brand: 'BPI', icon: 'bpi.svg', color: '#B11116' },
  { keys: ['china bank', 'chinabank'], brand: 'Chinabank', icon: 'chinabank.png', color: '#B00000' },
  { keys: ['cimb bank', 'cimb'], brand: 'CIMB', icon: 'cimb-logo.svg', color: '#7E002B' },
  { keys: ['east west bank', 'eastwest bank', 'east west', 'eastwest'], brand: 'EastWest', icon: 'eastwest.png', color: '#4D148C' },
  { keys: ['go tyme bank', 'gotyme bank', 'go tyme', 'gotyme'], brand: 'GoTyme', icon: 'go-tyme-bank.svg', color: '#00B1FF' },
  { keys: ['hsbc'], brand: 'HSBC', icon: 'hsbc.svg', color: '#db0011' },
  { keys: ['land bank', 'landbank'], brand: 'Landbank', icon: 'landbank.svg', color: '#005934' },
  { keys: ['mari bank', 'maribank'], brand: 'MariBank', icon: 'mari-bank-philippines.svg', color: '#FF5C00' },
  { keys: ['paymaya', 'maya bank', 'maya'], brand: 'Maya', icon: 'maya.svg', color: '#06C068' },
  { keys: ['metro bank', 'metrobank'], brand: 'Metrobank', icon: 'metrobank.svg', color: '#0033A0' },
  { keys: ['pay pal', 'paypal'], brand: 'PayPal', icon: 'pay-pal-logo-alternative.svg', color: '#003087' },
  { keys: ['philippine national bank', 'philippine national', 'pnb'], brand: 'PNB', icon: 'philippine-national-bank.svg', color: '#003A70' },
  { keys: ['ps bank', 'psbank'], brand: 'PSBank', icon: 'psbank-official.svg', color: '#005BAA' },
  { keys: ['rizal commercial banking', 'rizal commercial', 'rcbc'], brand: 'RCBC', icon: 'rizal-commercial-banking.svg', color: '#0038A8' },
  { keys: ['salmon'], brand: 'Salmon', icon: 'salmon.jpeg', color: '#FF7F50' },
  { keys: ['security bank', 'securitybank'], brand: 'Security Bank', icon: 'security-bank-corporation.svg', color: '#003F98' },
  { keys: ['tonik bank', 'tonik'], brand: 'Tonik', icon: 'tonik.svg', color: '#512A7C' },
  { keys: ['union bank of the philippines', 'union bank', 'unionbank'], brand: 'UnionBank', icon: 'union-bank-of-the-philippines.svg', color: '#ED6322' },
  { keys: ['transferwise', 'wise'], brand: 'Wise', icon: 'wise.svg', color: '#00B9FF' }
]
const form = reactive({ name: '', type: 'cash', balance: '', credit_limit: '', billing_cycle_day: '', due_date_day: '', color: '#6366f1', icon: 'wallet' })

const activeAccounts = computed(() => accounts.value.filter(a => !a.is_archived && a.type !== 'credit_card'))
const archivedAccounts = computed(() => accounts.value.filter(a => a.is_archived && a.type !== 'credit_card'))

const selectedAccountFilter = ref('all')
const accountFilterOptions = [
  { label: 'All Accounts', value: 'all' },
  { label: 'Bank & Savings', value: 'bank' },
  { label: 'Cash & Wallets', value: 'cash' },
]

const groupedAccounts = computed(() => {
  return {
    'Bank & Savings': activeAccounts.value.filter(a => a.type === 'bank' || a.type === 'savings'),
    'Cash & Wallets': activeAccounts.value.filter(a => a.type === 'cash' || a.type === 'other')
  }
})

const filteredGroupedAccounts = computed(() => {
  if (selectedAccountFilter.value === 'all') return groupedAccounts.value
  if (selectedAccountFilter.value === 'bank') {
    return { 'Bank & Savings': groupedAccounts.value['Bank & Savings'] }
  }
  if (selectedAccountFilter.value === 'cash') {
    return { 'Cash & Wallets': groupedAccounts.value['Cash & Wallets'] }
  }
  return groupedAccounts.value
})

const totalBankBalance = computed(() => activeAccounts.value
  .filter(a => a.type === 'bank' || a.type === 'savings')
  .reduce((s, a) => s + Math.max(parseFloat(a.balance) || 0, 0), 0))

const totalCashBalance = computed(() => activeAccounts.value
  .filter(a => a.type === 'cash' || a.type === 'other')
  .reduce((s, a) => s + Math.max(parseFloat(a.balance) || 0, 0), 0))

const totalAssets = computed(() => totalBankBalance.value + totalCashBalance.value)
const totalBalance = computed(() => totalAssets.value)

function getProportion(acc) {
  const bal = parseFloat(acc.balance) || 0
  if (bal === 0) return 0
  // For non-CC accounts: proportion of total assets
  if (acc.type !== 'credit_card' && totalAssets.value > 0) return (bal / totalAssets.value) * 100
  return 0
}

function openTransaction(acc) {
  quickTransactionAccountId.value = acc.id
  showTransactionModal.value = true
}

function onTransactionSaved() {
  showTransactionModal.value = false
  fetchAccounts()
  emit('refresh')
}

function openPayBill(acc) {
  // Pre-fill a transfer TO the credit card with the full outstanding balance
  quickTransactionAccountId.value = ''
  payBillTargetId.value = acc.id
  payBillAmount.value   = parseFloat(acc.balance) || 0
  showPayBillModal.value = true
}

function onPayBillSaved() {
  showPayBillModal.value = false
  fetchAccounts()
  emit('refresh')
}
const displayPalette = computed(() => {
  if (colorPalette.includes(form.color)) return colorPalette
  return [...colorPalette, form.color]
})

watch(() => form.name, (newName) => {
  // Only auto-assign colors/icons if we're not currently editing an existing account
  if (editingAcc.value) return;

  const lower = newName.toLowerCase()
  let foundIcon = 'wallet'
  let foundColor = '#6366f1' // default back to initial if no match, or we could leave it
  let matched = false
  for (const b of bankKeywords) {
    if (b.keys.some(k => lower.includes(k))) {
      foundIcon = b.icon
      foundColor = b.color
      matched = true
      break
    }
  }
  form.icon = foundIcon
  if (matched) form.color = foundColor
})

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
    Object.assign(form, {
      name:               acc.name,
      type:               acc.type,
      balance:            '',
      credit_limit:       acc.credit_limit ?? '',
      billing_cycle_day:  acc.billing_cycle_day ?? '',
      due_date_day:       acc.due_date_day ?? '',
      color:              acc.color,
      icon:               acc.icon || 'wallet',
    })
  } else {
    Object.assign(form, { name: '', type: 'cash', balance: '', credit_limit: '', billing_cycle_day: '', due_date_day: '', color: '#6366f1', icon: 'wallet' })
  }
  showColors.value = false
  showModal.value = true
}

async function saveAccount() {
  formError.value = ''
  saving.value    = true
  try {
    let formattedName = form.name.trim()
    
    // Normalize bank brand casing
    for (const b of bankKeywords) {
      if (!b.brand) continue;
      // The keys are already sorted by length descending in the array definition above
      const regex = new RegExp(`\\b(${b.keys.join('|')})\\b`, 'ig');
      formattedName = formattedName.replace(regex, b.brand);
    }
    
    // Title case any remaining words
    formattedName = formattedName.replace(/(^\w|\s\w)/g, m => m.toUpperCase())

    const payload = {
      name:               formattedName,
      type:               form.type,
      color:              form.color,
      icon:               form.icon,
      credit_limit:       form.type === 'credit_card' ? (parseFloat(form.credit_limit) || null) : null,
      billing_cycle_day:  form.billing_cycle_day ? parseInt(form.billing_cycle_day) : null,
      due_date_day:       form.due_date_day ? parseInt(form.due_date_day) : null,
    }
    if (!editingAcc.value) {
      // CC balances are stored as positive outstanding debt
      const bal = Math.abs(parseFloat(form.balance) || 0)
      payload.balance = bal
    }

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
    const errors = e.response?.data?.errors
    formError.value = errors
      ? Object.values(errors).flat().join(' ')
      : (e.response?.data?.message || 'Failed to save')
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

async function toggleArchive(acc) {
  try {
    const action = acc.is_archived ? 'restored' : 'archived'
    await axios.put(`/api/accounts/${acc.id}`, { is_archived: !acc.is_archived })
    toast(`Account ${action}`)
    fetchAccounts()
    emit('refresh')
  } catch (e) {
    toast('Failed to update account', 'error')
  }
}

function closeMenuOnClickOutside() {
  activeMenuId.value = null
}

onMounted(() => {
  fetchAccounts()
  window.addEventListener('click', closeMenuOnClickOutside)
})

onUnmounted(() => {
  window.removeEventListener('click', closeMenuOnClickOutside)
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

/* ── Filter Pills ─────────────────────────────────────────── */
.account-filter-pill {
  padding: 0.4rem 0.875rem;
  border-radius: 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  background: var(--bg-surface-2);
  color: var(--text-muted);
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.15s;
}
.account-filter-pill:hover {
  color: var(--text-primary);
}
.account-filter-pill--active {
  background: var(--primary);
  color: #ffffff;
  border-color: var(--primary);
}

.sk-card { pointer-events: none; }

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
.action-btn--archive:hover { background-color: var(--warning-light); color: var(--warning); }
.action-btn--add:hover { background-color: var(--success-light); color: var(--success); }

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

.empty-text { font-size: 0.9375rem; color: var(--text-muted); margin: 0; text-align: center; }

/* ── Groups ─────────────────────────────────────────────────── */
.account-groups-container {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.group-title {
  font-size: 0.875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-secondary);
  margin: 0 0 0.75rem 0.25rem;
}

.toggle-archived-btn {
  background: transparent;
  border: 1px dashed var(--border-strong);
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.15s;
  width: 100%;
}
.toggle-archived-btn:hover {
  background: var(--bg-surface-2);
  color: var(--text-primary);
}

/* ── Proportion Bar ─────────────────────────────────────────── */
.proportion-bar-wrapper {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 4px;
  background-color: var(--bg-surface-2);
}

.proportion-bar {
  height: 100%;
  border-top-right-radius: 2px;
  border-bottom-right-radius: 2px;
  transition: width 0.5s ease-out;
}

/* ── Net Worth ────────────────────────────────────────────── */
.net-worth-card {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.net-worth-card:hover { transform: none; box-shadow: var(--shadow-glass); }

.nw-breakdown {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.nw-item { display: flex; flex-direction: column; gap: 0.25rem; }

.nw-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin: 0;
}

.nw-value { font-size: 1.5rem; font-weight: 700; margin: 0; }
.nw-operator { font-size: 1.5rem; font-weight: 300; color: var(--border-strong); padding-top: 1.25rem; }

.nw-total { margin-left: auto; text-align: right; }
.nw-total .nw-value { font-size: 1.75rem; }

@media (max-width: 640px) {
  .nw-breakdown { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .nw-operator { display: none; }
  .nw-total { margin-left: 0; text-align: left; margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 1rem; width: 100%; }
}

/* ── Color Picker ─────────────────────────────────────────── */
.color-picker-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.color-toggle-btn {
  background: transparent;
  border: none;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--primary);
  cursor: pointer;
  padding: 0;
}
.color-toggle-btn:hover {
  text-decoration: underline;
}

.color-picker { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem; }

.color-swatch {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 2px solid transparent;
  cursor: pointer;
  transition: transform 0.15s, border-color 0.15s;
}

.color-swatch--active { border-color: var(--text-primary); transform: scale(1.2); }

.object-contain { object-fit: contain; }

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

/* Input with Prefix */
.input-with-prefix {
  position: relative;
  display: flex;
  align-items: center;
}
.input-prefix {
  position: absolute;
  left: 0.875rem;
  color: var(--text-muted);
  font-weight: 500;
  pointer-events: none;
}
.input-field--prefix {
  padding-left: 2rem !important;
}

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

.archived-section-mt { margin-top: 1.5rem; }
.archived-grid-wrap { margin-top: 1rem; opacity: 0.75; }
.archived-card { background-color: rgba(0,0,0,0.02); }
.archived-icon { background-color: transparent; border: 1px solid var(--border-strong); opacity: 0.5; }
.text-muted-color { color: var(--text-muted); }
.text-secondary-color { color: var(--text-secondary); }
.text-primary-color { color: var(--primary); }
.label-no-mb { margin-bottom: 0; }

/* ── Summary Hero Card ────────────────────────────────────── */
.summary-hero-card {
  margin-bottom: 1.5rem;
  padding: 1.5rem 1.75rem;
  border-radius: 1.25rem;
  background: var(--bg-glass);
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
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-secondary);
}

.summary-value {
  font-size: 1.875rem;
  font-weight: 800;
  color: var(--primary);
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
  align-items: center;
  gap: 0.75rem;
}

.summary-box {
  background: var(--bg-surface-2);
  border: 1px solid var(--border);
  border-radius: 0.875rem;
  padding: 0.625rem 1rem;
  display: flex;
  flex-direction: column;
  min-width: 110px;
}

.box-label {
  font-size: 0.6875rem;
  color: var(--text-secondary);
  font-weight: 600;
}

.box-value {
  font-size: 0.9375rem;
  font-weight: 700;
  margin-top: 0.125rem;
}

/* ── Menu Dots & Popup Dropdown ─────────────────────────────── */
.menu-dots-btn {
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 0.9375rem;
  font-weight: 700;
  letter-spacing: 0.15em;
  padding: 0.25rem 0.5rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.15s;
}

.menu-dots-btn:hover {
  background-color: var(--bg-surface-2);
  color: var(--text-primary);
}

.account-dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.375rem;
  width: 170px;
  background: var(--bg-surface);
  border: 1px solid var(--border-strong);
  border-radius: 0.875rem;
  box-shadow: var(--shadow-md);
  padding: 0.375rem;
  z-index: 40;
  animation: slide-up 0.15s ease-out;
}

.dropdown-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-primary);
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background-color 0.12s;
  text-align: left;
}

.dropdown-item:hover {
  background-color: var(--bg-surface-2);
}

.dropdown-item--danger {
  color: var(--danger);
}

.dropdown-item--danger:hover {
  background-color: var(--danger-light);
}

.dropdown-item--paybill {
  color: var(--primary);
}

.dropdown-item--paybill:hover {
  background-color: var(--primary-light);
}

.dropdown-divider {
  height: 1px;
  background: var(--border);
  margin: 0.25rem 0;
}

/* ── Credit Card Utilization Bar Colors ──────────────────── */
.proportion-bar--safe    { background-color: var(--success); }
.proportion-bar--warning { background-color: var(--warning, #f59e0b); }
.proportion-bar--danger  { background-color: var(--danger); }

/* ── Credit Warning Badge ────────────────────────────────── */
.credit-warning-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.6875rem;
  font-weight: 700;
  color: var(--danger);
  background-color: var(--danger-light);
  border-radius: 999px;
  padding: 0.125rem 0.5rem;
}

/* ── Credit Limit Info Display ───────────────────────────── */
.credit-limit-display {
  font-size: 0.6875rem;
  color: var(--text-muted);
  font-weight: 500;
  margin-top: 0.25rem;
}

/* ── Field Hints ─────────────────────────────────────────── */
.field-hint {
  font-size: 0.6875rem;
  color: var(--text-muted);
  margin: 0.25rem 0 0;
  line-height: 1.4;
}

/* ── Required label star ─────────────────────────────────── */
.label-required {
  color: var(--danger);
  margin-left: 0.125rem;
}

/* ── Two-column form row ─────────────────────────────────── */
.form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

</style>

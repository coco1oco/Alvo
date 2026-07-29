<template>
  <div class="view">
    <!-- Header -->
    <div class="view-header">
      <div>
        <h1 class="view-title">Savings Goals</h1>
        <p class="view-subtitle">Set financial targets, track progress, and reach your milestones</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
        New Savings Goal
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
            <span class="summary-label">Total Saved Across Goals</span>
            <h2 class="summary-value tabular-nums amount-positive">{{ formatCurrency(totalSaved) }}</h2>
            <p class="summary-subtext">Target: {{ formatCurrency(totalTarget) }} ({{ overallProgress }}% reached)</p>
          </div>

          <div class="summary-breakdown">
            <div class="summary-box">
              <span class="box-label">Active Goals</span>
              <span class="box-value tabular-nums text-primary-color">{{ goals.length }}</span>
            </div>
            <div class="summary-box">
              <span class="box-label">Completed</span>
              <span class="box-value amount-positive tabular-nums">{{ completedGoals.length }}</span>
            </div>
          </div>
        </div>

        <!-- Overall Progress Meter -->
        <div v-if="totalTarget > 0" class="mt-4">
          <div class="flex justify-between text-xs font-semibold text-muted mb-1.5">
            <span>Overall Milestone Progress: {{ overallProgress }}%</span>
            <span>{{ formatCurrency(Math.max(totalTarget - totalSaved, 0)) }} remaining</span>
          </div>
          <div class="overall-progress-bar">
            <div class="overall-progress-fill" :style="{ width: overallProgress + '%' }"></div>
          </div>
        </div>
      </div>

      <!-- Goals Grid -->
      <div class="mb-8">
        <div v-if="!goals.length" class="empty-state glass-card">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          <p class="empty-text">No savings goals created yet.</p>
          <button @click="openModal()" class="btn-primary mt-3">Create Your First Goal</button>
        </div>

        <div v-else class="goals-grid">
          <div
            v-for="goal in goals"
            :key="goal.id"
            class="glass-card goal-card group relative flex flex-col justify-between"
            :style="{
              background: `linear-gradient(140deg, color-mix(in srgb, ${goal.color} 20%, var(--bg-surface)) 0%, var(--bg-surface) 100%)`,
              borderColor: `color-mix(in srgb, ${goal.color} 35%, transparent)`
            }"
          >
            <!-- Background Glow -->
            <div class="goal-glow" :style="{ backgroundColor: goal.color }"></div>

            <div>
              <!-- Top Row: Icon, Name + Options Menu -->
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm shadow-sm"
                       :style="{ backgroundColor: goal.color + '25', color: goal.color }">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-bold text-sm text-primary-color">{{ goal.name }}</h3>
                    <p v-if="goal.linked_account" class="text-xs text-muted font-medium">
                      Linked: {{ goal.linked_account.name }}
                    </p>
                    <p v-else class="text-xs text-muted font-medium">Manual Deposit</p>
                  </div>
                </div>

                <!-- Dropdown Menu -->
                <div class="relative">
                  <button @click.stop="toggleMenu(goal.id)" class="menu-dots-btn">•••</button>

                  <div v-if="activeMenuId === goal.id" class="goal-dropdown-menu" @click.stop>
                    <button @click="openDepositModal(goal); activeMenuId = null" class="dropdown-item">
                      <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                      Add Deposit
                    </button>
                    <button @click="openModal(goal); activeMenuId = null" class="dropdown-item">
                      <svg class="w-4 h-4 text-secondary-color" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                      Edit Goal
                    </button>
                    <div class="dropdown-divider"></div>
                    <button @click="deleteGoal(goal); activeMenuId = null" class="dropdown-item dropdown-item--danger">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                      Delete Goal
                    </button>
                  </div>
                </div>
              </div>

              <!-- Saved Amount + Progress Badge -->
              <div class="mt-4">
                <div class="flex items-baseline justify-between">
                  <span class="text-2xl font-extrabold tabular-nums amount-positive">
                    {{ formatCurrency(goal.effective_current_amount) }}
                  </span>
                  <span class="badge" :class="goal.progress_percentage >= 100 ? 'badge-success' : 'badge-primary'">
                    {{ goal.progress_percentage }}%
                  </span>
                </div>
                <p class="text-xs text-muted mt-1">
                  Target: <strong>{{ formatCurrency(goal.target_amount) }}</strong>
                  ({{ formatCurrency(Math.max(goal.target_amount - goal.effective_current_amount, 0)) }} left)
                </p>
              </div>

              <!-- Goal Progress Bar -->
              <div class="mt-3">
                <div class="overall-progress-bar" style="height: 6px;">
                  <div class="overall-progress-fill"
                       :style="{ width: goal.progress_percentage + '%', backgroundColor: goal.color }"></div>
                </div>
              </div>

              <!-- Projections / Deadline -->
              <div class="mt-3 text-xs text-muted flex flex-wrap justify-between gap-1 border-t border-border/40 pt-2">
                <span v-if="goal.deadline">Deadline: {{ formatDate(goal.deadline) }}</span>
                <span v-else>No target deadline</span>
                <span>{{ getProjectionText(goal) }}</span>
              </div>
            </div>

            <!-- Footer Action Button -->
            <div class="mt-4 pt-3 flex gap-2 border-t border-border/40">
              <button @click="openDepositModal(goal)" class="btn-primary flex-1 text-xs py-2 justify-center">
                + Add Deposit
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Add/Edit Goal Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-panel">
        <div class="modal-header">
          <h3 class="modal-title">{{ editingGoal ? 'Edit Savings Goal' : 'New Savings Goal' }}</h3>
          <button @click="showModal = false" class="modal-close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveGoal" class="modal-form">
          <div>
            <label class="label">Goal Name</label>
            <input v-model="form.name" required placeholder="e.g. Emergency Fund, New Laptop, Japan Trip" class="input-field" />
          </div>

          <div class="form-row-2">
            <div>
              <label class="label">Target Amount (₱)</label>
              <input v-model="form.target_amount" type="number" step="0.01" min="1" required placeholder="50000" class="input-field" />
            </div>
            <div>
              <label class="label">Initial Saved (₱)</label>
              <input v-model="form.current_amount" type="number" step="0.01" min="0" placeholder="0" class="input-field" />
            </div>
          </div>

          <div>
            <label class="label">Link Savings Account <span class="label-optional">(optional)</span></label>
            <select v-model="form.linked_account_id" class="input-field">
              <option value="">No linked account (manual tracking)</option>
              <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                {{ acc.name }} ({{ formatCurrency(acc.balance) }})
              </option>
            </select>
            <p class="field-hint">Linking an account automatically uses its balance to calculate goal progress.</p>
          </div>

          <div class="form-row-2">
            <div>
              <label class="label">Target Deadline <span class="label-optional">(optional)</span></label>
              <input v-model="form.deadline" type="date" class="input-field" />
            </div>
            <div>
              <label class="label">Color Accent</label>
              <input v-model="form.color" type="color" class="input-field h-10 p-1 cursor-pointer" />
            </div>
          </div>

          <div v-if="formError" class="alert-danger">{{ formError }}</div>

          <div class="modal-footer">
            <button type="button" @click="showModal = false" class="btn-ghost modal-btn">Cancel</button>
            <button type="submit" :disabled="saving" class="btn-primary modal-btn">
              <span v-if="saving" class="btn-spinner"></span>
              {{ saving ? 'Saving...' : (editingGoal ? 'Update Goal' : 'Create Goal') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Quick Deposit Modal -->
    <div v-if="showDepositModal" class="modal-overlay">
      <div class="modal-panel">
        <div class="modal-header">
          <h3 class="modal-title">Add Deposit to "{{ depositGoal?.name }}"</h3>
          <button @click="showDepositModal = false" class="modal-close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitDeposit" class="modal-form">
          <div>
            <label class="label">Deposit Amount (₱)</label>
            <input v-model="depositAmount" type="number" step="0.01" min="0.01" required placeholder="1000.00" class="input-field" />
          </div>

          <div class="modal-footer">
            <button type="button" @click="showDepositModal = false" class="btn-ghost modal-btn">Cancel</button>
            <button type="submit" :disabled="depositing" class="btn-primary modal-btn">
              <span v-if="depositing" class="btn-spinner"></span>
              {{ depositing ? 'Processing...' : 'Confirm Deposit' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, inject } from 'vue'
import axios from 'axios'

const emit = defineEmits(['refresh'])
const toast = inject('toast')

const loading = ref(true)
const goals = ref([])
const accounts = ref([])
const showModal = ref(false)
const editingGoal = ref(null)
const saving = ref(false)
const formError = ref('')
const activeMenuId = ref(null)

// Deposit modal
const showDepositModal = ref(false)
const depositGoal = ref(null)
const depositAmount = ref('')
const depositing = ref(false)

const form = reactive({
  name: '',
  target_amount: '',
  current_amount: '',
  linked_account_id: '',
  deadline: '',
  color: '#6366f1',
  icon: 'target',
})

const totalTarget = computed(() => goals.value.reduce((s, g) => s + (parseFloat(g.target_amount) || 0), 0))
const totalSaved = computed(() => goals.value.reduce((s, g) => s + (parseFloat(g.effective_current_amount) || 0), 0))
const completedGoals = computed(() => goals.value.filter(g => g.progress_percentage >= 100))

const overallProgress = computed(() => {
  if (!totalTarget.value) return 0
  return Math.min(Math.round((totalSaved.value / totalTarget.value) * 100), 100)
})

function formatCurrency(val) {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 }).format(val || 0)
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function getProjectionText(goal) {
  if (goal.progress_percentage >= 100) return '🎉 Goal Reached!'
  if (!goal.deadline) return 'On track'
  const due = new Date(goal.deadline)
  const today = new Date()
  const diffDays = Math.round((due - today) / (1000 * 60 * 60 * 24))
  if (diffDays < 0) return 'Deadline passed'
  return `${diffDays} days left`
}

function toggleMenu(id) {
  activeMenuId.value = activeMenuId.value === id ? null : id
}

async function fetchGoals() {
  loading.value = true
  try {
    const [goalRes, accRes] = await Promise.all([
      axios.get('/api/goals'),
      axios.get('/api/accounts'),
    ])
    goals.value = goalRes.data
    accounts.value = accRes.data
  } catch (e) {
    toast('Failed to load goals', 'error')
  } finally {
    loading.value = false
  }
}

function openModal(goal = null) {
  editingGoal.value = goal
  formError.value = ''
  if (goal) {
    Object.assign(form, {
      name: goal.name,
      target_amount: goal.target_amount,
      current_amount: goal.current_amount,
      linked_account_id: goal.linked_account_id ?? '',
      deadline: goal.deadline ? goal.deadline.substring(0, 10) : '',
      color: goal.color || '#6366f1',
      icon: goal.icon || 'target',
    })
  } else {
    Object.assign(form, {
      name: '',
      target_amount: '',
      current_amount: '',
      linked_account_id: '',
      deadline: '',
      color: '#6366f1',
      icon: 'target',
    })
  }
  showModal.value = true
}

async function saveGoal() {
  formError.value = ''
  saving.value = true
  try {
    const payload = {
      ...form,
      target_amount: parseFloat(form.target_amount) || 0,
      current_amount: parseFloat(form.current_amount) || 0,
      linked_account_id: form.linked_account_id || null,
      deadline: form.deadline || null,
    }
    if (editingGoal.value) {
      await axios.put(`/api/goals/${editingGoal.value.id}`, payload)
      toast('Goal updated')
    } else {
      await axios.post('/api/goals', payload)
      toast('Savings goal created')
    }
    showModal.value = false
    fetchGoals()
    emit('refresh')
  } catch (e) {
    const errors = e.response?.data?.errors
    formError.value = errors
      ? Object.values(errors).flat().join(' ')
      : (e.response?.data?.message || 'Failed to save goal')
  } finally {
    saving.value = false
  }
}

function openDepositModal(goal) {
  depositGoal.value = goal
  depositAmount.value = ''
  showDepositModal.value = true
}

async function submitDeposit() {
  if (!depositGoal.value) return
  depositing.value = true
  try {
    await axios.post(`/api/goals/${depositGoal.value.id}/deposit`, { amount: parseFloat(depositAmount.value) || 0 })
    toast('Deposit added to goal')
    showDepositModal.value = false
    fetchGoals()
    emit('refresh')
  } catch (e) {
    toast(e.response?.data?.message || 'Failed to add deposit', 'error')
  } finally {
    depositing.value = false
  }
}

async function deleteGoal(goal) {
  if (!confirm(`Delete savings goal "${goal.name}"?`)) return
  try {
    await axios.delete(`/api/goals/${goal.id}`)
    toast('Goal deleted')
    fetchGoals()
    emit('refresh')
  } catch (e) {
    toast('Failed to delete goal', 'error')
  }
}

function closeMenuOnClickOutside() { activeMenuId.value = null }
onMounted(() => {
  fetchGoals()
  window.addEventListener('click', closeMenuOnClickOutside)
})
onUnmounted(() => { window.removeEventListener('click', closeMenuOnClickOutside) })
</script>

<style scoped>
.view { padding: 2rem; max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem; }
.view-header { display: flex; align-items: center; justify-content: space-between; }
.view-title { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 0; }
.view-subtitle { font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem; }

/* ── Hero Summary Card ────────────────────────────────────── */
.summary-hero-card { padding: 1.5rem; display: flex; flex-direction: column; }
.summary-hero-content { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem; }
.summary-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.summary-value { font-size: 2.25rem; font-weight: 800; line-height: 1.1; margin: 0.25rem 0; }
.summary-subtext { font-size: 0.75rem; color: var(--text-muted); }
.summary-breakdown { display: flex; gap: 1.25rem; flex-wrap: wrap; }
.summary-box { background: var(--bg-surface-2); padding: 0.75rem 1rem; border-radius: 0.875rem; display: flex; flex-direction: column; min-width: 130px; }
.box-label { font-size: 0.6875rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }
.box-value { font-size: 1.125rem; font-weight: 700; margin-top: 0.25rem; }

.overall-progress-bar { width: 100%; height: 8px; background-color: var(--bg-surface-2); border-radius: 999px; overflow: hidden; }
.overall-progress-fill { height: 100%; background-color: var(--primary); border-radius: 999px; transition: width 0.6s ease-out; }

/* ── Empty State ─────────────────────────────────────────── */
.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3.5rem 1.5rem; text-align: center; border-radius: 1.25rem; }
.empty-icon { width: 3rem; height: 3rem; color: var(--text-muted); opacity: 0.5; margin-bottom: 0.75rem; }
.empty-text { font-size: 0.875rem; color: var(--text-muted); margin: 0; }

/* ── Goals Grid ───────────────────────────────────────────── */
.goals-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem; }
.goal-card { padding: 1.25rem; border-radius: 1.25rem; position: relative; overflow: hidden; min-height: 220px; }
.goal-glow { position: absolute; top: -40px; right: -40px; width: 120px; height: 120px; border-radius: 50%; filter: blur(50px); opacity: 0.22; pointer-events: none; }

.menu-dots-btn { background: transparent; border: none; color: var(--text-muted); font-size: 1.25rem; font-weight: 700; cursor: pointer; padding: 0.25rem 0.5rem; border-radius: 0.5rem; }
.menu-dots-btn:hover { background-color: var(--bg-surface-2); color: var(--text-primary); }

.goal-dropdown-menu { position: absolute; right: 0; top: 100%; margin-top: 0.25rem; width: 160px; background: var(--bg-surface); border: 1px solid var(--border-strong); border-radius: 0.875rem; box-shadow: var(--shadow-md); padding: 0.375rem; z-index: 40; }
.dropdown-item { width: 100%; display: flex; align-items: center; gap: 0.625rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; color: var(--text-primary); background: transparent; border: none; cursor: pointer; text-align: left; }
.dropdown-item:hover { background-color: var(--bg-surface-2); }
.dropdown-item--danger { color: var(--danger); }
.dropdown-item--danger:hover { background-color: var(--danger-light); }
.dropdown-divider { height: 1px; background: var(--border); margin: 0.25rem 0; }

/* ── Modal Specs ──────────────────────────────────────────── */
.modal-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 50; }
.modal-panel { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 1.5rem; box-shadow: var(--shadow-md); padding: 1.75rem; width: 100%; max-width: 480px; }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
.modal-title { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0; }
.modal-close { background: transparent; border: none; color: var(--text-muted); cursor: pointer; border-radius: 0.5rem; padding: 0.25rem; }
.modal-close:hover { background-color: var(--bg-surface-2); color: var(--text-primary); }

.modal-form { display: flex; flex-direction: column; gap: 1rem; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.field-hint { font-size: 0.6875rem; color: var(--text-muted); margin: 0.25rem 0 0; }

.modal-footer { display: flex; gap: 0.75rem; padding-top: 0.5rem; }
.modal-btn { flex: 1; justify-content: center; }
</style>

<template>
  <div class="modal-overlay">
    <div class="modal-panel modal-panel--lg">
      <!-- Header -->
      <div class="modal-header">
        <h3 class="modal-title">{{ isEdit ? 'Edit Transaction' : 'Add Transaction' }}</h3>
        <button @click="$emit('close')" class="modal-close">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="submit" class="modal-form">
        <!-- Type Selector -->
        <div>
          <label class="label">Type</label>
          <div class="type-toggle">
            <button
              type="button"
              v-for="t in ['income', 'expense', 'transfer']"
              :key="t"
              @click="form.type = t"
              :class="['type-btn', typeClass(t)]"
            >{{ t }}</button>
          </div>
        </div>

        <!-- From Account -->
        <div>
          <label class="label">{{ form.type === 'transfer' ? 'From Account' : 'Account' }}</label>
          <select v-model="form.account_id" required class="input-field">
            <option value="">Select account...</option>
            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
              {{ acc.name }} ({{ formatCurrency(acc.balance) }})
            </option>
          </select>
        </div>

        <!-- To Account (transfer only) -->
        <div v-if="form.type === 'transfer'">
          <label class="label">To Account</label>
          <select v-model="form.to_account_id" required class="input-field">
            <option value="">Select destination...</option>
            <option
              v-for="acc in accounts.filter(a => a.id !== form.account_id)"
              :key="acc.id"
              :value="acc.id"
            >{{ acc.name }} ({{ formatCurrency(acc.balance) }})</option>
          </select>
        </div>

        <!-- Category (income / expense only) -->
        <div v-if="form.type !== 'transfer'">
          <label class="label">Category</label>
          <select v-model="form.category_id" class="input-field">
            <option value="">No category</option>
            <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>

        <!-- Amount + Date -->
        <div class="form-row">
          <div class="form-col">
            <label class="label">Amount (₱)</label>
            <input v-model="form.amount" type="number" step="0.01" min="0.01" required placeholder="0.00" class="input-field" />
          </div>
          <div class="form-col">
            <label class="label">Date</label>
            <input v-model="form.date" type="date" required class="input-field" />
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="label">Description <span class="label-optional">(optional)</span></label>
          <textarea
            v-model="form.description"
            rows="2"
            placeholder="What's this for?"
            class="input-field"
            style="resize: none;"
          ></textarea>
        </div>

        <!-- Error -->
        <div v-if="error" class="alert-danger">{{ error }}</div>

        <!-- Actions -->
        <div class="modal-footer">
          <button type="button" @click="$emit('close')" class="btn-ghost modal-btn">Cancel</button>
          <button
            type="submit"
            :disabled="loading"
            class="modal-btn submit-btn"
            :class="submitClass"
          >
            <span v-if="loading" class="btn-spinner"></span>
            {{ loading ? 'Saving...' : (isEdit ? 'Update' : 'Add Transaction') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, inject } from 'vue'
import axios from 'axios'

const props = defineProps({ transaction: { type: Object, default: null } })
const emit  = defineEmits(['close', 'saved'])
const toast = inject('toast')

const isEdit     = computed(() => !!props.transaction)
const loading    = ref(false)
const error      = ref('')
const accounts   = ref([])
const categories = ref([])

const form = reactive({
  type:          props.transaction?.type           ?? 'expense',
  account_id:    props.transaction?.account_id     ?? '',
  to_account_id: props.transaction?.to_account_id  ?? '',
  category_id:   props.transaction?.category_id    ?? '',
  amount:        props.transaction?.amount         ?? '',
  description:   props.transaction?.description    ?? '',
  date:          props.transaction?.date
    ? props.transaction.date.substring(0, 10)
    : new Date().toISOString().substring(0, 10),
})

const filteredCategories = computed(() =>
  categories.value.filter(c => c.type === form.type)
)

function typeClass(t) {
  if (form.type !== t) return ''
  if (t === 'income')   return 'type-btn--income'
  if (t === 'expense')  return 'type-btn--expense'
  return 'type-btn--transfer'
}

const submitClass = computed(() => {
  if (form.type === 'income')   return 'submit-btn--income'
  if (form.type === 'expense')  return 'submit-btn--expense'
  return 'submit-btn--transfer'
})

function formatCurrency(v) {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 0 }).format(v || 0)
}

async function fetchFormData() {
  const [accRes, catRes] = await Promise.all([
    axios.get('/api/accounts'),
    axios.get('/api/categories'),
  ])
  accounts.value   = accRes.data
  categories.value = catRes.data
}

async function submit() {
  error.value   = ''
  loading.value = true
  try {
    const payload = {
      type:        form.type,
      account_id:  form.account_id,
      amount:      form.amount,
      date:        form.date,
      description: form.description || null,
    }
    if (form.type === 'transfer') {
      payload.to_account_id = form.to_account_id
    } else {
      payload.category_id = form.category_id || null
    }

    if (isEdit.value) {
      await axios.put(`/api/transactions/${props.transaction.id}`, payload)
      toast('Transaction updated')
    } else {
      await axios.post('/api/transactions', payload)
      toast('Transaction added')
    }
    emit('saved')
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors
      ? Object.values(errors).flat().join(' ')
      : (e.response?.data?.message || 'Failed to save')
  } finally {
    loading.value = false
  }
}

onMounted(fetchFormData)
</script>

<style scoped>
/* Wider modal for transaction form */
.modal-panel--lg {
  max-width: 560px;
}

/* ── Modal Structure ──────────────────────────────────────── */
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

/* ── Type Toggle ──────────────────────────────────────────── */
.type-toggle {
  display: flex;
  gap: 0.5rem;
}

.type-btn {
  flex: 1;
  padding: 0.5rem;
  border-radius: 0.625rem;
  font-size: 0.8125rem;
  font-weight: 600;
  font-family: var(--font-sans);
  border: 1px solid var(--border-strong);
  background: transparent;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.15s;
  text-transform: capitalize;
}

.type-btn:hover { background-color: var(--bg-surface-2); color: var(--text-primary); }

.type-btn--income   { background-color: var(--success-light); border-color: var(--success); color: var(--success); }
.type-btn--expense  { background-color: var(--danger-light);  border-color: var(--danger);  color: var(--danger); }
.type-btn--transfer { background-color: var(--primary-light); border-color: var(--primary); color: var(--primary); }

/* ── Form Row ─────────────────────────────────────────────── */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.form-col { display: flex; flex-direction: column; }

/* ── Label optional note ──────────────────────────────────── */
.label-optional {
  font-weight: 400;
  color: var(--text-muted);
  text-transform: none;
  font-size: 0.6875rem;
}

/* ── Submit Button (type-colored) ─────────────────────────── */
.submit-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.875rem;
  font-weight: 600;
  font-family: var(--font-sans);
  padding: 0.625rem 1.25rem;
  border-radius: 0.75rem;
  border: none;
  cursor: pointer;
  transition: opacity 0.15s, transform 0.15s;
  color: #fff;
  white-space: nowrap;
}

.submit-btn--income   { background-color: var(--success); box-shadow: 0 2px 8px var(--success-light); }
.submit-btn--expense  { background-color: var(--danger);  box-shadow: 0 2px 8px var(--danger-light); }
.submit-btn--transfer { background-color: var(--primary); box-shadow: 0 2px 8px var(--primary-glass); }

.submit-btn:hover:not(:disabled)   { opacity: 0.88; transform: translateY(-1px); }
.submit-btn:active:not(:disabled)  { transform: translateY(0); }
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── Spinner ──────────────────────────────────────────────── */
.btn-spinner {
  width: 12px;
  height: 12px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}
</style>

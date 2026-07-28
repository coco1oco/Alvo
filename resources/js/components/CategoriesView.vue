<template>
  <div class="view">
    <!-- Header -->
    <div class="view-header">
      <div>
        <h1 class="view-title">Categories</h1>
        <p class="view-subtitle">Organize your transactions</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        <PlusIcon class="w-4 h-4" />
        Add Category
      </button>
    </div>

    <!-- Income Categories -->
    <section>
      <div class="section-heading section-heading--income">
        <span class="section-dot section-dot--income"></span>
        Income ({{ income.length }})
      </div>
      <div class="categories-grid">
        <div
          v-for="cat in income"
          :key="cat.id"
          class="glass-card category-card group"
        >
          <div class="cat-color-wash" :style="{ backgroundColor: `color-mix(in srgb, ${cat.color} 15%, transparent)` }"></div>
          <div class="cat-top">
            <div class="cat-icon" :style="{ backgroundColor: `color-mix(in srgb, ${cat.color} 15%, transparent)`, color: cat.color }">
              <component :is="getCategoryIcon(cat.name)" class="w-5 h-5" />
            </div>
            <div class="cat-actions">
              <button @click="openModal(cat)" class="action-btn action-btn--edit" title="Edit">
                <PencilIcon class="w-4 h-4" />
              </button>
              <button @click="confirmDelete(cat)" class="action-btn action-btn--delete" title="Delete">
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
          <div class="cat-info mt-2">
            <p class="cat-name">{{ cat.name }}</p>
            <div class="cat-stats text-xs text-muted mt-1 flex items-center justify-between">
              <span>{{ cat.transactions_count || 0 }} txns</span>
              <span class="tabular-nums font-medium" :style="{ color: cat.color }">
                {{ formatCurrency(cat.monthly_spend || 0) }}
              </span>
            </div>
          </div>
          <div class="cat-bar mt-2" :style="{ backgroundColor: cat.color }"></div>
        </div>
        
        <div v-if="!income.length" class="empty-state">
          <FolderOpenIcon class="w-10 h-10 text-muted mb-2" />
          <p>No income categories yet</p>
          <button @click="openModal(null, 'income')" class="btn-ghost btn-sm mt-3">
            <PlusIcon class="w-3.5 h-3.5" /> Add Category
          </button>
        </div>
      </div>
    </section>

    <!-- Expense Categories -->
    <section>
      <div class="section-heading section-heading--expense">
        <span class="section-dot section-dot--expense"></span>
        Expense ({{ expense.length }})
      </div>
      <div class="categories-grid">
        <div
          v-for="cat in expense"
          :key="cat.id"
          class="glass-card category-card group"
        >
          <div class="cat-color-wash" :style="{ backgroundColor: `color-mix(in srgb, ${cat.color} 15%, transparent)` }"></div>
          <div class="cat-top">
            <div class="cat-icon" :style="{ backgroundColor: `color-mix(in srgb, ${cat.color} 15%, transparent)`, color: cat.color }">
              <component :is="getCategoryIcon(cat.name)" class="w-5 h-5" />
            </div>
            <div class="cat-actions">
              <button @click="openModal(cat)" class="action-btn action-btn--edit" title="Edit">
                <PencilIcon class="w-4 h-4" />
              </button>
              <button @click="confirmDelete(cat)" class="action-btn action-btn--delete" title="Delete">
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
          <div class="cat-info mt-2">
            <p class="cat-name">{{ cat.name }}</p>
            <div class="cat-stats text-xs text-muted mt-1 flex items-center justify-between">
              <span>{{ cat.transactions_count || 0 }} txns</span>
              <span class="tabular-nums font-medium text-danger">
                {{ formatCurrency(cat.monthly_spend || 0) }}
              </span>
            </div>
          </div>
          <div class="cat-bar mt-2" :style="{ backgroundColor: cat.color }"></div>
        </div>

        <div v-if="!expense.length" class="empty-state">
          <FolderOpenIcon class="w-10 h-10 text-muted mb-2" />
          <p>No expense categories yet</p>
          <button @click="openModal(null, 'expense')" class="btn-ghost btn-sm mt-3">
            <PlusIcon class="w-3.5 h-3.5" /> Add Category
          </button>
        </div>
      </div>
    </section>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-panel">
        <div class="modal-header">
          <h3 class="modal-title">{{ editingCat ? 'Edit Category' : 'Add Category' }}</h3>
          <button @click="showModal = false" class="modal-close">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="saveCategory" class="modal-form">
          <div>
            <label class="label">Name</label>
            <input v-model="form.name" required placeholder="e.g. Groceries" class="input-field" />
          </div>
          <div>
            <label class="label">Type</label>
            <div class="type-toggle">
              <button
                type="button"
                @click="form.type = 'income'"
                :class="['type-btn', form.type === 'income' ? 'type-btn--income' : '']"
              >Income</button>
              <button
                type="button"
                @click="form.type = 'expense'"
                :class="['type-btn', form.type === 'expense' ? 'type-btn--expense' : '']"
              >Expense</button>
            </div>
          </div>
          <div>
            <label class="label">Color</label>
            <div class="color-picker">
              <button
                type="button"
                v-for="c in CATEGORY_COLORS"
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
              {{ saving ? 'Saving...' : (editingCat ? 'Update' : 'Add') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="categoryToDelete" class="modal-overlay">
      <div class="modal-panel delete-modal">
        <div class="modal-icon-header">
          <div class="modal-icon-bg">
            <ExclamationTriangleIcon class="w-6 h-6 text-danger" />
          </div>
        </div>
        <h3 class="modal-title text-center mt-4">Delete Category?</h3>
        <p class="modal-desc text-center mt-2">Are you sure you want to delete "<strong>{{ categoryToDelete.name }}</strong>"? This action cannot be undone.</p>
        <div class="modal-footer mt-6">
          <button @click="categoryToDelete = null" class="btn-ghost modal-btn">Cancel</button>
          <button @click="executeDelete" class="btn-danger modal-btn">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, inject } from 'vue'
import axios from 'axios'
import { 
  PlusIcon, FolderIcon, PencilIcon, TrashIcon, XMarkIcon, FolderOpenIcon, ExclamationTriangleIcon,
  ShoppingCartIcon, HomeIcon, BoltIcon, ArrowPathIcon, ShoppingBagIcon, BanknotesIcon, BeakerIcon, TruckIcon, SparklesIcon, TagIcon
} from '@heroicons/vue/24/outline'
import { CATEGORY_COLORS } from '../utils/constants'

const toast      = inject('toast')
const categories = ref([])
const showModal  = ref(false)
const editingCat = ref(null)
const categoryToDelete = ref(null)
const saving     = ref(false)
const formError  = ref('')

const form       = reactive({ name: '', type: 'expense', color: CATEGORY_COLORS[0] })

const income  = computed(() => categories.value.filter(c => c.type === 'income'))
const expense = computed(() => categories.value.filter(c => c.type === 'expense'))

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount || 0)
}

function getCategoryIcon(name = '') {
  const n = name.toLowerCase()
  if (n.includes('groc') || n.includes('market')) return ShoppingCartIcon
  if (n.includes('rent') || n.includes('hous') || n.includes('home')) return HomeIcon
  if (n.includes('elec') || n.includes('util') || n.includes('power')) return BoltIcon
  if (n.includes('sub') || n.includes('netfl') || n.includes('spot')) return ArrowPathIcon
  if (n.includes('shop') || n.includes('cloth') || n.includes('store')) return ShoppingBagIcon
  if (n.includes('sal') || n.includes('pay') || n.includes('incom') || n.includes('earn')) return BanknotesIcon
  if (n.includes('water') || n.includes('bill')) return BeakerIcon
  if (n.includes('trans') || n.includes('gas') || n.includes('car') || n.includes('ride')) return TruckIcon
  if (n.includes('eat') || n.includes('din') || n.includes('rest') || n.includes('food')) return SparklesIcon
  return TagIcon
}

async function fetchCategories() {
  const { data } = await axios.get('/api/categories')
  categories.value = data
}

function openModal(cat = null, defaultType = 'expense') {
  editingCat.value = cat
  formError.value  = ''
  Object.assign(form, cat ? { name: cat.name, type: cat.type, color: cat.color } : { name: '', type: defaultType, color: CATEGORY_COLORS[0] })
  showModal.value = true
}

async function saveCategory() {
  formError.value = ''
  saving.value    = true
  try {
    if (editingCat.value) {
      await axios.put(`/api/categories/${editingCat.value.id}`, form)
      toast('Category updated')
    } else {
      await axios.post('/api/categories', form)
      toast('Category added')
    }
    showModal.value = false
    fetchCategories()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Failed to save'
  } finally {
    saving.value = false
  }
}

function confirmDelete(cat) {
  categoryToDelete.value = cat
}

async function executeDelete() {
  if (!categoryToDelete.value) return
  try {
    await axios.delete(`/api/categories/${categoryToDelete.value.id}`)
    toast('Category deleted')
    categoryToDelete.value = null
    fetchCategories()
  } catch (e) {
    toast('Delete failed', 'error')
  }
}

onMounted(fetchCategories)
</script>

<style scoped>
.view {
  padding: 2rem;
  max-width: 1280px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 2rem;
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

/* ── Section Headings ─────────────────────────────────────── */
.section-heading {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.875rem;
}

.section-heading--income { color: var(--success); }
.section-heading--expense { color: var(--danger); }

.section-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
  flex-shrink: 0;
}

.section-dot--income { background-color: var(--success); }
.section-dot--expense { background-color: var(--danger); }

/* ── Categories Grid ──────────────────────────────────────── */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}

/* ── Category Card ────────────────────────────────────────── */
.category-card {
  position: relative;
  overflow: hidden;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.cat-color-wash {
  position: absolute;
  inset: 0;
  opacity: 0.04;
  pointer-events: none;
}

.cat-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.cat-icon {
  width: 36px;
  height: 36px;
  border-radius: 0.625rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.cat-actions {
  display: flex;
  gap: 0.25rem;
  opacity: 0.4;
  transition: opacity 0.15s;
}

.category-card:hover .cat-actions { opacity: 1; }

.action-btn {
  width: 26px;
  height: 26px;
  border-radius: 0.375rem;
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

.cat-name {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.cat-bar {
  width: 20px;
  height: 3px;
  border-radius: 9999px;
  margin-top: 0.25rem;
}

.empty-state {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  background: var(--bg-surface);
  border: 1px dashed var(--border-strong);
  border-radius: 1.25rem;
  color: var(--text-muted);
  font-size: 0.875rem;
}

.empty-state .text-muted { color: var(--text-muted); }
.empty-state .mb-2 { margin-bottom: 0.5rem; }
.empty-state .mt-3 { margin-top: 0.75rem; }
.empty-state .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; border-radius: 0.5rem; }

/* Modal Icon */
.modal-icon-header { display: flex; justify-content: center; }
.modal-icon-bg {
  width: 3rem; height: 3rem; border-radius: 50%;
  background: var(--danger-light); display: flex; align-items: center; justify-content: center;
}
.text-danger { color: var(--danger); }
.text-center { text-align: center; }
.mt-4 { margin-top: 1rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-6 { margin-top: 1.5rem; }
.modal-desc { font-size: 0.875rem; color: var(--text-secondary); line-height: 1.5; }

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
}

.type-btn:hover { background-color: var(--bg-surface-2); color: var(--text-primary); }

.type-btn--income  { background-color: var(--success-light); border-color: var(--success); color: var(--success); }
.type-btn--expense { background-color: var(--danger-light);  border-color: var(--danger);  color: var(--danger); }

/* ── Color Picker ─────────────────────────────────────────── */
.color-picker { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.25rem; }

.color-swatch {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 2px solid transparent;
  cursor: pointer;
  transition: transform 0.15s, border-color 0.15s;
}

.color-swatch--active { border-color: var(--text-primary); transform: scale(1.2); }
</style>

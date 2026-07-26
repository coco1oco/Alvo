<template>
  <div class="view">
    <!-- Header -->
    <div class="view-header">
      <div>
        <h1 class="view-title">Categories</h1>
        <p class="view-subtitle">Organize your transactions</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
        Add Category
      </button>
    </div>

    <!-- Income Categories -->
    <section>
      <div class="section-heading section-heading--income">
        <span class="section-dot section-dot--income"></span>
        Income
      </div>
      <div class="categories-grid">
        <div
          v-for="cat in income"
          :key="cat.id"
          class="glass-card category-card group"
        >
          <div class="cat-color-wash" :style="{ backgroundColor: cat.color }"></div>
          <div class="cat-top">
            <div class="cat-icon" :style="{ backgroundColor: cat.color + '25', color: cat.color }">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
              </svg>
            </div>
            <div class="cat-actions">
              <button @click="openModal(cat)" class="action-btn action-btn--edit" title="Edit">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button @click="deleteCategory(cat)" class="action-btn action-btn--delete" title="Delete">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
          <p class="cat-name">{{ cat.name }}</p>
          <div class="cat-bar" :style="{ backgroundColor: cat.color }"></div>
        </div>
        <div v-if="!income.length" class="cat-empty">No income categories yet</div>
      </div>
    </section>

    <!-- Expense Categories -->
    <section>
      <div class="section-heading section-heading--expense">
        <span class="section-dot section-dot--expense"></span>
        Expense
      </div>
      <div class="categories-grid">
        <div
          v-for="cat in expense"
          :key="cat.id"
          class="glass-card category-card group"
        >
          <div class="cat-color-wash" :style="{ backgroundColor: cat.color }"></div>
          <div class="cat-top">
            <div class="cat-icon" :style="{ backgroundColor: cat.color + '25', color: cat.color }">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
              </svg>
            </div>
            <div class="cat-actions">
              <button @click="openModal(cat)" class="action-btn action-btn--edit" title="Edit">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button @click="deleteCategory(cat)" class="action-btn action-btn--delete" title="Delete">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
          <p class="cat-name">{{ cat.name }}</p>
          <div class="cat-bar" :style="{ backgroundColor: cat.color }"></div>
        </div>
        <div v-if="!expense.length" class="cat-empty">No expense categories yet</div>
      </div>
    </section>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-panel">
        <div class="modal-header">
          <h3 class="modal-title">{{ editingCat ? 'Edit Category' : 'Add Category' }}</h3>
          <button @click="showModal = false" class="modal-close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
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
                v-for="c in colorPalette"
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
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, inject } from 'vue'
import axios from 'axios'

const toast      = inject('toast')
const categories = ref([])
const showModal  = ref(false)
const editingCat = ref(null)
const saving     = ref(false)
const formError  = ref('')

const colorPalette = ['#6366f1','#8b5cf6','#ec4899','#ef4444','#f97316','#f59e0b','#22c55e','#10b981','#06b6d4','#3b82f6','#64748b','#0ea5e9']
const form         = reactive({ name: '', type: 'expense', color: '#6366f1' })

const income  = computed(() => categories.value.filter(c => c.type === 'income'))
const expense = computed(() => categories.value.filter(c => c.type === 'expense'))

async function fetchCategories() {
  const { data } = await axios.get('/api/categories')
  categories.value = data
}

function openModal(cat = null) {
  editingCat.value = cat
  formError.value  = ''
  Object.assign(form, cat ? { name: cat.name, type: cat.type, color: cat.color } : { name: '', type: 'expense', color: '#6366f1' })
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

async function deleteCategory(cat) {
  if (!confirm(`Delete "${cat.name}"?`)) return
  try {
    await axios.delete(`/api/categories/${cat.id}`)
    toast('Category deleted')
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
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

@media (min-width: 640px)  { .categories-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1280px) { .categories-grid { grid-template-columns: repeat(4, 1fr); } }

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
  opacity: 0;
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

.cat-empty {
  grid-column: 1 / -1;
  font-size: 0.8125rem;
  color: var(--text-muted);
  padding: 1rem 0;
}

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

<template>
    <div class="view">
        <!-- Header -->
        <div class="view-header">
            <div>
                <h1 class="view-title">Budgets</h1>
                <p class="view-subtitle">
                    Set monthly spending limits per category
                </p>
            </div>
            <div class="header-actions">
                <!-- Styled Month Navigator Pill -->
                <div class="month-navigator-pill">
                    <button
                        @click="prevMonth"
                        class="month-nav-btn"
                        title="Previous Month"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </button>
                    <span class="month-label font-semibold">{{
                        formattedMonthLabel
                    }}</span>
                    <button
                        @click="nextMonth"
                        class="month-nav-btn"
                        title="Next Month"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>

                <button @click="showModal = true" class="btn-primary">
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2.5"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Set Budget
                </button>
            </div>
        </div>

        <!-- Skeleton Loading -->
        <template v-if="loading">
            <!-- Summary banner skeleton -->
            <div class="glass-card budget-summary-banner mb-6 sk-card">
                <div
                    style="
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 1rem;
                    "
                >
                    <div
                        style="
                            display: flex;
                            align-items: center;
                            gap: 0.875rem;
                        "
                    >
                        <div
                            class="skeleton"
                            style="
                                width: 2.75rem;
                                height: 2.75rem;
                                border-radius: 0.75rem;
                                flex-shrink: 0;
                            "
                        ></div>
                        <div
                            style="
                                display: flex;
                                flex-direction: column;
                                gap: 0.375rem;
                            "
                        >
                            <div
                                class="skeleton"
                                style="
                                    width: 10rem;
                                    height: 0.875rem;
                                    border-radius: 0.375rem;
                                "
                            ></div>
                            <div
                                class="skeleton"
                                style="
                                    width: 14rem;
                                    height: 0.75rem;
                                    border-radius: 0.375rem;
                                "
                            ></div>
                        </div>
                    </div>
                    <div
                        style="
                            flex: 1;
                            max-width: 16rem;
                            display: flex;
                            flex-direction: column;
                            gap: 0.5rem;
                        "
                    >
                        <div
                            style="
                                display: flex;
                                justify-content: space-between;
                            "
                        >
                            <div
                                class="skeleton"
                                style="
                                    width: 7rem;
                                    height: 0.75rem;
                                    border-radius: 0.375rem;
                                "
                            ></div>
                            <div
                                class="skeleton"
                                style="
                                    width: 2.5rem;
                                    height: 0.75rem;
                                    border-radius: 0.375rem;
                                "
                            ></div>
                        </div>
                        <div
                            class="skeleton"
                            style="
                                width: 100%;
                                height: 0.5rem;
                                border-radius: 999px;
                            "
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Budget cards skeleton -->
            <div class="budgets-grid sk-card">
                <div v-for="i in 4" :key="i" class="glass-card budget-card">
                    <!-- Header: icon + name + badge -->
                    <div
                        style="
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            margin-bottom: 1rem;
                        "
                    >
                        <div
                            style="
                                display: flex;
                                align-items: center;
                                gap: 0.625rem;
                            "
                        >
                            <div
                                class="skeleton"
                                style="
                                    width: 2rem;
                                    height: 2rem;
                                    border-radius: 0.625rem;
                                    flex-shrink: 0;
                                "
                            ></div>
                            <div
                                class="skeleton"
                                style="
                                    width: 6rem;
                                    height: 0.875rem;
                                    border-radius: 0.375rem;
                                "
                            ></div>
                        </div>
                        <div
                            class="skeleton"
                            style="
                                width: 3rem;
                                height: 1.375rem;
                                border-radius: 999px;
                            "
                        ></div>
                    </div>
                    <!-- Fuel gauge arc placeholder -->
                    <div
                        class="skeleton"
                        style="
                            width: 7rem;
                            height: 7rem;
                            border-radius: 50%;
                            margin: 0 auto 1rem;
                        "
                    ></div>
                    <!-- Progress bar -->
                    <div
                        class="skeleton"
                        style="
                            width: 100%;
                            height: 0.5rem;
                            border-radius: 999px;
                            margin-bottom: 0.75rem;
                        "
                    ></div>
                    <!-- Amounts -->
                    <div style="display: flex; justify-content: space-between">
                        <div
                            class="skeleton"
                            style="
                                width: 5rem;
                                height: 0.75rem;
                                border-radius: 0.375rem;
                            "
                        ></div>
                        <div
                            class="skeleton"
                            style="
                                width: 4rem;
                                height: 0.75rem;
                                border-radius: 0.375rem;
                            "
                        ></div>
                    </div>
                </div>
            </div>
        </template>

        <template v-else-if="budgets.length">
            <!-- Summary Status Header Banner -->
            <div class="glass-card budget-summary-banner mb-6">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="summary-badge-icon"
                            :class="
                                onTrackCount === budgets.length
                                    ? 'bg-success-light text-success'
                                    : 'bg-warning-light text-warning'
                            "
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-primary-color">
                                {{ onTrackCount }} of
                                {{ budgets.length }} budgets on track
                            </h3>
                            <p class="text-xs text-muted">
                                Total Budget:
                                {{ formatCurrency(totalBudgetAmount) }} | Total
                                Spent: {{ formatCurrency(totalSpentAmount) }}
                            </p>
                        </div>
                    </div>
                    <div class="overall-progress-track flex-1 max-w-xs">
                        <div
                            class="flex justify-between text-xs font-semibold mb-1"
                        >
                            <span>Overall Utilization</span>
                            <span
                                >{{
                                    Math.round(
                                        (totalSpentAmount /
                                            (totalBudgetAmount || 1)) *
                                            100,
                                    )
                                }}%</span
                            >
                        </div>
                        <div class="budget-bar-track">
                            <div
                                class="budget-bar-fill"
                                :class="
                                    totalSpentAmount / totalBudgetAmount > 1
                                        ? 'bar-danger'
                                        : totalSpentAmount / totalBudgetAmount >
                                            0.8
                                          ? 'bar-warning'
                                          : 'bar-success'
                                "
                                :style="{
                                    width:
                                        Math.min(
                                            Math.round(
                                                (totalSpentAmount /
                                                    (totalBudgetAmount || 1)) *
                                                    100,
                                            ),
                                            100,
                                        ) + '%',
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fuel Gauge Budget Cards -->
            <div class="budgets-grid">
                <div
                    v-for="b in budgets"
                    :key="b.id"
                    class="glass-card budget-card"
                    :class="
                        b.percentage > 100
                            ? 'budget-card--over'
                            : b.percentage > 80
                              ? 'budget-card--warn'
                              : ''
                    "
                >
                    <!-- Card Header -->
                    <div class="budget-top">
                        <div class="budget-identity">
                            <div
                                class="budget-icon"
                                :style="{
                                    backgroundColor: b.color + '20',
                                    color: b.color,
                                }"
                            >
                                <component
                                    :is="
                                        getCategoryIcon(
                                            b.category?.name || b.category,
                                        )
                                    "
                                    class="w-4 h-4"
                                />
                            </div>
                            <span class="budget-category">{{
                                b.category?.name || b.category
                            }}</span>
                        </div>
                        <div class="budget-header-right">
                            <button
                                @click="openModal(b)"
                                class="action-btn action-btn--edit"
                                title="Edit Budget"
                            >
                                <svg
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                            </button>
                            <span
                                :class="
                                    b.percentage > 100
                                        ? 'badge badge-danger'
                                        : b.percentage > 80
                                          ? 'badge badge-warning'
                                          : 'badge badge-success'
                                "
                            >
                                {{ b.percentage }}%
                            </span>
                            <button
                                @click="deleteBudget(b)"
                                class="action-btn action-btn--delete"
                                title="Delete"
                            >
                                <svg
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Progress Bar (Fuel Gauge Track) -->
                    <div class="budget-bar-track my-3">
                        <div
                            class="budget-bar-fill"
                            :class="
                                b.percentage > 100
                                    ? 'bar-danger'
                                    : b.percentage > 80
                                      ? 'bar-warning'
                                      : 'bar-success'
                            "
                            :style="{
                                width: Math.min(b.percentage, 100) + '%',
                            }"
                        ></div>
                    </div>

                    <!-- Amounts -->
                    <div class="budget-amounts">
                        <span class="tabular-nums"
                            >Spent:
                            <strong class="amount-spent">{{
                                formatCurrency(b.spent)
                            }}</strong></span
                        >
                        <span class="tabular-nums"
                            >Limit:
                            <strong class="amount-limit">{{
                                formatCurrency(b.budget)
                            }}</strong></span
                        >
                    </div>

                    <!-- Status Note -->
                    <div
                        v-if="b.percentage > 100"
                        class="budget-status budget-status--over"
                    >
                        <svg
                            class="w-3.5 h-3.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                        Over budget by {{ formatCurrency(b.spent - b.budget) }}
                    </div>
                    <div v-else class="budget-status budget-status--ok">
                        {{ formatCurrency(b.remaining) }} remaining
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty State -->
        <div v-else class="empty-state">
            <svg
                class="empty-icon"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                />
            </svg>
            <p class="empty-title">No budgets for {{ formattedMonthLabel }}</p>
            <p class="empty-hint">
                Click "Set Budget" to add a spending limit.
            </p>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal-overlay">
            <div class="modal-panel">
                <div class="modal-header">
                    <h3 class="modal-title">
                        {{ editingBudget ? "Edit Budget" : "Set Budget" }}
                    </h3>
                    <button @click="showModal = false" class="modal-close">
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="saveBudget" class="modal-form">
                    <div>
                        <label class="label">Category (Expense)</label>
                        <select
                            v-model="form.category_id"
                            required
                            class="input-field"
                        >
                            <option value="">Select category...</option>
                            <option
                                v-for="cat in expenseCategories"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="label"
                            >Monthly Limit ({{ getCurrencySymbol() }})</label
                        >
                        <input
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            min="1"
                            required
                            placeholder="e.g. 5000"
                            class="input-field"
                        />
                    </div>
                    <div>
                        <label class="label">Month</label>
                        <input
                            v-model="form.month"
                            type="month"
                            required
                            class="input-field"
                            :disabled="!!editingBudget"
                        />
                    </div>
                    <div v-if="formError" class="alert-danger">
                        {{ formError }}
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="btn-ghost modal-btn"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="btn-primary modal-btn"
                        >
                            {{
                                saving
                                    ? "Saving..."
                                    : editingBudget
                                      ? "Update Budget"
                                      : "Set Budget"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, inject } from "vue";
import axios from "axios";
import { formatCurrency, getCurrencySymbol } from "../utils/currency";
import {
    ShoppingCartIcon,
    HomeIcon,
    BoltIcon,
    ArrowPathIcon,
    ShoppingBagIcon,
    BanknotesIcon,
    BeakerIcon,
    TruckIcon,
    SparklesIcon,
    TagIcon,
} from "@heroicons/vue/24/outline";

const toast = inject("toast");
const loading = ref(true);
const budgets = ref([]);
const expenseCategories = ref([]);
const showModal = ref(false);
const editingBudget = ref(null);
const saving = ref(false);
const formError = ref("");

const now = new Date();
const selectedMonth = ref(
    `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`,
);

const form = reactive({
    category_id: "",
    amount: "",
    month: selectedMonth.value,
});

const formattedMonthLabel = computed(() => {
    if (!selectedMonth.value) return "";
    const [year, month] = selectedMonth.value.split("-");
    const date = new Date(parseInt(year), parseInt(month) - 1, 1);
    return date.toLocaleDateString("en-PH", { month: "long", year: "numeric" });
});

const onTrackCount = computed(
    () => budgets.value.filter((b) => b.percentage <= 100).length,
);
const totalBudgetAmount = computed(() =>
    budgets.value.reduce((acc, b) => acc + (parseFloat(b.budget) || 0), 0),
);
const totalSpentAmount = computed(() =>
    budgets.value.reduce((acc, b) => acc + (parseFloat(b.spent) || 0), 0),
);

function prevMonth() {
    const [year, month] = selectedMonth.value.split("-").map(Number);
    const d = new Date(year, month - 2, 1);
    selectedMonth.value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
    fetchBudgets();
}

function nextMonth() {
    const [year, month] = selectedMonth.value.split("-").map(Number);
    const d = new Date(year, month, 1);
    selectedMonth.value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
    fetchBudgets();
}

function getCategoryIcon(name = "") {
    const n = String(name).toLowerCase();
    if (n.includes("groc") || n.includes("market")) return ShoppingCartIcon;
    if (n.includes("rent") || n.includes("hous") || n.includes("home"))
        return HomeIcon;
    if (n.includes("elec") || n.includes("util") || n.includes("power"))
        return BoltIcon;
    if (n.includes("sub") || n.includes("netfl") || n.includes("spot"))
        return ArrowPathIcon;
    if (n.includes("shop") || n.includes("cloth") || n.includes("store"))
        return ShoppingBagIcon;
    if (
        n.includes("sal") ||
        n.includes("pay") ||
        n.includes("incom") ||
        n.includes("earn")
    )
        return BanknotesIcon;
    if (n.includes("water") || n.includes("bill")) return BeakerIcon;
    if (
        n.includes("trans") ||
        n.includes("gas") ||
        n.includes("car") ||
        n.includes("ride")
    )
        return TruckIcon;
    if (
        n.includes("eat") ||
        n.includes("din") ||
        n.includes("rest") ||
        n.includes("food")
    )
        return SparklesIcon;
    return TagIcon;
}

async function fetchBudgets() {
    loading.value = true;
    try {
        const { data } = await axios.get("/api/budgets", {
            params: { month: selectedMonth.value },
        });
        budgets.value = data;
    } finally {
        loading.value = false;
    }
}

async function fetchCategories() {
    const { data } = await axios.get("/api/categories");
    expenseCategories.value = data.filter((c) => c.type === "expense");
}

function openModal(budget = null) {
    formError.value = "";
    editingBudget.value = budget;

    if (budget) {
        form.category_id = budget.category_id;
        form.amount = budget.amount;
        form.month = budget.month;
    } else {
        form.category_id = "";
        form.amount = "";
        form.month = selectedMonth.value;
    }

    showModal.value = true;
}

async function saveBudget() {
    formError.value = "";
    saving.value = true;
    try {
        const payload = {
            category_id: form.category_id,
            amount: form.amount,
            month: form.month,
        };

        if (editingBudget.value) {
            await axios.put(`/api/budgets/${editingBudget.value.id}`, payload);
            toast("Budget updated");
        } else {
            await axios.post("/api/budgets", payload);
            toast("Budget set");
        }

        showModal.value = false;
        editingBudget.value = null;
        fetchBudgets();
    } catch (e) {
        formError.value = e.response?.data?.message || "Failed to save";
    } finally {
        saving.value = false;
    }
}

async function deleteBudget(b) {
    if (!confirm("Delete this budget?")) return;
    try {
        await axios.delete(`/api/budgets/${b.id}`);
        toast("Budget deleted");
        fetchBudgets();
    } catch (e) {
        toast("Delete failed", "error");
    }
}

onMounted(() => {
    fetchBudgets();
    fetchCategories();
});
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

.header-actions {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    flex-shrink: 0;
}

/* Month Navigator Pill */
.month-navigator-pill {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--bg-surface);
    border: 1px solid var(--border-strong);
    border-radius: 9999px;
    padding: 0.25rem 0.75rem;
}

.month-nav-btn {
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.15s;
}

.month-nav-btn:hover {
    color: var(--text-primary);
    background: var(--bg-surface-2);
}

.month-label {
    font-size: 0.8125rem;
    color: var(--text-primary);
    min-width: 6.5rem;
    text-align: center;
}

/* Budget Summary Banner */
.budget-summary-banner {
    padding: 1.25rem 1.5rem;
    border-radius: 1.25rem;
    background: var(--bg-glass);
}

.summary-badge-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sk-card {
    pointer-events: none;
}

/* ── Budgets Grid ─────────────────────────────────────────── */
.budgets-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

@media (min-width: 640px) {
    .budgets-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (min-width: 1280px) {
    .budgets-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* ── Budget Card ──────────────────────────────────────────── */
.budget-card {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.budget-card--over {
    border-color: var(--danger) !important;
}
.budget-card--warn {
    border-color: var(--warning) !important;
}

.budget-card:hover {
    transform: none;
}

.budget-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.budget-identity {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    min-width: 0;
}

.budget-icon {
    width: 32px;
    height: 32px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.budget-category {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.budget-header-right {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    flex-shrink: 0;
}

/* ── Progress Bar ─────────────────────────────────────────── */
.budget-bar-track {
    width: 100%;
    height: 8px;
    background-color: var(--bg-surface-2);
    border-radius: 9999px;
    overflow: hidden;
}

.budget-bar-fill {
    height: 100%;
    border-radius: 9999px;
    transition: width 0.5s ease;
}

.bar-success {
    background-color: var(--success);
}
.bar-warning {
    background-color: var(--warning);
}
.bar-danger {
    background-color: var(--danger);
}

/* ── Amounts ──────────────────────────────────────────────── */
.budget-amounts {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: var(--text-muted);
}

.amount-spent {
    color: var(--text-primary);
}
.amount-limit {
    color: var(--text-primary);
}

/* ── Status Notes ─────────────────────────────────────────── */
.budget-status {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.budget-status--over {
    color: var(--danger);
}
.budget-status--ok {
    color: var(--text-muted);
}

/* ── Action Buttons ───────────────────────────────────────── */
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
    transition:
        background-color 0.15s,
        color 0.15s;
    color: var(--text-muted);
}

.action-btn--edit:hover {
    background-color: var(--primary-light);
    color: var(--primary);
}

.action-btn--delete:hover {
    background-color: var(--danger-light);
    color: var(--danger);
}

/* ── Empty State ─────────────────────────────────────────── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 5rem 1rem;
    gap: 0.5rem;
    text-align: center;
}

.empty-icon {
    width: 48px;
    height: 48px;
    color: var(--text-muted);
    opacity: 0.4;
}

.empty-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin: 0.5rem 0 0;
}

.empty-hint {
    font-size: 0.8125rem;
    color: var(--text-muted);
    margin: 0;
}

/* ── Modal ────────────────────────────────────────────────── */
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

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
    transition:
        background-color 0.15s,
        color 0.15s;
}

.modal-close:hover {
    background-color: var(--bg-surface-2);
    color: var(--text-primary);
}

.modal-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.modal-footer {
    display: flex;
    gap: 0.75rem;
    padding-top: 0.25rem;
}

.modal-btn {
    flex: 1;
    justify-content: center;
}
</style>

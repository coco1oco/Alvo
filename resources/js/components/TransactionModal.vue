<template>
    <div class="modal-overlay" @paste="handlePaste">
        <div class="modal-panel modal-panel--xl">
            <!-- Header -->
            <div class="modal-header">
                <h3 class="modal-title">
                    {{ isEdit ? "Edit Transaction" : "Add Transaction" }}
                </h3>
                <button type="button" @click="$emit('close')" class="modal-close" title="Close">
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

            <form @submit.prevent="submit" class="modal-form">
                <!-- Pay Bill banner -->
                <div v-if="isPayBillMode" class="pay-bill-banner">
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
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                        />
                    </svg>
                    Pay Bill — Select which bank account to pay from. The amount
                    will reduce your card balance.
                </div>

                <!-- Responsive Grid Layout (Single Column centered for Income/Transfer, 2-Column for Expense) -->
                <div :class="['modal-grid', form.type !== 'expense' ? 'modal-grid--single' : '']">
                    <!-- LEFT COLUMN: Main Details & Receipt Upload -->
                    <div class="grid-col left-col">
                        <!-- Type Selector -->
                        <div>
                            <label class="label">Type</label>
                            <div class="type-toggle">
                                <button
                                    type="button"
                                    v-for="t in ['income', 'expense', 'transfer']"
                                    :key="t"
                                    @click="!isPayBillMode && (form.type = t)"
                                    :class="[
                                        'type-btn',
                                        typeClass(t),
                                        isPayBillMode && form.type !== t
                                            ? 'type-btn--disabled'
                                            : '',
                                    ]"
                                    :disabled="isPayBillMode"
                                >
                                    {{ t }}
                                </button>
                            </div>
                        </div>

                        <!-- Hero Amount & Date -->
                        <div class="form-row">
                            <div class="form-col amount-col">
                                <label class="label">Amount</label>
                                <div class="hero-amount-wrapper">
                                    <span class="currency-symbol">{{ getCurrencySymbol() }}</span>
                                    <input
                                        v-model="form.amount"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        required
                                        placeholder="0.00"
                                        class="input-field hero-amount-input"
                                        @input="recalculateEqualSplit"
                                    />
                                </div>
                            </div>
                            <div class="form-col date-col">
                                <label class="label">Date</label>
                                <input
                                    v-model="form.date"
                                    type="date"
                                    required
                                    class="input-field"
                                />
                            </div>
                        </div>

                        <!-- From Account -->
                        <div>
                            <label class="label">{{
                                form.type === "transfer" ? "From Account" : "Account"
                            }}</label>
                            <select
                                v-model="form.account_id"
                                required
                                class="input-field"
                            >
                                <option value="">Select account...</option>
                                <option
                                    v-for="acc in form.type === 'transfer'
                                        ? sourceAccounts
                                        : accounts"
                                    :key="acc.id"
                                    :value="acc.id"
                                >
                                    {{ formatAccountOption(acc) }}
                                </option>
                            </select>
                        </div>

                        <!-- To Account (transfer only) -->
                        <div v-if="form.type === 'transfer'">
                            <label class="label">To Account</label>
                            <select
                                v-model="form.to_account_id"
                                required
                                class="input-field"
                                :disabled="isPayBillMode"
                            >
                                <option value="">Select destination...</option>
                                <option
                                    v-for="acc in destinationAccounts"
                                    :key="acc.id"
                                    :value="acc.id"
                                >
                                    {{ formatAccountOption(acc) }}
                                </option>
                            </select>
                        </div>

                        <!-- Category (income / expense only) -->
                        <div v-if="form.type !== 'transfer'">
                            <label class="label">Category</label>
                            <select v-model="form.category_id" class="input-field">
                                <option value="">No category</option>
                                <option
                                    v-for="cat in filteredCategories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="label"
                                >Description
                                <span class="label-optional">(optional)</span></label
                            >
                            <input
                                v-model="form.description"
                                type="text"
                                placeholder="What's this for?"
                                class="input-field"
                            />
                        </div>

                        <!-- Receipt Attachment Dropzone / Clipboard Paste -->
                        <div class="receipt-section">
                            <label class="label flex items-center justify-between">
                                <span>Receipt / Screenshot</span>
                                <span class="text-xs text-muted font-normal">Paste image with Ctrl+V</span>
                            </label>

                            <div
                                class="receipt-dropzone"
                                :class="{ 'receipt-dropzone--active': isDragging }"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                            >
                                <input
                                    type="file"
                                    ref="fileInput"
                                    @change="onFileSelected"
                                    accept="image/jpeg,image/png,image/webp,application/pdf"
                                    class="hidden-file-input"
                                    id="receipt-file-input"
                                />

                                <div v-if="attachmentPreviewUrl || existingAttachmentPath" class="receipt-preview">
                                    <img
                                        v-if="isImageAttachment"
                                        :src="attachmentPreviewUrl || existingAttachmentPath"
                                        alt="Receipt thumbnail"
                                        class="receipt-thumb"
                                    />
                                    <div v-else class="pdf-thumb">
                                        <span>📄 PDF Document</span>
                                    </div>
                                    <div class="receipt-meta">
                                        <span class="truncate text-xs font-medium text-primary-color">{{ selectedFileName || 'Attached Receipt' }}</span>
                                        <button type="button" @click="clearAttachment" class="text-xs text-danger hover:underline">Remove</button>
                                    </div>
                                </div>

                                <label v-else for="receipt-file-input" class="dropzone-prompt">
                                    <svg class="w-5 h-5 text-muted mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-xs font-semibold text-primary">Upload screenshot / receipt</span>
                                    <span class="text-[11px] text-muted">Click, drag &amp; drop, or paste (Ctrl+V)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Expense-only options (Reimbursable & Split Expenses) -->
                    <div v-if="form.type === 'expense'" class="grid-col right-col">
                        <!-- Reimbursable Expense Switch (Expense type only) -->
                        <div class="reimbursable-card">
                            <label class="reimbursable-toggle">
                                <input v-model="form.is_reimbursable" type="checkbox" />
                                <span>
                                    <strong>Reimbursable expense</strong>
                                    <small>Mark for company or personal reimbursement</small>
                                </span>
                            </label>
                        </div>

                        <!-- Split Expense Section (Expense type only) -->
                        <div class="split-section">
                            <div class="split-toggle-row">
                                <label class="split-toggle">
                                    <input v-model="form.is_split" type="checkbox" />
                                    <span>
                                        <strong>Split this expense</strong>
                                        <small>Track shares owed by other people</small>
                                    </span>
                                </label>
                            </div>

                            <div v-if="form.is_split" class="split-panel">
                                <div class="split-panel-header">
                                    <div class="split-mode-selector">
                                        <button
                                            type="button"
                                            :class="['split-mode-btn', form.split_mode === 'equal' ? 'split-mode-btn--active' : '']"
                                            @click="setSplitMode('equal')"
                                        >
                                            Equal
                                        </button>
                                        <button
                                            type="button"
                                            :class="['split-mode-btn', form.split_mode === 'custom' ? 'split-mode-btn--active' : '']"
                                            @click="setSplitMode('custom')"
                                        >
                                            Custom
                                        </button>
                                    </div>

                                    <label v-if="form.split_mode === 'equal'" class="equal-self-checkbox">
                                        <input v-model="form.split_include_self" type="checkbox" @change="recalculateEqualSplit" />
                                        <span>Inc. me</span>
                                    </label>
                                </div>

                                <div class="split-rows">
                                    <div
                                        v-for="(participant, index) in form.split_participants"
                                        :key="participant.id"
                                        class="split-row-compact"
                                    >
                                        <input
                                            v-model="participant.name"
                                            type="text"
                                            placeholder="Name"
                                            class="input-field input-field--sm"
                                        />
                                        <input
                                            v-model="participant.amount"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            placeholder="0.00"
                                            class="input-field input-field--sm"
                                            :readonly="form.split_mode === 'equal'"
                                        />
                                        <label class="settle-compact-check" title="Mark as paid">
                                            <input v-model="participant.is_settled" type="checkbox" />
                                            <span :class="participant.is_settled ? 'text-success' : 'text-muted'">{{ participant.is_settled ? 'Paid' : 'Unpaid' }}</span>
                                        </label>
                                        <button
                                            v-if="form.split_participants.length > 1"
                                            type="button"
                                            @click="removeSplitParticipant(index)"
                                            class="split-remove-btn-compact"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </div>

                                <div class="split-panel-footer">
                                    <button
                                        type="button"
                                        @click="addSplitParticipant"
                                        class="btn-ghost split-add-btn"
                                    >
                                        + Add person
                                    </button>

                                    <div class="split-summary-compact">
                                        <span>Tracked: <strong>{{ formatCurrency(splitParticipantTotal) }}</strong></span>
                                        <span>Mine: <strong>{{ formatCurrency(splitRemainingAmount) }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error -->
                <div v-if="error" class="alert-danger mt-2">{{ error }}</div>

                <!-- Actions -->
                <div class="modal-footer">
                    <button
                        v-if="isEdit"
                        type="button"
                        @click="deleteTxn"
                        class="btn-ghost modal-btn text-danger"
                    >
                        Delete
                    </button>
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="btn-ghost modal-btn"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="modal-btn submit-btn"
                        :class="submitClass"
                    >
                        <span v-if="loading" class="btn-spinner"></span>
                        {{
                            loading
                                ? "Saving..."
                                : isEdit
                                  ? "Update"
                                  : "Add Transaction"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, inject, watch } from "vue";
import axios from "axios";
import { formatCurrency, getCurrencySymbol } from "../utils/currency";

const props = defineProps({
    transaction: { type: Object, default: null },
    defaultAccountId: { type: [Number, String], default: "" },
    payBillTargetId: { type: [Number, String], default: null },
    payBillAmount: { type: Number, default: 0 },
});
const emit = defineEmits(["close", "saved"]);
const toast = inject("toast");

const isPayBillMode = computed(() => !!props.payBillTargetId);
const isEdit = computed(() => !!props.transaction);
const loading = ref(false);
const error = ref("");
const accounts = ref([]);
const categories = ref([]);

const selectedFile = ref(null);
const selectedFileName = ref("");
const attachmentPreviewUrl = ref("");
const existingAttachmentPath = ref(props.transaction?.attachment_path ?? "");
const isDragging = ref(false);

const isImageAttachment = computed(() => {
    if (selectedFile.value) {
        return selectedFile.value.type.startsWith("image/");
    }
    const path = existingAttachmentPath.value;
    return path && (path.endsWith(".jpg") || path.endsWith(".jpeg") || path.endsWith(".png") || path.endsWith(".webp"));
});

const form = reactive({
    type: isPayBillMode.value
        ? "transfer"
        : (props.transaction?.type ?? "expense"),
    account_id: props.transaction?.account_id || props.defaultAccountId || (localStorage.getItem('alvo_pref_default_account') ? Number(localStorage.getItem('alvo_pref_default_account')) : ""),
    to_account_id: isPayBillMode.value
        ? props.payBillTargetId
        : (props.transaction?.to_account_id ?? ""),
    category_id: props.transaction?.category_id ?? "",
    amount: isPayBillMode.value
        ? props.payBillAmount
        : (props.transaction?.amount ?? ""),
    is_split: props.transaction?.is_split ?? false,
    split_mode: props.transaction?.split_data?.split_mode ?? "equal",
    split_include_self: props.transaction?.split_data?.include_self ?? true,
    split_participants: props.transaction?.split_data?.participants?.length
        ? props.transaction.split_data.participants.map(
              (participant, index) => ({
                  id: `${Date.now()}-${index}`,
                  name: participant.name ?? "",
                  amount: participant.amount ?? "",
                  is_settled: !!participant.is_settled,
              }),
          )
        : [{ id: `${Date.now()}-0`, name: "", amount: "", is_settled: false }],
    description: isPayBillMode.value
        ? "Credit card payment"
        : (props.transaction?.description ?? ""),
    is_reimbursable: props.transaction?.is_reimbursable ?? false,
    date: props.transaction?.date
        ? props.transaction.date.substring(0, 10)
        : new Date().toISOString().substring(0, 10),
});

const sourceAccounts = computed(() =>
    accounts.value.filter((a) => a.type !== "credit_card"),
);

const destinationAccounts = computed(() =>
    accounts.value.filter((a) => a.id !== form.account_id),
);

const filteredCategories = computed(() =>
    categories.value.filter((c) => c.type === form.type),
);

function typeClass(t) {
    if (form.type !== t) return "";
    if (t === "income") return "type-btn--income";
    if (t === "expense") return "type-btn--expense";
    return "type-btn--transfer";
}

const submitClass = computed(() => {
    if (form.type === "income") return "submit-btn--income";
    if (form.type === "expense") return "submit-btn--expense";
    return "submit-btn--transfer";
});

const splitParticipantTotal = computed(() =>
    form.split_participants.reduce(
        (sum, participant) => sum + (Number(participant.amount) || 0),
        0,
    ),
);

const splitRemainingAmount = computed(() => {
    const amount = Number(form.amount) || 0;
    return amount - splitParticipantTotal.value;
});

function recalculateEqualSplit() {
    if (form.split_mode !== "equal") return;
    const totalAmount = Number(form.amount) || 0;
    const count = form.split_participants.length;
    if (count === 0 || totalAmount <= 0) return;

    const divisor = form.split_include_self ? count + 1 : count;
    const perPersonShare = Math.round((totalAmount / divisor) * 100) / 100;

    form.split_participants.forEach((p) => {
        p.amount = perPersonShare;
    });
}

function setSplitMode(mode) {
    form.split_mode = mode;
    if (mode === "equal") {
        recalculateEqualSplit();
    }
}

function createSplitParticipant() {
    return {
        id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
        name: "",
        amount: "",
        is_settled: false,
    };
}

function addSplitParticipant() {
    form.split_participants.push(createSplitParticipant());
    if (form.split_mode === "equal") {
        recalculateEqualSplit();
    }
}

function removeSplitParticipant(index) {
    if (form.split_participants.length === 1) return;
    form.split_participants.splice(index, 1);
    if (form.split_mode === "equal") {
        recalculateEqualSplit();
    }
}

function normalizeSplitParticipants() {
    return form.split_participants
        .map((participant) => ({
            name: String(participant.name ?? "").trim(),
            amount: Number(participant.amount) || 0,
            is_settled: !!participant.is_settled,
        }))
        .filter((participant) => participant.name && participant.amount > 0);
}

function setFile(file) {
    if (!file) return;
    selectedFile.value = file;
    selectedFileName.value = file.name;
    attachmentPreviewUrl.value = URL.createObjectURL(file);
    toast("Receipt attachment added");
}

function onFileSelected(event) {
    const file = event.target.files?.[0];
    if (file) setFile(file);
}

function handleDrop(event) {
    isDragging.value = false;
    const file = event.dataTransfer?.files?.[0];
    if (file) setFile(file);
}

function handlePaste(event) {
    const items = event.clipboardData?.items;
    if (!items) return;

    for (let i = 0; i < items.length; i++) {
        if (items[i].type.startsWith("image/")) {
            const blob = items[i].getAsFile();
            if (blob) {
                const pastedFile = new File([blob], `screenshot-${Date.now()}.png`, { type: blob.type });
                setFile(pastedFile);
                break;
            }
        }
    }
}

function clearAttachment() {
    selectedFile.value = null;
    selectedFileName.value = "";
    attachmentPreviewUrl.value = "";
    existingAttachmentPath.value = "";
}

function formatAccountOption(acc) {
    if (!acc) return "";
    if (acc.type === "credit_card") {
        const owed = Math.max(parseFloat(acc.balance) || 0, 0);
        return `${acc.name} (${formatCurrency(owed)} Owed)`;
    }
    return `${acc.name} (${formatCurrency(acc.balance)})`;
}

async function fetchFormData() {
    const [accRes, catRes] = await Promise.all([
        axios.get("/api/accounts"),
        axios.get("/api/categories"),
    ]);
    accounts.value = accRes.data;
    categories.value = catRes.data;
}

async function submit() {
    error.value = "";
    loading.value = true;
    try {
        const isSplitExpense = form.type === "expense" && form.is_split;
        const splitParticipants = isSplitExpense
            ? normalizeSplitParticipants()
            : [];

        if (isSplitExpense) {
            if (!splitParticipants.length) {
                error.value = "Add at least one person for the split.";
                loading.value = false;
                return;
            }

            if (splitParticipantTotal.value - Number(form.amount || 0) > 0.01) {
                error.value =
                    "Split amounts cannot exceed the transaction total.";
                loading.value = false;
                return;
            }
        }

        const hasFile = !!selectedFile.value;

        if (hasFile) {
            const formData = new FormData();
            formData.append("type", form.type);
            formData.append("account_id", form.account_id);
            formData.append("amount", form.amount);
            formData.append("date", form.date);
            if (form.description) formData.append("description", form.description);
            formData.append("is_reimbursable", (form.type === "expense" && form.is_reimbursable) ? "1" : "0");
            formData.append("is_split", isSplitExpense ? "1" : "0");
            if (isSplitExpense) {
                formData.append("split_data[split_mode]", form.split_mode);
                formData.append("split_data[include_self]", form.split_include_self ? "1" : "0");
                splitParticipants.forEach((p, idx) => {
                    formData.append(`split_data[participants][${idx}][name]`, p.name);
                    formData.append(`split_data[participants][${idx}][amount]`, p.amount);
                    formData.append(`split_data[participants][${idx}][is_settled]`, p.is_settled ? "1" : "0");
                });
            }
            if (form.type === "transfer") {
                formData.append("to_account_id", form.to_account_id);
            } else if (form.category_id) {
                formData.append("category_id", form.category_id);
            }
            formData.append("attachment", selectedFile.value);

            if (isEdit.value) {
                formData.append("_method", "PUT");
                await axios.post(`/api/transactions/${props.transaction.id}`, formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
            } else {
                await axios.post("/api/transactions", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
            }
        } else {
            const payload = {
                type: form.type,
                account_id: form.account_id,
                amount: form.amount,
                date: form.date,
                description: form.description || null,
                is_reimbursable: form.type === "expense" ? form.is_reimbursable : false,
                is_split: isSplitExpense,
                split_data: isSplitExpense
                    ? {
                          split_mode: form.split_mode,
                          include_self: form.split_include_self,
                          participants: splitParticipants,
                      }
                    : null,
            };
            if (form.type === "transfer") {
                payload.to_account_id = form.to_account_id;
            } else {
                payload.category_id = form.category_id || null;
            }

            if (isEdit.value) {
                await axios.put(`/api/transactions/${props.transaction.id}`, payload);
            } else {
                await axios.post("/api/transactions", payload);
            }
        }

        toast(isEdit.value ? "Transaction updated" : "Transaction added");
        emit("saved");
    } catch (e) {
        const errors = e.response?.data?.errors;
        error.value = errors
            ? Object.values(errors).flat().join(" ")
            : e.response?.data?.message || "Failed to save";
    } finally {
        loading.value = false;
    }
}

async function deleteTxn() {
    if (
        !confirm(
            "Delete this transaction? This will reverse the balance change.",
        )
    )
        return;
    loading.value = true;
    try {
        await axios.delete(`/api/transactions/${props.transaction.id}`);
        toast("Transaction deleted");
        emit("saved");
    } catch (e) {
        toast("Delete failed", "error");
        loading.value = false;
    }
}

onMounted(fetchFormData);

watch(
    () => form.type,
    (type) => {
        if (type !== "expense") {
            form.is_split = false;
            form.is_reimbursable = false;
        }
    },
);

watch(
    () => form.is_split,
    (enabled) => {
        if (enabled && form.split_participants.length === 0) {
            form.split_participants = [createSplitParticipant()];
        }

        if (!enabled) {
            form.split_participants = [createSplitParticipant()];
        }
    },
);
</script>

<style scoped>
.modal-panel--xl {
    max-width: 720px;
    width: 90vw;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 1.25rem;
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
    transition: all 0.15s;
    margin-left: auto;
    flex-shrink: 0;
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

/* ── Modal Grid Layout ────────────────────────────────────────── */
.modal-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    width: 100%;
}

@media (min-width: 640px) {
    .modal-grid {
        grid-template-columns: 1.1fr 0.9fr;
    }

    /* Single column centered for Income & Transfer */
    .modal-grid--single {
        grid-template-columns: 1fr;
        max-width: 520px;
        margin: 0 auto;
    }
}

.grid-col {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

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
    border: 1px solid var(--border-strong);
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.15s;
    text-transform: capitalize;
}

.type-btn:hover {
    background-color: var(--bg-surface-2);
    color: var(--text-primary);
}

.type-btn--income {
    background-color: var(--success-light);
    border-color: var(--success);
    color: var(--success);
}
.type-btn--expense {
    background-color: var(--danger-light);
    border-color: var(--danger);
    color: var(--danger);
}
.type-btn--transfer {
    background-color: var(--primary-light);
    border-color: var(--primary);
    color: var(--primary);
}

/* ── Hero Amount Input ───────────────────────────────────── */
.hero-amount-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.currency-symbol {
    position: absolute;
    left: 0.75rem;
    font-weight: 700;
    font-size: 1rem;
    color: var(--text-secondary);
    pointer-events: none;
}

.hero-amount-input {
    padding-left: 2.25rem;
    font-size: 1.125rem;
    font-weight: 700;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.form-col {
    display: flex;
    flex-direction: column;
}

/* ── Receipt Screenshot Dropzone ──────────────────────────── */
.receipt-dropzone {
    border: 2px dashed var(--border-strong);
    border-radius: 0.75rem;
    padding: 0.875rem;
    background: var(--bg-surface-2);
    transition: all 0.15s ease;
    cursor: pointer;
    min-height: 84px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.receipt-dropzone:hover,
.receipt-dropzone--active {
    border-color: var(--primary);
    background: var(--primary-light);
}

.hidden-file-input {
    display: none;
}

.dropzone-prompt {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    cursor: pointer;
}

.receipt-preview {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
}

.receipt-thumb {
    width: 52px;
    height: 52px;
    object-fit: cover;
    border-radius: 0.5rem;
    border: 1px solid var(--border);
}

.pdf-thumb {
    width: 52px;
    height: 52px;
    border-radius: 0.5rem;
    background: var(--bg-surface);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6875rem;
    font-weight: 700;
}

.receipt-meta {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}

/* ── Reimbursable & Split Cards ────────────────────────────── */
.reimbursable-card {
    background: var(--bg-surface-2);
    border: 1px solid var(--border);
    border-radius: 0.875rem;
    padding: 0.75rem 0.875rem;
}

.reimbursable-toggle {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    cursor: pointer;
}

.reimbursable-toggle strong {
    display: block;
    font-size: 0.8125rem;
    color: var(--text-primary);
}

.reimbursable-toggle small {
    display: block;
    font-size: 0.75rem;
    color: var(--text-muted);
}

.split-toggle-row {
    display: flex;
}

.split-toggle {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    padding: 0.75rem 0.875rem;
    border: 1px solid var(--border-strong);
    border-radius: 0.875rem;
    background: var(--bg-surface-2);
    width: 100%;
    cursor: pointer;
}

.split-toggle strong,
.split-toggle small {
    display: block;
}

.split-toggle small {
    color: var(--text-muted);
    font-size: 0.75rem;
}

.split-panel {
    border: 1px solid var(--border);
    border-radius: 0.875rem;
    padding: 0.875rem;
    background: var(--bg-surface);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.split-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.split-mode-selector {
    display: flex;
    background: var(--bg-surface-2);
    padding: 2px;
    border-radius: 0.5rem;
    border: 1px solid var(--border-strong);
}

.split-mode-btn {
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 0.375rem;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.15s;
}

.split-mode-btn--active {
    background: var(--primary);
    color: white;
}

.equal-self-checkbox {
    font-size: 0.75rem;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 0.375rem;
    cursor: pointer;
}

.split-rows {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-height: 180px;
    overflow-y: auto;
}

.split-row-compact {
    display: grid;
    grid-template-columns: 1fr 1fr auto auto;
    gap: 0.375rem;
    align-items: center;
}

.input-field--sm {
    padding: 0.375rem 0.625rem;
    font-size: 0.75rem;
}

.settle-compact-check {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.6875rem;
    font-weight: 600;
    cursor: pointer;
}

.split-remove-btn-compact {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.split-remove-btn-compact:hover {
    color: var(--danger);
    background: var(--danger-light);
}

.split-panel-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.5rem;
    border-top: 1px dashed var(--border);
    font-size: 0.75rem;
}

.split-summary-compact {
    display: flex;
    flex-direction: column;
    text-align: right;
    font-size: 0.6875rem;
    color: var(--text-muted);
}

.split-summary-compact strong {
    color: var(--text-primary);
}

/* ── Footer ────────────────────────────────────────────────── */
.modal-footer {
    display: flex;
    gap: 0.75rem;
    padding-top: 0.5rem;
}

.modal-btn {
    flex: 1;
    justify-content: center;
}

.text-danger {
    color: var(--danger);
}
.text-danger:hover {
    background-color: var(--danger-light);
}

.submit-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
    color: #fff;
}

.submit-btn--income { background-color: var(--success); }
.submit-btn--expense { background-color: var(--danger); }
.submit-btn--transfer { background-color: var(--primary); }

.submit-btn:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-spinner {
    width: 12px;
    height: 12px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

.pay-bill-banner {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 0.875rem;
    border-radius: 0.75rem;
    background: var(--primary-light);
    color: var(--primary);
    font-size: 0.75rem;
    font-weight: 600;
}
</style>

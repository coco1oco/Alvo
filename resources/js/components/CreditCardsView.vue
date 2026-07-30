<template>
    <div class="view">
        <!-- Header -->
        <div class="view-header">
            <div>
                <h1 class="view-title">Credit Cards</h1>
                <p class="view-subtitle">
                    Track card balances, credit limits, billing cycles, and pay
                    bills
                </p>
            </div>
            <button @click="openModal()" class="btn-primary">
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
                Add Credit Card
            </button>
        </div>

        <!-- Skeleton Loading -->
        <template v-if="loading">
            <div class="glass-card summary-hero-card mb-6 sk-card">
                <div class="summary-hero-content">
                    <div
                        style="
                            display: flex;
                            flex-direction: column;
                            gap: 0.5rem;
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
                                width: 12rem;
                                height: 2rem;
                                border-radius: 0.5rem;
                            "
                        ></div>
                        <div
                            class="skeleton"
                            style="
                                width: 10rem;
                                height: 0.75rem;
                                border-radius: 0.375rem;
                            "
                        ></div>
                    </div>
                    <div class="summary-breakdown">
                        <div
                            class="summary-box"
                            style="
                                gap: 0.4rem;
                                display: flex;
                                flex-direction: column;
                            "
                        >
                            <div
                                class="skeleton"
                                style="
                                    width: 4rem;
                                    height: 0.75rem;
                                    border-radius: 0.375rem;
                                "
                            ></div>
                            <div
                                class="skeleton"
                                style="
                                    width: 6rem;
                                    height: 1.125rem;
                                    border-radius: 0.375rem;
                                "
                            ></div>
                        </div>
                        <div
                            class="summary-box"
                            style="
                                gap: 0.4rem;
                                display: flex;
                                flex-direction: column;
                            "
                        >
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
                                    width: 6rem;
                                    height: 1.125rem;
                                    border-radius: 0.375rem;
                                "
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-else>
            <!-- Summary Banner -->
            <div class="glass-card summary-hero-card mb-6">
                <div class="summary-hero-content">
                    <div>
                        <span class="summary-label">Total Credit Owed</span>
                        <h2 class="summary-value tabular-nums amount-negative">
                            {{ formatCurrency(totalOwed) }}
                        </h2>
                        <p class="summary-subtext">
                            Across {{ creditCardAccounts.length }} credit card{{
                                creditCardAccounts.length === 1 ? "" : "s"
                            }}
                        </p>
                    </div>

                    <div class="summary-breakdown">
                        <div class="summary-box">
                            <span class="box-label">Total Credit Limit</span>
                            <span class="box-value tabular-nums">{{
                                formatCurrency(totalLimit)
                            }}</span>
                        </div>
                        <div class="summary-box">
                            <span class="box-label">Available Credit</span>
                            <span
                                class="box-value amount-positive tabular-nums"
                                >{{ formatCurrency(totalAvailable) }}</span
                            >
                        </div>
                        <div class="summary-box">
                            <span class="box-label">Overall Utilization</span>
                            <span
                                class="box-value tabular-nums"
                                :class="
                                    getUtilizationTextColor(overallUtilization)
                                "
                            >
                                {{ overallUtilization }}%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Overall Gauge Bar -->
                <div v-if="totalLimit > 0" class="mt-4">
                    <div
                        class="flex justify-between text-xs font-semibold text-muted mb-1.5"
                    >
                        <span
                            >Overall Credit Used:
                            {{ overallUtilization }}%</span
                        >
                        <span
                            >Available:
                            {{ formatCurrency(totalAvailable) }}</span
                        >
                    </div>
                    <div class="overall-progress-bar">
                        <div
                            class="overall-progress-fill"
                            :class="getUtilizationBarClass(overallUtilization)"
                            :style="{ width: overallUtilization + '%' }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Payment Due Schedule Alert Box -->
            <div v-if="upcomingDueCards.length" class="mb-6">
                <div class="due-schedule-card glass-card">
                    <div class="due-schedule-header">
                        <div class="flex items-center gap-2">
                            <svg
                                class="w-5 h-5 text-warning"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <h3 class="text-sm font-bold text-primary-color">
                                Upcoming Bill Statements & Due Dates
                            </h3>
                        </div>
                    </div>
                    <div class="due-schedule-grid">
                        <div
                            v-for="item in upcomingDueCards"
                            :key="item.card.id"
                            class="due-card-item"
                        >
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                    :style="{
                                        backgroundColor: item.card.color + '25',
                                        color: item.card.color,
                                    }"
                                >
                                    <img
                                        v-if="
                                            item.card.icon &&
                                            item.card.icon !== 'wallet'
                                        "
                                        :src="`/bankIcons/${item.card.icon}`"
                                        class="w-4 h-4 object-contain"
                                    />
                                    <svg
                                        v-else
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
                                </div>
                                <div>
                                    <h4
                                        class="text-xs font-bold text-primary-color"
                                    >
                                        {{ item.card.name }}
                                    </h4>
                                    <p
                                        class="text-[11px] text-muted font-medium"
                                    >
                                        Owed:
                                        {{ formatCurrency(item.card.balance) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <span
                                        v-if="item.card.due_date_day"
                                        class="text-xs font-semibold block text-primary-color"
                                        >Due
                                        {{
                                            ordinal(item.card.due_date_day)
                                        }}</span
                                    >
                                    <span
                                        v-if="item.card.billing_cycle_day"
                                        class="text-[10px] text-muted block"
                                        >Statement
                                        {{
                                            ordinal(item.card.billing_cycle_day)
                                        }}</span
                                    >
                                </div>
                                <button
                                    @click="openPayBill(item.card)"
                                    class="btn-paybill-sm"
                                >
                                    Pay Bill
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credit Cards Grid -->
            <div class="mb-8">
                <h2 class="text-lg font-bold text-primary-color mb-4">
                    Your Cards
                </h2>

                <div
                    v-if="!creditCardAccounts.length"
                    class="empty-state glass-card"
                >
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
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                        />
                    </svg>
                    <p class="empty-text">No credit cards added yet.</p>
                    <button @click="openModal()" class="btn-primary mt-3">
                        Add Your First Credit Card
                    </button>
                </div>

                <div v-else class="accounts-grid">
                    <div
                        v-for="acc in creditCardAccounts"
                        :key="acc.id"
                        class="glass-card account-card account-brand-card group relative"
                        :style="{
                            background: `linear-gradient(140deg, color-mix(in srgb, ${acc.color} 24%, var(--bg-surface)) 0%, var(--bg-surface) 100%)`,
                            borderColor: `color-mix(in srgb, ${acc.color} 40%, transparent)`,
                        }"
                    >
                        <div
                            class="account-glow"
                            :style="{ backgroundColor: acc.color }"
                        ></div>

                        <!-- Top Row: Identity + Menu -->
                        <div
                            class="account-top flex items-center justify-between"
                        >
                            <div
                                class="account-identity flex items-center gap-3"
                            >
                                <div
                                    class="account-icon shadow-sm"
                                    :style="{
                                        backgroundColor: acc.color + '30',
                                        color: acc.color,
                                    }"
                                >
                                    <img
                                        v-if="acc.icon && acc.icon !== 'wallet'"
                                        :src="`/bankIcons/${acc.icon}`"
                                        class="w-6 h-6 object-contain"
                                    />
                                    <svg
                                        v-else
                                        class="w-5 h-5"
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
                                </div>
                                <div>
                                    <h3
                                        class="account-name font-bold text-sm text-primary-color"
                                    >
                                        {{ acc.name }}
                                    </h3>
                                    <p
                                        class="account-subtag text-xs text-muted font-medium"
                                    >
                                        Credit • PHP
                                    </p>
                                </div>
                            </div>

                            <!-- Menu Button Dropdown -->
                            <div class="relative">
                                <button
                                    @click.stop="toggleAccountMenu(acc.id)"
                                    class="menu-dots-btn"
                                    title="Options"
                                >
                                    •••
                                </button>

                                <div
                                    v-if="activeMenuId === acc.id"
                                    class="account-dropdown-menu"
                                    @click.stop
                                >
                                    <button
                                        @click="
                                            hasPayableBalance(acc) &&
                                                openPayBill(acc);
                                            activeMenuId = null;
                                        "
                                        class="dropdown-item dropdown-item--paybill"
                                        :disabled="!hasPayableBalance(acc)"
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
                                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                            />
                                        </svg>
                                        Pay Bill
                                    </button>
                                    <button
                                        @click="
                                            openTransaction(acc);
                                            activeMenuId = null;
                                        "
                                        class="dropdown-item"
                                    >
                                        <svg
                                            class="w-4 h-4 text-primary"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 4v16m8-8H4"
                                            />
                                        </svg>
                                        Add Charge
                                    </button>
                                    <button
                                        @click="
                                            openModal(acc);
                                            activeMenuId = null;
                                        "
                                        class="dropdown-item"
                                    >
                                        <svg
                                            class="w-4 h-4 text-secondary-color"
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
                                        Edit Details
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <button
                                        @click="
                                            toggleArchive(acc);
                                            activeMenuId = null;
                                        "
                                        class="dropdown-item dropdown-item--danger"
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
                                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                                            />
                                        </svg>
                                        Archive Card
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Balance Section -->
                        <div class="account-balance-section mt-4">
                            <p
                                class="balance-label uppercase text-[10px] tracking-wider font-bold text-muted"
                            >
                                Used Credit (Owed)
                            </p>
                            <p
                                class="balance-value tabular-nums font-extrabold text-2xl amount-negative"
                            >
                                {{ formatCurrency(acc.balance) }}
                            </p>
                        </div>

                        <!-- Credit Gauge Bar -->
                        <div class="credit-gauge-section mt-3">
                            <div
                                class="flex justify-between text-xs font-semibold mb-1"
                                :class="
                                    getUtilizationTextColor(
                                        getCardUtilization(acc),
                                    )
                                "
                            >
                                <span>{{ getCardUtilization(acc) }}% used</span>
                                <span
                                    >{{
                                        formatCurrency(getCardAvailable(acc))
                                    }}
                                    left</span
                                >
                            </div>
                            <div class="proportion-bar-wrapper">
                                <div
                                    class="proportion-bar"
                                    :class="
                                        getUtilizationBarClass(
                                            getCardUtilization(acc),
                                        )
                                    "
                                    :style="{
                                        width: getCardUtilization(acc) + '%',
                                    }"
                                ></div>
                            </div>

                            <!-- Badges -->
                            <div
                                v-if="getCardUtilization(acc) >= 75"
                                class="credit-warning-badge mt-2"
                            >
                                <svg
                                    class="w-3 h-3"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2.5"
                                        d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                                    />
                                </svg>
                                {{
                                    getCardUtilization(acc) >= 100
                                        ? "Limit Reached"
                                        : "High Utilization (>75%)"
                                }}
                            </div>

                            <!-- Billing Info -->
                            <div
                                class="credit-limit-display mt-2 flex flex-wrap gap-x-2 gap-y-1 items-center"
                            >
                                <span
                                    >Limit:
                                    <strong>{{
                                        formatCurrency(acc.credit_limit || 0)
                                    }}</strong></span
                                >
                                <span v-if="acc.billing_cycle_day"
                                    >· Statement:
                                    {{ ordinal(acc.billing_cycle_day) }}</span
                                >
                                <span v-if="acc.due_date_day"
                                    >· Due:
                                    {{ ordinal(acc.due_date_day) }}</span
                                >
                            </div>
                        </div>

                        <!-- Card Actions Footer -->
                        <div
                            class="card-action-bar mt-4 pt-3 flex gap-2 border-t border-border/50"
                        >
                            <button
                                @click="openPayBill(acc)"
                                class="btn-primary flex-1 text-xs py-2 justify-center"
                                :disabled="!hasPayableBalance(acc)"
                                :title="
                                    hasPayableBalance(acc)
                                        ? 'Pay bill'
                                        : 'No balance due'
                                "
                            >
                                Pay Bill
                            </button>
                            <button
                                @click="openTransaction(acc)"
                                class="btn-ghost flex-1 text-xs py-2 justify-center"
                            >
                                + Charge
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Credit Card Activity Feed -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-primary-color">
                            Recent Card Activity
                        </h2>
                        <p class="text-xs text-muted">
                            Charges, refunds, and bill payments across your
                            credit cards
                        </p>
                    </div>
                </div>

                <div class="table-card glass-card p-0 overflow-hidden">
                    <div
                        v-if="loadingTxns"
                        class="p-6 text-center text-xs text-muted"
                    >
                        Loading card activity...
                    </div>
                    <table
                        v-else-if="cardTransactions.length"
                        class="w-full text-left border-collapse"
                    >
                        <thead>
                            <tr
                                class="border-b border-border/60 text-[11px] font-bold text-muted uppercase tracking-wider"
                            >
                                <th class="p-3.5">Date</th>
                                <th class="p-3.5">Card</th>
                                <th class="p-3.5">Type</th>
                                <th class="p-3.5">Category / Description</th>
                                <th class="p-3.5 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40 text-xs">
                            <tr
                                v-for="txn in cardTransactions"
                                :key="txn.id"
                                class="hover:bg-bg-surface-2/50 transition-colors"
                            >
                                <td class="p-3.5 text-muted">
                                    {{ formatDate(txn.date) }}
                                </td>
                                <td
                                    class="p-3.5 font-semibold text-primary-color"
                                >
                                    <span
                                        v-if="
                                            txn.account?.type === 'credit_card'
                                        "
                                        >{{ txn.account?.name }}</span
                                    >
                                    <span
                                        v-else-if="
                                            txn.to_account?.type ===
                                            'credit_card'
                                        "
                                        >{{ txn.to_account?.name }}</span
                                    >
                                </td>
                                <td class="p-3.5">
                                    <span
                                        v-if="
                                            txn.type === 'transfer' &&
                                            txn.to_account?.type ===
                                                'credit_card'
                                        "
                                        class="badge badge-primary"
                                    >
                                        Pay Bill (from {{ txn.account?.name }})
                                    </span>
                                    <span
                                        v-else-if="txn.type === 'expense'"
                                        class="badge badge-danger"
                                        >Charge</span
                                    >
                                    <span v-else class="badge badge-success"
                                        >Refund</span
                                    >
                                </td>
                                <td class="p-3.5 text-secondary-color">
                                    <span>{{
                                        txn.category?.name ||
                                        txn.description ||
                                        "Credit Card Activity"
                                    }}</span>
                                </td>
                                <td
                                    class="p-3.5 text-right font-bold tabular-nums"
                                    :class="
                                        txn.type === 'transfer' &&
                                        txn.to_account?.type === 'credit_card'
                                            ? 'amount-positive'
                                            : 'amount-negative'
                                    "
                                >
                                    {{
                                        txn.type === "transfer" &&
                                        txn.to_account?.type === "credit_card"
                                            ? "−"
                                            : "+"
                                    }}{{ formatCurrency(txn.amount) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="p-8 text-center text-xs text-muted">
                        No credit card charges or bill payments recorded yet.
                    </div>
                </div>
            </div>
        </template>

        <!-- Modal for Adding / Editing Credit Card -->
        <div v-if="showModal" class="modal-overlay">
            <div class="modal-panel">
                <div class="modal-header">
                    <h3 class="modal-title">
                        {{
                            editingCard ? "Edit Credit Card" : "Add Credit Card"
                        }}
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

                <form @submit.prevent="saveCard" class="modal-form">
                    <div>
                        <label class="label">Card / Account Name</label>
                        <input
                            v-model="form.name"
                            required
                            placeholder="e.g. BPI Gold Rewards"
                            class="input-field"
                        />
                    </div>

                    <div>
                        <label class="label"
                            >Total Credit Limit
                            <span class="label-required">*</span></label
                        >
                        <div class="input-with-prefix">
                            <span class="input-prefix">{{
                                getCurrencySymbol()
                            }}</span>
                            <input
                                v-model="form.credit_limit"
                                type="number"
                                step="0.01"
                                min="1"
                                required
                                placeholder="e.g. 50000"
                                class="input-field input-field--prefix"
                            />
                        </div>
                    </div>

                    <div v-if="!editingCard">
                        <label class="label"
                            >Starting Outstanding Balance
                            <span class="label-optional"
                                >(owed on card)</span
                            ></label
                        >
                        <div class="input-with-prefix">
                            <span class="input-prefix">{{
                                getCurrencySymbol()
                            }}</span>
                            <input
                                v-model="form.balance"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="input-field input-field--prefix"
                            />
                        </div>
                        <p class="field-hint">
                            Enter your current unpaid balance owed on this card.
                        </p>
                    </div>

                    <div class="form-row-2">
                        <div>
                            <label class="label"
                                >Billing Cycle Day
                                <span class="label-optional"
                                    >(optional)</span
                                ></label
                            >
                            <input
                                v-model="form.billing_cycle_day"
                                type="number"
                                min="1"
                                max="28"
                                placeholder="e.g. 25"
                                class="input-field"
                            />
                            <p class="field-hint">
                                Day of month statement cuts.
                            </p>
                        </div>
                        <div>
                            <label class="label"
                                >Payment Due Day
                                <span class="label-optional"
                                    >(optional)</span
                                ></label
                            >
                            <input
                                v-model="form.due_date_day"
                                type="number"
                                min="1"
                                max="28"
                                placeholder="e.g. 15"
                                class="input-field"
                            />
                            <p class="field-hint">
                                Day of month payment is due.
                            </p>
                        </div>
                    </div>

                    <div>
                        <div class="color-picker-header">
                            <label class="label label-no-mb"
                                >Card Color & Brand</label
                            >
                            <button
                                type="button"
                                @click="showColors = !showColors"
                                class="color-toggle-btn"
                            >
                                {{ showColors ? "Hide" : "Customize" }}
                            </button>
                        </div>
                        <div v-show="showColors" class="color-picker">
                            <button
                                type="button"
                                v-for="c in displayPalette"
                                :key="c"
                                @click="form.color = c"
                                :class="[
                                    'color-swatch',
                                    form.color === c
                                        ? 'color-swatch--active'
                                        : '',
                                ]"
                                :style="{ backgroundColor: c }"
                            ></button>
                        </div>
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
                            <span v-if="saving" class="btn-spinner"></span>
                            {{
                                saving
                                    ? "Saving..."
                                    : editingCard
                                      ? "Update Card"
                                      : "Add Card"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Transaction Modal -->
        <TransactionModal
            v-if="showTransactionModal"
            :defaultAccountId="quickTransactionAccountId"
            @close="showTransactionModal = false"
            @saved="onTransactionSaved"
        />

        <!-- Pay Bill Modal -->
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
import {
    ref,
    reactive,
    computed,
    onMounted,
    onUnmounted,
    inject,
    watch,
} from "vue";
import axios from "axios";
import TransactionModal from "./TransactionModal.vue";
import { formatCurrency, getCurrencySymbol } from "../utils/currency";

const emit = defineEmits(["refresh"]);
const toast = inject("toast");

const loading = ref(true);
const accounts = ref([]);
const cardTransactions = ref([]);
const loadingTxns = ref(false);
const showModal = ref(false);
const editingCard = ref(null);
const saving = ref(false);
const formError = ref("");
const showColors = ref(false);
const activeMenuId = ref(null);

// Transaction / Pay Bill Modals
const showTransactionModal = ref(false);
const quickTransactionAccountId = ref("");
const showPayBillModal = ref(false);
const payBillTargetId = ref(null);
const payBillAmount = ref(0);

function formatDate(d) {
    if (!d) return "—";
    return new Date(d).toLocaleDateString("en-PH", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
}

const colorPalette = [
    "#6366f1",
    "#8b5cf6",
    "#ec4899",
    "#ef4444",
    "#f97316",
    "#f59e0b",
    "#22c55e",
    "#10b981",
    "#06b6d4",
    "#3b82f6",
    "#64748b",
    "#0ea5e9",
];
const bankKeywords = [
    {
        keys: ["asia united bank", "asia united", "aub"],
        brand: "AUB",
        icon: "asia-united-bank.png",
        color: "#00478f",
    },
    {
        keys: ["banco de oro", "bdo"],
        brand: "BDO",
        icon: "bdo-unibank.svg",
        color: "#002C77",
    },
    {
        keys: [
            "bank of the philippine islands",
            "bank of the philippine",
            "bpi",
        ],
        brand: "BPI",
        icon: "bpi.svg",
        color: "#B11116",
    },
    {
        keys: ["china bank", "chinabank"],
        brand: "Chinabank",
        icon: "chinabank.png",
        color: "#B00000",
    },
    {
        keys: ["cimb bank", "cimb"],
        brand: "CIMB",
        icon: "cimb-logo.svg",
        color: "#7E002B",
    },
    {
        keys: ["east west bank", "eastwest bank", "east west", "eastwest"],
        brand: "EastWest",
        icon: "eastwest.png",
        color: "#4D148C",
    },
    {
        keys: ["go tyme bank", "gotyme bank", "go tyme", "gotyme"],
        brand: "GoTyme",
        icon: "go-tyme-bank.svg",
        color: "#00B1FF",
    },
    { keys: ["hsbc"], brand: "HSBC", icon: "hsbc.svg", color: "#db0011" },
    {
        keys: ["land bank", "landbank"],
        brand: "Landbank",
        icon: "landbank.svg",
        color: "#005934",
    },
    {
        keys: ["mari bank", "maribank"],
        brand: "MariBank",
        icon: "mari-bank-philippines.svg",
        color: "#FF5C00",
    },
    {
        keys: ["paymaya", "maya bank", "maya"],
        brand: "Maya",
        icon: "maya.svg",
        color: "#06C068",
    },
    {
        keys: ["metro bank", "metrobank"],
        brand: "Metrobank",
        icon: "metrobank.svg",
        color: "#0033A0",
    },
    {
        keys: ["pay pal", "paypal"],
        brand: "PayPal",
        icon: "pay-pal-logo-alternative.svg",
        color: "#003087",
    },
    {
        keys: ["philippine national bank", "philippine national", "pnb"],
        brand: "PNB",
        icon: "philippine-national-bank.svg",
        color: "#003A70",
    },
    {
        keys: ["ps bank", "psbank"],
        brand: "PSBank",
        icon: "psbank-official.svg",
        color: "#005BAA",
    },
    {
        keys: ["rizal commercial banking", "rizal commercial", "rcbc"],
        brand: "RCBC",
        icon: "rizal-commercial-banking.svg",
        color: "#0038A8",
    },
    {
        keys: ["salmon"],
        brand: "Salmon",
        icon: "salmon.jpeg",
        color: "#FF7F50",
    },
    {
        keys: ["security bank", "securitybank"],
        brand: "Security Bank",
        icon: "security-bank-corporation.svg",
        color: "#003F98",
    },
    {
        keys: ["tonik bank", "tonik"],
        brand: "Tonik",
        icon: "tonik.svg",
        color: "#512A7C",
    },
    {
        keys: ["union bank of the philippines", "union bank", "unionbank"],
        brand: "UnionBank",
        icon: "union-bank-of-the-philippines.svg",
        color: "#ED6322",
    },
    {
        keys: ["transferwise", "wise"],
        brand: "Wise",
        icon: "wise.svg",
        color: "#00B9FF",
    },
];

const form = reactive({
    name: "",
    type: "credit_card",
    balance: "",
    credit_limit: "",
    billing_cycle_day: "",
    due_date_day: "",
    color: "#6366f1",
    icon: "wallet",
});

const creditCardAccounts = computed(() =>
    accounts.value.filter((a) => a.type === "credit_card" && !a.is_archived),
);

const totalLimit = computed(() =>
    creditCardAccounts.value.reduce(
        (sum, a) => sum + (parseFloat(a.credit_limit) || 0),
        0,
    ),
);
const totalOwed = computed(() =>
    creditCardAccounts.value.reduce(
        (sum, a) => sum + Math.max(parseFloat(a.balance) || 0, 0),
        0,
    ),
);
const totalAvailable = computed(() =>
    Math.max(totalLimit.value - totalOwed.value, 0),
);

const overallUtilization = computed(() => {
    if (!totalLimit.value) return 0;
    return Math.min(
        Math.round((totalOwed.value / totalLimit.value) * 100),
        100,
    );
});

const upcomingDueCards = computed(() => {
    return creditCardAccounts.value
        .filter(
            (card) =>
                (card.due_date_day || card.billing_cycle_day) &&
                parseFloat(card.balance) > 0,
        )
        .map((card) => ({ card }));
});

function getCardUtilization(acc) {
    const limit = parseFloat(acc.credit_limit) || 0;
    if (!limit) return 0;
    const used = Math.max(parseFloat(acc.balance) || 0, 0);
    return Math.min(Math.round((used / limit) * 100), 100);
}

function getCardAvailable(acc) {
    const limit = parseFloat(acc.credit_limit) || 0;
    const used = Math.max(parseFloat(acc.balance) || 0, 0);
    return Math.max(limit - used, 0);
}

function hasPayableBalance(acc) {
    return Math.max(parseFloat(acc.balance) || 0, 0) > 0;
}

function getUtilizationTextColor(pct) {
    if (pct >= 75) return "text-danger font-bold";
    if (pct >= 30) return "text-warning font-semibold";
    return "text-primary-color";
}

function getUtilizationBarClass(pct) {
    if (pct >= 75) return "proportion-bar--danger";
    if (pct >= 30) return "proportion-bar--warning";
    return "proportion-bar--safe";
}

function ordinal(n) {
    const s = ["th", "st", "nd", "rd"],
        v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
}

function toggleAccountMenu(id) {
    activeMenuId.value = activeMenuId.value === id ? null : id;
}

function openModal(card = null) {
    editingCard.value = card;
    formError.value = "";
    if (card) {
        Object.assign(form, {
            name: card.name,
            type: "credit_card",
            balance: "",
            credit_limit: card.credit_limit ?? "",
            billing_cycle_day: card.billing_cycle_day ?? "",
            due_date_day: card.due_date_day ?? "",
            color: card.color,
            icon: card.icon || "wallet",
        });
    } else {
        Object.assign(form, {
            name: "",
            type: "credit_card",
            balance: "",
            credit_limit: "",
            billing_cycle_day: "",
            due_date_day: "",
            color: "#6366f1",
            icon: "wallet",
        });
    }
    showColors.value = false;
    showModal.value = true;
}

async function saveCard() {
    formError.value = "";
    saving.value = true;
    try {
        let formattedName = form.name.trim();
        for (const b of bankKeywords) {
            if (!b.brand) continue;
            const regex = new RegExp(`\\b(${b.keys.join("|")})\\b`, "ig");
            formattedName = formattedName.replace(regex, b.brand);
        }
        formattedName = formattedName.replace(/(^\w|\s\w)/g, (m) =>
            m.toUpperCase(),
        );

        const payload = {
            name: formattedName,
            type: "credit_card",
            color: form.color,
            icon: form.icon,
            credit_limit: parseFloat(form.credit_limit) || null,
            billing_cycle_day: form.billing_cycle_day
                ? parseInt(form.billing_cycle_day)
                : null,
            due_date_day: form.due_date_day
                ? parseInt(form.due_date_day)
                : null,
        };

        if (!editingCard.value) {
            payload.balance = Math.abs(parseFloat(form.balance) || 0);
        }

        if (editingCard.value) {
            await axios.put(`/api/accounts/${editingCard.value.id}`, payload);
            toast("Credit card updated");
        } else {
            await axios.post("/api/accounts", payload);
            toast("Credit card added");
        }

        showModal.value = false;
        fetchAccounts();
        emit("refresh");
    } catch (e) {
        const errors = e.response?.data?.errors;
        formError.value = errors
            ? Object.values(errors).flat().join(" ")
            : e.response?.data?.message || "Failed to save card";
    } finally {
        saving.value = false;
    }
}

async function toggleArchive(card) {
    try {
        await axios.put(`/api/accounts/${card.id}`, { is_archived: true });
        toast("Credit card archived");
        fetchAccounts();
        emit("refresh");
    } catch (e) {
        toast("Failed to archive card", "error");
    }
}

function openTransaction(card) {
    quickTransactionAccountId.value = card.id;
    showTransactionModal.value = true;
}

function onTransactionSaved() {
    showTransactionModal.value = false;
    fetchAccounts();
    fetchCardTransactions();
    emit("refresh");
}

function openPayBill(card) {
    if (!hasPayableBalance(card)) {
        toast("This card has no balance due", "error");
        return;
    }

    quickTransactionAccountId.value = "";
    payBillTargetId.value = card.id;
    payBillAmount.value = parseFloat(card.balance) || 0;
    showPayBillModal.value = true;
}

function onPayBillSaved() {
    showPayBillModal.value = false;
    fetchAccounts();
    fetchCardTransactions();
    emit("refresh");
}

const displayPalette = computed(() => {
    if (colorPalette.includes(form.color)) return colorPalette;
    return [...colorPalette, form.color];
});

watch(
    () => form.name,
    (newName) => {
        if (editingCard.value) return;
        const lower = newName.toLowerCase();
        let foundIcon = "wallet";
        let foundColor = "#6366f1";
        let matched = false;
        for (const b of bankKeywords) {
            if (b.keys.some((k) => lower.includes(k))) {
                foundIcon = b.icon;
                foundColor = b.color;
                matched = true;
                break;
            }
        }
        form.icon = foundIcon;
        if (matched) form.color = foundColor;
    },
);

async function fetchCardTransactions() {
    loadingTxns.value = true;
    try {
        const { data } = await axios.get("/api/transactions", {
            params: { type: "credit_card" },
        });
        cardTransactions.value = data.data || data;
    } catch (e) {
        console.error("Failed to load card activity", e);
    } finally {
        loadingTxns.value = false;
    }
}

async function fetchAccounts() {
    loading.value = true;
    try {
        const { data } = await axios.get("/api/accounts");
        accounts.value = data;
    } finally {
        loading.value = false;
    }
}

function closeMenuOnClickOutside() {
    activeMenuId.value = null;
}

onMounted(() => {
    fetchAccounts();
    fetchCardTransactions();
    window.addEventListener("click", closeMenuOnClickOutside);
});

onUnmounted(() => {
    window.removeEventListener("click", closeMenuOnClickOutside);
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
    align-items: center;
    justify-content: space-between;
}

.view-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}
.view-subtitle {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

/* ── Hero Summary Banner ──────────────────────────────────── */
.summary-hero-card {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
}

.summary-hero-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.summary-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
}

.summary-value {
    font-size: 2.25rem;
    font-weight: 800;
    line-height: 1.1;
    margin: 0.25rem 0;
}

.summary-subtext {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.summary-breakdown {
    display: flex;
    gap: 1.25rem;
    flex-wrap: wrap;
}

.summary-box {
    background: var(--bg-surface-2);
    padding: 0.75rem 1rem;
    border-radius: 0.875rem;
    display: flex;
    flex-direction: column;
    min-width: 130px;
}

.box-label {
    font-size: 0.6875rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
}
.box-value {
    font-size: 1.125rem;
    font-weight: 700;
    margin-top: 0.25rem;
}

.overall-progress-bar {
    width: 100%;
    height: 8px;
    background-color: var(--bg-surface-2);
    border-radius: 999px;
    overflow: hidden;
}

.overall-progress-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.6s ease-out;
}

/* ── Due Schedule Card ────────────────────────────────────── */
.due-schedule-card {
    padding: 1.25rem;
    border-left: 4px solid var(--warning, #f59e0b);
}

.due-schedule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 0.75rem;
    margin-top: 1rem;
}

.due-card-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    background: var(--bg-surface-2);
    border-radius: 0.75rem;
}

.btn-paybill-sm {
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 0.5rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s;
}

.btn-paybill-sm:hover {
    opacity: 0.9;
}

.btn-paybill-sm:disabled,
.btn-primary:disabled,
.dropdown-item--paybill:disabled {
    cursor: not-allowed;
    opacity: 0.45;
    box-shadow: none;
}

.btn-paybill-sm:disabled:hover,
.btn-primary:disabled:hover,
.dropdown-item--paybill:disabled:hover {
    opacity: 0.45;
}

/* ── Grid & Card Specs ────────────────────────────────────── */
.accounts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.25rem;
}

.account-card {
    padding: 1.25rem;
    border-radius: 1.25rem;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 220px;
}

.account-glow {
    position: absolute;
    top: -40px;
    right: -40px;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    filter: blur(50px);
    opacity: 0.25;
    pointer-events: none;
}

.account-icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.menu-dots-btn {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 1.25rem;
    font-weight: 700;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    transition:
        background-color 0.15s,
        color 0.15s;
}

.menu-dots-btn:hover {
    background-color: var(--bg-surface-2);
    color: var(--text-primary);
}

.account-dropdown-menu {
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 0.25rem;
    width: 170px;
    background: var(--bg-surface);
    border: 1px solid var(--border-strong);
    border-radius: 0.875rem;
    box-shadow: var(--shadow-md);
    padding: 0.375rem;
    z-index: 40;
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
.dropdown-item--paybill {
    color: var(--primary);
}
.dropdown-item--paybill:hover {
    background-color: var(--primary-light);
}
.dropdown-item--paybill:disabled {
    color: var(--text-muted);
    background: transparent;
}
.dropdown-item--paybill:disabled:hover {
    background: transparent;
}
.dropdown-item--danger {
    color: var(--danger);
}
.dropdown-item--danger:hover {
    background-color: var(--danger-light);
}

.dropdown-divider {
    height: 1px;
    background: var(--border);
    margin: 0.25rem 0;
}

/* ── Proportion / Gauge Bars ──────────────────────────────── */
.proportion-bar-wrapper {
    width: 100%;
    height: 6px;
    background-color: var(--bg-surface-2);
    border-radius: 999px;
    overflow: hidden;
}

.proportion-bar {
    height: 100%;
    border-radius: 999px;
    transition: width 0.5s ease-out;
}

.proportion-bar--safe {
    background-color: var(--success);
}
.proportion-bar--warning {
    background-color: var(--warning, #f59e0b);
}
.proportion-bar--danger {
    background-color: var(--danger);
}

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

.credit-limit-display {
    font-size: 0.6875rem;
    color: var(--text-muted);
}

/* ── Empty State ──────────────────────────────────────────── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
    text-align: center;
}

.empty-icon {
    width: 3rem;
    height: 3rem;
    color: var(--text-muted);
    margin-bottom: 0.75rem;
}
.empty-text {
    font-size: 0.875rem;
    color: var(--text-muted);
    font-weight: 500;
}

/* ── Modal Specs ──────────────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
}

.modal-panel {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    box-shadow: var(--shadow-md);
    padding: 1.75rem;
    width: 100%;
    max-width: 480px;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}
.modal-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}
.modal-close {
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    border-radius: 0.5rem;
    padding: 0.25rem;
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
.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.input-with-prefix {
    position: relative;
    display: flex;
    align-items: center;
}
.input-prefix {
    position: absolute;
    left: 0.875rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-muted);
}
.input-field--prefix {
    padding-left: 2rem;
}

.field-hint {
    font-size: 0.6875rem;
    color: var(--text-muted);
    margin: 0.25rem 0 0;
}
.label-required {
    color: var(--danger);
    margin-left: 0.125rem;
}

.color-picker-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.375rem;
}
.color-toggle-btn {
    background: none;
    border: none;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--primary);
    cursor: pointer;
}
.color-picker {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.color-swatch {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 2px solid transparent;
    cursor: pointer;
    transition: transform 0.15s;
}
.color-swatch:hover {
    transform: scale(1.15);
}
.color-swatch--active {
    border-color: var(--text-primary);
    transform: scale(1.1);
}

.modal-footer {
    display: flex;
    gap: 0.75rem;
    padding-top: 0.5rem;
}
.modal-btn {
    flex: 1;
    justify-content: center;
}
</style>

<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends AbstractController
{
    /**
     * Return aggregated dashboard statistics for the authenticated user.
     *
     * @param  Request  $request  the incoming HTTP request
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $month = now()->format('Y-m');
        $year = now()->year;
        $monthNum = now()->month;

        // Net Worth = total non-CC balances (assets) minus total CC outstanding balances (liabilities).
        // CC balances are stored as positive outstanding debt, so they are subtracted.
        $totalAssets = (float) $user->accounts()->whereNotIn('type', ['credit_card'])->sum('balance');
        $totalLiabilities = (float) $user->accounts()->where('type', 'credit_card')->sum('balance');
        $totalBalance = $totalAssets - $totalLiabilities;
        $accounts = $user->accounts()->orderBy('name')->get();

        // This month income & expense
        $monthlyIncome = $user->transactions()
            ->where('type', 'income')
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->sum('amount');

        $monthlyExpense = $user->transactions()
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->sum('amount');

        // Monthly cashflow - last 6 months grouped by month in PHP for DB compatibility
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth()->toDateString();
        $recentTxns = $user->transactions()
            ->where('date', '>=', $sixMonthsAgo)
            ->whereIn('type', ['income', 'expense'])
            ->get();

        $cashflowRaw = $recentTxns->groupBy(fn ($t) => Carbon::parse($t->date)->format('Y-m'));

        $cashflow = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $ym = $date->format('Y-m');
            $label = $date->format('M Y');
            $txns = $cashflowRaw->get($ym, collect());

            $income = (float) $txns->where('type', 'income')->sum('amount');
            $expense = (float) $txns->where('type', 'expense')->sum('amount');

            $cashflow[] = [
                'label' => $label,
                'income' => $income,
                'expense' => $expense,
            ];
        }

        // Expense by category (current month) for doughnut chart
        $expenseByCategory = $user->transactions()
            ->with('category')
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category?->name ?? 'Uncategorized',
                'color' => $row->category?->color ?? '#64748b',
                'total' => (float) $row->total,
            ]);

        // Budget status this month (bulk query spending instead of N+1 per budget)
        $spentByCat = $user->transactions()
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->select('category_id', DB::raw('SUM(amount) as total_spent'))
            ->groupBy('category_id')
            ->pluck('total_spent', 'category_id');

        $budgets = $user->budgets()
            ->with('category')
            ->where('month', $month)
            ->get()
            ->map(function ($budget) use ($spentByCat) {
                $spent = (float) ($spentByCat->get($budget->category_id) ?? 0);

                return [
                    'id' => $budget->id,
                    'category' => $budget->category?->name,
                    'color' => $budget->category?->color ?? '#6366f1',
                    'budget' => (float) $budget->amount,
                    'spent' => $spent,
                    'percentage' => $budget->amount > 0
                        ? round(($spent / (float) $budget->amount) * 100, 1)
                        : 0,
                ];
            });

        // Recent transactions
        $recentTransactions = $user->transactions()
            ->with(['account', 'toAccount', 'category'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Upcoming recurring bills / transactions (due within next 7 days or overdue)
        $upcomingBills = $user->recurringTransactions()
            ->with(['account', 'toAccount', 'category'])
            ->where('is_active', true)
            ->whereDate('next_due_date', '<=', now()->addDays(7))
            ->orderBy('next_due_date', 'asc')
            ->limit(5)
            ->get();

        // Top active savings goals
        $goals = $user->goals()
            ->with(['linkedAccount'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // Active subscriptions
        $subscriptions = $user->subscriptions()
            ->with(['account', 'category'])
            ->where('is_active', true)
            ->orderBy('next_renewal_date', 'asc')
            ->limit(5)
            ->get();

        return response()->json([
            'total_balance' => (float) $totalBalance,
            'monthly_income' => (float) $monthlyIncome,
            'monthly_expense' => (float) $monthlyExpense,
            'net' => (float) $monthlyIncome - (float) $monthlyExpense,
            'accounts' => $accounts,
            'cashflow' => $cashflow,
            'expense_by_category' => $expenseByCategory,
            'budgets' => $budgets,
            'recent_transactions' => $recentTransactions,
            'upcoming_bills' => $upcomingBills,
            'goals' => $goals,
            'subscriptions' => $subscriptions,
        ]);
    }
}

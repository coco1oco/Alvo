<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends AbstractController
{
    /**
     * Return aggregated dashboard statistics for the authenticated user.
     *
     * @param  Request $request the incoming HTTP request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user     = $request->user();
        $month    = now()->format('Y-m');
        $year     = now()->year;
        $monthNum = now()->month;

        // Total balances
        $totalBalance = $user->accounts()->sum('balance');
        $accounts     = $user->accounts()->orderBy('name')->get();

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

        // Monthly cashflow - last 6 months grouped by month
        $cashflow = [];
        for ($i = 5; $i >= 0; $i--) {
            $date    = now()->subMonths($i);
            $y       = $date->year;
            $m       = $date->month;
            $label   = $date->format('M Y');

            $income  = $user->transactions()
                ->where('type', 'income')
                ->whereYear('date', $y)
                ->whereMonth('date', $m)
                ->sum('amount');

            $expense = $user->transactions()
                ->where('type', 'expense')
                ->whereYear('date', $y)
                ->whereMonth('date', $m)
                ->sum('amount');

            $cashflow[] = [
                'label'   => $label,
                'income'  => (float) $income,
                'expense' => (float) $expense,
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
            ->map(fn($row) => [
                'category' => $row->category?->name ?? 'Uncategorized',
                'color'    => $row->category?->color ?? '#64748b',
                'total'    => (float) $row->total,
            ]);

        // Budget status this month
        $budgets = $user->budgets()
            ->with('category')
            ->where('month', $month)
            ->get()
            ->map(function ($budget) use ($user, $year, $monthNum) {
                $spent = $user->transactions()
                    ->where('type', 'expense')
                    ->where('category_id', $budget->category_id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum)
                    ->sum('amount');

                return [
                    'id'         => $budget->id,
                    'category'   => $budget->category?->name,
                    'color'      => $budget->category?->color ?? '#6366f1',
                    'budget'     => (float) $budget->amount,
                    'spent'      => (float) $spent,
                    'percentage' => $budget->amount > 0
                        ? round(((float) $spent / (float) $budget->amount) * 100, 1)
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

        return response()->json([
            'total_balance'       => (float) $totalBalance,
            'monthly_income'      => (float) $monthlyIncome,
            'monthly_expense'     => (float) $monthlyExpense,
            'net'                 => (float) $monthlyIncome - (float) $monthlyExpense,
            'accounts'            => $accounts,
            'cashflow'            => $cashflow,
            'expense_by_category' => $expenseByCategory,
            'budgets'             => $budgets,
            'recent_transactions' => $recentTransactions,
        ]);
    }
}

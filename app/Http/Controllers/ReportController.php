<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends AbstractController
{
    /**
     * Get aggregated financial reports and analytics.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = $request->get('period', 'this_month');

        // Determine date boundaries based on selected period
        [$fromDate, $toDate] = $this->resolveDateRange($period, $request->get('from'), $request->get('to'));

        // Query transactions in range
        $txnsQuery = $user->transactions()
            ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()]);

        $totalIncome = (float) (clone $txnsQuery)->where('type', 'income')->sum('amount');
        $totalExpense = (float) (clone $txnsQuery)->where('type', 'expense')->sum('amount');
        $netCashflow = $totalIncome - $totalExpense;

        $savingsRate = $totalIncome > 0 ? round(max(($netCashflow / $totalIncome) * 100, 0), 1) : 0;

        $daysInPeriod = max((int) $fromDate->diffInDays($toDate) + 1, 1);
        $avgDailySpend = $daysInPeriod > 0 ? round($totalExpense / $daysInPeriod, 2) : 0;

        // Monthly Income vs Expense Trend (last 6 to 12 months)
        $trendMonths = 6;
        $cashflowTrend = [];
        for ($i = $trendMonths - 1; $i >= 0; $i--) {
            $mStart = now()->subMonths($i)->startOfMonth();
            $mEnd = now()->subMonths($i)->endOfMonth();

            $mIncome = (float) $user->transactions()
                ->where('type', 'income')
                ->whereBetween('date', [$mStart->toDateString(), $mEnd->toDateString()])
                ->sum('amount');

            $mExpense = (float) $user->transactions()
                ->where('type', 'expense')
                ->whereBetween('date', [$mStart->toDateString(), $mEnd->toDateString()])
                ->sum('amount');

            $cashflowTrend[] = [
                'month' => $mStart->format('M Y'),
                'income' => $mIncome,
                'expense' => $mExpense,
                'net' => $mIncome - $mExpense,
            ];
        }

        // Category breakdown in selected date range
        $categoryBreakdown = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $user->id)
            ->whereNull('transactions.deleted_at')
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->select(
                'categories.id',
                'categories.name',
                'categories.color',
                'categories.icon',
                DB::raw('SUM(transactions.amount) as total_amount'),
                DB::raw('COUNT(transactions.id) as txn_count')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.color', 'categories.icon')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($cat) use ($totalExpense) {
                $amt = (float) $cat->total_amount;

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'color' => $cat->color ?? '#6366f1',
                    'icon' => $cat->icon,
                    'amount' => $amt,
                    'count' => (int) $cat->txn_count,
                    'percentage' => $totalExpense > 0 ? round(($amt / $totalExpense) * 100, 1) : 0,
                ];
            });

        // Daily spending heatmap data for the period
        $dailyExpenses = DB::table('transactions')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->where('type', 'expense')
            ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->select('date', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('date')
            ->pluck('total_amount', 'date')
            ->toArray();

        return response()->json([
            'period' => $period,
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'kpis' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_cashflow' => $netCashflow,
                'savings_rate' => $savingsRate,
                'avg_daily_spend' => $avgDailySpend,
                'days_count' => $daysInPeriod,
            ],
            'cashflow_trend' => $cashflowTrend,
            'category_report' => $categoryBreakdown,
            'daily_expenses' => $dailyExpenses,
        ]);
    }

    /**
     * Resolve start and end Carbon dates from period string or custom inputs.
     */
    private function resolveDateRange(string $period, ?string $customFrom, ?string $customTo): array
    {
        return match ($period) {
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last_3_months' => [now()->subMonths(2)->startOfMonth(), now()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                $customFrom ? Carbon::parse($customFrom) : now()->startOfMonth(),
                $customTo ? Carbon::parse($customTo) : now()->endOfMonth(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}

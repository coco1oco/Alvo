<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NetWorthController extends AbstractController
{
    /**
     * Get Balance Sheet and Net Worth Statement data.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get user accounts
        $accounts = $user->accounts()->where('is_archived', false)->get();

        // Filter Assets (Bank, Cash, Savings, Other with positive balance)
        $assetAccounts = $accounts->filter(fn ($a) => $a->type !== 'credit_card')
            ->values()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->name,
                    'type' => $a->type,
                    'color' => $a->color ?? '#6366f1',
                    'icon' => $a->icon,
                    'balance' => (float) $a->balance,
                ];
            });

        $totalAssets = $assetAccounts->sum('balance');

        // Filter Liabilities (Credit Cards outstanding balances)
        $liabilityAccounts = $accounts->filter(fn ($a) => $a->type === 'credit_card')
            ->values()
            ->map(function ($a) {
                $owed = Math_max((float) $a->balance, 0);

                return [
                    'id' => $a->id,
                    'name' => $a->name,
                    'type' => $a->type,
                    'color' => $a->color ?? '#ef4444',
                    'icon' => $a->icon,
                    'owed' => $owed,
                    'credit_limit' => (float) $a->credit_limit,
                    'utilization' => $a->credit_limit > 0 ? round(($owed / (float) $a->credit_limit) * 100, 1) : 0,
                ];
            });

        $totalLiabilities = $liabilityAccounts->sum('owed');
        $netWorth = $totalAssets - $totalLiabilities;

        // Solvency Ratio (Assets / Liabilities)
        $solvencyRatio = $totalLiabilities > 0 ? round($totalAssets / $totalLiabilities, 2) : ($totalAssets > 0 ? 999 : 0);

        // Historical Net Worth Trend (6 months)
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i)->endOfMonth();

            // Calculate historical cashflow delta from transactions to extrapolate past net worth
            $futureExpense = (float) $user->transactions()
                ->where('type', 'expense')
                ->where('date', '>', $monthDate->toDateString())
                ->sum('amount');

            $futureIncome = (float) $user->transactions()
                ->where('type', 'income')
                ->where('date', '>', $monthDate->toDateString())
                ->sum('amount');

            // Estimated net worth at month end = Current Net Worth - future income + future expenses
            $historicalNetWorth = $netWorth - $futureIncome + $futureExpense;

            $trend[] = [
                'month' => $monthDate->format('M Y'),
                'net_worth' => round($historicalNetWorth, 2),
            ];
        }

        return response()->json([
            'net_worth' => round($netWorth, 2),
            'total_assets' => round($totalAssets, 2),
            'total_liabilities' => round($totalLiabilities, 2),
            'solvency_ratio' => $solvencyRatio,
            'assets' => $assetAccounts,
            'liabilities' => $liabilityAccounts,
            'historical_trend' => $trend,
        ]);
    }
}

function Math_max($a, $b)
{
    return $a > $b ? $a : $b;
}

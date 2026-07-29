<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Credit card specific fields — NULL for all non-CC account types
            $table->decimal('credit_limit', 15, 2)->nullable()->after('balance');
            $table->unsignedTinyInteger('billing_cycle_day')->nullable()->after('credit_limit');
            $table->unsignedTinyInteger('due_date_day')->nullable()->after('billing_cycle_day');
        });

        // Normalize existing credit card balances from negative (old convention)
        // to positive (new convention: outstanding debt stored as positive number).
        // Any CC account with a negative balance had debt recorded as −N; flip to +N.
        DB::statement("
            UPDATE accounts
            SET balance = ABS(balance)
            WHERE type = 'credit_card' AND balance < 0
        ");
    }

    public function down(): void
    {
        // Reverse the normalization back to negative-debt convention
        DB::statement("
            UPDATE accounts
            SET balance = -ABS(balance)
            WHERE type = 'credit_card' AND balance > 0
        ");

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['credit_limit', 'billing_cycle_day', 'due_date_day']);
        });
    }
};

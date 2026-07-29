<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->boolean('is_subscription')->default(false)->after('auto_process');
            $table->string('logo_url')->nullable()->after('is_subscription');
            $table->string('color', 30)->nullable()->after('logo_url');
        });

        // Migrate existing subscriptions to recurring_transactions table if any exist
        if (Schema::hasTable('subscriptions')) {
            $subs = DB::table('subscriptions')->get();
            foreach ($subs as $sub) {
                DB::table('recurring_transactions')->insert([
                    'user_id' => $sub->user_id,
                    'account_id' => $sub->account_id,
                    'to_account_id' => null,
                    'category_id' => $sub->category_id,
                    'type' => 'expense',
                    'amount' => $sub->amount,
                    'description' => $sub->name,
                    'frequency' => match ($sub->billing_cycle) {
                        'yearly' => 'yearly',
                        'weekly' => 'weekly',
                        default => 'monthly',
                    },
                    'start_date' => now()->toDateString(),
                    'next_due_date' => $sub->next_renewal_date ?? now()->toDateString(),
                    'is_active' => $sub->is_active ?? true,
                    'auto_process' => false,
                    'is_subscription' => true,
                    'logo_url' => $sub->logo_url ?? null,
                    'color' => $sub->color ?? '#6366f1',
                    'created_at' => $sub->created_at ?? now(),
                    'updated_at' => $sub->updated_at ?? now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->dropColumn(['is_subscription', 'logo_url', 'color']);
        });
    }
};

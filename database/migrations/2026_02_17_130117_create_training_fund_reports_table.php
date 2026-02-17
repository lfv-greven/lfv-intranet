<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_fund_reports', function (Blueprint $table) {
            $table->id();
            $table->date('month')->unique();
            $table->string('status', 32)->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('motor_ul_minutes')->default(0);
            $table->decimal('motor_ul_amount', 10, 2)->default(0);
            $table->unsignedInteger('winch_starts')->default(0);
            $table->decimal('winch_amount', 10, 2)->default(0);
            $table->unsignedInteger('tow_minutes')->default(0);
            $table->decimal('tow_amount', 10, 2)->default(0);
            $table->unsignedInteger('start_pauschale_quarterly_count')->default(0);
            $table->unsignedInteger('start_pauschale_monthly_count')->default(0);
            $table->decimal('start_pauschale_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->json('source_meta')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_fund_reports');
    }
};

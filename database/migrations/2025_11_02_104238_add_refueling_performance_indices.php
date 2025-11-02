<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('refuelings', function (Blueprint $table) {
            $table->index(['gas_station_id', 'counter_reading', 'id']);
            $table->index(['counter_reading', 'date', 'id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refuelings', function (Blueprint $table) {
            $table->dropIndex('refuelings_gas_station_id_counter_reading_id_index');
            $table->dropIndex('refuelings_counter_reading_date_id_index');
        });
    }
};

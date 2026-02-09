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
        Schema::create('fi_settlement_flights', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('fi_settlement_id')->index();
            $table->string('vf_flight_id');
            $table->string('fi_vf_uid')->nullable();
            $table->string('fi_name')->nullable();
            $table->date('flight_date')->nullable();
            $table->string('departure_time')->nullable();
            $table->string('arrival_time')->nullable();
            $table->unsignedInteger('flighttime_minutes')->nullable();
            $table->unsignedInteger('blocktime_minutes')->nullable();
            $table->string('planetype')->nullable();
            $table->string('callsign')->nullable();
            $table->string('pilotname')->nullable();
            $table->string('excluded_reason')->nullable();
            $table->string('vf_workhour_id')->nullable();
            $table->timestamp('workhour_sent_at')->nullable();
            $table->json('raw_payload');
            $table->timestamps();

            $table->unique(['fi_settlement_id', 'vf_flight_id'], 'fi_settlement_flights_unique');
            $table->foreign('fi_settlement_id')
                ->references('id')
                ->on('fi_settlements')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fi_settlement_flights');
    }
};

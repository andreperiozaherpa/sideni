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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->foreignId('submitted_by_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('village_id')->constrained()->restrictOnDelete();
            $table->foreignId('report_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('incident_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reporter_name');
            $table->text('reporter_nik')->nullable();
            $table->dateTime('reported_at');
            $table->dateTime('occurred_at')->nullable();
            $table->string('location_name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 11, 7);
            $table->text('description');
            $table->string('risk_level');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['status', 'reported_at']);
            $table->index(['village_id', 'reported_at']);
            $table->index(['report_category_id', 'reported_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

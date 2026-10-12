<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The disposition and field follow-up features were removed in favour of
     * capturing "Tindak Lanjut" and "Saran Tindakan" during verification.
     */
    public function up(): void
    {
        Schema::dropIfExists('report_dispositions');
        Schema::dropIfExists('report_follow_ups');
    }

    /**
     * Reverse the migrations.
     *
     * Intentionally not reversible: the dropped tables belonged to a removed feature.
     */
    public function down(): void
    {
        //
    }
};

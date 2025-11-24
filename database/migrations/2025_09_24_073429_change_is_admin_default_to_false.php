<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change the default value of is_admin column to false
        DB::statement('ALTER TABLE users ALTER COLUMN is_admin SET DEFAULT false');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the default value back to true
        DB::statement('ALTER TABLE users ALTER COLUMN is_admin SET DEFAULT true');
    }
};

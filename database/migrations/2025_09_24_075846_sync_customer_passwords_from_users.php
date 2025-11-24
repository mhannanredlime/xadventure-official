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
        // Sync passwords from users table to customers table for customers with user_id
        DB::statement("
            UPDATE customers 
            INNER JOIN users ON customers.user_id = users.id 
            SET customers.password = users.password 
            WHERE customers.password IS NULL 
            AND users.password IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as we don't want to remove passwords
        // from customers who now have them
    }
};

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
        Schema::table('packages', function (Blueprint $table) {
            // Main package type (Regular / ATV)
            $table->unsignedBigInteger('package_type_id')->nullable()->after('id');
            
            // Sub-type (Single / Bundle / Group) – only for Regular
            $table->unsignedBigInteger('package_sub_type_id')->nullable()->after('package_type_id');

            // Optional: add foreign key constraints
            $table->foreign('package_type_id')
                  ->references('id')->on('package_types')
                  ->onDelete('set null');

            $table->foreign('package_sub_type_id')
                  ->references('id')->on('package_types')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['package_sub_type_id']);
            $table->dropForeign(['package_type_id']);
            $table->dropColumn(['package_type_id', 'package_sub_type_id']);
        });
    }
};

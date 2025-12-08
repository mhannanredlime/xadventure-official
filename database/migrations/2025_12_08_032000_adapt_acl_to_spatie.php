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
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        // 1. Update Permissions Table
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                if (!Schema::hasColumn('permissions', 'guard_name')) {
                    $table->string('guard_name')->default('web')->after('name');
                }
            });
        }

        // 2. Update Roles Table
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'guard_name')) {
                    $table->string('guard_name')->default('web')->after('name');
                }
            });
        }

        // 3. Create Spatie Pivot Tables if they don't exist
        
        // model_has_permissions
        if (!Schema::hasTable($tableNames['model_has_permissions'])) {
            Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission) {
                $table->unsignedBigInteger($pivotPermission);
                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

                $table->foreign($pivotPermission)
                    ->references('id') // permission id
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            });
        }

        // model_has_roles
        if (!Schema::hasTable($tableNames['model_has_roles'])) {
            Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole) {
                $table->unsignedBigInteger($pivotRole);
                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

                $table->foreign($pivotRole)
                    ->references('id') // role id
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            });
            
            // Access existing 'role_user' data if exists and migrate
             if (Schema::hasTable('role_user')) {
                $oldRecords = DB::table('role_user')->get();
                foreach ($oldRecords as $rec) {
                    try {
                        DB::table($tableNames['model_has_roles'])->insertOrIgnore([
                            $pivotRole => $rec->role_id,
                            'model_type' => 'App\Models\User',
                            $columnNames['model_morph_key'] => $rec->user_id
                        ]);
                    } catch (\Exception $e) {
                        // ignore duplicates
                    }
                }
            }
        }

        // role_has_permissions
        if (!Schema::hasTable($tableNames['role_has_permissions'])) {
            Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
                $table->unsignedBigInteger($pivotPermission);
                $table->unsignedBigInteger($pivotRole);

                $table->foreign($pivotPermission)
                    ->references('id') // permission id
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->foreign($pivotRole)
                    ->references('id') // role id
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
            });
            
             // Migrate from permission_role
             if (Schema::hasTable('permission_role')) {
                $oldRecords = DB::table('permission_role')->get();
                foreach ($oldRecords as $rec) {
                    try {
                        DB::table($tableNames['role_has_permissions'])->insertOrIgnore([
                            $pivotPermission => $rec->permission_id,
                            $pivotRole => $rec->role_id
                        ]);
                    } catch (\Exception $e) {
                         // ignore
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We generally don't drop columns in down() here to avoid data loss if we roll back
        // but strict rollback would drop the new tables.
    }
};

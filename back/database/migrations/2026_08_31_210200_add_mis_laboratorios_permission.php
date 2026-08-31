<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permiso del menú "Mis laboratorios" (vista del doctor).
 */
return new class extends Migration
{
    private string $permission = 'Mis laboratorios';

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $existe = DB::table('permissions')
            ->where('name', $this->permission)
            ->where('guard_name', 'web')
            ->exists();

        if (! $existe) {
            DB::table('permissions')->insert([
                'name' => $this->permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $id = DB::table('permissions')
            ->where('name', $this->permission)
            ->where('guard_name', 'web')
            ->value('id');

        if (! $id) {
            return;
        }

        DB::table('model_has_permissions')->where('permission_id', $id)->delete();
        DB::table('role_has_permissions')->where('permission_id', $id)->delete();
        DB::table('permissions')->where('id', $id)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

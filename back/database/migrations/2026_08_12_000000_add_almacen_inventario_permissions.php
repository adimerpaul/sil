<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Permisos que antes estaban cubiertos por "Módulo inventario".
     */
    private array $permissions = [
        'Módulo productos por vencer',
        'Módulo productos vencidos',
        'Módulo proveedores',
        'Ver stock de almacén',
    ];

    private string $origen = 'Módulo inventario';

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $origenId = DB::table('permissions')
            ->where('name', $this->origen)
            ->where('guard_name', 'web')
            ->value('id');

        foreach ($this->permissions as $permission) {
            $permissionId = DB::table('permissions')
                ->where('name', $permission)
                ->where('guard_name', 'web')
                ->value('id');

            if (! $permissionId) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (! $origenId) {
                continue;
            }

            // Quien ya tenía "Módulo inventario" conserva el acceso a las 4 pantallas.
            $usuarios = DB::table('model_has_permissions')
                ->where('permission_id', $origenId)
                ->get(['model_type', 'model_id']);

            foreach ($usuarios as $usuario) {
                $existe = DB::table('model_has_permissions')
                    ->where('permission_id', $permissionId)
                    ->where('model_type', $usuario->model_type)
                    ->where('model_id', $usuario->model_id)
                    ->exists();

                if ($existe) {
                    continue;
                }

                DB::table('model_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'model_type' => $usuario->model_type,
                    'model_id' => $usuario->model_id,
                ]);
            }

            $roles = DB::table('role_has_permissions')
                ->where('permission_id', $origenId)
                ->pluck('role_id');

            foreach ($roles as $roleId) {
                $existe = DB::table('role_has_permissions')
                    ->where('permission_id', $permissionId)
                    ->where('role_id', $roleId)
                    ->exists();

                if ($existe) {
                    continue;
                }

                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $ids = DB::table('permissions')
            ->whereIn('name', $this->permissions)
            ->where('guard_name', 'web')
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

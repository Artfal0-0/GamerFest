<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $jugadorRole = Role::firstOrCreate(['name' => 'jugador']);

        $adminPermissions = [
            'create_juego',
            'delete_juego',
            'update_juego',
            'create_ganador_individual',
            'delete_ganador_individual',
            'update_ganador_individual',
            'create_ganador_grupal',
            'delete_ganador_grupal',
            'update_ganador_grupal',
        ];

        $jugadorPermissions = [
            'view_self_user',
            'logout_self',
            'view_participantes',
            'view_self_participante',
            'create_participante',
            'delete_self_participante',
            'update_self_participante',
            'view_equipos',
            'view_self_equipo',
            'create_equipo',
            'delete_self_equipo',
            'update_self_equipo',
            'view_ganadores_individuales',
            'view_self_ganador_individual',
            'view_ganadores_grupales',
            'view_self_ganador_grupal',
            'view_inscripciones_individuales',
            'view_self_inscripcion_individual',
            'create_inscripcion_individual',
            'delete_self_inscripcion_individual',
            'update_self_inscripcion_individual',
            'view_inscripciones_grupales',
            'view_self_inscripcion_grupal',
            'create_inscripcion_grupal',
            'delete_self_inscripcion_grupal',
            'update_self_inscripcion_grupal',
        ];

        // Crear los permisos si no existen
        foreach (array_merge($adminPermissions, $jugadorPermissions) as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Asignar permisos a los roles usando syncPermissions
        $adminRole->syncPermissions($adminPermissions);
        $jugadorRole->syncPermissions($jugadorPermissions);

        $adminEmail = strtolower(trim((string) env('APP_ADMIN_INITIAL_EMAIL')));

        if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Configura APP_ADMIN_INITIAL_EMAIL con el correo del administrador antes de ejecutar los seeders.');
        }

        $adminUser = User::firstOrNew(['email' => $adminEmail]);

        if (! $adminUser->exists) {
            $adminPassword = (string) env('APP_ADMIN_INITIAL_PASSWORD');

            if (strlen($adminPassword) < 16) {
                throw new \InvalidArgumentException('APP_ADMIN_INITIAL_PASSWORD debe tener al menos 16 caracteres antes de crear el administrador.');
            }

            $adminUser->forceFill([
                'name' => 'Admin',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ])->save();
        }

        $adminUser->assignRole($adminRole);

    }
}

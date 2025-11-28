<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
//        'nombre',
//        'tipo',
//        'nivel',
//        'direccion',
//        'telefono_contacto',
//        'responsable_laboratorio',
//        'telefono_responsable',
//        'estado',
        $establecimiento = \App\Models\Establecimiento::create([
            'nombre' => 'Hospital General',
            'direccion' => 'San Felipe Y 6 De Octubre Oruro, Bolivia',
            'telefono_contacto' => '2 5275405',
            'responsable_laboratorio' => 'Dr. House',
            'telefono_responsable' => '555-1111',
            'tipo' => 'Privado',
            'nivel' => 'Terciario',
            'estado' => 'ACTIVO',
        ]);
        $establecimiento2 = \App\Models\Establecimiento::create([
            'nombre' => 'Centro De Salud Chiripujio',
            'direccion' => 'Calle Falsa 123 La Paz, Bolivia',
            'telefono_contacto' => '2 1234567',
            'responsable_laboratorio' => 'Dra. Smith',
            'telefono_responsable' => '555-2222',
            'tipo' => 'Publico',
            'nivel' => 'Primario',
            'estado' => 'ACTIVO',
        ]);

        $userAdmin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'role' => 'Administrador',
            'avatar' => 'default.png',
            'email' => '',
            'password' => 'admin123Admin',
            'establecimiento_id' => 1,
        ]);
        $permisos = [
            'Usuarios',
            'Pacientes',
            'Hematologia',
            'Quimica Sanguinea',
            'Uruanalisis y Parasitologia',
            'Bacteriologia',
            'Inmunologia',


//                'Insumos',
//                'Productos',
//                'Clientes',
//                'Ventas',
//                'Compras',
//                'Reportes',
        ];
        foreach ($permisos as $permiso) {
            Permission::create(['name' => $permiso]);
        }
        $userAdmin->givePermissionTo(Permission::all());

//        protected $fillable = [
//        'nombre',
//        'especialidad',
//        'ci',
//        'telefono',
//        'email',
//        'registro',
//        'estado',
//        'establecimiento_id'
//    ];
        $doctor = Doctor::create([
            'nombre' => 'Dr. Juan Perez',
            'especialidad' => 'Cardiologia',
            'ci' => '12345678',
            'telefono' => '555-1234',
            'email' => 'perez@gmail.com',
            'registro' => 'REG-001',
            'estado' => 'ACTIVO',
            'establecimiento_id' => 1,
        ]);
        $paciente = \App\Models\Paciente::create([
            'fecha_recepcion' => '2024-01-15',
            'hora_recepcion' => '10:30:00',
            'nombre_completo' => 'Maria Lopez',
            'fecha_nac' => '1990-05-20',
            'genero' => 'F',
            'edad' => 33,
            'ci' => '87654321',
            'telefono' => '555-5678',
            'direccion' => 'Calle Falsa 123',
            'discapacidad' => false,
            'embarazo' => false,
        ]);

//            paciente fake datos 10000 fake
        for ($i = 0; $i < 100; $i++) {
            \App\Models\Paciente::create([
                'fecha_recepcion' => now()->subDays(rand(0, 365))->toDateString(),
                'hora_recepcion' => now()->subMinutes(rand(0, 1440))->toTimeString(),
                'nombre_completo' => 'Paciente ' . ($i + 1),
                'fecha_nac' => now()->subYears(rand(1, 100))->toDateString(),
                'genero' => ['F', 'M', 'OTRO'][array_rand(['F', 'M', 'OTRO'])],
                'edad' => rand(1, 100),
                'ci' => strval(rand(1000000, 99999999)),
                'telefono' => '555-' . rand(1000, 9999),
                'direccion' => 'Direccion ' . ($i + 1),
                'discapacidad' => (bool)rand(0, 1),
                'embarazo' => (bool)rand(0, 1),
            ]);
        }
        $this->call([
            ServiciosSeeder::class,
            AreaTipoMuestraSeeder::class,
            AreaRangoSeeder::class,
            AreaRangoQuimicaSeeder::class,
        ]);
    }
}

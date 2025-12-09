<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Formularios;

class FormularioSeeder extends Seeder
{
    public function run(): void
    {
        $formularios = [
            [
                'nombre'  => 'Hormonas Tiroideas – Área de Inmunología',
                'area_id' => 5, // <-- Cambia al ID real del área INMUNOLOGÍA en tu BD
                'html'    => <<<HTML
<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>ANALITO</th>
            <th colspan="2">RESULTADOS</th>
            <th>VALORES DE REFERENCIA</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>TIROTROPINA (TSH)</td>
            <td></td>
            <td>uIU/ml</td>
            <td>0,28 a 6,82 uIU/ml</td>
        </tr>
        <tr>
            <td>TRIYODOTIRONINA (T3)</td>
            <td></td>
            <td>ng/ml</td>
            <td>0,52 a 1,85 ng/ml</td>
        </tr>
        <tr>
            <td>TIROXINA LIBRE (FT4)</td>
            <td></td>
            <td>ng/dl</td>
            <td>0,8 a 2,0 ng/dl</td>
        </tr>
        <tr>
            <td>TIROXINA TOTAL (T4)</td>
            <td></td>
            <td>ng/dl</td>
            <td>5,0 a 13,0 ng/dl</td>
        </tr>
    </tbody>
</table>
HTML,
            ],
        ];

        foreach ($formularios as $formulario) {
            Formularios::create($formulario);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            'consultoria'            => 'Consultoría Jurídica General',
            'derecho-civil'          => 'Derecho Civil y Contratos',
            'derecho-comercial'      => 'Derecho Comercial y Financiero',
            'derecho-penal'          => 'Derecho Penal',
            'derecho-laboral'        => 'Derecho Laboral',
            'derecho-administrativo' => 'Derecho Administrativo',
            'otro'                   => 'Otro asunto legal',
        ];

        foreach ($areas as $slug => $nombre) {
            Area::updateOrCreate(['slug' => $slug], ['nombre' => $nombre]);
        }
    }
}

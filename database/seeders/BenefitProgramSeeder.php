<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BenefitProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\BenefitProgram::upsert([
            ['code' => 'bolsa_familia',      'name' => 'Bolsa Família'],
            ['code' => 'bpc',                'name' => 'BPC'],
            ['code' => 'bpc_idoso',          'name' => 'BPC Idoso'],
            ['code' => 'auxilio_emergencial', 'name' => 'Auxílio Emergencial'],
            ['code' => 'seguro_desemprego',  'name' => 'Seguro Desemprego'],
            ['code' => 'outro',              'name' => 'Outro'],
        ], ['code']);
    }
}

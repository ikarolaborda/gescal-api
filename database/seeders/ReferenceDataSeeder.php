<?php

namespace Database\Seeders;

use App\Models\BenefitProgram;
use App\Models\DocumentType;
use App\Models\FederationUnit;
use App\Models\Kinship;
use App\Models\MaritalStatus;
use App\Models\OccurrenceType;
use App\Models\RaceEthnicity;
use App\Models\SchoolingLevel;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedFederationUnits();
        $this->seedRaceEthnicities();
        $this->seedMaritalStatuses();
        $this->seedSchoolingLevels();
        $this->seedKinships();
        $this->seedDocumentTypes();
        $this->seedOccurrenceTypes();
        $this->seedBenefitPrograms();
    }

    private function seedFederationUnits(): void
    {
        $units = [
            'AC',
            'AL',
            'AP',
            'AM',
            'BA',
            'CE',
            'DF',
            'ES',
            'GO',
            'MA',
            'MT',
            'MS',
            'MG',
            'PA',
            'PB',
            'PE',
            'PI',
            'PR',
            'RJ',
            'RN',
            'RO',
            'RR',
            'RS',
            'SC',
            'SE',
            'SP',
            'TO',
            'nao_declarado',
        ];

        foreach ($units as $unit) {
            FederationUnit::firstOrCreate(['federation_unit' => $unit]);
        }
    }

    private function seedRaceEthnicities(): void
    {
        $races = [
            'branca',
            'preta',
            'parda',
            'amarela',
            'indigena',
            'nao_declarada',
        ];

        foreach ($races as $race) {
            RaceEthnicity::firstOrCreate(['race_color' => $race]);
        }
    }

    private function seedMaritalStatuses(): void
    {
        $statuses = [
            'solteiro',
            'casado',
            'divorciado',
            'viuvo',
            'união_estável',
            'nao_declarado',
        ];

        foreach ($statuses as $status) {
            MaritalStatus::firstOrCreate(['marital_status' => $status]);
        }
    }

    private function seedSchoolingLevels(): void
    {
        $levels = [
            'fundamental_incompleto',
            'fundamental_completo',
            'medio_incompleto',
            'medio_completo',
            'superior_incompleto',
            'superior_completo',
            'pos_graduacao_incompleto',
            'pos_graduacao_completo',
            'nao_declarado',
        ];

        foreach ($levels as $level) {
            SchoolingLevel::firstOrCreate(['schooling_level' => $level]);
        }
    }

    private function seedKinships(): void
    {
        $kinships = [
            'pai',
            'mae',
            'filho',
            'filha',
            'irmao',
            'irma',
            'avo',
            'avo_feminino',
            'tio',
            'tia',
            'sobrinho',
            'sobrinha',
            'primo',
            'prima',
            'sogro',
            'sogra',
            'genro',
            'nora',
            'esposa',
            'marido',
            'filho_adotivo',
            'filha_adotiva',
            'neto',
            'neta',
            'bisneto',
            'bisneta',
            'nao_declarado',
            'outro',
        ];

        foreach ($kinships as $kinship) {
            Kinship::firstOrCreate(['kinship' => $kinship]);
        }
    }

    private function seedDocumentTypes(): void
    {
        $types = [
            'cpf',
            'cnpj',
            'rg',
            'cnh',
            'passaporte',
            'outro',
            'nao_declarado',
        ];

        foreach ($types as $type) {
            DocumentType::firstOrCreate(['document_type' => $type]);
        }
    }

    private function seedOccurrenceTypes(): void
    {
        $types = [
            'incendio',
            'deslizamento',
            'inundação',
            'risco_de_incendio',
            'risco_de_deslizamento',
            'risco_de_inundação',
            'desabamento',
            'destelhamento',
            'queda_de_muro',
            'queda_de_arvore',
            'queda_de_edificio',
            'queda_de_ponte',
            'enxurrada',
            'terremoto',
            'outro',
            'nao_declarado',
        ];

        foreach ($types as $type) {
            OccurrenceType::firstOrCreate(['occurrence_type' => $type]);
        }
    }

    private function seedBenefitPrograms(): void
    {
        $programs = [
            ['name' => 'Auxílio Emergencial', 'code' => 'AUXILIO_EMERGENCIAL', 'description' => 'Auxílio Emergencial para famílias em situação de calamidade'],
            ['name' => 'Cesta Básica', 'code' => 'CESTA_BASICA', 'description' => 'Distribuição de cestas básicas para famílias vulneráveis'],
            ['name' => 'Auxílio Moradia', 'code' => 'AUXILIO_MORADIA', 'description' => 'Auxílio financeiro para moradia temporária'],
            ['name' => 'Kit Higiene', 'code' => 'KIT_HIGIENE', 'description' => 'Kit de produtos de higiene e limpeza'],
            ['name' => 'Material de Construção', 'code' => 'MATERIAL_CONSTRUCAO', 'description' => 'Material de construção para reconstrução de moradias'],
            ['name' => 'Aluguel Social', 'code' => 'ALUGUEL_SOCIAL', 'description' => 'Subsídio de aluguel para famílias desabrigadas'],
        ];

        foreach ($programs as $program) {
            BenefitProgram::firstOrCreate(
                ['code' => $program['code']],
                [
                    'name' => $program['name'],
                    'description' => $program['description'] ?? null,
                ]
            );
        }
    }
}

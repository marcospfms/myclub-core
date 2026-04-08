<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('positions')->upsert([
            ['key' => 'goleiro', 'label_key' => 'positions.goleiro.label', 'description_key' => 'positions.goleiro.description', 'icon' => 'shield', 'abbreviation' => 'GOL', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'zagueiro', 'label_key' => 'positions.zagueiro.label', 'description_key' => 'positions.zagueiro.description', 'icon' => 'shield', 'abbreviation' => 'ZC', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'lateral_direito', 'label_key' => 'positions.lateral_direito.label', 'description_key' => 'positions.lateral_direito.description', 'icon' => 'arrow-right', 'abbreviation' => 'LD', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'lateral_esquerdo', 'label_key' => 'positions.lateral_esquerdo.label', 'description_key' => 'positions.lateral_esquerdo.description', 'icon' => 'arrow-left', 'abbreviation' => 'LE', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'volante', 'label_key' => 'positions.volante.label', 'description_key' => 'positions.volante.description', 'icon' => 'circle', 'abbreviation' => 'VOL', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'meia_ligacao', 'label_key' => 'positions.meia_ligacao.label', 'description_key' => 'positions.meia_ligacao.description', 'icon' => 'link', 'abbreviation' => 'ML', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'meia_lateral_direito', 'label_key' => 'positions.meia_lateral_direito.label', 'description_key' => 'positions.meia_lateral_direito.description', 'icon' => 'move-right', 'abbreviation' => 'MLD', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'meia_lateral_esquerdo', 'label_key' => 'positions.meia_lateral_esquerdo.label', 'description_key' => 'positions.meia_lateral_esquerdo.description', 'icon' => 'move-left', 'abbreviation' => 'MLE', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'meia_atacante', 'label_key' => 'positions.meia_atacante.label', 'description_key' => 'positions.meia_atacante.description', 'icon' => 'sparkles', 'abbreviation' => 'MAT', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'segundo_atacante', 'label_key' => 'positions.segundo_atacante.label', 'description_key' => 'positions.segundo_atacante.description', 'icon' => 'zap', 'abbreviation' => 'SA', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ponta_direita', 'label_key' => 'positions.ponta_direita.label', 'description_key' => 'positions.ponta_direita.description', 'icon' => 'corner-up-right', 'abbreviation' => 'PD', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ponta_esquerda', 'label_key' => 'positions.ponta_esquerda.label', 'description_key' => 'positions.ponta_esquerda.description', 'icon' => 'corner-up-left', 'abbreviation' => 'PE', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'atacante', 'label_key' => 'positions.atacante.label', 'description_key' => 'positions.atacante.description', 'icon' => 'target', 'abbreviation' => 'ATA', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'fixo', 'label_key' => 'positions.fixo.label', 'description_key' => 'positions.fixo.description', 'icon' => 'shield', 'abbreviation' => 'FIX', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ala_direito', 'label_key' => 'positions.ala_direito.label', 'description_key' => 'positions.ala_direito.description', 'icon' => 'arrow-right', 'abbreviation' => 'ALD', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ala_esquerdo', 'label_key' => 'positions.ala_esquerdo.label', 'description_key' => 'positions.ala_esquerdo.description', 'icon' => 'arrow-left', 'abbreviation' => 'ALE', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'pivo', 'label_key' => 'positions.pivo.label', 'description_key' => 'positions.pivo.description', 'icon' => 'target', 'abbreviation' => 'PIV', 'created_at' => $now, 'updated_at' => $now],
        ], ['key'], ['label_key', 'description_key', 'icon', 'abbreviation', 'updated_at']);
    }
}

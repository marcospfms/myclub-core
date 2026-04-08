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

        DB::table('positions')->insert([
            ['key' => 'goleiro', 'name' => 'Goleiro', 'abbreviation' => 'GOL', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'zagueiro', 'name' => 'Zagueiro', 'abbreviation' => 'ZC', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'lateral_direito', 'name' => 'Lateral Direito', 'abbreviation' => 'LD', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'lateral_esquerdo', 'name' => 'Lateral Esquerdo', 'abbreviation' => 'LE', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'volante', 'name' => 'Volante', 'abbreviation' => 'VOL', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'meia_ligacao', 'name' => 'Meia de Ligação', 'abbreviation' => 'ML', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'meia_lateral_direito', 'name' => 'Meia Lateral Direito', 'abbreviation' => 'MLD', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'meia_lateral_esquerdo', 'name' => 'Meia Lateral Esquerdo', 'abbreviation' => 'MLE', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'meia_atacante', 'name' => 'Meia Atacante', 'abbreviation' => 'MAT', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'segundo_atacante', 'name' => 'Segundo Atacante', 'abbreviation' => 'SA', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ponta_direita', 'name' => 'Ponta Direita', 'abbreviation' => 'PD', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ponta_esquerda', 'name' => 'Ponta Esquerda', 'abbreviation' => 'PE', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'atacante', 'name' => 'Atacante', 'abbreviation' => 'ATA', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'fixo', 'name' => 'Fixo', 'abbreviation' => 'FIX', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ala_direito', 'name' => 'Ala Direito', 'abbreviation' => 'ALD', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ala_esquerdo', 'name' => 'Ala Esquerdo', 'abbreviation' => 'ALE', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'pivo', 'name' => 'Pivô', 'abbreviation' => 'PIV', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}

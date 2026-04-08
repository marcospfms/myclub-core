<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $badges = [
            ['name' => 'golden_ball', 'label' => 'Bola de Ouro', 'scope' => 'championship', 'description' => 'Mais votos de MVP num campeonato', 'icon' => null],
            ['name' => 'top_scorer', 'label' => 'Artilheiro', 'scope' => 'championship', 'description' => 'Maior número de gols num campeonato', 'icon' => null],
            ['name' => 'best_assist', 'label' => 'Garçom', 'scope' => 'championship', 'description' => 'Maior número de assistências num campeonato', 'icon' => null],
            ['name' => 'best_goalkeeper', 'label' => 'Melhor Goleiro', 'scope' => 'championship', 'description' => 'Goleiro com menor média de gols sofridos', 'icon' => null],
            ['name' => 'fair_play', 'label' => 'Fair Play', 'scope' => 'championship', 'description' => 'Zero cartões durante todo o campeonato', 'icon' => null],
            ['name' => 'hat_trick', 'label' => 'Hat-trick', 'scope' => 'career', 'description' => 'Marcou 3+ gols em uma única partida', 'icon' => null],
            ['name' => 'iron_man', 'label' => 'Homem de Ferro', 'scope' => 'championship', 'description' => 'Participou de 100% das partidas do campeonato', 'icon' => null],
            ['name' => 'unbeaten_champion', 'label' => 'Campeão Invicto', 'scope' => 'championship', 'description' => 'Conquistou o título sem perder nenhuma partida', 'icon' => null],
            ['name' => 'top_scorer_season', 'label' => 'Artilheiro da Temporada', 'scope' => 'seasonal', 'description' => 'Maior total de gols na temporada', 'icon' => null],
            ['name' => 'best_assist_season', 'label' => 'Garçom da Temporada', 'scope' => 'seasonal', 'description' => 'Maior total de assistências na temporada', 'icon' => null],
            ['name' => 'mvp_streak', 'label' => 'MVP em Série', 'scope' => 'career', 'description' => 'Ganhou MVP em 3 ou mais partidas consecutivas', 'icon' => null],
            ['name' => 'loyal_player', 'label' => 'Jogador Fiel', 'scope' => 'career', 'description' => 'Participou de 5+ campeonatos pelo mesmo time', 'icon' => null],
            ['name' => 'rising_star', 'label' => 'Estrela em Ascensão', 'scope' => 'seasonal', 'description' => 'Destaque de desempenho na primeira temporada completa', 'icon' => null],
            ['name' => 'clean_sweep', 'label' => 'Varredura', 'scope' => 'championship', 'description' => 'Venceu todas as partidas da fase de grupos', 'icon' => null],
        ];

        DB::table('badge_types')->insert(array_map(
            fn (array $badge): array => array_merge($badge, [
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            $badges,
        ));
    }
}

<?php

namespace Database\Seeders;

use App\Models\Juego;
use Illuminate\Database\Seeder;

class GamesSeeder extends Seeder
{
    public function run(): void
    {
        $fechaLimite = '2026-12-07';
        $juegos = [
            [
                'nombre' => 'COD Mobile',
                'genero' => 'Shooter',
                'costo_inscripcion' => 25.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'grupo',
                'img_id' => '/imgGames/cod-mobile.webp',
                'reglamentos_pdf' => '/reglamento/cod-mobile.pdf',
            ],
            [
                'nombre' => 'Valorant',
                'genero' => 'Shooter',
                'costo_inscripcion' => 25.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'grupo',
                'img_id' => '/imgGames/valorant.webp',
                'reglamentos_pdf' => '/reglamento/valorant.pdf',
                'legacy_img_ids' => ['valorant'],
            ],
            [
                'nombre' => 'Left 4 Dead 2',
                'genero' => 'Shooter',
                'costo_inscripcion' => 25.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'grupo',
                'img_id' => '/imgGames/left-4-dead-2.webp',
                'reglamentos_pdf' => '/reglamento/left-4-dead-2.pdf',
            ],
            [
                'nombre' => 'FIFA 26',
                'genero' => 'Deportes',
                'costo_inscripcion' => 3.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'individual',
                'img_id' => '/imgGames/fifa-26.webp',
                'reglamentos_pdf' => '/reglamento/fifa-26.pdf',
                'legacy_img_ids' => ['fifa'],
            ],
            [
                'nombre' => 'Clash Royale',
                'genero' => 'Estrategia',
                'costo_inscripcion' => 3.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'individual',
                'img_id' => '/imgGames/clash-royale.webp',
                'reglamentos_pdf' => '/reglamento/clash-royale.pdf',
                'legacy_img_ids' => ['clash'],
            ],
            [
                'nombre' => 'Dragon Ball Sparking Zero',
                'genero' => 'Peleas',
                'costo_inscripcion' => 3.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'individual',
                'img_id' => '/imgGames/dragon-ball-sparking-zero.webp',
                'reglamentos_pdf' => '/reglamento/dragon-ball-sparking-zero.pdf',
                'legacy_img_ids' => ['dragon_ball'],
            ],
            [
                'nombre' => 'Mortal Kombat XL',
                'genero' => 'Peleas',
                'costo_inscripcion' => 3.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'individual',
                'img_id' => '/imgGames/mortal-kombat-xl.webp',
                'reglamentos_pdf' => '/reglamento/mortal-kombat-xl.pdf',
                'legacy_img_ids' => ['mortal'],
            ],
            [
                'nombre' => 'Fortnite',
                'genero' => 'Battle Royale',
                'costo_inscripcion' => 3.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'individual',
                'img_id' => '/imgGames/fortnite.webp',
                'reglamentos_pdf' => '/reglamento/fortnite.pdf',
                'legacy_img_ids' => ['fornite'],
            ],
            [
                'nombre' => 'Free Fire',
                'genero' => 'Battle Royale',
                'costo_inscripcion' => 3.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'individual',
                'img_id' => '/imgGames/free-fire.webp',
                'reglamentos_pdf' => '/reglamento/free-fire.pdf',
                'legacy_img_ids' => ['free_ind'],
            ],
            [
                'nombre' => 'King of Fighters',
                'genero' => 'Peleas',
                'costo_inscripcion' => 3.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'individual',
                'img_id' => '/imgGames/king-of-fighters.webp',
                'reglamentos_pdf' => '/reglamento/king-of-fighters.pdf',
            ],
            [
                'nombre' => 'Smash Bros Ultimate',
                'genero' => 'Peleas',
                'costo_inscripcion' => 3.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'individual',
                'img_id' => '/imgGames/smash-bros-ultimate.webp',
                'reglamentos_pdf' => '/reglamento/smash-bros-ultimate.pdf',
            ],
            [
                'nombre' => '40 (cartas físicas)',
                'genero' => 'Cartas',
                'costo_inscripcion' => 3.00,
                'fecha_limite_inscripcion' => $fechaLimite,
                'modalidad' => 'individual',
                'img_id' => '/imgGames/juego-de-40.webp',
                'reglamentos_pdf' => '/reglamento/juego-de-40.pdf',
            ],
        ];

        $activeGameIds = [];

        foreach ($juegos as $datosJuego) {
            $legacyImageIds = $datosJuego['legacy_img_ids'] ?? [];
            unset($datosJuego['legacy_img_ids']);

            $juego = Juego::query()
                ->where('modalidad', $datosJuego['modalidad'])
                ->where(function ($query) use ($datosJuego, $legacyImageIds): void {
                    $query->where('img_id', $datosJuego['img_id']);

                    if ($legacyImageIds !== []) {
                        $query->orWhereIn('img_id', $legacyImageIds);
                    }
                })
                ->first();

            $juego ??= new Juego;
            $juego->fill($datosJuego);
            $juego->active = true;
            $juego->save();

            $activeGameIds[] = $juego->id;
        }

        Juego::query()
            ->whereNotIn('id', $activeGameIds)
            ->update(['active' => false]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $scrapyardUser = User::query()->firstOrNew([
            'email' => 'casse@example.com',
        ]);

        $scrapyardUser->fill([
            'name' => 'Casse Auto Nord',
            'password' => Hash::make('password'),
            'phone' => '0696000001',
        ]);
        $scrapyardUser->role = 'scrapyard';
        $scrapyardUser->save();

        $scrapyard = Scrapyard::query()->updateOrCreate(
            ['slug' => 'casse-auto-nord'],
            [
                'user_id' => $scrapyardUser->id,
                'name' => 'Casse Auto Nord',
                'city' => 'Fort-de-France',
                'postal_code' => '97200',
                'phone' => '0696000001',
                'email' => 'casse@example.com',
                'description' => 'Casse automobile spécialisée dans les pièces d’occasion.',
            ],
        );

        $clio = Vehicle::query()->updateOrCreate(
            [
                'scrapyard_id' => $scrapyard->id,
                'brand' => 'Renault',
                'model' => 'Clio IV',
                'year' => 2017,
            ],
            [
                'stock_origin' => 'new_arrival',
                'fuel' => 'diesel',
            ],
        );

        $peugeot = Vehicle::query()->updateOrCreate(
            [
                'scrapyard_id' => $scrapyard->id,
                'brand' => 'Peugeot',
                'model' => '208',
                'year' => 2016,
            ],
            [
                'stock_origin' => 'existing_stock',
                'fuel' => 'essence',
            ],
        );

        $headlight = Part::query()->updateOrCreate(
            [
                'vehicle_id' => $clio->id,
                'name' => 'Phare avant droit',
            ],
            [
                'status' => 'available',
                'is_published' => true,
                'price' => 85,
            ],
        );

        Part::query()->updateOrCreate(
            [
                'vehicle_id' => $clio->id,
                'name' => 'Rétroviseur gauche',
            ],
            [
                'status' => 'available',
                'is_published' => true,
                'price' => 45,
            ],
        );

        Part::query()->updateOrCreate(
            [
                'vehicle_id' => $peugeot->id,
                'name' => 'Pare-chocs avant',
            ],
            [
                'status' => 'preparing',
                'is_published' => false,
                'price' => 120,
            ],
        );

        Part::query()->updateOrCreate(
            [
                'vehicle_id' => $peugeot->id,
                'name' => 'Alternateur',
            ],
            [
                'status' => 'available',
                'is_published' => true,
                'price' => 95,
            ],
        );

        $client = User::query()->firstOrNew([
            'email' => 'client@example.com',
        ]);

        $client->fill([
            'name' => 'Client Test',
            'password' => Hash::make('password'),
            'phone' => '0696000002',
        ]);
        $client->role = 'client';
        $client->save();

        PartHoldRequest::query()->updateOrCreate(
            [
                'user_id' => $client->id,
                'part_id' => $headlight->id,
            ],
            [
                'status' => 'pending',
                'customer_message' => 'Bonjour, je souhaite mettre cette pièce de côté.',
            ],
        );
    }
}

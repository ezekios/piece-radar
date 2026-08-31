<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartImage;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_scrapyard_can_create_vehicle_without_photo(): void
    {
        [$user] = $this->createScrapyardAccount();

        $this->actingAs($user)
            ->post(route('scrapyard.vehicles.store'), [
                'brand' => 'Renault',
                'model' => 'Clio',
                'year' => 2018,
            ])
            ->assertRedirect();

        $vehicle = Vehicle::query()->where('brand', 'Renault')->firstOrFail();

        $this->assertSame(0, $vehicle->images()->count());
    }

    public function test_scrapyard_can_create_vehicle_with_one_photo(): void
    {
        [$user] = $this->createScrapyardAccount();

        $this->actingAs($user)
            ->post(route('scrapyard.vehicles.store'), [
                'brand' => 'Peugeot',
                'model' => '208',
                'photos' => [
                    UploadedFile::fake()->image('vehicle.jpg')->size(128),
                ],
            ])
            ->assertRedirect();

        $vehicle = Vehicle::query()->where('brand', 'Peugeot')->firstOrFail();
        $image = $vehicle->images()->firstOrFail();

        $this->assertSame(1, $vehicle->images()->count());
        $this->assertSame(1, $image->position);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_scrapyard_can_create_vehicle_with_multiple_photos(): void
    {
        [$user] = $this->createScrapyardAccount();

        $this->actingAs($user)
            ->post(route('scrapyard.vehicles.store'), [
                'brand' => 'Citroen',
                'model' => 'C3',
                'photos' => [
                    UploadedFile::fake()->image('first.jpg')->size(128),
                    UploadedFile::fake()->image('second.png')->size(128),
                    UploadedFile::fake()->image('third.jpg')->size(128),
                ],
            ])
            ->assertRedirect();

        $vehicle = Vehicle::query()->where('brand', 'Citroen')->firstOrFail();

        $this->assertSame([1, 2, 3], $vehicle->images()->pluck('position')->all());
    }

    public function test_vehicle_upload_is_limited_to_five_photos(): void
    {
        [$user] = $this->createScrapyardAccount();

        $this->actingAs($user)
            ->from(route('scrapyard.vehicles.create'))
            ->post(route('scrapyard.vehicles.store'), [
                'brand' => 'Toyota',
                'model' => 'Yaris',
                'photos' => $this->fakeImages(6),
            ])
            ->assertRedirect(route('scrapyard.vehicles.create'))
            ->assertSessionHasErrors('photos');

        $this->assertFalse(Vehicle::query()->where('brand', 'Toyota')->exists());
    }

    public function test_invalid_vehicle_photo_is_rejected(): void
    {
        [$user] = $this->createScrapyardAccount();

        $this->actingAs($user)
            ->from(route('scrapyard.vehicles.create'))
            ->post(route('scrapyard.vehicles.store'), [
                'brand' => 'Ford',
                'model' => 'Fiesta',
                'photos' => [
                    UploadedFile::fake()->create('document.pdf', 128, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('scrapyard.vehicles.create'))
            ->assertSessionHasErrors('photos.0');

        $this->assertFalse(Vehicle::query()->where('brand', 'Ford')->exists());
    }

    public function test_vehicle_photo_larger_than_five_megabytes_is_rejected(): void
    {
        [$user] = $this->createScrapyardAccount();

        $this->actingAs($user)
            ->from(route('scrapyard.vehicles.create'))
            ->post(route('scrapyard.vehicles.store'), [
                'brand' => 'Nissan',
                'model' => 'Micra',
                'photos' => [
                    UploadedFile::fake()->image('too-large.jpg')->size(5121),
                ],
            ])
            ->assertRedirect(route('scrapyard.vehicles.create'))
            ->assertSessionHasErrors('photos.0');

        $this->assertFalse(Vehicle::query()->where('brand', 'Nissan')->exists());
    }

    public function test_scrapyard_can_add_photos_to_existing_vehicle(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $vehicle = $this->createVehicle($scrapyard);

        $this->actingAs($user)
            ->post(route('scrapyard.vehicles.update', $vehicle), [
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'photos' => [
                    UploadedFile::fake()->image('added.jpg')->size(128),
                    UploadedFile::fake()->image('added-2.jpg')->size(128),
                ],
            ])
            ->assertRedirect(route('scrapyard.vehicles.show', $vehicle));

        $this->assertSame([1, 2], $vehicle->fresh()->images()->pluck('position')->all());
    }

    public function test_existing_vehicle_cannot_exceed_five_photos(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $vehicle = $this->createVehicle($scrapyard);
        $this->attachVehicleImages($vehicle, 4);

        $this->actingAs($user)
            ->from(route('scrapyard.vehicles.edit', $vehicle))
            ->post(route('scrapyard.vehicles.update', $vehicle), [
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'photos' => $this->fakeImages(2),
            ])
            ->assertRedirect(route('scrapyard.vehicles.edit', $vehicle))
            ->assertSessionHasErrors('photos');

        $this->assertSame(4, $vehicle->fresh()->images()->count());
    }

    public function test_scrapyard_can_delete_own_vehicle_photo_and_physical_file(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $vehicle = $this->createVehicle($scrapyard);
        $image = $this->attachVehicleImages($vehicle, 1)->first();

        $this->actingAs($user)
            ->delete(route('scrapyard.vehicles.images.destroy', [$vehicle, $image]))
            ->assertRedirect();

        $this->assertDatabaseMissing('vehicle_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_scrapyard_cannot_delete_vehicle_photo_from_another_scrapyard(): void
    {
        [$userA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $vehicleB = $this->createVehicle($scrapyardB);
        $image = $this->attachVehicleImages($vehicleB, 1)->first();

        $this->actingAs($userA)
            ->delete(route('scrapyard.vehicles.images.destroy', [$vehicleB, $image]))
            ->assertNotFound();

        $this->assertDatabaseHas('vehicle_images', ['id' => $image->id]);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_scrapyard_can_create_part_without_photo(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $vehicle = $this->createVehicle($scrapyard);

        $this->actingAs($user)
            ->post(route('scrapyard.vehicles.parts.store', $vehicle), [
                'name' => 'Phare avant droit',
                'status' => 'preparing',
            ])
            ->assertRedirect(route('scrapyard.vehicles.show', $vehicle));

        $part = Part::query()->where('name', 'Phare avant droit')->firstOrFail();

        $this->assertSame(0, $part->images()->count());
    }

    public function test_scrapyard_can_create_part_with_photos(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $vehicle = $this->createVehicle($scrapyard);

        $this->actingAs($user)
            ->post(route('scrapyard.vehicles.parts.store', $vehicle), [
                'name' => 'Alternateur',
                'status' => 'preparing',
                'photos' => [
                    UploadedFile::fake()->image('part-one.jpg')->size(128),
                    UploadedFile::fake()->image('part-two.png')->size(128),
                ],
            ])
            ->assertRedirect(route('scrapyard.vehicles.show', $vehicle));

        $part = Part::query()->where('name', 'Alternateur')->firstOrFail();

        $this->assertSame([1, 2], $part->images()->pluck('position')->all());
        Storage::disk('public')->assertExists($part->images()->firstOrFail()->path);
    }

    public function test_part_upload_is_limited_to_five_photos(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $vehicle = $this->createVehicle($scrapyard);

        $this->actingAs($user)
            ->from(route('scrapyard.vehicles.parts.create', $vehicle))
            ->post(route('scrapyard.vehicles.parts.store', $vehicle), [
                'name' => 'Capot',
                'status' => 'preparing',
                'photos' => $this->fakeImages(6),
            ])
            ->assertRedirect(route('scrapyard.vehicles.parts.create', $vehicle))
            ->assertSessionHasErrors('photos');

        $this->assertFalse(Part::query()->where('name', 'Capot')->exists());
    }

    public function test_existing_part_cannot_exceed_five_photos(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $part = $this->createPart($this->createVehicle($scrapyard));
        $this->attachPartImages($part, 5);

        $this->actingAs($user)
            ->from(route('scrapyard.parts.preparation.edit', $part))
            ->post(route('scrapyard.parts.preparation.update', $part), [
                'name' => $part->name,
                'status' => $part->status,
                'photos' => [
                    UploadedFile::fake()->image('too-much.jpg')->size(128),
                ],
            ])
            ->assertRedirect(route('scrapyard.parts.preparation.edit', $part))
            ->assertSessionHasErrors('photos');

        $this->assertSame(5, $part->fresh()->images()->count());
    }

    public function test_invalid_part_photo_is_rejected(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $vehicle = $this->createVehicle($scrapyard);

        $this->actingAs($user)
            ->from(route('scrapyard.vehicles.parts.create', $vehicle))
            ->post(route('scrapyard.vehicles.parts.store', $vehicle), [
                'name' => 'Aile avant',
                'status' => 'preparing',
                'photos' => [
                    UploadedFile::fake()->create('archive.zip', 128, 'application/zip'),
                ],
            ])
            ->assertRedirect(route('scrapyard.vehicles.parts.create', $vehicle))
            ->assertSessionHasErrors('photos.0');

        $this->assertFalse(Part::query()->where('name', 'Aile avant')->exists());
    }

    public function test_part_photo_larger_than_five_megabytes_is_rejected(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $vehicle = $this->createVehicle($scrapyard);

        $this->actingAs($user)
            ->from(route('scrapyard.vehicles.parts.create', $vehicle))
            ->post(route('scrapyard.vehicles.parts.store', $vehicle), [
                'name' => 'Pare-chocs',
                'status' => 'preparing',
                'photos' => [
                    UploadedFile::fake()->image('too-large.jpg')->size(5121),
                ],
            ])
            ->assertRedirect(route('scrapyard.vehicles.parts.create', $vehicle))
            ->assertSessionHasErrors('photos.0');

        $this->assertFalse(Part::query()->where('name', 'Pare-chocs')->exists());
    }

    public function test_scrapyard_can_add_photos_to_existing_part_from_preparation(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $part = $this->createPart($this->createVehicle($scrapyard));

        $this->actingAs($user)
            ->post(route('scrapyard.parts.preparation.update', $part), [
                'name' => $part->name,
                'status' => $part->status,
                'photos' => [
                    UploadedFile::fake()->image('prepared.jpg')->size(128),
                ],
            ])
            ->assertRedirect(route('scrapyard.parts.show', $part));

        $image = $part->fresh()->images()->firstOrFail();

        $this->assertSame(1, $image->position);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_scrapyard_can_delete_own_part_photo_and_physical_file(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $part = $this->createPart($this->createVehicle($scrapyard));
        $image = $this->attachPartImages($part, 1)->first();

        $this->actingAs($user)
            ->delete(route('scrapyard.parts.images.destroy', [$part, $image]))
            ->assertRedirect();

        $this->assertDatabaseMissing('part_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_scrapyard_cannot_delete_part_photo_from_another_scrapyard(): void
    {
        [$userA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $partB = $this->createPart($this->createVehicle($scrapyardB));
        $image = $this->attachPartImages($partB, 1)->first();

        $this->actingAs($userA)
            ->delete(route('scrapyard.parts.images.destroy', [$partB, $image]))
            ->assertNotFound();

        $this->assertDatabaseHas('part_images', ['id' => $image->id]);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_public_parts_index_displays_first_part_photo(): void
    {
        [, $scrapyard] = $this->createScrapyardAccount();
        $part = $this->createPart($this->createVehicle($scrapyard), [
            'name' => 'Phare public photo',
            'status' => 'available',
            'is_published' => true,
        ]);
        $this->attachPartImages($part, 1, 'part-images/public-index');

        $this->get(route('client.parts.index'))
            ->assertOk()
            ->assertSee('Phare public photo')
            ->assertSee('part-images/public-index-1.jpg');
    }

    public function test_public_part_detail_and_request_form_display_part_photos(): void
    {
        [, $scrapyard] = $this->createScrapyardAccount();
        $part = $this->createPart($this->createVehicle($scrapyard), [
            'name' => 'Alternateur public photo',
            'status' => 'available',
            'is_published' => true,
        ]);
        $this->attachPartImages($part, 2, 'part-images/public-detail');

        $client = User::factory()->create();
        $client->forceFill([
            'role' => 'client',
        ])->save();

        $this->get(route('pieces.show', $part))
            ->assertOk()
            ->assertSee('Alternateur public photo')
            ->assertSee('part-images/public-detail-1.jpg')
            ->assertSee('part-images/public-detail-2.jpg');

        $this->actingAs($client)
            ->get(route('pieces.request', $part))
            ->assertOk()
            ->assertSee('part-images/public-detail-1.jpg');
    }

    public function test_scrapyard_views_work_when_vehicle_and_part_have_no_photo(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount();
        $vehicle = $this->createVehicle($scrapyard, ['brand' => 'Dacia']);
        $part = $this->createPart($vehicle, ['name' => 'Rétroviseur sans photo']);

        $this->actingAs($user)
            ->get(route('scrapyard.vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('Photos du véhicule');

        $this->actingAs($user)
            ->get(route('scrapyard.parts.show', $part))
            ->assertOk()
            ->assertSee('Photos de la pièce');
    }

    /**
     * @return array{0: User, 1: Scrapyard}
     */
    private function createScrapyardAccount(string $name = 'Casse Test'): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->forceFill([
            'role' => 'scrapyard',
        ])->save();

        $scrapyard = Scrapyard::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
            'city' => 'Fort-de-France',
            'is_active' => true,
        ]);

        return [$user, $scrapyard];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createVehicle(Scrapyard $scrapyard, array $attributes = []): Vehicle
    {
        return Vehicle::query()->create([
            'scrapyard_id' => $scrapyard->id,
            'brand' => $attributes['brand'] ?? 'Renault',
            'model' => $attributes['model'] ?? 'Clio',
            'year' => $attributes['year'] ?? 2018,
            'engine' => $attributes['engine'] ?? '1.5 dCi',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createPart(Vehicle $vehicle, array $attributes = []): Part
    {
        return Part::query()->create([
            'vehicle_id' => $vehicle->id,
            'name' => $attributes['name'] ?? 'Phare avant droit',
            'category' => $attributes['category'] ?? 'Optique',
            'condition' => $attributes['condition'] ?? 'used_good',
            'status' => $attributes['status'] ?? 'available',
            'price' => $attributes['price'] ?? 85,
            'is_published' => $attributes['is_published'] ?? true,
        ]);
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function fakeImages(int $count): array
    {
        return collect(range(1, $count))
            ->map(fn (int $index) => UploadedFile::fake()->image("photo-{$index}.jpg")->size(128))
            ->all();
    }

    private function attachVehicleImages(Vehicle $vehicle, int $count, string $pathPrefix = 'vehicle-images/test'): \Illuminate\Support\Collection
    {
        return collect(range(1, $count))->map(function (int $position) use ($vehicle, $pathPrefix): VehicleImage {
            $path = $pathPrefix . '-' . $position . '.jpg';
            Storage::disk('public')->put($path, 'vehicle-image');

            return $vehicle->images()->create([
                'path' => $path,
                'position' => $position,
            ]);
        });
    }

    private function attachPartImages(Part $part, int $count, string $pathPrefix = 'part-images/test'): \Illuminate\Support\Collection
    {
        return collect(range(1, $count))->map(function (int $position) use ($part, $pathPrefix): PartImage {
            $path = $pathPrefix . '-' . $position . '.jpg';
            Storage::disk('public')->put($path, 'part-image');

            return $part->images()->create([
                'path' => $path,
                'position' => $position,
            ]);
        });
    }
}

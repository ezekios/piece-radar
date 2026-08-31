<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use App\Notifications\PartHoldRequests\NewPartHoldRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_name_uses_piece_radar_branding(): void
    {
        $this->assertSame('Pièce Radar', config('app.name'));
    }

    public function test_auth_notifications_use_piece_radar_branding(): void
    {
        $user = $this->createUser();

        $resetMail = (new ResetPasswordNotification('reset-token'))->toMail($user);
        $verificationMail = (new VerifyEmailNotification)->toMail($user);

        $this->assertSame('Réinitialiser votre mot de passe', $resetMail->subject);
        $this->assertStringContainsString('Pièce Radar', $this->mailText($resetMail));
        $this->assertStringContainsString('Pièce Radar', $this->renderMail($resetMail));
        $this->assertSame('Vérifiez votre adresse email', $verificationMail->subject);
        $this->assertStringContainsString('Pièce Radar', $this->mailText($verificationMail));
        $this->assertStringContainsString('Pièce Radar', $this->renderMail($verificationMail));

        $this->assertMailDoesNotContainLaravelDefaults($resetMail);
        $this->assertMailDoesNotContainLaravelDefaults($verificationMail);
    }

    public function test_business_notifications_use_piece_radar_signature_and_mail_templates(): void
    {
        $scrapyard = $this->createScrapyard();
        $part = $this->createPart($this->createVehicle($scrapyard));
        $holdRequest = $this->createHoldRequest($part, $this->createUser());

        $mail = (new NewPartHoldRequestNotification($holdRequest))->toMail($scrapyard);

        $this->assertSame('Nouvelle demande de mise de côté', $mail->subject);
        $this->assertStringContainsString('Pièce Radar', $this->mailText($mail));
        $this->assertStringContainsString("L'équipe Pièce Radar", $mail->salutation);
        $this->assertStringContainsString('Pièce Radar', $this->renderMail($mail));
        $this->assertMailDoesNotContainLaravelDefaults($mail);
    }

    public function test_error_pages_are_branded_without_sensitive_debug_details(): void
    {
        $this->get('/page-introuvable-test')
            ->assertNotFound()
            ->assertSee('Page introuvable')
            ->assertSee('Pièce Radar')
            ->assertDontSee('Stack trace')
            ->assertDontSee('SQLSTATE')
            ->assertDontSee('Laravel');

        $client = $this->createUser(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('scrapyard.dashboard'))
            ->assertForbidden()
            ->assertSee('Accès non autorisé')
            ->assertSee('Pièce Radar')
            ->assertDontSee('Stack trace')
            ->assertDontSee('SQLSTATE')
            ->assertDontSee('Laravel');

        $this->view('errors.419')
            ->assertSee('Session expirée')
            ->assertSee('Pièce Radar')
            ->assertDontSee('Laravel');
    }

    public function test_main_public_pages_do_not_show_default_laravel_branding(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Pièce Radar')
            ->assertDontSee('Laravel News')
            ->assertDontSee('Laravel');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Pièce Radar')
            ->assertDontSee('<title>Laravel</title>', false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(array $attributes = []): User
    {
        $role = $attributes['role'] ?? 'client';
        unset($attributes['role']);

        $user = User::factory()->create(array_merge([
            'name' => 'Client Test',
            'email' => 'client-' . uniqid() . '@example.com',
            'phone' => '0696000000',
            'password' => Hash::make('password'),
        ], $attributes));

        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function createScrapyard(): Scrapyard
    {
        $user = $this->createUser([
            'role' => 'scrapyard',
            'name' => 'Casse Test',
            'email' => 'casse-' . uniqid() . '@example.com',
        ]);

        return Scrapyard::query()->create([
            'user_id' => $user->id,
            'name' => 'Casse Test',
            'slug' => 'casse-test-' . uniqid(),
            'city' => 'Fort-de-France',
            'email' => $user->email,
            'is_active' => true,
        ]);
    }

    private function createVehicle(Scrapyard $scrapyard): Vehicle
    {
        return Vehicle::query()->create([
            'scrapyard_id' => $scrapyard->id,
            'brand' => 'Renault',
            'model' => 'Clio',
            'year' => 2018,
            'engine' => '1.5 dCi',
        ]);
    }

    private function createPart(Vehicle $vehicle): Part
    {
        return Part::query()->create([
            'vehicle_id' => $vehicle->id,
            'name' => 'Phare avant droit',
            'category' => 'Optique',
            'reference' => 'REF-BRAND',
            'condition' => 'used_good',
            'status' => 'available',
            'price' => 85,
            'is_published' => true,
        ]);
    }

    private function createHoldRequest(Part $part, User $client): PartHoldRequest
    {
        return PartHoldRequest::query()->create([
            'user_id' => $client->id,
            'part_id' => $part->id,
            'status' => 'pending',
            'customer_message' => 'Je souhaite réserver cette pièce.',
        ]);
    }

    private function mailText(MailMessage $mail): string
    {
        return implode("\n", array_filter([
            $mail->subject,
            $mail->greeting,
            ...array_map('strval', $mail->introLines),
            $mail->actionText,
            ...array_map('strval', $mail->outroLines),
            $mail->salutation,
        ]));
    }

    private function renderMail(MailMessage $mail): string
    {
        return (string) $mail->render();
    }

    private function assertMailDoesNotContainLaravelDefaults(MailMessage $mail): void
    {
        $content = $this->mailText($mail) . "\n" . $this->renderMail($mail);

        $this->assertStringNotContainsString('laravel.com/img/notification-logo', $content);
        $this->assertStringNotContainsString('Regards,<br>', $content);
        $this->assertStringNotContainsString('All rights reserved', $content);
        $this->assertStringNotContainsString('Laravel', $content);
    }
}

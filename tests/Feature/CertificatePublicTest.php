<?php

namespace Tests\Feature;

use App\Enums\CertificateScope;
use App\Enums\MedalType;
use App\Models\CertificateTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsCertificates;
use Tests\TestCase;

class CertificatePublicTest extends TestCase
{
    use BuildsCertificates, RefreshDatabase;

    #[Test]
    public function index_page_loads_without_auth(): void
    {
        $this->get('/sertifikat')->assertOk();
    }

    #[Test]
    public function lookup_with_unknown_nik_returns_error(): void
    {
        $this->post('/sertifikat', ['nik' => '9999999999999999'])
            ->assertSessionHasErrors('nik');
    }

    #[Test]
    public function lookup_with_valid_nik_shows_participant_name(): void
    {
        $this->makeEligibleRegistration('1234567890123456');

        $this->post('/sertifikat', ['nik' => '1234567890123456'])
            ->assertOk()
            ->assertSee('Atlet Publik');
    }

    #[Test]
    public function pdf_download_for_eligible_registration(): void
    {
        $reg = $this->makeEligibleRegistration('1234567890123456');

        $response = $this->get("/sertifikat/{$reg->id}/pdf");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function pdf_for_pending_payment_registration_returns_404(): void
    {
        $reg = $this->makeEligibleRegistration('1234567890123456', 'pending');

        $this->get("/sertifikat/{$reg->id}/pdf")->assertNotFound();
    }

    #[Test]
    public function pdf_with_custom_flexible_texts_renders(): void
    {
        $reg = $this->makeEligibleRegistration('1234567890123456');

        Storage::fake('public');
        $path = Storage::disk('public')->putFile('certificate-templates', UploadedFile::fake()->image('tpl2.png'));

        CertificateTemplate::create([
            'name' => 'Custom 2 Teks',
            'scope' => CertificateScope::Fallback,
            'image_path' => $path,
            'texts' => [
                ['content' => '{nama}', 'x' => 50, 'y' => 40, 'font_size' => 5, 'bold' => true, 'font_family' => 'greatvibes', 'color' => '#1a5276'],
                ['content' => 'Festival {event} — {kontingen}', 'x' => 50, 'y' => 60, 'font_size' => 3, 'bold' => false, 'font_family' => 'dancingscript', 'color' => '#000000'],
                ['content' => 'No. apr/7yh652/260829/{xxx}', 'x' => 50, 'y' => 90, 'font_size' => 2, 'bold' => false, 'font_family' => 'helvetica', 'color' => '#000000'],
            ],
        ]);

        $response = $this->get("/sertifikat/{$reg->id}/pdf");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    /**
     * Buat peserta verified+paid dgn NIK tertentu + result Gold + template fallback global.
     */
    protected function makeEligibleRegistration(string $nik, ?string $paymentStatus = 'verified')
    {
        $reg = $this->makeRegistration(
            ['rank_name' => 'Juara 1', 'medal_type' => MedalType::Gold],
            $paymentStatus,
            $nik,
        );

        Storage::fake('public');
        $path = Storage::disk('public')->putFile('certificate-templates', UploadedFile::fake()->image('tpl.png'));

        CertificateTemplate::create([
            'name' => 'Fallback Global',
            'scope' => CertificateScope::Fallback,
            'image_path' => $path,
        ]);

        return $reg;
    }
}

<?php

namespace Tests\Feature;

use App\Enums\CertificateScope;
use App\Enums\MedalType;
use App\Models\CertificateTemplate;
use App\Models\SubCategory;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsCertificates;
use Tests\TestCase;

class CertificateServiceTest extends TestCase
{
    use BuildsCertificates, RefreshDatabase;

    #[Test]
    public function gold_juara_1_yields_juara_1_and_champion_gold(): void
    {
        $reg = $this->makeRegistration([
            'rank_name' => 'Juara 1',
            'medal_type' => MedalType::Gold,
        ]);

        $entries = app(CertificateService::class)->forParticipant($reg->participant);

        $this->assertCount(1, $entries);
        $this->assertSame('JUARA 1', $entries[0]['status']);
        $this->assertSame(CertificateScope::ChampionGold, $entries[0]['scope']);
    }

    #[Test]
    public function no_result_yields_peserta_and_participant_scope(): void
    {
        $reg = $this->makeRegistration();

        $entries = app(CertificateService::class)->forParticipant($reg->participant);

        $this->assertCount(1, $entries);
        $this->assertSame('PESERTA', $entries[0]['status']);
        $this->assertSame(CertificateScope::Participant, $entries[0]['scope']);
    }

    #[Test]
    public function rank_favorit_yields_juara_favorit_and_champion_other(): void
    {
        $reg = $this->makeRegistration([
            'rank_name' => 'Favorit',
            'medal_type' => MedalType::Bronze,
        ]);

        $entries = app(CertificateService::class)->forParticipant($reg->participant);

        $this->assertSame('JUARA FAVORIT', $entries[0]['status']);
        $this->assertSame(CertificateScope::ChampionOther, $entries[0]['scope']);
    }

    #[Test]
    public function rank_juara_3_bersama_does_not_double_juara(): void
    {
        $reg = $this->makeRegistration([
            'rank_name' => 'Juara 3 Bersama',
            'medal_type' => MedalType::Bronze,
        ]);

        $entries = app(CertificateService::class)->forParticipant($reg->participant);

        $this->assertSame('JUARA 3 BERSAMA', $entries[0]['status']);
        $this->assertSame(CertificateScope::ChampionBronze, $entries[0]['scope']);
    }

    #[Test]
    public function pending_payment_registration_excluded(): void
    {
        $reg = $this->makeRegistration(null, 'pending');

        $entries = app(CertificateService::class)->forParticipant($reg->participant);

        $this->assertCount(0, $entries);
    }

    #[Test]
    public function coach_registration_excluded(): void
    {
        $reg = $this->makeRegistration();
        // Ubah jadi coach: sub_category null
        $reg->update(['sub_category_id' => null]);

        $entries = app(CertificateService::class)->forParticipant($reg->participant);

        $this->assertCount(0, $entries);
    }

    #[Test]
    public function resolve_template_fallback_chain(): void
    {
        $subCategory = SubCategory::factory()->create();
        $event = $subCategory->eventCategory->event;

        Storage::fake('public');
        $path = Storage::disk('public')->putFile('certificate-templates', UploadedFile::fake()->image('tpl.png'));

        $service = app(CertificateService::class);

        // 1. Hanya fallback global → dipakai untuk scope apa pun
        $global = CertificateTemplate::create([
            'name' => 'Global Fallback',
            'scope' => CertificateScope::Fallback,
            'image_path' => $path,
        ]);
        $this->assertTrue($service->resolveTemplate($event, CertificateScope::ChampionGold)->is($global));

        // 2. Fallback event menang atas fallback global
        $eventFallback = CertificateTemplate::create([
            'event_id' => $event->id,
            'name' => 'Event Fallback',
            'scope' => CertificateScope::Fallback,
            'image_path' => $path,
        ]);
        $this->assertTrue($service->resolveTemplate($event, CertificateScope::ChampionGold)->is($eventFallback));

        // 3. Scope spesifik event menang atas fallback event
        $specific = CertificateTemplate::create([
            'event_id' => $event->id,
            'name' => 'Gold Specific',
            'scope' => CertificateScope::ChampionGold,
            'image_path' => $path,
        ]);
        $this->assertTrue($service->resolveTemplate($event, CertificateScope::ChampionGold)->is($specific));
    }

    #[Test]
    public function no_template_returns_null(): void
    {
        $subCategory = SubCategory::factory()->create();
        $event = $subCategory->eventCategory->event;

        $this->assertNull(app(CertificateService::class)->resolveTemplate($event, CertificateScope::Participant));
    }

    #[Test]
    public function festival_dan_juara_tetap_scope_festival(): void
    {
        // Festival = pertandingan hiburan: apa pun hasilnya, sertifikat biasa seragam
        $reg = $this->makeRegistration([
            'rank_name' => 'Juara 1',
            'medal_type' => MedalType::Gold,
        ]);
        // Default factory = Open; ubah event category-nya jadi Festival
        $reg->subCategory->eventCategory->update(['type' => \App\Enums\EventCategoryType::Festival]);

        $entries = app(CertificateService::class)->forParticipant($reg->participant);

        $this->assertSame(CertificateScope::Festival, $entries[0]['scope']);
        // Status teks tetap JUARA 1 — yang berubah hanya template-nya
        $this->assertSame('JUARA 1', $entries[0]['status']);
    }

    #[Test]
    public function festival_tanpa_result_juga_scope_festival(): void
    {
        $reg = $this->makeRegistration();
        $reg->subCategory->eventCategory->update(['type' => \App\Enums\EventCategoryType::Festival]);

        $entries = app(CertificateService::class)->forParticipant($reg->participant);

        $this->assertSame(CertificateScope::Festival, $entries[0]['scope']);
    }

    #[Test]
    public function open_tanpa_result_tetap_scope_participant(): void
    {
        // Non-Festival tanpa Result = Sertifikat Penghargaan — perilaku lama tidak berubah
        $reg = $this->makeRegistration(); // default factory type = Open

        $entries = app(CertificateService::class)->forParticipant($reg->participant);

        $this->assertSame(CertificateScope::Participant, $entries[0]['scope']);
    }

    #[Test]
    public function template_scope_festival_dipilih_untuk_registrasi_festival(): void
    {
        $reg = $this->makeRegistration([
            'rank_name' => 'Juara 1',
            'medal_type' => MedalType::Gold,
        ]);
        $reg->subCategory->eventCategory->update([
            'type' => \App\Enums\EventCategoryType::Festival,
        ]);
        $event = $reg->subCategory->eventCategory->event;

        $festivalTpl = \App\Models\CertificateTemplate::create([
            'event_id' => $event->id,
            'name' => 'Sertifikat Festival',
            'scope' => CertificateScope::Festival,
            'image_path' => 'certificate-templates/test.png',
        ]);

        $entries = app(CertificateService::class)->forParticipant($reg->participant);
        $template = app(CertificateService::class)
            ->resolveTemplate($entries[0]['event'], $entries[0]['scope']);

        $this->assertTrue($template->is($festivalTpl));
    }
}

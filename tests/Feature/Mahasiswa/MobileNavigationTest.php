<?php

use App\Models\Mahasiswa;
use App\Models\TahunAkademik;

beforeEach(function () {
    $this->withoutVite();
    TahunAkademik::factory()->create(['is_active' => true]);
    $this->mahasiswa = Mahasiswa::factory()->create();
});

it('provides all student desktop destinations on mobile except thesis and internships', function () {
    $response = $this->actingAs($this->mahasiswa->user)->get(route('mahasiswa.dashboard'))->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
    $xpath = new DOMXPath($document);
    $mobileLinks = collect($xpath->query('//*[@id="student-mobile-navigation" or @id="student-mobile-menu"]//a'))
        ->map(fn (DOMElement $link): string => $link->getAttribute('href'))->unique()->sort()->values()->all();
    $desktopLinks = collect($xpath->query('//aside//a[contains(@href,"/mahasiswa/")]'))
        ->map(fn (DOMElement $link): string => $link->getAttribute('href'))
        ->reject(fn (string $url): bool => in_array($url, [route('mahasiswa.skripsi.index'), route('mahasiswa.kp.index')]))
        ->unique()->sort()->values()->all();

    expect($desktopLinks)->not->toBeEmpty()
        ->and($mobileLinks)->toBe($desktopLinks)
        ->and($mobileLinks)->toContain(route('mahasiswa.lms.index'), route('mahasiswa.ai-advisor.index'))
        ->and($mobileLinks)->not->toContain(route('mahasiswa.skripsi.index'), route('mahasiswa.kp.index'));
});

it('keeps thesis and internship links available only at desktop sizes', function () {
    $response = $this->actingAs($this->mahasiswa->user)->get(route('mahasiswa.dashboard'))->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
    $xpath = new DOMXPath($document);

    foreach (['mahasiswa.skripsi.index', 'mahasiswa.kp.index'] as $route) {
        $links = $xpath->query('//a[@href="'.route($route).'"]');
        expect($links->length)->toBe(1);
        $classes = explode(' ', $links->item(0)->getAttribute('class'));
        expect($classes)->toContain('hidden', 'md:flex')->not->toContain('flex');
    }
});

it('offers the completed mobile menu on other student pages too', function () {
    $response = $this->actingAs($this->mahasiswa->user)->get(route('mahasiswa.biodata.index'))->assertSuccessful();
    $response->assertSee('id="student-mobile-menu"', false)
        ->assertSee('aria-controls="student-mobile-menu"', false)
        ->assertSee('max-h-[85vh]', false)->assertSee('overflow-y-auto', false)
        ->assertSee('Materi & Tugas', false)->assertSee('AI Advisor')->assertSee('Tutup menu');
});

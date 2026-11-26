<?php

namespace Tests\Feature\Docs;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LegalOwnerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'company.owner.name' => 'Ana Pérez Gómez',
            'company.owner.nif' => '12345678Z',
            'company.owner.address' => 'Calle Falsa 123, 41001 Sevilla',
            'company.owner.email' => 'hola@example.com',
        ]);
    }

    public static function legalTexts(): array
    {
        return [
            'es privacy' => ['/docs/es/privacy', 'Titular'],
            'en privacy' => ['/docs/en/privacy', 'Owner'],
            'es conditions' => ['/docs/es/conditions', 'Titular'],
            'en conditions' => ['/docs/en/conditions', 'Owner'],
        ];
    }

    #[Test]
    #[DataProvider('legalTexts')]
    public function legal_texts_show_who_runs_the_site_in_their_language(string $url, string $ownerLabel)
    {
        $this->get($url)
            ->assertOk()
            ->assertSeeInOrder([$ownerLabel, 'Ana Pérez Gómez', '12345678Z', 'Calle Falsa 123, 41001 Sevilla', 'hola@example.com'])
            ->assertDontSee('noreply@talentbattles.com')
            ->assertDontSee('<LegalOwner');
    }

    #[Test]
    public function owner_data_the_env_does_not_set_are_left_out()
    {
        config(['company.owner.nif' => null, 'company.owner.address' => null]);

        $this->get('/docs/es/privacy')
            ->assertOk()
            ->assertSee('Ana Pérez Gómez')
            ->assertDontSee('NIF:')
            ->assertDontSee('Domicilio:');
    }
}

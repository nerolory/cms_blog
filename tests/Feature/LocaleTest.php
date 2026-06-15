<?php

namespace Tests\Feature;

use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс locale.
 */
class LocaleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * test user can switch locale to english.
     */
    public function test_user_can_switch_locale_to_english(): void
    {
        $this->post(route('locale.update'), ['locale' => 'en'])->assertRedirect();
        $this->get(route('home'))->assertOk();
        $this->assertSame('en', app()->getLocale());
    }
}

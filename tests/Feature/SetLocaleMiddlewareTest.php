<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetLocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_locale_is_used_when_no_session(): void
    {
        $this->get('/');
        $this->assertSame(config('app.locale'), app()->getLocale());
    }

    public function test_locale_switch_sets_session(): void
    {
        $response = $this->post('/locale/pl');

        $response->assertRedirect();
        $this->assertSame('pl', session('locale'));
    }

    public function test_locale_switch_rejects_invalid_locale(): void
    {
        $this->post('/locale/de');

        $this->assertNull(session('locale'));
    }

    public function test_session_locale_is_applied_on_next_request(): void
    {
        $this->withSession(['locale' => 'pl'])->get('/');

        $this->assertSame('pl', app()->getLocale());
    }
}

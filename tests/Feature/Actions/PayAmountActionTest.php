<?php

namespace Tests\Feature\Actions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Actions\PayAmountAction;
use Tests\TestCase;

class PayAmountActionTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function pay_amount_action_requires_amount_returns_boolean(): void
    {
        $amount = $this->faker->numberBetween(100, 10000);
        $action = app(PayAmountAction::class);
        $attribs = compact('amount');
        $this->assertTrue($action->run($attribs));
    }

    /** @test */
    public function pay_amount_action_has_controller(): void
    {
        $amount = $this->faker->numberBetween(100, 10000);
        $attribs = compact('amount');
        $response = $this->postJson(route('pay-amount'), $attribs);
        $response->assertStatus(200);
        $response->assertContent(json_encode($attribs));
    }
}

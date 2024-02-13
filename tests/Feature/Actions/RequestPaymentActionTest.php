<?php

namespace Tests\Feature\Actions;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Actions\RequestPaymentAction;
use Illuminate\Support\Facades\Event;
use App\Events\PaymentRequested;
use Tests\TestCase;

class RequestPaymentActionTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function pay_amount_action_requires_amount_returns_boolean(): void
    {
        Event::fake();
        $reference_code = substr($this->faker->uuid(), -10);
        $amount = $this->faker->numberBetween(100, 200) * 100;
        $action = app(RequestPaymentAction::class);
        $attribs = compact('reference_code', 'amount');
        $this->assertTrue($action->run($attribs));
        Event::assertDispatched(PaymentRequested::class, function ( PaymentRequested $notification ) use ($reference_code) {
            return $notification->responseData->reference_code = $reference_code;
        });
    }

    /** @test */
    public function pay_amount_action_has_controller(): void
    {
        $reference_code = substr($this->faker->uuid(), -10);
        $amount = $this->faker->numberBetween(100, 200) * 100; //* 100 is required so there will be no cents
        $attribs = compact('reference_code', 'amount');
        $user = User::factory()->create();
        $token = $user->createToken('pipe-dream', ['collect-payment'])->plainTextToken;
        $response = $this->withHeaders(['Authorization'=>'Bearer '.$token])->postJson(route('collect-payment'), $attribs);
        $response->assertStatus(200);
        $response->assertContent(json_encode($attribs));
    }
}

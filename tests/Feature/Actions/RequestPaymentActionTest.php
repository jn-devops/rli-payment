<?php

namespace Tests\Feature\Actions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Actions\RequestPaymentAction;
use Illuminate\Support\Facades\Event;
use App\Events\PaymentRequested;
use App\Data\ResponseData;
use App\Models\User;
use Tests\TestCase;

class RequestPaymentActionTest extends TestCase
{
    use WithFaker;

    protected array $attribs;

//    protected ParamsData $paramsData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attribs = [
            'reference_code' => substr($this->faker->uuid(), -10),
            'amount' => $this->faker->numberBetween(100, 200) * 100
        ];

//        $config = config('rli-payment.aub_paymate.client');
//        $out_trade_no = $this->attribs['reference_code'];
//        $total_fee = $this->attribs['amount'];
//        $mch_create_ip = getLocalIpAddress();
//        $nonce_str = generateNonceWithTimestamp();
//        $mutable = compact('out_trade_no', 'total_fee', 'mch_create_ip', 'nonce_str');
//        $key = $this->faker->uuid();
//        $attribs = array_merge($config, $mutable, ['key' => $key]);
//        $this->paramsData = ParamsData::from($attribs);
    }

    /** @test */
    public function pay_amount_action_requires_amount_returns_response_data(): void
    {
        Event::fake();
        $action = app(RequestPaymentAction::class);
        $this->assertInstanceOf(ResponseData::class, $action->run($this->attribs));
        Event::assertDispatched(PaymentRequested::class, function ( PaymentRequested $notification ) {
            return $notification->responseData->reference_code = $this->attribs['reference_code'];
        });
    }

    /** @test */
    public function pay_amount_action_has_collect_payment_end_point(): void
    {
        $responseData = new ResponseData(
            reference_code: $this->attribs['reference_code'],
            code_url: $this->faker->url(),
            code_img_url: $this->faker->url(),
            device_info: $this->faker->word(),
            expiration_date: $this->faker->word(),
            invoice_id: $this->faker->uuid(),
            mch_id: $this->faker->uuid(),
            nonce_str: $this->faker->word(),
            result_code: $this->faker->word(),
            sign: $this->faker->word(),
            sign_type: $this->faker->word(),
            status: $this->faker->word(),
            uuid: $this->faker->uuid(),
            version: $this->faker->word()
        ); //TODO: mock this
        $user = User::factory()->create();
        $token = $user->createToken('pipe-dream', ['collect:wallet'])->plainTextToken;
        $response = $this->withHeaders(['Authorization'=>'Bearer '.$token])->postJson(route('collect-payment'), $this->attribs);
        $response->assertStatus(200);
        $response->assertJsonFragment(['reference_code' => $this->attribs['reference_code'], 'sign_type' => 'SHA256', 'status' => '0']);
    }
}

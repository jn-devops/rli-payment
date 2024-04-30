<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Validator;
use Lorisleiva\Actions\ActionRequest;
use App\Interfaces\PaymentGateway;
use App\Events\PaymentRequested;
use Illuminate\Support\Arr;
use App\Data\ResponseData;
use Brick\Money\Money;

class RequestPaymentAction
{
    use AsAction;

    /**
     * @param PaymentGateway $gateway
     */
    public function __construct(public PaymentGateway $gateway){}

    /**
     * @param string $reference_code
     * @param Money $amount
     * @return ResponseData|bool
     */
    protected function collect(string $reference_code, Money $amount): ResponseData|bool
    {
        $responseData = false;
        try {
            $responseData =  $this->gateway
                ->setReferenceCode($reference_code)
                ->setAmount($amount)
                ->send()
            ;
            PaymentRequested::dispatch($responseData);

        } catch (\Exception $exception) {
            //TODO: process error here
        }

        return $responseData;
    }

    /**
     * @param array $attribs
     * @return ResponseData|bool
     * @throws \Brick\Math\Exception\NumberFormatException
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function handle(array $attribs): ResponseData|bool
    {
        $validated = Validator::validate($attribs, $this->rules());
        $reference_code = Arr::get($validated, 'reference_code');
        $amount = Money::of(Arr::get($validated, 'amount'), 'PHP'); //change from ofMinor
        $amount = $amount->multipliedBy('100');//added to include decimal
        return $this->collect($reference_code, $amount);
    }

    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'reference_code' => ['required', 'string', 'min:2'],
            'amount' => ['required', 'integer', 'min:1', 'max:20000'], //change min from 10000 to 50
        ];
    }

    /**
     * @param ActionRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Brick\Math\Exception\MathException
     * @throws \Brick\Math\Exception\NumberFormatException
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     * @throws \DOMException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function asController(ActionRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();
        $responseData = $this->handle($validated);

        return response()->json($responseData ? $responseData->toArray() : $responseData);
    }
}

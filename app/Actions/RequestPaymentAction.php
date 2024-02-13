<?php

namespace App\Actions;

use App\Notifications\SendInvoiceNotification;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Validator;
use Lorisleiva\Actions\ActionRequest;
use Mtownsend\XmlToArray\XmlToArray;
use App\Events\PaymentRequested;
use Illuminate\Support\Arr;
use App\Data\ResponseData;
use App\Data\ParamsData;
use GuzzleHttp\Client;
use Brick\Money\Money;
use NotificationChannels\Webhook\WebhookChannel;
use App\Models\User;

class RequestPaymentAction
{
    use AsAction;

    protected function pay($reference_code, Money $amount): bool
    {
        $config = config('rli-payment.aub_paymate.client');

        $out_trade_no = $reference_code;
        $total_fee = $amount->getAmount()->toInt();
        $mch_create_ip = getLocalIpAddress();//165.22.109.29
        $nonce_str = generateNonceWithTimestamp();

        $mutable = compact('out_trade_no', 'total_fee', 'mch_create_ip', 'nonce_str');
        $attribs = array_merge($config, $mutable);
        ksort($attribs);
        $attribs['key'] = config('rli-payment.aub_paymate.api.key');
        $paramsData = ParamsData::from($attribs);

        $url = config('rli-payment.aub_paymate.server.api_url');
        $xml =  $paramsData->xmlToSend();

        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'text/xml'
            ],
            'body'   => $xml
        ]);
        $array = XmlToArray::convert($response->getBody()->getContents());

        $responseData = ResponseData::from(array_merge(['reference_code' => $reference_code], $array));

        if ($response->getStatusCode() == 200) {
            PaymentRequested::dispatch($responseData);

            return true;
        }
        else {
            return false;
        }
    }

    public function handle(array $attribs): bool
    {
        $validated = Validator::validate($attribs, $this->rules());
        $reference_code = Arr::get($validated, 'reference_code');
        $amount = Money::ofMinor(Arr::get($validated, 'amount'), 'PHP');

        return $this->pay($reference_code, $amount);
    }

    public function rules(): array
    {
        return [
            'reference_code' => ['required', 'string', 'min:2'],
            'amount' => ['required', 'integer', 'min:10000', 'max:20000'],
        ];
    }

    public function asController(ActionRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();
        $this->handle($validated);

        return response()->json(Arr::only($validated, ['reference_code', 'amount']));
    }
}

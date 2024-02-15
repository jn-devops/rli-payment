<?php

namespace App\Classes;

use App\Data\{ParamsData, ResponseData};
use Mtownsend\XmlToArray\XmlToArray;
use App\Interfaces\PaymentGateway;
use Illuminate\Support\Arr;
use Brick\Money\Money;
use GuzzleHttp\Client;
use Carbon\Carbon;

class AUBPaymate implements PaymentGateway
{
    protected string $reference_code;

    protected Money $amount;

    function getClientConfig(): array
    {
        return config('rli-payment.aub_paymate.client');
    }

    function getServerConfig(string $key = null): mixed
    {
        $array = config('rli-payment.aub_paymate.server');

        return $key ? Arr::get($array, $key) : $array;
    }

    public function setReferenceCode(string $reference_code): PaymentGateway
    {
        $this->reference_code = $reference_code;

        return $this;
    }

    public function setAmount(Money $amount): PaymentGateway
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAttribs(): array
    {
        $config = $this->getClientConfig();
        $date = Carbon::now();
        $date->addDays(config('rli-payment.expires_in'));
        $expiration_date = $date->format('YmdHis'); // 2024-03-06 06:32:28 -> 20240306063228
        $out_trade_no = $this->reference_code;
        $total_fee = $this->amount->getAmount()->toInt();
        $mch_create_ip = getLocalIpAddress();//165.22.109.29
        $nonce_str = generateNonceWithTimestamp();

        $mutable = compact('expiration_date','out_trade_no', 'total_fee', 'mch_create_ip', 'nonce_str');
        $attribs = array_merge($config, $mutable);
        ksort($attribs);
        $attribs['key'] = config('rli-payment.aub_paymate.api.key');

        return $attribs;
    }

    function getParamsData(): ParamsData
    {
        return ParamsData::fromAUBPaymate($this);
    }

    function send(): ResponseData
    {
        $client = new Client();
        $xml =  $this->getParamsData()->xmlToSend();
        $url = $this->getServerConfig('api_url');
        $response = $client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'text/xml'
            ],
            'body'   => $xml
        ]);
        $array = XmlToArray::convert($response->getBody()->getContents());

        return ResponseData::from(array_merge(['reference_code' => $this->reference_code], $array));
    }
}

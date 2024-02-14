<?php

namespace App\Classes;

use App\Data\{ParamsData, ResponseData};
use Mtownsend\XmlToArray\XmlToArray;
use App\Interfaces\PaymentGateway;
use Illuminate\Support\Arr;
use Brick\Money\Money;
use GuzzleHttp\Client;

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

        $out_trade_no = $this->reference_code;
        $total_fee = $this->amount->getAmount()->toInt();
        $mch_create_ip = getLocalIpAddress();//165.22.109.29
        $nonce_str = generateNonceWithTimestamp();

        $mutable = compact('out_trade_no', 'total_fee', 'mch_create_ip', 'nonce_str');
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

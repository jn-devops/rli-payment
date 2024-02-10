<?php

namespace App\Data;

use Spatie\ArrayToXml\ArrayToXml;
use Spatie\LaravelData\Data;
use Illuminate\Support\Arr;
use App\Classes\Signature;

class ParamsData extends Data
{
    public function __construct(
        public string $body,
        public string $device_info,
        public string $mch_create_ip,
        public string $mch_id,
        public string $nonce_str,
        public string $notify_url,
        public string $out_trade_no,
        public string $service,
        public string $sign_type,
        public string $total_fee,
        public string $key,
    ) {}

    /**
     * @return string
     * @throws \DOMException
     */
    public function toXML(): string
    {
        $root = [ 'rootElementName' => 'xml' ];
        $data = $this->toArray();
        $key = Arr::pull($data, 'key');
        $data ['sign'] = Signature::create($this)->toString();
        $arrayToXml = new ArrayToXml($data, $root);
        $arrayToXml->setDomProperties(['formatOutput' => true]);

        return $arrayToXml->dropXmlDeclaration()->toXml();
    }
}

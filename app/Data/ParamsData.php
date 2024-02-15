<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\In;
use App\Classes\{AUBPaymate, Signature};
use Spatie\ArrayToXml\ArrayToXml;
use Spatie\LaravelData\Data;

class ParamsData extends Data
{
    public function __construct(
        public string $body,
        public string $device_info,
        public string $expiration_date,
        public string $mch_create_ip,
        public string $mch_id,
        public string $nonce_str,
        public string $notify_url,
        public string $out_trade_no,
        public string $service,
        #[In('SHA256')]
        public string $sign_type,
        public string $total_fee,
        public string $key,
    ) {}

    public static function fromAUBPaymate(AUBPaymate $gateway): self
    {
        return self::from($gateway->getAttribs());
    }

    /**
     * @return string
     * @throws \DOMException
     */
    public function xmlToSend(): string
    {
        $root = [ 'rootElementName' => 'xml' ];

        return with(new ArrayToXml($this->withoutKeyButWithUppercaseSignatureArray(), $root, true, 'UTF-8'), function ($arrayToXml) {
            return $arrayToXml
//                ->setDomProperties(['formatOutput' => true])
                ->dropXmlDeclaration()
                ->toXml()
            ;
        });
    }

    public function withoutKeyButWithUppercaseSignatureArray(): array
    {
        return tap($this->toArray(), function (&$data) {
            unset($data['key']);
            $data += Signature::create($this)->toArray('sign');
        });
    }

    public function toQuery(): string
    {
        return urldecode(http_build_query($this->toArray()));
    }
}

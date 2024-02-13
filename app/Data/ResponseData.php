<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ResponseData extends Data
{
    public function __construct(
        public string $reference_code,
        public string $code_url,
        public string $code_img_url,
        public string $device_info,
        public string $expiration_date,
        public string $invoice_id,
        public string $mch_id,
        public string $nonce_str,
        public string $result_code,
        public string $sign,
        public string $sign_type,
        public string $status,
        public string $uuid,
        public string $version,
    ) {}
}

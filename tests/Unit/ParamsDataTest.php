<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use App\Classes\Signature;
use App\Data\ParamsData;
use Tests\TestCase;

class ParamsDataTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function params_data_accepts_an_array(): void
    {
        $body = $this->faker->sentence();
        $device_info = $this->faker->word();
        $mch_create_ip = $this->faker->localIpv4();
        $mch_id = $this->faker->word();
        $nonce_str = generateNonceWithTimestamp();
        $notify_url = $this->faker->url();
        $out_trade_no = $this->faker->numberBetween(1000000,9000000);
        $service = $this->faker->word();
        $sign_type = $this->faker->word();
        $total_fee = $this->faker->numberBetween(1000,10000);
        $key = $this->faker->uuid();

        $params = compact('body', 'device_info', 'mch_create_ip', 'mch_id', 'nonce_str', 'notify_url', 'out_trade_no', 'service', 'sign_type', 'total_fee', 'key');
        $params_data = ParamsData::from($params);

        $xmlDataTemplate =
            <<<'EOD'
<xml>
  <body>:body</body>
  <device_info>:device_info</device_info>
  <mch_create_ip>:mch_create_ip</mch_create_ip>
  <mch_id>:mch_id</mch_id>
  <nonce_str>:nonce_str</nonce_str>
  <notify_url>:notify_url</notify_url>
  <out_trade_no>:out_trade_no</out_trade_no>
  <service>:service</service>
  <sign_type>:sign_type</sign_type>
  <total_fee>:total_fee</total_fee>
  <sign>:signature</sign>
</xml>
EOD;

        $data = array_merge($params_data->toArray(), Signature::create($params_data)->toArray());
        $xmlData = trans($xmlDataTemplate, $data);
        $this->assertEquals($params_data->toXML(), $xmlData);
    }
}

<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Arr;
use App\Classes\Signature;
use App\Data\ParamsData;
use Tests\TestCase;

class ParamsDataTest extends TestCase
{
    use WithFaker;

    /** @test
     * @throws \DOMException
     */
    public function params_data_accepts_an_array(): void
    {
        $body = $this->faker->sentence();
        $device_info = $this->faker->word();
        $expiration_date = '20240306063228'; // 2024-03-06 06:32:28 -> 20240306063228
        $mch_create_ip = $this->faker->localIpv4();
        $mch_id = $this->faker->word();
        $nonce_str = generateNonceWithTimestamp();
        $notify_url = $this->faker->url();
        $out_trade_no = $this->faker->uuid();
        $service = $this->faker->word();
        $sign_type = $this->faker->word();
        $total_fee = $this->faker->numberBetween(1000,10000);
        $key = $this->faker->uuid();

        $params = compact('body', 'device_info', 'expiration_date', 'mch_create_ip', 'mch_id', 'nonce_str', 'notify_url', 'out_trade_no', 'service', 'sign_type', 'total_fee', 'key');
        $paramsData = ParamsData::from($params);

        $xmlDataTemplate = <<<XML
<xml><body>:body</body><device_info>:device_info</device_info><expiration_date>20240306063228</expiration_date><mch_create_ip>:mch_create_ip</mch_create_ip><mch_id>:mch_id</mch_id><nonce_str>:nonce_str</nonce_str><notify_url>:notify_url</notify_url><out_trade_no>:out_trade_no</out_trade_no><service>:service</service><sign_type>:sign_type</sign_type><total_fee>:total_fee</total_fee><sign>:signature</sign></xml>
XML;
        $signature = with(urldecode(http_build_query($params)), function ($sortedParams) {
            return tap(hash('sha256', $sortedParams), function (&$hash) {
                $hash = strtoupper($hash);
            });
        });
        $signatureObject = Signature::create($paramsData);
        $this->assertEquals($signature, $signatureObject->toString());

        Arr::pull($params,  'key');
        $paramsWithoutKeyButWithSignature = array_merge($params, [ 'signature' => $signature ]);
        $this->assertEmpty(array_diff($paramsWithoutKeyButWithSignature, $paramsData->withoutKeyButWithUppercaseSignatureArray()));

        $xmlData = with(trans($xmlDataTemplate, $paramsWithoutKeyButWithSignature), function ($str) {
            return iconv('ASCII', 'UTF-8', $str);
        });
        $this->assertEquals( 'UTF-8', mb_detect_encoding($xmlData, ['UTF-8', 'ASCII']));
        $this->assertEquals($xmlData, $paramsData->xmlToSend());
    }

    /** @test */
    public function params_data_has_default_attribs_and_mutable_attribs(): void
    {
        $config = config('rli-payment.aub_paymate.client');
        $expiration_date = '20240306063228'; // 2024-03-06 06:32:28 -> 20240306063228
        $out_trade_no = $this->faker->uuid();
        $total_fee = $this->faker->numberBetween(1000,10000);
        $mch_create_ip = getLocalIpAddress();
        $nonce_str = generateNonceWithTimestamp();
        $mutable = compact('expiration_date', 'out_trade_no', 'total_fee', 'mch_create_ip', 'nonce_str');
        $key = $this->faker->uuid();
        $attribs = array_merge($config, $mutable, ['key' => $key]);
        $params_data = ParamsData::from($attribs);
        $this->assertEmpty(array_diff($params_data->toArray(), $attribs));
    }

    /** @test */
    public function params_data_has_computed_properties(): void
    {
        $config = config('rli-payment.aub_paymate.client');
        $expiration_date = '20240306063228'; // 2024-03-06 06:32:28 -> 20240306063228
        $out_trade_no = 'JN-537537';
        $total_fee = 100;
        $mch_create_ip = '127.0.0.1';
        $nonce_str = '1707715341';
        $mutable = compact('expiration_date', 'out_trade_no', 'total_fee', 'mch_create_ip', 'nonce_str');
        $attribs = array_merge($config, $mutable);
        ksort($attribs);
        $attribs['key'] = config('rli-payment.aub_paymate.api.key');

        $query_string = urldecode(http_build_query($attribs));
        $sign = strtoupper(hash('sha256', $query_string, false));
//        $this->assertEquals( '46E98B010FD04D32DFA05FA997A1642E753C2112DF66B3ED5BED9BC4CBCC710A', $sign);
        $this->assertEquals( '591D33CD1777CAF66CDFDD0E0E1F5588E5D8C07F36F1B09695034C3416003B0D', $sign);
        $signed_attribs = $attribs;
        $root = [ 'rootElementName' => 'xml' ];
        unset($signed_attribs['key']);
        $signed_attribs['sign'] = $sign;

        $xml = with(new ArrayToXml($signed_attribs, $root, true, 'UTF-8'), function ($arrayToXml) {
            $xml = $arrayToXml
//                ->setDomProperties(['formatOutput' => true])
                ->dropXmlDeclaration()
                ->toXml()
            ;

            return htmlspecialchars_decode($xml);
        });

        $params_data = ParamsData::from($attribs);
        $this->assertEquals($params_data->toQuery(), urldecode(http_build_query($attribs)));
        $this->assertEquals($sign, Signature::create($params_data)->toString());
        $this->assertEquals($xml, $params_data->xmlToSend());
    }
}

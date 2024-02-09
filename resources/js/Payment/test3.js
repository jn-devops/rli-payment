const { parseString } = require("xml2js");

const body = "RaemulanLandsInc";
const device_info = "100";
const mch_id = "102530003108";
const notify_url = "https://eo6kgt9fgbo4q9.m.pipedream.net";
const service = "pay.instapay.native.v2";
const sign_type = "SHA256";
const key = "a0b1b6529b9a90efb5a80eba6ba0a7c6";
const mch_create_ip = getLocalIpAddress();
const nonce_str = generateNonceWithTimestamp();
const total_fee = 100;
const voucher_number = "1234567891";

const getLocalIpAddress = () => {
    const interfaces = networkInterfaces();
    let ipAddress;

    Object.keys(interfaces).forEach((key) => {
        interfaces[key].forEach((iface) => {
            if (!iface.internal && iface.family === 'IPv4') {
                ipAddress = iface.address;
            }
        });
    });

    return ipAddress;
};

const generateNonceWithTimestamp = () => {
    const timestamp = Date.now().toString();
    return timestamp;
};

const params = {
    body: body,
    device_info: device_info,
    mch_create_ip: mch_create_ip,
    mch_id: mch_id,
    nonce_str: nonce_str,
    notify_url: notify_url,
    out_trade_no: voucher_number, // Use the voucher_number from the request
    service: service,
    sign_type: sign_type,
    total_fee: total_fee,
    key: key
};

const sortedParams = Object.keys(params).map(key => `${key}=${params[key]}`).join('&').trim();
const signature = createHash('sha256').update(sortedParams).digest('hex').toUpperCase();

const xmlData = `<xml>
    <body>${body}</body>
    <device_info>${device_info}</device_info>
    <mch_create_ip>${mch_create_ip}</mch_create_ip>
    <mch_id>${mch_id}</mch_id>
    <nonce_str>${nonce_str}</nonce_str>
    <notify_url>${notify_url}</notify_url>
    <out_trade_no>${voucher_number}</out_trade_no>
    <service>${service}</service>
    <sign_type>${sign_type}</sign_type>
    <total_fee>${total_fee}</total_fee>
    <sign>${signature}</sign>
</xml>`;

// Parse the XML data
parseString(xmlData, { explicitArray: false }, (err, result) => {
  if (err) {
    console.error("Error parsing XML:", err);
  } else {
    // Access values from the parsed result
    const parsedBody = result.xml.body;
    const parsedDeviceInfo = result.xml.device_info;
    // Add more variables as needed

    // Log or use the parsed values
    console.log("Parsed Body:", parsedBody);
    console.log("Parsed Device Info:", parsedDeviceInfo);
    // Log or use other parsed values accordingly
  }
});

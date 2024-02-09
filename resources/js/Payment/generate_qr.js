//Generate payment QR

const { createHash } = require('crypto');
const axios = require('axios');
const xml2js = require('xml2js');
const iconv = require('iconv-lite');
const { networkInterfaces } = require('os');

var body = 'RaemulanLandsInc'
var device_info = '100'
var mch_id = '102530003108'
var notify_url = 'https://eo6kgt9fgbo4q9.m.pipedream.net'
var out_trade_no = '102530003129'
var service = 'pay.instapay.native.v2'
var sign_type = 'SHA256'
var total_fee = '100'
var key = 'a0b1b6529b9a90efb5a80eba6ba0a7c6'

// Get local IP address
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

const mch_create_ip = getLocalIpAddress();
// console.log(mch_create_ip);

const generateNonceWithTimestamp = () => {
    const timestamp = Date.now().toString();
    return timestamp;
};

const nonce_str = generateNonceWithTimestamp();
// console.log(nonce_str);

const params = {
    body: body,
    device_info: device_info,
    mch_create_ip: mch_create_ip,
    mch_id: mch_id,
    nonce_str: nonce_str,
    notify_url: notify_url,
    out_trade_no: out_trade_no,
    service: service,
    sign_type: sign_type,
    total_fee: total_fee,
    key: key
};

// Sort the parameters by key
const sortedParams = Object.keys(params).map(key => `${key}=${params[key]}`).join('&').trim();
const signature = createHash('sha256').update(sortedParams).digest('hex').toUpperCase();

const xmlData = `<xml>
  <body>${body}</body>
  <device_info>${device_info}</device_info>
  <mch_create_ip>${mch_create_ip}</mch_create_ip>
  <mch_id>${mch_id}</mch_id>
  <nonce_str>${nonce_str}</nonce_str>
  <notify_url>${notify_url}</notify_url>
  <out_trade_no>${out_trade_no}</out_trade_no>
  <service>${service}</service>
  <sign_type>${sign_type}</sign_type>
  <total_fee>${total_fee}</total_fee>
  <sign>${signature}</sign>
</xml>`;

// Convert XML to JSON for easier handling with explicit UTF-8 encoding
xml2js.parseString(iconv.encode(xmlData, 'utf-8'), { explicitArray: false }, (err, json) => {
    if (err) {
      console.error('Error parsing XML:', err);
      return;
    }
  
    const builder = new xml2js.Builder();
    const xmlToSend = builder.buildObject(json);
  
    const apiUrl = 'https://gateway.wepayez.com/pay/gateway';
  
    axios.post(apiUrl, xmlToSend, {
      headers: {
        'Content-Type': 'application/xml',
      },
    })
    .then((response) => {
      // Parse XML response to extract code_img_url
      xml2js.parseString(response.data, { explicitArray: false }, (err, result) => {
        if (err) {
          console.error('Error parsing API response:', err);
          return;
        }
  
        const codeImgUrl = result.xml.code_img_url;
        console.log(codeImgUrl);
      });
    })
    .catch((error) => {
      console.error('Error making API request:', error.message);
    });
  });
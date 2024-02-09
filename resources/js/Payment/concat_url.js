// const crypto = require('crypto');

// const sha256 = require('sha256');
const { createHash } = require('crypto');
const axios = require('axios');
const xml2js = require('xml2js');
const iconv = require('iconv-lite');

var body = 'RaemulanLandsInc'
var device_info = '100'
var mch_create_ip = '127.0.0.1'
var mch_id = '102530003108'
var nonce_str = '2209193392'
var notify_url = 'https://eo6kgt9fgbo4q9.m.pipedream.net'
var out_trade_no = '102530003127'
var service = 'pay.instapay.native.v2'
var sign_type = 'SHA256'
var total_fee = '100'
var key = 'a0b1b6529b9a90efb5a80eba6ba0a7c6'


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
// console.log(sortedParams);

// const sha256Hash = crypto.createHmac('sha256', params.key).update(sortedParams).digest('hex');
// console.log(sha256Hash);

// sha256(sortedParams);
const signature = createHash('sha256').update(sortedParams).digest('hex').toUpperCase();
// console.log(signature);

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
// xml2js.parseString(iconv.encode(xmlData, 'utf-8'), { explicitArray: false }, (err, json) => {
//   if (err) {
//     console.error('Error parsing XML:', err);
//     return;
//   }

//   const builder = new xml2js.Builder();
//   const xmlToSend = builder.buildObject(json);

//   const apiUrl = 'https://gateway.wepayez.com/pay/gateway';

//   axios.post(apiUrl, xmlToSend, {
//     headers: {
//       'Content-Type': 'application/xml',
//     },
//   })
//   .then((response) => {
//     console.log('API response:', response.data);
//   })
//   .catch((error) => {
//     console.error('Error making API request:', error.message);
//   });
// });


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
        // console.log(response.data);
  
        const codeImgUrl = result.xml.code_img_url;
        // console.log('Code Image URL:', codeImgUrl);
        // console.log(codeImgUrl);
        console.log(codeImgUrl);
      });
    })
    .catch((error) => {
      console.error('Error making API request:', error.message);
    });
  });
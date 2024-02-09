const { createHash } = require('crypto');
const express = require('express');
const bodyParser = require('body-parser');
const axios = require('axios');
const xml2js = require('xml2js');
const iconv = require('iconv-lite');
const { networkInterfaces } = require('os');

const app = express();
const port = 3000; // Choose a suitable port

let globalCodeImgUrl; // Define a variable to store the result globally

// Create a function that returns a Promise with codeImgUrl
const getCodeImgUrl = () => {
  return new Promise((resolve, reject) => {
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
    const voucher_number = "123452";

    const params = {
      body: body,
      device_info: device_info,
      mch_create_ip: mch_create_ip,
      mch_id: mch_id,
      nonce_str: nonce_str,
      notify_url: notify_url,
      out_trade_no: voucher_number,
      service: service,
      sign_type: sign_type,
      total_fee: total_fee,
      key: key,
    };

    const sortedParams = Object.keys(params)
      .map((key) => `${key}=${params[key]}`)
      .join("&")
      .trim();
    const signature = createHash("sha256")
      .update(sortedParams)
      .digest("hex")
      .toUpperCase();

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

    axios
      .post("https://gateway.wepayez.com/pay/gateway", xmlData, {
        headers: {
          "Content-Type": "application/xml",
        },
      })
      .then((response) => {
        xml2js.parseString(
          response.data,
          { explicitArray: false },
          (err, result) => {
            if (err) {
              console.error("Error parsing API response:", err);
              reject(err);
              return;
            }

            const codeImgUrl = result.xml.code_img_url;
            console.log(codeImgUrl);
            // globalCodeImgUrl = codeImgUrl; // Store the result in the global variable
            // resolve(codeImgUrl);
          }
        );
      })
      .catch((error) => {
        console.error("Error making API request:", error.message);
        reject(error);
      });
  });
};

const getLocalIpAddress = () => {
  const interfaces = networkInterfaces();
  let ipAddress;

  Object.keys(interfaces).forEach((key) => {
    interfaces[key].forEach((iface) => {
      if (!iface.internal && iface.family === "IPv4") {
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

// console.log(globalCodeImgUrl);

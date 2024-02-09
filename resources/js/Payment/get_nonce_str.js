// Generate a random nonce string
// const generateNonce = () => {
//     return Math.random().toString(36).substring(2);
// };

// const nonce = generateNonce();
// console.log('Nonce:', nonce);


// Generate a nonce string with timestamp
const generateNonceWithTimestamp = () => {
    const timestamp = Date.now().toString();
    const randomString = Math.random().toString(36).substring(2);
    return timestamp + randomString;
};

const nonceWithTimestamp = generateNonceWithTimestamp();
// console.log('Nonce with Timestamp:', nonceWithTimestamp);
console.log(nonceWithTimestamp);


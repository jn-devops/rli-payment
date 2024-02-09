const { networkInterfaces } = require('os');

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

// console.log('Local IP Address:', getLocalIpAddress());
console.log(getLocalIpAddress());

const axios = require('axios');

// Gửi yêu cầu đến API IP Geolocation
axios.get('https://ipinfo.io/json')
    .then(response => {
        const data = response.data;
        const location = data;
        console.log(location); // In vị trí ra console
    })
    .catch(error => {
        console.error('Error fetching location:', error);
        process.exit(1); // Kết thúc tiến trình với mã lỗi
    });

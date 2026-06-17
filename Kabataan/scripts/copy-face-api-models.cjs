const fs = require('fs');
const path = require('path');

const src = path.join(__dirname, '../node_modules/@vladmandic/face-api/model');
const dest = path.join(__dirname, '../public/vendor/face-api');

if (!fs.existsSync(src)) {
    console.warn('face-api models not found — CDN fallback will be used at runtime.');
    process.exit(0);
}

fs.mkdirSync(dest, { recursive: true });
fs.cpSync(src, dest, { recursive: true });
console.log('Copied face-api models to public/vendor/face-api');

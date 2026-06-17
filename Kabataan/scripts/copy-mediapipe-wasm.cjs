const fs = require('fs');
const path = require('path');

const src = path.join(__dirname, '../node_modules/@mediapipe/tasks-vision/wasm');
const dest = path.join(__dirname, '../public/vendor/mediapipe/wasm');

if (!fs.existsSync(src)) {
    console.error('MediaPipe WASM source not found. Run npm install first.');
    process.exit(1);
}

fs.mkdirSync(path.dirname(dest), { recursive: true });
fs.cpSync(src, dest, { recursive: true });
console.log('Copied MediaPipe WASM to public/vendor/mediapipe/wasm');

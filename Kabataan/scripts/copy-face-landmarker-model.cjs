const fs = require('fs');
const path = require('path');
const https = require('https');

const MODEL_URL =
    'https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/1/face_landmarker.task';
const dest = path.join(__dirname, '../public/vendor/mediapipe/face_landmarker.task');
const MIN_BYTES = 1_000_000;

function download(url, destPath) {
    return new Promise((resolve, reject) => {
        https.get(url, (response) => {
            if ([301, 302, 307, 308].includes(response.statusCode)) {
                const next = response.headers.location;
                if (!next) {
                    reject(new Error('Redirect without location header'));
                    return;
                }
                download(next, destPath).then(resolve).catch(reject);
                return;
            }

            if (response.statusCode !== 200) {
                reject(new Error(`HTTP ${response.statusCode} downloading face landmarker model`));
                return;
            }

            const file = fs.createWriteStream(destPath);
            response.pipe(file);
            file.on('finish', () => {
                file.close();
                resolve();
            });
            file.on('error', (error) => {
                fs.unlink(destPath, () => reject(error));
            });
        }).on('error', reject);
    });
}

async function main() {
    fs.mkdirSync(path.dirname(dest), { recursive: true });

    if (fs.existsSync(dest) && fs.statSync(dest).size >= MIN_BYTES) {
        console.log('Face landmarker model already present at public/vendor/mediapipe/face_landmarker.task');
        return;
    }

    console.log('Downloading MediaPipe face landmarker model…');
    await download(MODEL_URL, dest);
    console.log('Saved face landmarker model to public/vendor/mediapipe/face_landmarker.task');
}

main().catch((error) => {
    console.error('Face landmarker model copy failed:', error.message);
    process.exit(1);
});

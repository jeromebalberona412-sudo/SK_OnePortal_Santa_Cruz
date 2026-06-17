/**
 * Layer 2 — FaceNet identity embeddings (@vladmandic/face-api).
 * MediaPipe = detection/liveness only; this module = real face identity lock.
 * face-api is loaded on demand so the main bundle stays small.
 */

export const NEURAL_EMBED_CONFIG = {
    /** Euclidean distance — lower = same person (FaceNet standard ~0.6). */
    distanceThreshold: 0.55,
    distanceThresholdFinal: 0.50,
    minConfidence: 0.45,
    extractIntervalMs: 90,
};

const MODEL_PATHS = [
    () => `${window.location.origin}/vendor/face-api`,
    () => 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.15/model',
];

/** @type {typeof import('@vladmandic/face-api') | null} */
let faceapiModule = null;
/** @type {Promise<typeof import('@vladmandic/face-api')> | null} */
let faceapiLoadPromise = null;

async function getFaceApi() {
    if (faceapiModule) {
        return faceapiModule;
    }

    if (!faceapiLoadPromise) {
        faceapiLoadPromise = import('@vladmandic/face-api').then((module) => {
            faceapiModule = module;
            return module;
        });
    }

    return faceapiLoadPromise;
}

function euclideanDistance(a, b) {
    let sum = 0;
    for (let i = 0; i < a.length; i += 1) {
        const delta = a[i] - b[i];
        sum += delta * delta;
    }
    return Math.sqrt(sum);
}

export class FaceEmbeddingEngine {
    constructor() {
        this.ready = false;
        this.loading = false;
        /** @type {HTMLCanvasElement|null} */
        this.canvas = null;
        /** @type {number[]|null} */
        this.lastDescriptor = null;
        this.lastExtractAt = 0;
        this.extracting = false;
    }

    /**
     * @param {(msg: string) => void} [onStatus]
     */
    async init(onStatus) {
        if (this.ready || this.loading) {
            return this.ready;
        }

        this.loading = true;
        onStatus?.('Loading FaceNet identity model (background)…');

        let lastError = null;
        const loadTimeoutMs = 45000;

        try {
            const faceapi = await getFaceApi();

            for (const resolvePath of MODEL_PATHS) {
                const base = resolvePath();
                try {
                    await Promise.race([
                        Promise.all([
                            faceapi.nets.ssdMobilenetv1.loadFromUri(base),
                            faceapi.nets.faceLandmark68Net.loadFromUri(base),
                            faceapi.nets.faceRecognitionNet.loadFromUri(base),
                        ]),
                        new Promise((_, reject) => {
                            setTimeout(() => reject(new Error('FaceNet model load timed out')), loadTimeoutMs);
                        }),
                    ]);
                    this.ready = true;
                    break;
                } catch (error) {
                    lastError = error;
                }
            }
        } catch (error) {
            lastError = error;
        }

        this.loading = false;

        if (!this.ready) {
            console.warn('FaceNet model load failed:', lastError);
        }

        return this.ready;
    }

    /**
     * Extract 128-d FaceNet descriptor from video frame (throttled).
     * @param {HTMLVideoElement} video
     * @param {number} [now]
     */
    async extractFromVideo(video, now = performance.now()) {
        if (!this.ready || !video?.videoWidth || this.extracting) {
            return this.lastDescriptor;
        }

        if (now - this.lastExtractAt < NEURAL_EMBED_CONFIG.extractIntervalMs) {
            return this.lastDescriptor;
        }

        this.extracting = true;

        try {
            const faceapi = await getFaceApi();

            if (!this.canvas) {
                this.canvas = document.createElement('canvas');
            }

            const width = video.videoWidth;
            const height = video.videoHeight;
            this.canvas.width = width;
            this.canvas.height = height;

            const ctx = this.canvas.getContext('2d');
            ctx.save();
            ctx.translate(width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, width, height);
            ctx.restore();

            const detection = await faceapi
                .detectSingleFace(
                    this.canvas,
                    new faceapi.SsdMobilenetv1Options({
                        minConfidence: NEURAL_EMBED_CONFIG.minConfidence,
                    })
                )
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (detection?.descriptor) {
                this.lastDescriptor = Array.from(detection.descriptor);
                this.lastExtractAt = now;
            }
        } catch (error) {
            console.warn('Neural embedding extraction failed:', error);
        } finally {
            this.extracting = false;
        }

        return this.lastDescriptor;
    }

    getLastDescriptor() {
        return this.lastDescriptor;
    }

    reset() {
        this.lastDescriptor = null;
        this.lastExtractAt = 0;
    }
}

/**
 * Compare two FaceNet descriptors using Euclidean distance.
 * @param {number[]|Float32Array|null} a
 * @param {number[]|Float32Array|null} b
 * @param {number} [threshold]
 */
export function compareNeuralEmbeddings(a, b, threshold = NEURAL_EMBED_CONFIG.distanceThreshold) {
    if (!a?.length || !b?.length || a.length !== b.length) {
        return { matched: false, distance: 1, similarity: 0 };
    }

    const vecA = a instanceof Float32Array ? a : new Float32Array(a);
    const vecB = b instanceof Float32Array ? b : new Float32Array(b);
    const distance = euclideanDistance(vecA, vecB);
    const similarity = Math.max(0, 1 - distance / 1.2);

    return {
        matched: distance < threshold,
        distance,
        similarity,
    };
}

/** Pick best available neural descriptor from frame batch for step signature. */
export function pickStepNeuralEmbedding(frames) {
    if (!frames?.length) {
        return null;
    }

    for (let i = frames.length - 1; i >= 0; i -= 1) {
        if (frames[i].neuralEmbedding?.length) {
            return frames[i].neuralEmbedding;
        }
    }

    return null;
}

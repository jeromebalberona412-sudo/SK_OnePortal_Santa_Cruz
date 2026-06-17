/**
 * Kabataan Face Liveness Verification — MediaPipe + multi-frame analysis
 */

import { FaceLandmarker, FilesetResolver } from '@mediapipe/tasks-vision';
import {
    LivenessSession,
    LIVENESS_CONFIG,
    CHALLENGE_CATALOG,
    getBlendshapeMap,
    checkFrameIdentity,
    SECURITY_CODES,
} from './liveness/liveness-engine.js';
import { validateFrameSecurity, validateBasicFaceDetection, validateStep1HumanFrame, validateChainedIdentity, validateSmileHoldFrame, Step1InitTracker, SECURITY_CODES as FACE_SEC_CODES } from './liveness/face-security.js';
import { FaceEmbeddingEngine, NEURAL_EMBED_CONFIG } from './liveness/face-embedding-engine.js';

const FATAL_SECURITY_CODES = new Set([
    FACE_SEC_CODES.SPOOF_STATIC,
    FACE_SEC_CODES.SPOOF_FLAT_DEPTH,
]);


const SMILE_HOLD_MAX_MISSES = 10;
const CHAIN_IDENTITY_OPTS = {
    embeddingThreshold: LIVENESS_CONFIG.chainIdentityThreshold,
    geometryThreshold: LIVENESS_CONFIG.geometryIdentityThreshold,
    structuralThreshold: LIVENESS_CONFIG.structuralIdentityThreshold,
    neuralDistanceThreshold: NEURAL_EMBED_CONFIG.distanceThreshold,
    requireGeometry: false,
    minPassRatio: 0.68,
};
const FINAL_IDENTITY_OPTS = {
    embeddingThreshold: LIVENESS_CONFIG.smileIdentityThreshold,
    geometryThreshold: LIVENESS_CONFIG.geometryIdentityThreshold,
    structuralThreshold: LIVENESS_CONFIG.structuralIdentityThreshold,
    neuralDistanceThreshold: NEURAL_EMBED_CONFIG.distanceThresholdFinal,
    requireGeometry: true,
    minPassRatio: 0.68,
};

const WASM_PATHS = [
    () => `${window.location.origin}/vendor/mediapipe/wasm`,
    () => 'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.35/wasm',
];

const MODEL_URLS = [
    () => `${window.location.origin}/vendor/mediapipe/face_landmarker.task`,
    'https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/1/face_landmarker.task',
];

const LOCAL_HOSTS = ['localhost', '127.0.0.1', '[::1]'];
const CAMERA_RETRY_ATTEMPTS = 3;
const CAMERA_PERMISSION_TIMEOUT_MS = 25000;
const MEDIAPIPE_INIT_TIMEOUT_MS = 45000;
const MEDIAPIPE_GPU_TIMEOUT_MS = 12000;

/** SVG icons for step cues and badges — no emojis. */
const FV_ICONS = {
    human: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c2-4 5-6 8-6s6 2 8 6" stroke-linecap="round"/></svg>',
    straight: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c2-4 5-6 8-6s6 2 8 6" stroke-linecap="round"/></svg>',
    'arrow-right': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'arrow-left': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    blink: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke-linecap="round"/><circle cx="12" cy="12" r="2" fill="currentColor" stroke="none"/></svg>',
    smile: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="9" cy="10" r="1" fill="currentColor" stroke="none"/><circle cx="15" cy="10" r="1" fill="currentColor" stroke="none"/><path d="M8 15c1.5 2 6.5 2 8 0" stroke-linecap="round"/></svg>',
    camera: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 8h4l2-2h4l2 2h4v10H4V8z"/><circle cx="12" cy="13" r="3"/></svg>',
    pending: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>',
    success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M7 7l10 10M17 7L7 17" stroke-linecap="round"/></svg>',
    progress: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    analyzing: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke-dasharray="14 40"/></svg>',
};

function fvIcon(name) {
    return FV_ICONS[name] || FV_ICONS.human;
}

function resolveStepIconKey(challenge, override) {
    if (override) {
        return override;
    }
    if (!challenge) {
        return 'human';
    }
    return challenge.stepIcon || challenge.videoCue || challenge.guideIcon || 'human';
}

function withTimeout(promise, ms, label) {
    return Promise.race([
        promise,
        new Promise((_, reject) => {
            setTimeout(() => reject(new Error(`${label} timed out (${Math.round(ms / 1000)}s)`)), ms);
        }),
    ]);
}

function resolveModelUrl(entry) {
    return typeof entry === 'function' ? entry() : entry;
}
const CAMERA_RETRY_DELAY_MS = 900;
const WARMUP_FRAMES = 3;
const PROCESS_INTERVAL_MS = LIVENESS_CONFIG.processIntervalMs ?? 70;

const ERROR = {
    PERMISSION: 'permission',
    NOT_FOUND: 'not_found',
    IN_USE: 'in_use',
    INSECURE: 'insecure',
    MODEL: 'model',
    UNSUPPORTED: 'unsupported',
    GENERIC: 'generic',
};

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

function getFvConfig() {
    const raw = document.querySelector('meta[name="kk-fv-config"]')?.content;
    if (!raw) {
        return { devHttpsPort: 8443, devHttpsEnabled: false, isSecureRequest: window.isSecureContext };
    }

    try {
        return JSON.parse(raw);
    } catch {
        return { devHttpsPort: 8443, devHttpsEnabled: false, isSecureRequest: window.isSecureContext };
    }
}

function isPrivateLanHost(host) {
    return /^(192\.168\.|10\.|172\.(1[6-9]|2\d|3[01])\.)/.test(host);
}

function getCameraAccessInfo() {
    const { hostname, port, pathname, protocol } = window.location;
    const fvConfig = getFvConfig();
    const isLocalhost = LOCAL_HOSTS.includes(hostname);
    const isLanIp = isPrivateLanHost(hostname);
    const isHttps = protocol === 'https:' || window.isSecureContext;
    const devHttpsPort = fvConfig.devHttpsPort || 8443;
    const httpsLanUrl = `https://${hostname}:${devHttpsPort}${pathname}`;
    const localhostUrl = `http://localhost:${port || '8002'}${pathname}`;

    let accessLevel = 'allowed';

    if (!navigator.mediaDevices?.getUserMedia) {
        accessLevel = 'unsupported';
    } else if (!isHttps && !isLocalhost) {
        accessLevel = 'needs_https';
    }

    return {
        hostname,
        port,
        pathname,
        isLocalhost,
        isLanIp,
        isHttps,
        devHttpsPort,
        httpsLanUrl,
        localhostUrl,
        accessLevel,
        devHttpsEnabled: Boolean(fvConfig.devHttpsEnabled),
    };
}

function buildInsecureHelp(info) {
    const steps = [];

    if (info.devHttpsEnabled) {
        steps.push(
            `Run <code>npm run serve:lan</code> in the Kabataan folder (starts Laravel + HTTPS proxy).`,
            `Open <a href="${info.httpsLanUrl}">${info.httpsLanUrl}</a> and accept the certificate warning once.`,
            'Click Verify Identity again and allow camera permission.'
        );
    } else {
        steps.push('Use HTTPS for face verification in this environment.');
    }

    steps.push(
        `On this PC only, you can use <a href="${info.localhostUrl}">${info.localhostUrl}</a> (HTTP localhost is allowed).`,
        'Chrome dev workaround: open <code>chrome://flags/#unsafely-treat-insecure-origin-as-secure</code>, add your HTTP URL, restart Chrome.'
    );

    return `
        <strong>Use HTTPS for production face verification</strong>
        <p>Camera access is blocked on <code>http://${info.hostname}:${info.port || '80'}</code> because browsers require HTTPS on network IP addresses.</p>
        <ol>${steps.map((s) => `<li>${s}</li>`).join('')}</ol>
    `;
}

function mapCameraError(err, info) {
    const name = err?.name || '';
    const code = err?.code || '';

    if (name === 'NotAllowedError' || name === 'PermissionDeniedError' || code === 'permission_denied') {
        return {
            type: ERROR.PERMISSION,
            message: 'Camera permission required. Click the camera/lock icon in your browser address bar, allow access, then click Retake Verification.',
            help: '<strong>How to allow camera</strong><ol><li>Click the camera or site settings icon in the address bar.</li><li>Set Camera to Allow.</li><li>Refresh the page and try again.</li></ol>',
        };
    }

    if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
        return {
            type: ERROR.NOT_FOUND,
            message: 'No camera detected. Connect a webcam or use a device with a front camera.',
            help: null,
        };
    }

    if (name === 'NotReadableError' || name === 'TrackStartError') {
        return {
            type: ERROR.IN_USE,
            message: 'Camera is in use by another application. Close other apps using the camera and try again.',
            help: null,
        };
    }

    if (name === 'SecurityError' || name === 'NotSupportedError') {
        return {
            type: ERROR.INSECURE,
            message: 'Camera access is blocked on this connection. Use HTTPS for production face verification.',
            help: buildInsecureHelp(info),
        };
    }

    if (name === 'OverconstrainedError' || name === 'AbortError') {
        return {
            type: ERROR.GENERIC,
            message: 'Could not start the camera with the current settings. Retrying may help.',
            help: null,
        };
    }

    if (info.accessLevel === 'needs_https') {
        return {
            type: ERROR.INSECURE,
            message: 'Camera access is blocked on HTTP for network IP addresses. Use HTTPS for production face verification.',
            help: buildInsecureHelp(info),
        };
    }

    return {
        type: ERROR.GENERIC,
        message: err?.message || 'Unable to access the camera. Please check your device settings and try again.',
        help: null,
    };
}

function isRetryableCameraError(err) {
    const name = err?.name || '';
    return ['NotReadableError', 'TrackStartError', 'OverconstrainedError', 'AbortError'].includes(name);
}

class LivenessVerification {
    constructor(options = {}) {
        this.onProgress = options.onProgress || (() => {});
        this.onComplete = options.onComplete || (() => {});
        this.onError = options.onError || (() => {});
        this.onStatus = options.onStatus || (() => {});
        this.onUiUpdate = options.onUiUpdate || (() => {});

        this.video = null;
        this.stream = null;
        this.landmarker = null;
        this.modelReady = false;
        this.animationId = null;
        this.isRunning = false;
        this.capturedDataUrl = null;
        this.warmupFrames = 0;
        this.analyzing = false;
        this.blinkCompleting = false;
        this.smileHoldPhase = false;
        this.smileHoldSecondsLeft = 0;
        this.smileHoldLastTick = 0;
        this.lastProcessAt = 0;
        this.mediaPipeTimestampMs = 0;
        this.detectFailCount = 0;
        this.step1Init = new Step1InitTracker({
            windowMs: LIVENESS_CONFIG.step1WindowMs,
            minDetectionRatio: LIVENESS_CONFIG.step1MinDetectionRatio,
            minAcceptMs: LIVENESS_CONFIG.step1MinAcceptMs,
            timeoutMs: LIVENESS_CONFIG.step1TimeoutMs,
        });
        this.smileHoldIdentityMisses = 0;
        this.smileHoldPaused = false;
        this.faceEmbedder = new FaceEmbeddingEngine();
        this.session = new LivenessSession();
    }

    getNeuralDescriptor() {
        return this.faceEmbedder?.getLastDescriptor() ?? null;
    }

    chainIdentityOptions(stepNumber) {
        const neural = this.getNeuralDescriptor();
        const base = stepNumber >= 5 ? FINAL_IDENTITY_OPTS : CHAIN_IDENTITY_OPTS;
        return {
            ...base,
            neuralEmbedding: neural,
            requireNeural: Boolean(this.faceEmbedder?.ready && neural?.length && stepNumber >= 2),
        };
    }

    /** Step 5 — global validation against chained Steps 1–4. */
    validateFinalStepIdentity(landmarks) {
        const chain = this.session.getChainedReferencesForStep(5);
        return validateChainedIdentity(landmarks, chain.refs, {
            ...this.chainIdentityOptions(5),
            chainStepIndices: chain.indices,
        });
    }

    validateChainedStepIdentity(landmarks, stepNumber) {
        const chain = this.session.getChainedReferencesForStep(stepNumber);
        return validateChainedIdentity(landmarks, chain.refs, {
            ...this.chainIdentityOptions(stepNumber),
            chainStepIndices: chain.indices,
        });
    }

    validateSmileIdentity(landmarks) {
        return this.validateFinalStepIdentity(landmarks);
    }

    validateSmileIdentityFromVideo() {
        if (!this.landmarker || !this.video) {
            return Promise.resolve({ valid: false, code: 'collecting', reason: 'Camera not ready for identity check.' });
        }

        return this.faceEmbedder.extractFromVideo(this.video, performance.now()).then(() => {
            let result;
            try {
                result = this.landmarker.detectForVideo(this.video, this.nextMediaPipeTimestamp());
            } catch {
                return { valid: false, code: 'collecting', reason: 'Face detection failed — try again.' };
            }

            const faceCount = result.faceLandmarks?.length || 0;
            if (faceCount !== 1) {
                return {
                    valid: false,
                    code: 'collecting',
                    reason: 'Center one face in the frame before capture.',
                };
            }

            return this.validateSmileIdentity(result.faceLandmarks[0]);
        });
    }

    get totalSteps() {
        return this.session.totalSteps;
    }

    get completedCount() {
        return this.session.completedCount;
    }

    async initModel() {
        if (this.landmarker) {
            this.modelReady = true;
            if (!this.faceEmbedder.ready && !this.faceEmbedder.loading) {
                this.faceEmbedder.init((msg) => this.onStatus(msg)).catch(() => {});
            }
            return;
        }

        this.onStatus('Loading face detection model…');

        this.faceEmbedder.init((msg) => this.onStatus(msg)).catch((error) => {
            console.warn('FaceNet identity model unavailable — using landmark fallback.', error);
        });

        let vision = null;
        let lastError = null;

        for (const resolvePath of WASM_PATHS) {
            try {
                vision = await withTimeout(
                    FilesetResolver.forVisionTasks(resolvePath()),
                    MEDIAPIPE_INIT_TIMEOUT_MS,
                    'MediaPipe WASM load'
                );
                break;
            } catch (error) {
                lastError = error;
            }
        }

        if (!vision) {
            console.error('MediaPipe WASM load failed:', lastError);
            const modelError = new Error('Failed to load the face detection model. Check your internet connection and refresh the page.');
            modelError.fvType = ERROR.MODEL;
            throw modelError;
        }

        const delegates = ['CPU', 'GPU'];

        for (const modelEntry of MODEL_URLS) {
            const modelAssetPath = resolveModelUrl(modelEntry);

            for (const delegate of delegates) {
                const timeoutMs = delegate === 'GPU' ? MEDIAPIPE_GPU_TIMEOUT_MS : MEDIAPIPE_INIT_TIMEOUT_MS;

                try {
                    this.onStatus(`Loading face detector (${delegate})…`);
                    this.landmarker = await withTimeout(
                        FaceLandmarker.createFromOptions(vision, {
                            baseOptions: {
                                modelAssetPath,
                                delegate,
                            },
                            runningMode: 'VIDEO',
                            numFaces: 2,
                            outputFaceBlendshapes: true,
                            outputFacialTransformationMatrixes: true,
                        }),
                        timeoutMs,
                        `Face detection init (${delegate})`
                    );
                    this.modelReady = true;
                    this.mediaPipeTimestampMs = 0;
                    this.onStatus('Face detector ready.');
                    return;
                } catch (error) {
                    lastError = error;
                    console.warn(`FaceLandmarker init failed (${delegate}, ${modelAssetPath}):`, error);
                    if (this.landmarker) {
                        try {
                            this.landmarker.close();
                        } catch {
                            /* ignore */
                        }
                        this.landmarker = null;
                    }
                }
            }
        }

        console.error('FaceLandmarker init failed:', lastError);
        const modelError = new Error('Failed to initialize face detection. Please refresh and try again.');
        modelError.fvType = ERROR.MODEL;
        throw modelError;
    }

    async startCamera(videoElement) {
        this.video = videoElement;

        if (this.stream) {
            this.stream.getTracks().forEach((track) => track.stop());
            this.stream = null;
        }

        const info = getCameraAccessInfo();

        if (info.accessLevel === 'unsupported') {
            const err = new Error('Your browser does not support camera access. Use Chrome, Edge, or Firefox.');
            err.fvType = ERROR.UNSUPPORTED;
            throw err;
        }

        const constraintSets = [
            { video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false },
            { video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }, audio: false },
            { video: { facingMode: 'user' }, audio: false },
            { video: true, audio: false },
        ];

        let lastError = null;

        for (const constraints of constraintSets) {
            try {
                this.stream = await Promise.race([
                    navigator.mediaDevices.getUserMedia(constraints),
                    sleep(CAMERA_PERMISSION_TIMEOUT_MS).then(() => {
                        throw Object.assign(
                            new Error('Camera permission timed out — click Allow when prompted, then Retake Verification.'),
                            { name: 'NotAllowedError' }
                        );
                    }),
                ]);
                break;
            } catch (error) {
                lastError = error;
                if (!isRetryableCameraError(error)) {
                    break;
                }
            }
        }

        if (!this.stream) {
            const mapped = mapCameraError(lastError || new Error('Camera failed'), info);
            const err = new Error(mapped.message);
            err.fvType = mapped.type;
            err.fvHelp = mapped.help;
            throw err;
        }

        this.video.srcObject = this.stream;
        this.video.setAttribute('playsinline', 'true');
        this.video.muted = true;

        await new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Camera stream timed out.')), 15000);
            const onReady = () => {
                clearTimeout(timeout);
                this.video.removeEventListener('loadedmetadata', onReady);
                resolve();
            };
            this.video.addEventListener('loadedmetadata', onReady);
            this.video.play().catch(reject);
        });
    }

    async startCameraWithRetry(videoElement) {
        let lastErr = null;

        for (let attempt = 1; attempt <= CAMERA_RETRY_ATTEMPTS; attempt++) {
            try {
                if (attempt > 1) {
                    this.onStatus(`Retrying camera (${attempt}/${CAMERA_RETRY_ATTEMPTS})…`);
                    await sleep(CAMERA_RETRY_DELAY_MS * attempt);
                } else {
                    this.onStatus('Camera permission required — please allow access when prompted.');
                }

                await this.startCamera(videoElement);
                this.onStatus('Camera ready. Position your face in the frame.');
                return;
            } catch (error) {
                lastErr = error;
                if (!isRetryableCameraError(error) || attempt >= CAMERA_RETRY_ATTEMPTS) {
                    throw error;
                }
            }
        }

        throw lastErr;
    }

    resetSession() {
        this.session.reset();
        this.warmupFrames = 0;
        this.analyzing = false;
        this.blinkCompleting = false;
        this.smileHoldPhase = false;
        this.smileHoldSecondsLeft = 0;
        this.smileHoldLastTick = 0;
        this.capturedDataUrl = null;
        this.step1Init.reset();
        this.smileHoldIdentityMisses = 0;
        this.smileHoldPaused = false;
        this.faceEmbedder?.reset();
        this.lastProcessAt = 0;
        this.emitProgress();
    }

    /** Stay on current step — reset collector only, never rewind to Step 1. */
    retryCurrentStep(result, challenge) {
        if (!this.session.currentChallenge) {
            this.session.reopenLastStep();
        }

        const current = challenge || this.session.currentChallenge;

        this.analyzing = false;
        this.warmupFrames = 0;
        this.smileHoldPhase = false;
        this.smileHoldPaused = false;
        this.session.collector?.reset();

        const isIdentity = result.code === FACE_SEC_CODES.IDENTITY_MISMATCH
            || result.code === SECURITY_CODES.IDENTITY_MISMATCH;

        this.emitUi({
            analyzing: false,
            faceDetected: true,
            identityMatched: isIdentity ? false : null,
            faceState: isIdentity ? 'error' : 'detected',
            instruction: current?.instruction,
            actionHint: result.reason || current?.actionHint || 'Try again',
            guideIcon: current?.guideIcon,
            status: result.reason,
        });
    }

    handleSecurityViolation(result, challenge) {
        if (challenge?.isBaseStep) {
            this.emitUi({
                faceDetected: false,
                identityMatched: null,
                faceState: 'detected',
                instruction: challenge.instruction,
                actionHint: result.reason || 'Adjust your face position',
                guideIcon: challenge.guideIcon,
                step1Init: true,
            });
            return;
        }

        this.retryCurrentStep(result, challenge);
    }

    startDetection() {
        if (!this.landmarker || !this.video) {
            const err = new Error('Face detector did not start. Please refresh and try again.');
            err.fvType = ERROR.MODEL;
            this.onError(err.message);
            return;
        }

        this.isRunning = true;
        this.mediaPipeTimestampMs = 0;
        this.detectFailCount = 0;
        this.lastProcessAt = 0;
        this.session.ensureCollector();
        this.emitProgress();
        this.emitUi({
            faceDetected: false,
            identityMatched: null,
            faceState: 'idle',
            step1Init: true,
            actionHint: 'Look straight at the camera with eyes open',
        });

        const detect = () => {
            if (!this.isRunning) {
                return;
            }

            if (this.video.readyState >= 2 && this.video.videoWidth > 0) {
                this.processFrame();
                void this.faceEmbedder.extractFromVideo(this.video, performance.now());
            }

            this.animationId = requestAnimationFrame(detect);
        };

        this.animationId = requestAnimationFrame(detect);
    }

    nextMediaPipeTimestamp() {
        this.mediaPipeTimestampMs = Math.max(this.mediaPipeTimestampMs + 33, Math.round(performance.now()));
        return this.mediaPipeTimestampMs;
    }

    processStep1Frame(result, challenge, collector, faceCount, now) {
        const detected = faceCount === 1;

        const timeProgress = this.step1Init.getTimeProgress(now);
        const timedOut = this.step1Init.isTimedOut(now);

        if (!detected) {
            this.step1Init.recordTick(false, now);
            if (faceCount > 1) {
                this.emitUi({
                    faceDetected: false,
                    identityMatched: null,
                    faceState: 'error',
                    step1Init: true,
                    frameProgress: timeProgress,
                    instruction: challenge.instruction,
                    actionHint: 'Only one person visible',
                    guideIcon: challenge.guideIcon,
                });
                return;
            }

            this.emitUi({
                faceDetected: false,
                identityMatched: null,
                faceState: timedOut ? 'error' : 'idle',
                step1Init: true,
                step1Timeout: timedOut,
                frameProgress: timeProgress,
                actionHint: timedOut
                    ? 'No human face found — improve lighting and try again'
                    : 'Looking for a live human face…',
                guideIcon: challenge.guideIcon,
            });
            return;
        }

        const landmarks = result.faceLandmarks[0];
        const blendshapes = getBlendshapeMap(result.faceBlendshapes?.[0]);

        const basic = validateBasicFaceDetection(landmarks);
        if (!basic.valid) {
            this.step1Init.recordTick(false, now);
            this.emitUi({
                faceDetected: false,
                identityMatched: null,
                faceState: 'idle',
                step1Init: true,
                frameProgress: timeProgress,
                actionHint: basic.reason || 'Move closer and center your face',
                guideIcon: challenge.guideIcon,
            });
            return;
        }

        const human = validateStep1HumanFrame(landmarks);
        if (!human.valid) {
            this.step1Init.recordTick(false, now);
            this.emitUi({
                faceDetected: false,
                identityMatched: null,
                faceState: human.code === FACE_SEC_CODES.NOT_HUMAN ? 'error' : 'idle',
                step1Init: true,
                frameProgress: timeProgress,
                actionHint: human.reason || 'Live human face required',
                guideIcon: challenge.guideIcon,
            });
            return;
        }

        this.step1Init.recordTick(true, now);

        if (collector.shouldSample(now)) {
            collector.addSample(landmarks, blendshapes, now, this.getNeuralDescriptor(), { humanPass: true });
        }

        const ready = this.step1Init.isReady(now) && collector.hasEnoughFrames();

        this.emitUi({
            faceDetected: true,
            identityMatched: null,
            faceState: ready ? 'valid' : 'detected',
            step1Init: true,
            frameProgress: timeProgress,
            actionHint: ready
                ? 'Human verified — moving to next step…'
                : 'Human face detected — hold still…',
            guideIcon: challenge.guideIcon,
        });

        if (ready) {
            this.runStepAnalysis(collector);
        }
    }

    stopDetection() {
        this.isRunning = false;
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
            this.animationId = null;
        }
    }

    processFrame() {
        if (this.analyzing) {
            return;
        }

        const now = performance.now();
        if (now - this.lastProcessAt < PROCESS_INTERVAL_MS) {
            return;
        }
        this.lastProcessAt = now;

        if (this.smileHoldPhase) {
            this.processSmileHoldFrame();
            return;
        }

        if (this.session.isComplete()) {
            return;
        }

        const timestamp = this.nextMediaPipeTimestamp();
        let result;

        try {
            result = this.landmarker.detectForVideo(this.video, timestamp);
        } catch (error) {
            this.detectFailCount += 1;
            if (this.detectFailCount <= 3) {
                console.warn('Face detection frame error:', error);
            }
            if (this.detectFailCount >= 20) {
                this.emitUi({
                    faceDetected: false,
                    identityMatched: null,
                    faceState: 'error',
                    step1Init: this.session.currentChallenge?.isBaseStep,
                    actionHint: 'Face detector error — refresh the page and try again',
                });
            }
            return;
        }

        this.detectFailCount = 0;

        const faceCount = result.faceLandmarks?.length || 0;
        const challenge = this.session.currentChallenge;
        const collector = this.session.collector;

        if (!challenge || !collector) {
            return;
        }

        const isBaseStep = challenge.isBaseStep === true;
        const isBlinkStep = challenge.id === 'blink';
        const isSmileStep = challenge.id === 'smile';

        if (isBaseStep) {
            this.processStep1Frame(result, challenge, collector, faceCount, now);
            return;
        }

        if (faceCount === 0) {
            this.warmupFrames = 0;
            this.emitUi({
                faceDetected: false,
                identityMatched: null,
                faceState: 'idle',
                actionHint: 'Center your face in the guide',
                guideIcon: challenge.guideIcon,
            });
            return;
        }

        if (faceCount > 1) {
            this.warmupFrames = 0;
            this.emitUi({
                faceDetected: false,
                identityMatched: null,
                faceState: 'error',
                actionHint: 'Only one person visible',
                guideIcon: challenge.guideIcon,
            });
            return;
        }

        this.warmupFrames++;

        if (!isBlinkStep && this.warmupFrames < WARMUP_FRAMES) {
            this.emitUi({
                faceDetected: true,
                identityMatched: challenge.isBaseStep ? null : true,
                faceState: 'warming',
                actionHint: 'Hold still…',
                guideIcon: challenge.guideIcon,
            });
            return;
        }

        const landmarks = result.faceLandmarks[0];
        const blendshapes = getBlendshapeMap(result.faceBlendshapes?.[0]);

        const stepNum = challenge.stepNumber;
        const security = isSmileStep
            ? this.validateFinalStepIdentity(landmarks)
            : this.validateChainedStepIdentity(landmarks, stepNum);

        if (!security.valid) {
            const isIdentity = security.code === FACE_SEC_CODES.IDENTITY_MISMATCH;
            const isNonHuman = security.code === FACE_SEC_CODES.NOT_HUMAN;
            this.emitUi({
                faceDetected: true,
                identityMatched: isIdentity ? false : null,
                faceState: (isIdentity || isNonHuman) ? 'error' : 'detected',
                instruction: challenge.instruction,
                actionHint: isIdentity
                    ? (security.reason || 'Identity chain broken — same person required')
                    : (security.reason || challenge.actionHint),
                guideIcon: challenge.guideIcon,
            });
            return;
        }

        const frameSignature = security.signature;

        if (isBlinkStep) {
            collector.blinkTracker.update(landmarks, now);

            if (collector.shouldSample(now)) {
                collector.addSample(landmarks, blendshapes, now, this.getNeuralDescriptor());
            }

            const challengeReady = collector.blinkTracker.blinkDetected;
            this.emitUi({
                faceDetected: true,
                identityMatched: true,
                faceState: challengeReady ? 'valid' : 'detected',
                actionHint: challengeReady ? 'Blink detected — verifying…' : challenge.actionHint,
                instruction: challenge.instruction,
                guideIcon: challenge.guideIcon,
                frameProgress: Math.min(100, Math.round((collector.frameCount / collector.getMinFrames()) * 100)),
            });

            if (challengeReady && collector.hasEnoughFrames()) {
                this.runStepAnalysis(collector);
            }
            return;
        }

        if (collector.shouldSample(now)) {
            collector.addSample(landmarks, blendshapes, now, this.getNeuralDescriptor());
        }

        const minFrames = collector.getMinFrames();
        const frameProgress = Math.min(100, Math.round((collector.frameCount / minFrames) * 100));
        const challengeReady = collector.hasSustainedChallenge();
        const faceState = challengeReady ? 'valid' : (collector.consecutivePass > 1 ? 'detected' : 'detected');

        this.emitUi({
            faceDetected: true,
            identityMatched: true,
            faceState,
            actionHint: isSmileStep && challengeReady
                ? 'Identity matched — verifying smile…'
                : (challengeReady ? 'Action detected — verifying…' : challenge.actionHint),
            instruction: challenge.instruction,
            guideIcon: challenge.guideIcon,
            frameProgress,
            frameCount: collector.frameCount,
            minFrames,
        });

        if (!collector.hasEnoughFrames() || !collector.hasSustainedChallenge()) {
            return;
        }

        this.runStepAnalysis(collector);
    }

    async runStepAnalysis(collector) {
        if (this.analyzing) {
            return;
        }

        this.analyzing = true;
        const isInitStep = this.session.currentChallenge?.isBaseStep === true;
        const delay = isInitStep
            ? LIVENESS_CONFIG.step1AnalyzeDelayMs
            : LIVENESS_CONFIG.analyzeDelayMs;
        await sleep(delay);

        const chain = this.session.getChainedReferencesForStep(this.session.currentChallenge?.stepNumber ?? 0);
        const analysis = collector.analyze(this.session.baseFaceSignature, {
            chainedReferences: chain.refs,
        });

        if (!analysis.pass) {
            this.analyzing = false;
            this.retryCurrentStep(analysis, this.session.currentChallenge);
            return;
        }

        const advanced = this.session.completeCurrentStep(analysis);
        this.analyzing = false;

        if (!advanced) {
            return;
        }

        this.warmupFrames = 0;
        if (this.session.completedCount >= 1) {
            this.step1Init.reset();
        }
        this.emitProgress();

        if (this.session.isComplete()) {
            this.startSmileHoldCountdown();
            return;
        }

        this.session.ensureCollector();
        this.emitUi({
            analyzing: false,
            actionHint: 'Step complete — next instruction loading…',
            faceState: 'valid',
            stepTransition: true,
        });
    }

    async captureAndFinish() {
        const identityCheck = await this.validateSmileIdentityFromVideo();
        if (!identityCheck.valid) {
            this.smileHoldPhase = false;
            if (!this.session.currentChallenge) {
                this.session.reopenLastStep();
            }
            this.retryCurrentStep(identityCheck, this.session.currentChallenge);
            return;
        }

        this.emitUi({
            identityMatched: true,
            overrideInstruction: 'Capturing photo…',
            actionHint: 'Hold still',
            faceState: 'valid',
        });

        try {
            const dataUrl = await this.captureSelfie();
            this.capturedDataUrl = dataUrl;
            this.stopDetection();
            this.onComplete(dataUrl);
        } catch (err) {
            this.onError(err.message || 'Failed to capture selfie.');
        }
    }

    startSmileHoldCountdown() {
        this.smileHoldPhase = true;
        this.smileHoldSecondsLeft = LIVENESS_CONFIG.smileHoldSeconds;
        this.smileHoldLastTick = performance.now();
        this.smileHoldIdentityMisses = 0;
        this.smileHoldPaused = true;

        this.emitUi({
            analyzing: false,
            smileHold: true,
            smileHoldPaused: true,
            smileHoldSeconds: this.smileHoldSecondsLeft,
            overrideInstruction: 'Please smile',
            actionHint: `Smile naturally to start the ${LIVENESS_CONFIG.smileHoldSeconds}-second photo countdown`,
            guideIcon: 'smile',
            faceState: 'detected',
            faceDetected: true,
            identityMatched: null,
            frameProgress: null,
        });
    }

    resetSmileHoldCountdown(ui = {}) {
        this.smileHoldSecondsLeft = LIVENESS_CONFIG.smileHoldSeconds;
        this.smileHoldLastTick = performance.now();
        this.smileHoldPaused = true;

        this.emitUi({
            smileHold: true,
            smileHoldPaused: true,
            smileHoldSeconds: this.smileHoldSecondsLeft,
            guideIcon: 'smile',
            faceDetected: ui.faceDetected ?? true,
            identityMatched: ui.identityMatched ?? null,
            faceState: ui.faceState ?? 'detected',
            overrideInstruction: ui.overrideInstruction || 'Please smile',
            actionHint: ui.actionHint || `Smile to restart the ${LIVENESS_CONFIG.smileHoldSeconds}-second countdown`,
            frameProgress: null,
        });
    }

    evaluateSmileHoldFrame(landmarks, blendshapes) {
        const frameCheck = validateSmileHoldFrame(
            landmarks,
            this.session.baselineMouthWidth,
            blendshapes
        );

        if (!frameCheck.valid) {
            return {
                ok: false,
                overrideInstruction: 'Face only',
                actionHint: frameCheck.reason,
                faceState: 'error',
                faceDetected: true,
            };
        }

        const security = this.validateSmileIdentity(landmarks);
        if (!security.valid) {
            const isIdentity = security.code === FACE_SEC_CODES.IDENTITY_MISMATCH;
            return {
                ok: false,
                overrideInstruction: isIdentity ? 'Identity mismatch' : 'Hold your smile',
                actionHint: security.reason || 'Same person from Steps 1–4 required',
                faceState: isIdentity ? 'error' : 'detected',
                faceDetected: true,
                identityMatched: isIdentity ? false : null,
            };
        }

        if (!frameCheck.smiling) {
            return {
                ok: false,
                overrideInstruction: 'Please smile',
                actionHint: 'Relax and smile naturally — slight lip movement is enough',
                faceState: 'detected',
                faceDetected: true,
                identityMatched: true,
            };
        }

        return { ok: true, frameCheck, security };
    }

    async processSmileHoldFrame() {
        let result;

        try {
            result = this.landmarker.detectForVideo(this.video, this.nextMediaPipeTimestamp());
        } catch {
            return;
        }

        const faceCount = result.faceLandmarks?.length || 0;

        if (faceCount !== 1) {
            this.resetSmileHoldCountdown({
                faceDetected: faceCount > 0,
                faceState: faceCount > 1 ? 'error' : 'idle',
                overrideInstruction: faceCount > 1 ? 'One face only' : 'Face not found',
                actionHint: faceCount > 1
                    ? 'Remove hands, fingers, or other people — only your face is allowed'
                    : 'Center your face in the circle',
            });
            return;
        }

        const landmarks = result.faceLandmarks[0];
        const blendshapes = getBlendshapeMap(result.faceBlendshapes?.[0]);
        const evaluation = this.evaluateSmileHoldFrame(landmarks, blendshapes);

        if (!evaluation.ok) {
            this.resetSmileHoldCountdown(evaluation);
            return;
        }

        this.smileHoldIdentityMisses = 0;
        const now = performance.now();

        if (this.smileHoldPaused) {
            this.smileHoldPaused = false;
            this.smileHoldLastTick = now;
        }

        if (now - this.smileHoldLastTick >= 1000) {
            this.smileHoldSecondsLeft -= 1;
            this.smileHoldLastTick = now;
        }

        if (this.smileHoldSecondsLeft <= 0) {
            const finalEvaluation = this.evaluateSmileHoldFrame(landmarks, blendshapes);
            if (!finalEvaluation.ok) {
                this.resetSmileHoldCountdown(finalEvaluation);
                return;
            }

            const identityCheck = await this.validateSmileIdentityFromVideo();
            if (!identityCheck.valid) {
                this.resetSmileHoldCountdown({
                    overrideInstruction: 'Please smile',
                    actionHint: identityCheck.reason || 'Hold your smile and try the countdown again',
                    identityMatched: identityCheck.code === FACE_SEC_CODES.IDENTITY_MISMATCH ? false : null,
                    faceState: identityCheck.code === FACE_SEC_CODES.IDENTITY_MISMATCH ? 'error' : 'detected',
                });
                return;
            }

            this.smileHoldPhase = false;
            await this.captureAndFinish();
            return;
        }

        this.emitUi({
            faceDetected: true,
            identityMatched: true,
            faceState: 'valid',
            smileHold: true,
            smileHoldPaused: false,
            smileHoldSeconds: this.smileHoldSecondsLeft,
            overrideInstruction: 'Hold your smile',
            actionHint: `Keep smiling — photo in ${this.smileHoldSecondsLeft}s`,
            guideIcon: 'smile',
        });
    }

    emitProgress() {
        const challenge = this.session.currentChallenge;

        this.onProgress({
            completed: this.session.completedCount,
            total: this.session.totalSteps,
            current: challenge,
            challenges: this.session.challenges.map((item) => ({
                id: item.id,
                label: item.label,
                stepNumber: item.stepNumber,
                completed: this.session.completed.has(item.id),
            })),
        });
    }

    emitUi(payload) {
        this.onUiUpdate({
            ...payload,
            challenge: this.session.currentChallenge,
            completed: this.session.completedCount,
            total: this.session.totalSteps,
        });
    }

    async captureSelfie() {
        if (!this.video) {
            throw new Error('Camera is not available.');
        }

        const canvas = document.createElement('canvas');
        const width = this.video.videoWidth || 1280;
        const height = this.video.videoHeight || 720;
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        ctx.translate(width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(this.video, 0, 0, width, height);

        return canvas.toDataURL('image/jpeg', 0.92);
    }

    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach((track) => track.stop());
            this.stream = null;
        }
        if (this.video) {
            this.video.srcObject = null;
        }
    }

    destroy() {
        this.stopDetection();
        this.stopCamera();
        if (this.landmarker) {
            this.landmarker.close();
            this.landmarker = null;
            this.modelReady = false;
        }
    }
}

function renderContextBanner() {
    const hint = document.querySelector('.kkp-identity-hint');
    if (!hint) {
        return;
    }

    document.querySelector('.kkp-identity-secure-note')?.remove();

    const info = getCameraAccessInfo();

    if (info.isHttps || info.isLocalhost) {
        return;
    }

    const note = document.createElement('div');
    note.className = 'kkp-identity-secure-note';

    if (info.devHttpsEnabled) {
        note.innerHTML =
            `<strong>LAN camera access:</strong> HTTP on <code>${info.hostname}</code> blocks the camera. ` +
            `Run <code>npm run serve:lan</code>, then open ` +
            `<a href="${info.httpsLanUrl}">${info.httpsLanUrl}</a> (accept certificate once). ` +
            `Or use <a href="${info.localhostUrl}">localhost</a> on this PC.`;
    } else {
        note.innerHTML =
            `<strong>Use HTTPS for production face verification.</strong> ` +
            `Open this site with HTTPS, or use <a href="${info.localhostUrl}">localhost</a> on this PC.`;
    }

    hint.after(note);
}

function initFacialVerificationUI() {
    const triggerBtn = document.getElementById('kkpVerifyIdentityBtn');
    const modal = document.getElementById('kkpFacialVerificationModal');
    if (!triggerBtn || !modal) {
        return;
    }

    renderContextBanner();

    const overlay = modal.querySelector('.kkp-fv-overlay');
    const closeBtn = modal.querySelector('.kkp-fv-close');
    const retakeBtn = document.getElementById('kkpFvRetakeBtn');
    const confirmBtn = document.getElementById('kkpFvConfirmBtn');
    const video = document.getElementById('kkpFvVideo');
    const previewImg = document.getElementById('kkpFvPreview');
    const previewWrap = document.getElementById('kkpFvPreviewWrap');
    const videoWrap = document.getElementById('kkpFvVideoWrap');
    const errorEl = document.getElementById('kkpFvError');
    const helpEl = document.getElementById('kkpFvHelp');
    const successEl = document.getElementById('kkpFvSuccess');
    const loadingEl = document.getElementById('kkpFvLoading');
    const verifiedInput = document.getElementById('kkpVerifiedSelfie');
    const completedInput = document.getElementById('kkpFacialVerificationCompleted');
    const statusBadge = document.getElementById('kkpVerificationStatus');
    const retakeFormBtn = document.getElementById('kkpRetakeVerificationBtn');
    const formPreview = document.getElementById('kkpVerificationPreview');
    const formPreviewImg = document.getElementById('kkpVerificationPreviewImg');
    const formPreviewPlaceholder = document.getElementById('kkpVerificationPlaceholder');

    const stepNumEl = document.getElementById('kkpFvStepNum');
    const stepTotalEl = document.getElementById('kkpFvStepTotal');
    const stepDotsEl = document.getElementById('kkpFvStepDots');
    const instructionEl = document.getElementById('kkpFvInstruction');
    const instructionHelperEl = document.getElementById('kkpFvInstructionHelper');
    const stepCueIconEl = document.getElementById('kkpFvStepCueIcon');
    const stepCueLabelEl = document.getElementById('kkpFvStepCueLabel');
    const badgeFaceEl = document.getElementById('kkpFvBadgeFace');
    const badgeIdentityEl = document.getElementById('kkpFvBadgeIdentity');
    const badgeStatusEl = document.getElementById('kkpFvBadgeStatus');
    const faceRingEl = document.getElementById('kkpFvFaceRing');
    const guideIconEl = document.getElementById('kkpFvGuideIcon');
    const videoCueEl = document.getElementById('kkpFvVideoCue');
    const smileTimerEl = document.getElementById('kkpFvSmileTimer');
    const smileTimerCountEl = document.getElementById('kkpFvSmileTimerCount');
    const frameProgressEl = document.getElementById('kkpFvFrameProgress');
    const frameProgressBarEl = document.getElementById('kkpFvFrameProgressBar');

    let verifier = null;
    let isVerified = false;

    function showError(message, helpHtml = null) {
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.hidden = false;
        }
        if (helpEl) {
            if (helpHtml) {
                helpEl.innerHTML = helpHtml;
                helpEl.hidden = false;
            } else {
                helpEl.innerHTML = '';
                helpEl.hidden = true;
            }
        }
    }

    function clearError() {
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.hidden = true;
        }
        if (helpEl) {
            helpEl.innerHTML = '';
            helpEl.hidden = true;
        }
    }

    function setLoading(message, visible = true) {
        if (!loadingEl) {
            return;
        }
        loadingEl.textContent = message;
        loadingEl.hidden = !visible;
    }

    const STEP_ORDER = ['humanVerify', 'lookRight', 'lookLeft', 'blink', 'smile'];

    function renderStepInstruction(challenge, options = {}) {
        if (!challenge) {
            return;
        }

        const iconKey = resolveStepIconKey(challenge, options.stepIcon);
        const label = options.label || challenge.label || '';
        const mainText = options.overrideInstruction || challenge.instruction || '';
        const helperText = options.actionHint || options.helperText || challenge.helperText || challenge.actionHint || '';

        if (stepCueIconEl) {
            stepCueIconEl.innerHTML = fvIcon(iconKey);
        }
        if (stepCueLabelEl) {
            stepCueLabelEl.textContent = label;
        }
        if (instructionEl) {
            instructionEl.textContent = mainText;
        }
        if (instructionHelperEl) {
            instructionHelperEl.textContent = helperText;
            instructionHelperEl.hidden = !helperText;
            instructionHelperEl.classList.toggle('is-error', Boolean(options.helperError));
            instructionHelperEl.classList.toggle('is-success', Boolean(options.helperSuccess));
        }

        updateVideoCue(options.videoCue ?? challenge.videoCue ?? challenge.guideIcon);
    }

    function updateVideoCue(cue) {
        if (!videoCueEl) {
            return;
        }

        const allowed = ['arrow-right', 'arrow-left', 'blink', 'smile'];
        const activeCue = allowed.includes(cue) ? cue : '';

        videoCueEl.hidden = !activeCue;
        videoCueEl.dataset.cue = activeCue;
    }

    function renderStepDots(challenges, completedCount, activeStep) {
        if (!stepDotsEl) {
            return;
        }

        stepDotsEl.innerHTML = (challenges || STEP_ORDER.map((id) => ({ id })))
            .map((item, index) => {
                const meta = CHALLENGE_CATALOG[item.id] || {};
                let cls = 'kkp-fv-dot';
                if (item.completed || index < completedCount) {
                    cls += ' is-complete';
                } else if (index + 1 === activeStep) {
                    cls += ' is-active';
                }
                const title = meta.label || `Step ${index + 1}`;
                return `<span class="${cls}" title="${title}"></span>`;
            })
            .join('');
    }

    function updateGuideIcon() {
        if (guideIconEl) {
            guideIconEl.hidden = true;
            guideIconEl.dataset.icon = '';
        }
    }

    function updateUiFromProgress({ completed, total, current, challenges }) {
        const stepNumber = current?.stepNumber || Math.min(completed + 1, total);

        if (stepNumEl) {
            stepNumEl.textContent = String(stepNumber);
        }
        if (stepTotalEl) {
            stepTotalEl.textContent = String(total);
        }

        renderStepInstruction(current);
        renderStepDots(challenges || [], completed, stepNumber);
    }

    function updateLiveUi(payload) {
        const {
            faceDetected,
            identityMatched,
            faceState,
            actionHint,
            frameProgress,
            smileHold,
            smileHoldPaused,
            smileHoldSeconds,
        } = payload;
        const challenge = payload.challenge;
        const showIdentity = payload.completed > 0
            || smileHold
            || (challenge?.stepNumber && challenge.stepNumber > 1);

        renderStepInstruction(challenge, {
            actionHint,
            stepIcon: smileHold ? 'smile' : undefined,
            overrideInstruction: payload.overrideInstruction,
            helperError: faceState === 'error' || identityMatched === false,
            helperSuccess: faceState === 'valid' || identityMatched === true,
            videoCue: smileHold ? 'smile' : undefined,
        });

        if (faceRingEl && faceState) {
            let ringState = faceState;
            if (!faceDetected) {
                ringState = 'idle';
            } else if (identityMatched === false) {
                ringState = 'error';
            } else if (faceState === 'valid' || identityMatched === true) {
                ringState = 'valid';
            } else if (faceDetected) {
                ringState = 'detected';
            }
            faceRingEl.dataset.state = ringState;
        }

        if (badgeFaceEl) {
            badgeFaceEl.className = 'kkp-fv-badge ' + (faceDetected ? 'kkp-fv-badge-success' : 'kkp-fv-badge-neutral');
            if (payload.step1Init) {
                badgeFaceEl.innerHTML = faceDetected
                    ? `<span class="kkp-fv-badge-icon">${fvIcon('success')}</span> Live human face`
                    : `<span class="kkp-fv-badge-icon">${fvIcon('pending')}</span> Waiting for human face`;
            } else {
                badgeFaceEl.innerHTML = faceDetected
                    ? `<span class="kkp-fv-badge-icon">${fvIcon('success')}</span> Face in frame`
                    : `<span class="kkp-fv-badge-icon">${fvIcon('pending')}</span> No face detected`;
            }
        }

        if (badgeIdentityEl) {
            if (showIdentity) {
                badgeIdentityEl.hidden = false;
                if (!faceDetected || identityMatched === null) {
                    badgeIdentityEl.className = 'kkp-fv-badge kkp-fv-badge-neutral';
                    badgeIdentityEl.innerHTML = `<span class="kkp-fv-badge-icon">${fvIcon('pending')}</span> Checking identity…`;
                } else if (identityMatched === false) {
                    badgeIdentityEl.className = 'kkp-fv-badge kkp-fv-badge-error';
                    badgeIdentityEl.innerHTML = `<span class="kkp-fv-badge-icon">${fvIcon('error')}</span> ${smileHold || challenge?.id === 'smile' ? 'Identity mismatch' : 'Wrong person'}`;
                } else {
                    badgeIdentityEl.className = 'kkp-fv-badge kkp-fv-badge-success';
                    badgeIdentityEl.innerHTML = `<span class="kkp-fv-badge-icon">${fvIcon('success')}</span> ${smileHold || challenge?.id === 'smile' ? 'Same person verified' : 'Identity matched'}`;
                }
            } else {
                badgeIdentityEl.hidden = true;
            }
        }

        if (badgeStatusEl) {
            if (smileHold) {
                badgeStatusEl.hidden = false;
                badgeStatusEl.className = 'kkp-fv-badge kkp-fv-badge-info';
                if (smileHoldPaused) {
                    badgeStatusEl.innerHTML = `<span class="kkp-fv-badge-icon">${fvIcon('smile')}</span> Smile to start ${LIVENESS_CONFIG.smileHoldSeconds}s countdown`;
                } else {
                    badgeStatusEl.innerHTML = `<span class="kkp-fv-badge-icon">${fvIcon('camera')}</span> Photo in ${Math.max(0, smileHoldSeconds ?? 0)}s`;
                }
            } else if (faceState === 'valid') {
                badgeStatusEl.hidden = false;
                badgeStatusEl.className = 'kkp-fv-badge kkp-fv-badge-success';
                badgeStatusEl.innerHTML = `<span class="kkp-fv-badge-icon">${fvIcon('success')}</span> Step complete`;
            } else if (typeof frameProgress === 'number' && frameProgress > 0) {
                badgeStatusEl.hidden = false;
                badgeStatusEl.className = 'kkp-fv-badge kkp-fv-badge-info';
                badgeStatusEl.innerHTML = `<span class="kkp-fv-badge-icon">${fvIcon('progress')}</span> ${frameProgress}%`;
            } else {
                badgeStatusEl.hidden = true;
            }
        }

        if (payload.guideIcon !== undefined) {
            updateGuideIcon();
        }

        if (smileTimerEl && smileTimerCountEl) {
            if (smileHold && !smileHoldPaused) {
                smileTimerEl.hidden = false;
                smileTimerCountEl.textContent = String(Math.max(0, smileHoldSeconds ?? 0));
            } else {
                smileTimerEl.hidden = true;
            }
        }

        if (frameProgressEl && frameProgressBarEl) {
            if (typeof frameProgress === 'number' && !smileHold && frameProgress > 0) {
                frameProgressEl.hidden = false;
                frameProgressBarEl.style.width = `${frameProgress}%`;
            } else {
                frameProgressEl.hidden = true;
            }
        }
    }

    function showPreviewPending() {
        if (formPreview) {
            formPreview.dataset.state = 'pending';
        }
        if (formPreviewImg) {
            formPreviewImg.hidden = true;
            formPreviewImg.removeAttribute('src');
        }
        if (formPreviewPlaceholder) {
            formPreviewPlaceholder.hidden = false;
        }
    }

    function showPreviewVerified(dataUrl) {
        if (formPreview) {
            formPreview.dataset.state = 'verified';
        }
        if (formPreviewImg) {
            formPreviewImg.src = dataUrl;
            formPreviewImg.hidden = false;
            formPreviewImg.onerror = () => {
                showPreviewPending();
            };
        }
        if (formPreviewPlaceholder) {
            formPreviewPlaceholder.hidden = true;
        }
    }

    function setFormVerified(dataUrl) {
        if (!dataUrl?.startsWith('data:image/')) {
            return;
        }

        isVerified = true;
        if (verifiedInput) {
            verifiedInput.value = dataUrl;
        }
        if (completedInput) {
            completedInput.value = '1';
        }
        if (statusBadge) {
            statusBadge.textContent = 'Identity Verification Successful';
            statusBadge.classList.add('is-success');
            statusBadge.hidden = false;
        }
        showPreviewVerified(dataUrl);
        if (retakeFormBtn) {
            retakeFormBtn.hidden = false;
        }
        triggerBtn.classList.add('is-verified');
        triggerBtn.textContent = 'Identity Verified ✓';
    }

    function resetFormVerification() {
        isVerified = false;
        if (verifiedInput) {
            verifiedInput.value = '';
        }
        if (completedInput) {
            completedInput.value = '';
        }
        if (statusBadge) {
            statusBadge.textContent = '';
            statusBadge.classList.remove('is-success');
            statusBadge.hidden = true;
        }
        showPreviewPending();
        if (retakeFormBtn) {
            retakeFormBtn.hidden = true;
        }
        triggerBtn.classList.remove('is-verified');
        triggerBtn.innerHTML = `
            <span class="kkp-verify-identity-btn-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 14a4 4 0 1 0-4-4" stroke-linecap="round"/>
                    <path d="M4 20c2-4 5-6 8-6s6 2 8 6" stroke-linecap="round"/>
                    <circle cx="12" cy="10" r="3"/>
                    <path d="M17 8V5M19 6h-4" stroke-linecap="round"/>
                </svg>
            </span>
            Verify Identity`;
    }

    function showPreviewState(dataUrl) {
        if (videoWrap) {
            videoWrap.hidden = true;
        }
        if (previewWrap) {
            previewWrap.hidden = false;
        }
        if (previewImg) {
            previewImg.src = dataUrl;
        }
        if (successEl) {
            successEl.hidden = false;
        }
        if (confirmBtn) {
            confirmBtn.hidden = false;
        }
        if (retakeBtn) {
            retakeBtn.hidden = false;
        }
    }

    function showLiveState() {
        if (loadingEl) {
            loadingEl.hidden = true;
        }
        if (videoWrap) {
            videoWrap.hidden = false;
        }
        if (previewWrap) {
            previewWrap.hidden = true;
        }
        if (previewImg) {
            previewImg.removeAttribute('src');
        }
        if (successEl) {
            successEl.hidden = true;
        }
        if (confirmBtn) {
            confirmBtn.hidden = true;
        }
        if (retakeBtn) {
            retakeBtn.hidden = true;
        }
    }

    function setDetectorStatus(message) {
        if (instructionHelperEl && message) {
            instructionHelperEl.textContent = message;
            instructionHelperEl.hidden = false;
            instructionHelperEl.classList.remove('is-error', 'is-success');
        }
    }

    async function startVerificationSession() {
        const info = getCameraAccessInfo();

        if (info.accessLevel === 'unsupported') {
            throw Object.assign(new Error('Your browser does not support camera access.'), { fvType: ERROR.UNSUPPORTED });
        }

        if (!verifier) {
            verifier = new LivenessVerification({
                onProgress: (data) => updateUiFromProgress(data),
                onUiUpdate: (data) => {
                    updateLiveUi(data);
                    if (data.faceState === 'valid' || (data.faceDetected && data.identityMatched !== false)) {
                        clearError();
                    }
                },
                onStatus: () => {},
                onComplete: (dataUrl) => {
                    setLoading('', false);
                    showPreviewState(dataUrl);
                },
                onError: (msg) => showError(msg),
            });
        }

        verifier.resetSession();

        setLoading('Allow camera access when prompted…', true);
        try {
            await verifier.startCameraWithRetry(video);
        } finally {
            setLoading('', false);
        }

        verifier.onStatus = (msg) => setDetectorStatus(msg);

        setDetectorStatus('Loading face detector…');
        await verifier.initModel();

        if (!verifier.landmarker) {
            throw Object.assign(new Error('Face detector failed to start. Refresh the page and try again.'), { fvType: ERROR.MODEL });
        }

        verifier.startDetection();
    }

    function handleSessionError(err) {
        setLoading('', false);
        const info = getCameraAccessInfo();
        const mapped = err.fvHelp
            ? { message: err.message, help: err.fvHelp }
            : mapCameraError(err, info);

        showError(mapped.message, mapped.help || (err.fvType === ERROR.INSECURE ? buildInsecureHelp(info) : null));
        verifier?.stopCamera();
    }

    async function openModal() {
        clearError();
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('kkp-fv-modal-open');
        showLiveState();

        try {
            await startVerificationSession();
        } catch (err) {
            handleSessionError(err);
        }
    }

    function closeModal() {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('kkp-fv-modal-open');
        verifier?.stopDetection();
        verifier?.stopCamera();
    }

    async function restartVerification() {
        clearError();
        showLiveState();

        try {
            if (!verifier) {
                await openModal();
                return;
            }
            verifier.resetSession();
            await startVerificationSession();
        } catch (err) {
            handleSessionError(err);
        }
    }

    triggerBtn.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    overlay?.addEventListener('click', closeModal);
    retakeBtn?.addEventListener('click', restartVerification);
    retakeFormBtn?.addEventListener('click', () => {
        resetFormVerification();
        openModal();
    });
    confirmBtn?.addEventListener('click', () => {
        if (verifier?.capturedDataUrl) {
            setFormVerified(verifier.capturedDataUrl);
        }
        closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });

    window.kkpFacialVerification = {
        isVerified: () => isVerified,
        reset: resetFormVerification,
        getCameraAccessInfo,
    };

    showPreviewPending();
}

document.addEventListener('DOMContentLoaded', initFacialVerificationUI);

export { LivenessVerification, initFacialVerificationUI, getCameraAccessInfo };

/**
 * Biometric security layer — human face validation, identity lock, anti-spoof.
 */

import { compareNeuralEmbeddings, NEURAL_EMBED_CONFIG } from './face-embedding-engine.js';

const LEFT_EYE_EAR = [33, 160, 158, 133, 153, 144];
const RIGHT_EYE_EAR = [362, 385, 387, 263, 373, 380];
const EMBEDDING_INDICES = [
    1, 4, 33, 133, 159, 145, 263, 362, 386, 374, 61, 291, 199, 152, 234, 454, 10, 152,
];

/** Minimal landmarks for Step 1 init — no full 468 mesh required. */
export const BASIC_LANDMARK_INDICES = [1, 33, 61, 133, 152, 159, 234, 263, 291, 362, 454];

/** Critical MediaPipe Face Mesh indices — eyes, nose, mouth, jawline, forehead. */
export const REQUIRED_LANDMARK_INDICES = [
    1, 4, 10, 13, 14, 33, 61, 133, 145, 152, 159, 199, 234, 263, 291, 362, 374, 386, 454,
];

export const SECURITY_CODES = {
    INCOMPLETE_LANDMARKS: 'incomplete_landmarks',
    NOT_HUMAN: 'not_human',
    IDENTITY_MISMATCH: 'identity_mismatch',
    SPOOF_STATIC: 'spoof_static',
    SPOOF_NO_MOTION: 'spoof_no_motion',
    SPOOF_FLAT_DEPTH: 'spoof_flat_depth',
};

/** Human facial proportion ranges — wide enough for real faces, tight enough for obvious non-human. */
const HUMAN_PROPORTIONS = {
    faceHeightWidth: { min: 0.88, max: 2.15 },
    interEyeFaceWidth: { min: 0.18, max: 0.52 },
    noseLengthFaceHeight: { min: 0.12, max: 0.52 },
    mouthWidthFaceWidth: { min: 0.26, max: 0.72 },
    eyeLineFaceHeight: { min: 0.22, max: 0.58 },
    noseToMouthFaceHeight: { min: 0.08, max: 0.42 },
    openEar: { min: 0.08, max: 0.45 },
    eyeSymmetryRatio: { min: 0.48, max: 1.55 },
    jawForeheadWidth: { min: 0.58, max: 1.55 },
};

/** Max nose-to-mouth ratio before rejecting snout-heavy (primate/cartoon) profiles. */
const MAX_SNOUT_RATIO = 0.40;

function dist(a, b) {
    return Math.hypot(a.x - b.x, a.y - b.y);
}

function dist3(a, b) {
    return Math.hypot(a.x - b.x, a.y - b.y, (a.z || 0) - (b.z || 0));
}

function getHeadYawOffset(landmarks) {
    const leftCheek = landmarks[234];
    const rightCheek = landmarks[454];
    const nose = landmarks[1];
    const centerX = (leftCheek.x + rightCheek.x) / 2;
    const width = Math.abs(rightCheek.x - leftCheek.x) || 0.001;
    return (nose.x - centerX) / width;
}

function isValidPoint(p) {
    return p
        && typeof p.x === 'number' && Number.isFinite(p.x)
        && typeof p.y === 'number' && Number.isFinite(p.y)
        && p.x >= -0.1 && p.x <= 1.1 && p.y >= -0.1 && p.y <= 1.1;
}

function landmarkDist(a, b) {
    return Math.hypot(a.x - b.x, a.y - b.y);
}

function computeEyeEAR(landmarks, indices) {
    const points = indices.map((i) => landmarks[i]);
    const vertical1 = landmarkDist(points[1], points[5]);
    const vertical2 = landmarkDist(points[2], points[4]);
    const horizontal = landmarkDist(points[0], points[3]) || 0.001;
    return (vertical1 + vertical2) / (2 * horizontal);
}

function computeAverageEAR(landmarks) {
    return (computeEyeEAR(landmarks, LEFT_EYE_EAR) + computeEyeEAR(landmarks, RIGHT_EYE_EAR)) / 2;
}

function extractFaceEmbedding(landmarks) {
    const leftCheek = landmarks[234];
    const rightCheek = landmarks[454];
    const centerX = (leftCheek.x + rightCheek.x) / 2;
    const centerY = (leftCheek.y + rightCheek.y) / 2;
    const width = Math.abs(rightCheek.x - leftCheek.x) || 0.001;

    return EMBEDDING_INDICES.flatMap((index) => {
        const point = landmarks[index];
        return [
            (point.x - centerX) / width,
            (point.y - centerY) / width,
            (point.z || 0) / width,
        ];
    });
}

function cosineSimilarity(a, b) {
    if (!a?.length || !b?.length || a.length !== b.length) {
        return 0;
    }

    let dot = 0;
    let normA = 0;
    let normB = 0;

    for (let i = 0; i < a.length; i++) {
        dot += a[i] * b[i];
        normA += a[i] * a[i];
        normB += b[i] * b[i];
    }

    const denom = Math.sqrt(normA) * Math.sqrt(normB);
    return denom > 0 ? dot / denom : 0;
}

/**
 * @typedef {{ x: number, y: number, z?: number }} Landmark
 */

/**
 * Relaxed face check for Step 1 initialization only.
 * @param {Landmark[]|undefined} landmarks
 */
export function validateBasicFaceDetection(landmarks) {
    if (!landmarks || landmarks.length < 10) {
        return { valid: false, code: SECURITY_CODES.INCOMPLETE_LANDMARKS, reason: 'Face not detected — move closer to the camera.' };
    }

    for (const index of BASIC_LANDMARK_INDICES) {
        if (!isValidPoint(landmarks[index])) {
            return { valid: false, code: SECURITY_CODES.INCOMPLETE_LANDMARKS, reason: 'Face partially visible — center your face in the frame.' };
        }
    }

    return { valid: true, signature: extractFaceSignature(landmarks) };
}

/**
 * Step 1 sliding-window tracker — accepts intermittent detection loss.
 */
export class Step1InitTracker {
    constructor(options = {}) {
        this.windowMs = options.windowMs ?? 2000;
        this.minDetectionRatio = options.minDetectionRatio ?? 0.65;
        this.minAcceptMs = options.minAcceptMs ?? 1500;
        this.timeoutMs = options.timeoutMs ?? 5000;
        /** @type {{ detected: boolean, timestamp: number }[]} */
        this.ticks = [];
        this.startedAt = null;
    }

    reset() {
        this.ticks = [];
        this.startedAt = null;
    }

    /** @param {boolean} detected @param {number} timestamp */
    recordTick(detected, timestamp) {
        if (!this.startedAt) {
            this.startedAt = timestamp;
        }

        this.ticks.push({ detected, timestamp });
        const cutoff = timestamp - this.windowMs;
        this.ticks = this.ticks.filter((t) => t.timestamp >= cutoff);
    }

    getDetectionRatio() {
        if (!this.ticks.length) {
            return 0;
        }
        return this.ticks.filter((t) => t.detected).length / this.ticks.length;
    }

    getElapsedMs(timestamp) {
        return timestamp - (this.startedAt || timestamp);
    }

    getTimeProgress(timestamp) {
        return Math.min(100, Math.round((this.getElapsedMs(timestamp) / this.windowMs) * 100));
    }

    isTimedOut(timestamp) {
        return this.getElapsedMs(timestamp) >= this.timeoutMs && this.getDetectionRatio() < this.minDetectionRatio;
    }

    isReady(timestamp) {
        const elapsed = this.getElapsedMs(timestamp);
        if (elapsed < this.minAcceptMs) {
            return false;
        }
        return this.getDetectionRatio() >= this.minDetectionRatio;
    }
}

/**
 * Light anti-spoof for Step 1 baseline capture only.
 * @param {Array<{ fingerprint: string, timestamp: number }>} frames
 */
export function validateStep1InitFromFrames(frames) {
    if (frames.length < 6) {
        return { valid: false, code: 'collecting', reason: 'Detecting face…' };
    }

    const timeSpan = frames[frames.length - 1].timestamp - frames[0].timestamp;
    if (timeSpan < 900) {
        return { valid: false, code: 'collecting', reason: 'Hold still — detecting face…' };
    }

    const uniqueRatio = new Set(frames.map((f) => f.fingerprint)).size / frames.length;

    if (frames.length >= 8 && uniqueRatio < 0.15) {
        return {
            valid: false,
            code: SECURITY_CODES.SPOOF_STATIC,
            reason: 'Static image detected — use your live camera.',
        };
    }

    const zValues = frames.map((f) => f.noseZ).filter((z) => typeof z === 'number');
    if (zValues.length >= 6) {
        const mean = zValues.reduce((a, b) => a + b, 0) / zValues.length;
        const zVar = zValues.reduce((acc, v) => acc + (v - mean) ** 2, 0) / zValues.length;
        if (zVar < 0.000008) {
            return {
                valid: false,
                code: SECURITY_CODES.SPOOF_FLAT_DEPTH,
                reason: 'Flat image detected — show your live face to the camera.',
            };
        }
    }

    const humanRatio = frames.filter((f) => f.humanPass !== false).length / frames.length;
    if (frames.length >= 6 && humanRatio < 0.75) {
        return {
            valid: false,
            code: SECURITY_CODES.NOT_HUMAN,
            reason: 'Live human face required — not a valid human face.',
        };
    }

    const firstEmb = frames[0].embedding;
    const allIdentical = frames.length >= 12 && frames.every(
        (f) => cosineSimilarity(f.embedding, firstEmb) > 0.9995
    );
    if (allIdentical) {
        return {
            valid: false,
            code: SECURITY_CODES.SPOOF_STATIC,
            reason: 'Identical frames detected — live camera required.',
        };
    }

    return { valid: true, uniqueRatio };
}

/**
 * @param {Landmark[]|undefined} landmarks
 */
export function validateLandmarksComplete(landmarks) {
    if (!landmarks || landmarks.length < 468) {
        return { valid: false, code: SECURITY_CODES.INCOMPLETE_LANDMARKS, reason: 'Incomplete face data — full human face required.' };
    }

    for (const index of REQUIRED_LANDMARK_INDICES) {
        if (!isValidPoint(landmarks[index])) {
            return { valid: false, code: SECURITY_CODES.INCOMPLETE_LANDMARKS, reason: 'Facial landmarks incomplete — use a live human face.' };
        }
    }

    return { valid: true };
}

/**
 * @param {Landmark[]} landmarks
 */
export function computeFaceMetrics(landmarks) {
    const leftCheek = landmarks[234];
    const rightCheek = landmarks[454];
    const forehead = landmarks[10];
    const chin = landmarks[152];
    const noseTip = landmarks[1];
    const noseBridge = landmarks[4];
    const leftEyeOuter = landmarks[33];
    const rightEyeOuter = landmarks[362];
    const mouthLeft = landmarks[61];
    const mouthRight = landmarks[291];
    const upperLip = landmarks[13];

    const faceWidth = dist(leftCheek, rightCheek) || 0.001;
    const faceHeight = dist(forehead, chin) || 0.001;
    const interEye = dist(leftEyeOuter, rightEyeOuter);
    const noseLength = dist(noseBridge, noseTip);
    const mouthWidth = dist(mouthLeft, mouthRight);
    const eyeLineY = (leftEyeOuter.y + rightEyeOuter.y) / 2;
    const eyeLineFromForehead = (eyeLineY - forehead.y) / faceHeight;
    const noseToMouth = dist(noseTip, upperLip) / faceHeight;
    const leftEyeNose = dist(leftEyeOuter, noseTip);
    const rightEyeNose = dist(rightEyeOuter, noseTip);
    const eyeSymmetryRatio = leftEyeNose / (rightEyeNose || 0.001);
    const jawWidth = dist(landmarks[172], landmarks[397]) / faceWidth;
    const foreheadWidth = dist(landmarks[71], landmarks[301]) / faceWidth;
    const jawForeheadWidth = jawWidth / (foreheadWidth || 0.001);
    const ear = computeAverageEAR(landmarks);

    return {
        faceHeightWidth: faceHeight / faceWidth,
        interEyeFaceWidth: interEye / faceWidth,
        noseLengthFaceHeight: noseLength / faceHeight,
        mouthWidthFaceWidth: mouthWidth / faceWidth,
        eyeLineFaceHeight: eyeLineFromForehead,
        noseToMouthFaceHeight: noseToMouth,
        openEar: ear,
        eyeSymmetryRatio,
        jawForeheadWidth,
        faceWidth,
        faceHeight,
        noseTipZ: noseTip.z || 0,
        leftCheekZ: leftCheek.z || 0,
        rightCheekZ: rightCheek.z || 0,
    };
}

function inRange(value, { min, max }) {
    return value >= min && value <= max;
}

/**
 * Step 1 live frame — basic detection + score-based human geometry (not all-or-nothing).
 * @param {Landmark[]} landmarks
 */
export function validateStep1HumanFrame(landmarks) {
    const complete = validateLandmarksComplete(landmarks);
    if (!complete.valid) {
        return complete;
    }

    const m = computeFaceMetrics(landmarks);
    const yaw = getHeadYawOffset(landmarks);

    if (Math.abs(yaw) > 0.1) {
        return {
            valid: false,
            code: SECURITY_CODES.NOT_HUMAN,
            reason: 'Face forward — look straight at the camera.',
        };
    }

    if (!inRange(m.openEar, HUMAN_PROPORTIONS.openEar)) {
        return {
            valid: false,
            code: SECURITY_CODES.NOT_HUMAN,
            reason: 'Keep both eyes open and clearly visible.',
        };
    }

    if (m.noseToMouthFaceHeight > MAX_SNOUT_RATIO) {
        return {
            valid: false,
            code: SECURITY_CODES.NOT_HUMAN,
            reason: 'Live human face required — use your real face only.',
        };
    }

    return validateHumanFaceGeometry(landmarks, { minPassRatio: 0.72 });
}

const SMILE_HOLD_CLEAR_INDICES = [10, 13, 14, 33, 61, 133, 152, 159, 234, 263, 291, 362, 454];
const SMILE_HOLD_MOUTH_INDICES = [61, 291, 13, 14, 178, 402];

/**
 * Detect active smile during Step 5 photo hold.
 * @param {Landmark[]} landmarks
 * @param {number|null} baselineMouthWidth
 * @param {Record<string, number>} [blendshapes]
 */
export function isSmileExpressionActive(landmarks, baselineMouthWidth, blendshapes = {}) {
    const leftCheek = landmarks[234];
    const rightCheek = landmarks[454];
    const faceWidth = Math.abs(rightCheek.x - leftCheek.x) || 0.001;
    const mouthWidth = dist(landmarks[61], landmarks[291]) / faceWidth;

    const leftSmile = blendshapes.mouthSmileLeft || 0;
    const rightSmile = blendshapes.mouthSmileRight || 0;
    const smileBlend = leftSmile + rightSmile;

    // Any MediaPipe smile blendshape — very low bar
    if (leftSmile >= 0.015 || rightSmile >= 0.015 || smileBlend >= 0.03) {
        return true;
    }

    // Broader mouth / lip motion (stretch, jaw, puff)
    const mouthMotion = smileBlend
        + (blendshapes.mouthStretchLeft || 0)
        + (blendshapes.mouthStretchRight || 0)
        + (blendshapes.mouthDimpleLeft || 0)
        + (blendshapes.mouthDimpleRight || 0)
        + (blendshapes.jawOpen || 0) * 0.4
        + (blendshapes.mouthPucker || 0) * 0.25;

    if (mouthMotion >= 0.045) {
        return true;
    }

    // Lips separated or corners moved — "gumalaw ang labi"
    const upperLip = landmarks[13];
    const lowerLip = landmarks[14];
    const lipGap = Math.abs(lowerLip.y - upperLip.y);
    if (lipGap >= 0.006) {
        return true;
    }

    const mouthLeft = landmarks[61];
    const mouthRight = landmarks[291];
    const mouthCenterY = (mouthLeft.y + mouthRight.y) / 2;
    const noseTip = landmarks[1];
    const cornersLifted = mouthCenterY < noseTip.y + 0.14;

    if (cornersLifted && mouthWidth >= 0.30) {
        return true;
    }

    // Slightly wider than Step 1 baseline (tiny tolerance — baseline may already be smiling)
    if (baselineMouthWidth && baselineMouthWidth > 0) {
        if (mouthWidth >= baselineMouthWidth * 1.001) {
            return true;
        }
        // Baseline already wide — accept current grin-like geometry
        if (mouthWidth >= baselineMouthWidth * 0.97 && (smileBlend > 0.01 || lipGap >= 0.004)) {
            return true;
        }
    }

    return mouthWidth >= 0.32;
}

/**
 * Step 5 photo-hold frame — single clear face, no hands/objects, smile state.
 * @param {Landmark[]} landmarks
 * @param {number|null} baselineMouthWidth
 * @param {Record<string, number>} [blendshapes]
 */
export function validateSmileHoldFrame(landmarks, baselineMouthWidth, blendshapes = {}) {
    const complete = validateLandmarksComplete(landmarks);
    if (!complete.valid) {
        return {
            valid: false,
            smiling: false,
            code: complete.code,
            reason: 'Keep your full face visible — move hands away from the camera.',
        };
    }

    const leftCheek = landmarks[234];
    const rightCheek = landmarks[454];
    const forehead = landmarks[10];
    const chin = landmarks[152];
    const faceSpanX = Math.abs(rightCheek.x - leftCheek.x);
    const faceSpanY = Math.abs(chin.y - forehead.y);

    if (faceSpanX < 0.20 || faceSpanY < 0.22) {
        return {
            valid: false,
            smiling: false,
            reason: 'Move closer — your face should fill most of the circle.',
        };
    }

    const centerX = (leftCheek.x + rightCheek.x) / 2;
    const centerY = (forehead.y + chin.y) / 2;
    if (centerX < 0.16 || centerX > 0.84 || centerY < 0.12 || centerY > 0.88) {
        return {
            valid: false,
            smiling: false,
            reason: 'Center your face — remove hands or objects from the frame.',
        };
    }

    for (const index of SMILE_HOLD_CLEAR_INDICES) {
        const point = landmarks[index];
        if (!isValidPoint(point) || point.x < 0.04 || point.x > 0.96 || point.y < 0.02 || point.y > 0.98) {
            return {
                valid: false,
                smiling: false,
                reason: 'Remove hands, fingers, or objects — only your face is allowed.',
            };
        }
    }

    for (const index of SMILE_HOLD_MOUTH_INDICES) {
        if (!isValidPoint(landmarks[index])) {
            return {
                valid: false,
                smiling: false,
                reason: 'Keep your mouth and chin clear — move fingers away.',
            };
        }
    }

    const smiling = isSmileExpressionActive(landmarks, baselineMouthWidth, blendshapes);

    return { valid: true, smiling };
}

/**
 * HARD GATE — batch/deep checks only, not per-frame Step 1.
 * @param {Landmark[]} landmarks
 */
export function validateStrictHumanFace(landmarks) {
    const complete = validateLandmarksComplete(landmarks);
    if (!complete.valid) {
        return complete;
    }

    const m = computeFaceMetrics(landmarks);
    const yaw = getHeadYawOffset(landmarks);

    if (Math.abs(yaw) > 0.1) {
        return {
            valid: false,
            code: SECURITY_CODES.NOT_HUMAN,
            reason: 'Face forward — look straight at the camera for human verification.',
        };
    }

    const checks = [
        ['face shape', m.faceHeightWidth, HUMAN_PROPORTIONS.faceHeightWidth],
        ['eye spacing', m.interEyeFaceWidth, HUMAN_PROPORTIONS.interEyeFaceWidth],
        ['nose proportion', m.noseLengthFaceHeight, HUMAN_PROPORTIONS.noseLengthFaceHeight],
        ['mouth proportion', m.mouthWidthFaceWidth, HUMAN_PROPORTIONS.mouthWidthFaceWidth],
        ['eye position', m.eyeLineFaceHeight, HUMAN_PROPORTIONS.eyeLineFaceHeight],
        ['nose-mouth alignment', m.noseToMouthFaceHeight, HUMAN_PROPORTIONS.noseToMouthFaceHeight],
        ['jaw structure', m.jawForeheadWidth, HUMAN_PROPORTIONS.jawForeheadWidth],
        ['eye symmetry', m.eyeSymmetryRatio, HUMAN_PROPORTIONS.eyeSymmetryRatio],
        ['open eyes', m.openEar, HUMAN_PROPORTIONS.openEar],
    ];

    for (const [, value, range] of checks) {
        if (!inRange(value, range)) {
            return {
                valid: false,
                code: SECURITY_CODES.NOT_HUMAN,
                reason: 'Not a valid human face — live camera with your face only.',
            };
        }
    }

    if (m.noseToMouthFaceHeight > MAX_SNOUT_RATIO) {
        return {
            valid: false,
            code: SECURITY_CODES.NOT_HUMAN,
            reason: 'Non-human facial structure detected.',
        };
    }

    const nose = landmarks[1];
    const leftCheek = landmarks[234];
    const rightCheek = landmarks[454];
    const centerX = (leftCheek.x + rightCheek.x) / 2;
    const faceWidth = Math.abs(rightCheek.x - leftCheek.x) || 0.001;
    const noseCenterOffset = Math.abs(nose.x - centerX) / faceWidth;
    if (noseCenterOffset > 0.12) {
        return {
            valid: false,
            code: SECURITY_CODES.NOT_HUMAN,
            reason: 'Nose not centered — use a forward-facing human face.',
        };
    }

    return { valid: true, metrics: m, signature: extractFaceSignature(landmarks) };
}

/**
 * @param {Landmark[]} landmarks
 */
export function validateHumanFaceGeometry(landmarks, options = {}) {
    const basic = validateBasicFaceDetection(landmarks);
    if (!basic.valid) {
        return basic;
    }

    const m = computeFaceMetrics(landmarks);
    const yaw = getHeadYawOffset(landmarks);
    const isTurned = Math.abs(yaw) > 0.08;
    const minPassRatio = options.minPassRatio ?? 0.68;

    const interEyeRange = isTurned
        ? { min: 0.14, max: 0.55 }
        : HUMAN_PROPORTIONS.interEyeFaceWidth;

    const checks = [
        ['face shape', m.faceHeightWidth, HUMAN_PROPORTIONS.faceHeightWidth],
        ['eye spacing', m.interEyeFaceWidth, interEyeRange],
        ['nose proportion', m.noseLengthFaceHeight, HUMAN_PROPORTIONS.noseLengthFaceHeight],
        ['mouth proportion', m.mouthWidthFaceWidth, HUMAN_PROPORTIONS.mouthWidthFaceWidth],
        ['eye position', m.eyeLineFaceHeight, HUMAN_PROPORTIONS.eyeLineFaceHeight],
        ['nose-mouth alignment', m.noseToMouthFaceHeight, HUMAN_PROPORTIONS.noseToMouthFaceHeight],
        ['jaw structure', m.jawForeheadWidth, isTurned ? { min: 0.5, max: 1.6 } : HUMAN_PROPORTIONS.jawForeheadWidth],
    ];

    if (!isTurned) {
        checks.push(['eye symmetry', m.eyeSymmetryRatio, HUMAN_PROPORTIONS.eyeSymmetryRatio]);
    }

    let passed = 0;
    for (const [, value, range] of checks) {
        if (inRange(value, range)) {
            passed += 1;
        }
    }

    const earOk = inRange(m.openEar, HUMAN_PROPORTIONS.openEar);
    if (earOk) {
        passed += 1;
    }

    const passRatio = passed / (checks.length + 1);
    if (passRatio < minPassRatio) {
        return {
            valid: false,
            code: SECURITY_CODES.NOT_HUMAN,
            reason: 'Not a valid human face — live camera with your face only.',
        };
    }

    return { valid: true, metrics: m, signature: extractFaceSignature(landmarks) };
}

/**
 * @param {Landmark[]} landmarks
 */
export function extractGeometrySignature(landmarks) {
    const m = computeFaceMetrics(landmarks);
    return [
        m.interEyeFaceWidth,
        m.noseLengthFaceHeight,
        m.mouthWidthFaceWidth,
        m.noseToMouthFaceHeight,
        m.eyeLineFaceHeight,
        m.jawForeheadWidth,
        m.faceHeightWidth,
    ];
}

/**
 * @param {Landmark[]} landmarks
 * @param {number[]|null} [neuralEmbedding]
 */
export function extractFaceSignature(landmarks, neuralEmbedding = null) {
    const m = computeFaceMetrics(landmarks);
    const jawStructureVector = [
        m.jawForeheadWidth,
        m.faceHeightWidth,
        m.interEyeFaceWidth,
        m.mouthWidthFaceWidth,
        m.noseLengthFaceHeight,
    ];

    const signature = {
        embedding: extractFaceEmbedding(landmarks),
        geometry: extractGeometrySignature(landmarks),
        landmarkVector: extractFaceEmbedding(landmarks),
        eyeDistanceRatio: m.interEyeFaceWidth,
        noseMouthAlignment: m.noseToMouthFaceHeight,
        jawStructureVector,
    };

    if (neuralEmbedding?.length) {
        signature.neuralEmbedding = neuralEmbedding;
    }

    return signature;
}

/**
 * @param {{ embedding: number[], geometry: number[], neuralEmbedding?: number[] }} current
 * @param {{ embedding: number[], geometry: number[], neuralEmbedding?: number[] }} base
 * @param {{ embeddingThreshold?: number, geometryThreshold?: number, structuralThreshold?: number, neuralDistanceThreshold?: number, requireGeometry?: boolean, requireNeural?: boolean }} thresholds
 */
export function compareFaceSignatures(current, base, thresholds = {}) {
    const embeddingThreshold = thresholds.embeddingThreshold ?? 0.88;
    const geometryThreshold = thresholds.geometryThreshold ?? 0.78;
    const structuralThreshold = thresholds.structuralThreshold ?? 0.85;
    const neuralDistanceThreshold = thresholds.neuralDistanceThreshold ?? NEURAL_EMBED_CONFIG.distanceThreshold;
    const requireGeometry = thresholds.requireGeometry !== false && geometryThreshold > 0;
    const requireNeural = thresholds.requireNeural === true;

    if (current.neuralEmbedding?.length && base.neuralEmbedding?.length) {
        const neural = compareNeuralEmbeddings(
            current.neuralEmbedding,
            base.neuralEmbedding,
            neuralDistanceThreshold
        );

        if (!neural.matched) {
            return {
                matched: false,
                embeddingSim: neural.similarity,
                geometrySim: 0,
                neuralDistance: neural.distance,
                identityLayer: 'neural',
            };
        }

        const embeddingSim = cosineSimilarity(current.embedding, base.embedding);
        const geometrySim = cosineSimilarity(current.geometry, base.geometry);

        return {
            matched: true,
            embeddingSim: neural.similarity,
            geometrySim,
            neuralDistance: neural.distance,
            identityLayer: 'neural',
        };
    }

    if (requireNeural) {
        return {
            matched: false,
            embeddingSim: 0,
            geometrySim: 0,
            identityLayer: 'neural',
        };
    }

    const embeddingSim = cosineSimilarity(current.embedding, base.embedding);
    const geometrySim = cosineSimilarity(current.geometry, base.geometry);

    let structuralOk = true;
    if (current.jawStructureVector?.length && base.jawStructureVector?.length) {
        const jawSim = cosineSimilarity(current.jawStructureVector, base.jawStructureVector);
        structuralOk = jawSim >= structuralThreshold;
    }

    if (current.eyeDistanceRatio != null && base.eyeDistanceRatio != null) {
        structuralOk = structuralOk && Math.abs(current.eyeDistanceRatio - base.eyeDistanceRatio) < 0.07;
    }

    if (current.noseMouthAlignment != null && base.noseMouthAlignment != null) {
        structuralOk = structuralOk && Math.abs(current.noseMouthAlignment - base.noseMouthAlignment) < 0.06;
    }

    return {
        matched: embeddingSim >= embeddingThreshold
            && (!requireGeometry || geometrySim >= geometryThreshold)
            && structuralOk,
        embeddingSim,
        geometrySim,
        structuralOk,
        identityLayer: 'landmark',
    };
}

/**
 * Chained biometric lock — validate face against required prior step signatures.
 * @param {Landmark[]} landmarks
 * @param {Array<{ embedding: number[], geometry: number[], jawStructureVector?: number[] }>} references
 * @param {{ embeddingThreshold?: number, geometryThreshold?: number, structuralThreshold?: number, requireGeometry?: boolean, strictHuman?: boolean, minPassRatio?: number, stepNumber?: number, neuralEmbedding?: number[]|null, neuralDistanceThreshold?: number, requireNeural?: boolean }} options
 */
export function validateChainedIdentity(landmarks, references, options = {}) {
    const human = options.strictHuman
        ? validateStrictHumanFace(landmarks)
        : validateHumanFaceGeometry(landmarks, { minPassRatio: options.minPassRatio ?? 0.68 });

    if (!human.valid) {
        return human;
    }

    if (!references?.length) {
        return { valid: true, signature: human.signature, identityMatched: true };
    }

    const current = extractFaceSignature(landmarks, options.neuralEmbedding ?? null);
    const embeddingThreshold = options.embeddingThreshold ?? 0.90;
    const geometryThreshold = options.geometryThreshold ?? 0.78;
    const structuralThreshold = options.structuralThreshold ?? 0.85;
    const neuralDistanceThreshold = options.neuralDistanceThreshold ?? NEURAL_EMBED_CONFIG.distanceThreshold;
    const requireGeometry = options.requireGeometry !== false;
    const requireNeural = options.requireNeural === true;

    for (let i = 0; i < references.length; i += 1) {
        const ref = references[i];
        if (!ref?.embedding?.length && !ref?.neuralEmbedding?.length) {
            continue;
        }

        const cmp = compareFaceSignatures(current, ref, {
            embeddingThreshold,
            geometryThreshold,
            structuralThreshold,
            neuralDistanceThreshold,
            requireGeometry,
            requireNeural,
        });

        if (!cmp.matched) {
            const chainStep = options.chainStepIndices?.[i] ?? (i + 1);
            return {
                valid: false,
                code: SECURITY_CODES.IDENTITY_MISMATCH,
                reason: `Identity chain broken — face does not match Step ${chainStep}.`,
                failedStep: chainStep,
                embeddingSim: cmp.embeddingSim,
                geometrySim: cmp.geometrySim,
                identityMatched: false,
            };
        }
    }

    return { valid: true, signature: current, identityMatched: true };
}

/** @deprecated Use validateChainedIdentity */
export function validateIdentityAgainstPriorSteps(landmarks, references, options = {}) {
    return validateChainedIdentity(landmarks, references, options);
}

function variance(values) {
    if (values.length < 2) {
        return 0;
    }
    const mean = values.reduce((a, b) => a + b, 0) / values.length;
    return values.reduce((acc, v) => acc + (v - mean) ** 2, 0) / values.length;
}

/**
 * Multi-frame anti-spoof: reject static photos, screen replay, identical injection.
 * @param {Array<{ fingerprint: string, yaw: number, pitch: number, ear: number, embedding: number[], timestamp: number, noseZ?: number, metrics?: ReturnType<typeof computeFaceMetrics> }>} frames
 * @param {{ isBlinkStep?: boolean, isTurnStep?: boolean, minFrames?: number }} options
 */
export function validateMultiFrameLiveness(frames, options = {}) {
    const {
        isBlinkStep = false,
        isTurnStep = false,
        minFrames = 10,
    } = options;

    if (frames.length < minFrames) {
        return { valid: false, code: 'collecting', reason: 'Collecting security frames…' };
    }

    const uniqueRatio = new Set(frames.map((f) => f.fingerprint)).size / frames.length;
    if (!isBlinkStep && uniqueRatio < 0.28) {
        return {
            valid: false,
            code: SECURITY_CODES.SPOOF_STATIC,
            reason: 'Static image detected — use your live camera, not a photo or screen.',
        };
    }

    const yawVar = variance(frames.map((f) => f.yaw));
    const pitchVar = variance(frames.map((f) => f.pitch));
    const earVar = variance(frames.map((f) => f.ear));
    const noseZVar = variance(frames.map((f) => f.noseZ ?? 0));
    const timeSpan = frames[frames.length - 1].timestamp - frames[0].timestamp;

    if (timeSpan < 400 && !isBlinkStep) {
        return {
            valid: false,
            code: SECURITY_CODES.SPOOF_STATIC,
            reason: 'Insufficient live capture — hold your face in view.',
        };
    }

    if (isTurnStep && yawVar < 0.00025) {
        return {
            valid: false,
            code: SECURITY_CODES.SPOOF_NO_MOTION,
            reason: 'No head movement detected — turn slowly as instructed.',
        };
    }

    if (!isBlinkStep && !isTurnStep && yawVar + pitchVar + earVar < 0.00008) {
        return {
            valid: false,
            code: SECURITY_CODES.SPOOF_NO_MOTION,
            reason: 'No natural micro-movement — use a live camera feed.',
        };
    }

    if (isBlinkStep && earVar < 0.0004) {
        return {
            valid: false,
            code: SECURITY_CODES.SPOOF_NO_MOTION,
            reason: 'Blink not verified — blink naturally with your live face.',
        };
    }

    if (noseZVar < 0.0000008 && earVar < 0.0002 && !isTurnStep) {
        return {
            valid: false,
            code: SECURITY_CODES.SPOOF_FLAT_DEPTH,
            reason: 'Flat image detected — printed photos and screens are not accepted.',
        };
    }

    const firstEmb = frames[0].embedding;
    const allIdentical = frames.every(
        (f) => cosineSimilarity(f.embedding, firstEmb) > 0.999
    );
    if (allIdentical && frames.length >= 8) {
        return {
            valid: false,
            code: SECURITY_CODES.SPOOF_STATIC,
            reason: 'Identical frames detected — live camera required.',
        };
    }

    return { valid: true, uniqueRatio, yawVar, earVar };
}

/**
 * @param {Landmark[]} landmarks
 * @param {{ referenceSignature?: { embedding: number[], geometry: number[] }|null, requireIdentity?: boolean, checkGeometry?: boolean, initMode?: boolean, humanVerifyMode?: boolean, relaxedMode?: boolean, embeddingThreshold?: number, geometryThreshold?: number }} options
 */
export function validateFrameSecurity(landmarks, options = {}) {
    if (options.humanVerifyMode) {
        const human = validateStep1HumanFrame(landmarks);
        if (!human.valid) {
            return human;
        }
        return { valid: true, signature: human.signature, metrics: human.metrics };
    }

    const useBasicOnly = options.initMode || options.relaxedMode === true;

    if (useBasicOnly) {
        const basic = validateBasicFaceDetection(landmarks);
        if (!basic.valid) {
            return basic;
        }

        const current = basic.signature;

        if (options.requireIdentity && options.referenceSignature) {
            const cmp = compareFaceSignatures(current, options.referenceSignature, {
                embeddingThreshold: options.embeddingThreshold ?? 0.88,
                geometryThreshold: options.geometryThreshold ?? 0.75,
                requireGeometry: options.checkGeometry !== false,
            });

            if (!cmp.matched) {
                return {
                    valid: false,
                    code: SECURITY_CODES.IDENTITY_MISMATCH,
                    reason: 'Identity mismatch — the same person from Step 1 is required.',
                    embeddingSim: cmp.embeddingSim,
                    geometrySim: cmp.geometrySim,
                };
            }
        }

        return { valid: true, signature: current };
    }

    const human = validateHumanFaceGeometry(landmarks, { minPassRatio: options.minPassRatio ?? 0.68 });
    if (!human.valid) {
        return human;
    }

    const current = extractFaceSignature(landmarks);

    if (options.requireIdentity && options.referenceSignature) {
        const cmp = compareFaceSignatures(current, options.referenceSignature, {
            embeddingThreshold: options.embeddingThreshold ?? 0.88,
            geometryThreshold: options.geometryThreshold ?? 0.75,
            requireGeometry: options.checkGeometry !== false,
        });

        if (!cmp.matched) {
            return {
                valid: false,
                code: SECURITY_CODES.IDENTITY_MISMATCH,
                reason: 'Identity mismatch — the same person from Step 1 is required.',
                embeddingSim: cmp.embeddingSim,
                geometrySim: cmp.geometrySim,
            };
        }
    }

    return { valid: true, signature: current, metrics: human.metrics };
}

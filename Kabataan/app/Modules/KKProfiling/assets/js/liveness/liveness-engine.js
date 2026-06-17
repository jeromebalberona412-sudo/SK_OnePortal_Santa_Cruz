/**
 * Multi-frame face liveness engine — EAR blink, landmark smile, strict identity matching.
 */

import {
    SECURITY_CODES,
    validateMultiFrameLiveness,
    validateStep1InitFromFrames,
    compareFaceSignatures,
    extractFaceSignature,
    isSmileExpressionActive,
} from './face-security.js';
import { pickStepNeuralEmbedding, NEURAL_EMBED_CONFIG } from './face-embedding-engine.js';

/** @typedef {{ x: number, y: number, z?: number }} Landmark */
/** @typedef {{ embedding: number[], fingerprint: string, yaw: number, pitch: number, mouthWidth: number, ear: number, blendshapes: Record<string, number>, timestamp: number, identitySimilarity?: number, noseZ?: number, geometry?: number[], neuralEmbedding?: number[] }} FrameSample */
/** @typedef {{ embedding: number[], geometry: number[], landmarkVector?: number[], eyeDistanceRatio?: number, noseMouthAlignment?: number, jawStructureVector?: number[], neuralEmbedding?: number[] }} FaceSignature */

export const LIVENESS_CONFIG = {
    sampleIntervalMs: 50,
    minFramesDefault: 15,
    minFramesStraight: 6,
    minFramesBlink: 10,
    minFramesSmile: 15,
    blinkAnalyzeDelayMs: 0,
    maxFramesPerStep: 30,
    minUniqueFrameRatio: 0.28,
    minPositionVariance: 0.0004,
    minYawVarianceForMotion: 0.00025,
    identitySimilarityThreshold: 0.90,
    chainIdentityThreshold: 0.88,
    smileIdentityThreshold: 0.92,
    smileMaxIdentityFailRatio: 0.02,
    frameIdentityThreshold: 0.88,
    geometryIdentityThreshold: 0.78,
    structuralIdentityThreshold: 0.85,
    neuralDistanceThreshold: 0.55,
    neuralDistanceThresholdFinal: 0.50,
    maxIdentityFailRatio: 0.03,
    sustainedChallengeFrames: 5,
    sustainedSmileMs: 180,
    yawStraightThreshold: 0.08,
    yawTurnThreshold: 0.1,
    smileWidthIncreaseRatio: 1.005,
    smileBlendshapeThreshold: 0.06,
    smileHoldSeconds: 5,
    earDropRatio: 0.7,
    earAbsoluteMax: 0.23,
    blinkRecoveryMaxMs: 550,
    analyzeDelayMs: 300,
    processIntervalMs: 80,
    step1WindowMs: 2000,
    step1MinAcceptMs: 1200,
    step1TimeoutMs: 6000,
    step1MinDetectionRatio: 0.65,
    step1MinSamples: 6,
    step1AnalyzeDelayMs: 120,
};

const LEFT_EYE_EAR = [33, 160, 158, 133, 153, 144];
const RIGHT_EYE_EAR = [362, 385, 387, 263, 373, 380];
const MOUTH_LEFT = 61;
const MOUTH_RIGHT = 291;

const EMBEDDING_INDICES = [
    1, 4, 33, 133, 159, 145, 263, 362, 386, 374, 61, 291, 199, 152, 234, 454, 10, 152,
];

export const CHALLENGE_CATALOG = {
    humanVerify: {
        id: 'humanVerify',
        stepIcon: 'human',
        label: 'Human Check',
        instruction: 'Show your live face to the camera',
        helperText: 'Look straight with eyes open — only one real person in frame.',
        actionHint: 'Hold still while we confirm you are human',
        guideIcon: 'straight',
        videoCue: null,
    },
    lookStraight: {
        id: 'lookStraight',
        stepIcon: 'human',
        label: 'Face Forward',
        instruction: 'Look straight at the camera',
        helperText: 'Center your face inside the circle and hold still.',
        actionHint: 'Center your face and hold still',
        guideIcon: 'straight',
        videoCue: null,
    },
    lookRight: {
        id: 'lookRight',
        stepIcon: 'arrow-right',
        label: 'Turn Right',
        instruction: 'Slowly turn your head to the right',
        helperText: 'Move your head — not just your eyes. Same person must stay in frame.',
        actionHint: 'Turn your head slowly to the right',
        guideIcon: 'arrow-right',
        videoCue: 'arrow-right',
    },
    lookLeft: {
        id: 'lookLeft',
        stepIcon: 'arrow-left',
        label: 'Turn Left',
        instruction: 'Slowly turn your head to the left',
        helperText: 'Move your head — not just your eyes. Same person must stay in frame.',
        actionHint: 'Turn your head slowly to the left',
        guideIcon: 'arrow-left',
        videoCue: 'arrow-left',
    },
    blink: {
        id: 'blink',
        stepIcon: 'blink',
        label: 'Blink',
        instruction: 'Blink your eyes once, naturally',
        helperText: 'One quick, natural blink — keep your face in the circle.',
        actionHint: 'Blink once — quick and natural',
        guideIcon: 'blink',
        videoCue: 'blink',
    },
    smile: {
        id: 'smile',
        stepIcon: 'smile',
        label: 'Smile & Capture',
        instruction: 'Smile for your verified photo',
        helperText: 'Smile naturally — slight lip movement is enough. Face must match Steps 1–4.',
        actionHint: 'Smile naturally — lips moving is OK',
        guideIcon: 'smile',
        videoCue: 'smile',
    },
};

/** Chained lock: Human (base) → Look Right → Look Left → Blink → Final verify + capture */
export function buildSessionChallenges() {
    return ['humanVerify', 'lookRight', 'lookLeft', 'blink', 'smile'].map((id, index) => ({
        ...CHALLENGE_CATALOG[id],
        stepNumber: index + 1,
        totalSteps: 5,
        isBaseStep: index === 0,
    }));
}

function landmarkDist(a, b) {
    return Math.hypot(a.x - b.x, a.y - b.y);
}

/**
 * Eye Aspect Ratio (EAR) — lower value = more closed eye.
 * @param {Landmark[]} landmarks
 * @param {number[]} indices
 */
export function computeEyeEAR(landmarks, indices) {
    const points = indices.map((i) => landmarks[i]);
    const vertical1 = landmarkDist(points[1], points[5]);
    const vertical2 = landmarkDist(points[2], points[4]);
    const horizontal = landmarkDist(points[0], points[3]) || 0.001;
    return (vertical1 + vertical2) / (2 * horizontal);
}

export function computeAverageEAR(landmarks) {
    return (computeEyeEAR(landmarks, LEFT_EYE_EAR) + computeEyeEAR(landmarks, RIGHT_EYE_EAR)) / 2;
}

/** Normalized mouth width relative to face width. */
export function getNormalizedMouthWidth(landmarks) {
    const faceWidth = Math.abs(landmarks[454].x - landmarks[234].x) || 0.001;
    return landmarkDist(landmarks[MOUTH_LEFT], landmarks[MOUTH_RIGHT]) / faceWidth;
}

export function getHeadYawOffset(landmarks) {
    const leftCheek = landmarks[234];
    const rightCheek = landmarks[454];
    const nose = landmarks[1];
    const centerX = (leftCheek.x + rightCheek.x) / 2;
    const width = Math.abs(rightCheek.x - leftCheek.x) || 0.001;
    return (nose.x - centerX) / width;
}

export function getHeadPitchOffset(landmarks) {
    const nose = landmarks[1];
    const chin = landmarks[152];
    const forehead = landmarks[10];
    const centerY = (forehead.y + chin.y) / 2;
    const height = Math.abs(chin.y - forehead.y) || 0.001;
    return (nose.y - centerY) / height;
}

export function getBlendshapeMap(classifications) {
    const map = {};
    (classifications?.categories || []).forEach((item) => {
        map[item.categoryName] = item.score;
    });
    return map;
}

export function extractFaceEmbedding(landmarks) {
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

export function frameFingerprint(landmarks) {
    return EMBEDDING_INDICES.map((i) => {
        const p = landmarks[i];
        return `${Math.round(p.x * 800)}:${Math.round(p.y * 800)}`;
    }).join('|');
}

export function cosineSimilarity(a, b) {
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

export function averageEmbedding(frames) {
    if (!frames.length) {
        return [];
    }

    const size = frames[0].embedding.length;
    const sum = new Array(size).fill(0);

    frames.forEach((frame) => {
        frame.embedding.forEach((value, i) => {
            sum[i] += value;
        });
    });

    return sum.map((value) => value / frames.length);
}

function averageGeometry(frames) {
    if (!frames.length || !frames[0].geometry?.length) {
        return [];
    }

    const size = frames[0].geometry.length;
    const sum = new Array(size).fill(0);

    frames.forEach((frame) => {
        frame.geometry?.forEach((value, i) => {
            sum[i] += value;
        });
    });

    return sum.map((value) => value / frames.length);
}

function variance(values) {
    if (values.length < 2) {
        return 0;
    }

    const mean = values.reduce((a, b) => a + b, 0) / values.length;
    return values.reduce((acc, v) => acc + (v - mean) ** 2, 0) / values.length;
}

/** Fast EAR-based blink — accepts quick natural blinks (300–550ms). */
export class EarBlinkTracker {
    constructor() {
        this.phase = 'open';
        this.baselineEar = null;
        this.closeStartedAt = 0;
        this.blinkDetected = false;
    }

    reset() {
        this.phase = 'open';
        this.baselineEar = null;
        this.closeStartedAt = 0;
        this.blinkDetected = false;
    }

    /**
     * @param {Landmark[]} landmarks
     * @param {number} now
     */
    update(landmarks, now) {
        if (this.blinkDetected) {
            return;
        }

        const ear = computeAverageEAR(landmarks);

        if (this.baselineEar === null) {
            this.baselineEar = ear;
        }

        const closeThreshold = Math.min(
            this.baselineEar * LIVENESS_CONFIG.earDropRatio,
            LIVENESS_CONFIG.earAbsoluteMax
        );
        const openThreshold = this.baselineEar * 0.88;

        if (this.phase === 'open') {
            if (ear < closeThreshold) {
                this.blinkDetected = true;
                this.phase = 'closing';
                this.closeStartedAt = now;
            } else if (ear > this.baselineEar) {
                this.baselineEar = this.baselineEar * 0.92 + ear * 0.08;
            }
            return;
        }

        if (this.phase === 'closing') {
            if (ear >= openThreshold) {
                this.phase = 'open';
                return;
            }

            if (now - this.closeStartedAt > LIVENESS_CONFIG.blinkRecoveryMaxMs) {
                this.phase = 'open';
            }
        }
    }
}

/** Mouth-width smile vs neutral baseline — sustained ~400ms. */
export class MouthSmileTracker {
    /**
     * @param {number|null} baselineMouthWidth
     */
    constructor(baselineMouthWidth) {
        this.baseline = baselineMouthWidth;
        this.smileStartedAt = null;
        this.smileSustained = false;
    }

    reset(baselineMouthWidth) {
        if (baselineMouthWidth !== undefined) {
            this.baseline = baselineMouthWidth;
        }
        this.smileStartedAt = null;
        this.smileSustained = false;
    }

    /**
     * @param {Landmark[]} landmarks
     * @param {number} now
     * @param {Record<string, number>} [blendshapes]
     */
    update(landmarks, now, blendshapes = {}) {
        if (this.smileSustained) {
            return;
        }

        const isSmiling = isSmileExpressionActive(landmarks, this.baseline, blendshapes);

        if (isSmiling) {
            if (!this.smileStartedAt) {
                this.smileStartedAt = now;
            } else if (now - this.smileStartedAt >= LIVENESS_CONFIG.sustainedSmileMs) {
                this.smileSustained = true;
            }
        } else {
            this.smileStartedAt = null;
        }
    }

    isSmiling(landmarks, blendshapes = {}) {
        return isSmileExpressionActive(landmarks, this.baseline, blendshapes);
    }
}

export function isSmilingLandmarks(landmarks, baselineMouthWidth, blendshapes = {}) {
    return isSmileExpressionActive(landmarks, baselineMouthWidth, blendshapes);
}

export class StepFrameCollector {
    /**
     * @param {string} challengeId
     * @param {{ baselineMouthWidth?: number|null, referenceSignature?: FaceSignature|null, chainedReferences?: FaceSignature[], chainStepIndices?: number[], stepNumber?: number }} options
     */
    constructor(challengeId, options = {}) {
        this.challengeId = challengeId;
        this.baselineMouthWidth = options.baselineMouthWidth ?? null;
        this.referenceSignature = options.referenceSignature ?? null;
        this.chainedReferences = options.chainedReferences ?? [];
        this.chainStepIndices = options.chainStepIndices ?? [];
        this.stepNumber = options.stepNumber ?? 0;
        /** @type {FrameSample[]} */
        this.frames = [];
        this.lastSampleAt = 0;
        this.consecutivePass = 0;
        this.blinkTracker = new EarBlinkTracker();
        this.smileTracker = new MouthSmileTracker(this.baselineMouthWidth);
        this.capturedBaselineMouth = null;
    }

    get frameCount() {
        return this.frames.length;
    }

    getMinFrames() {
        if (this.challengeId === 'humanVerify' || this.challengeId === 'lookStraight') {
            return LIVENESS_CONFIG.minFramesStraight;
        }
        if (this.challengeId === 'blink') {
            return LIVENESS_CONFIG.minFramesBlink;
        }
        if (this.challengeId === 'smile') {
            return LIVENESS_CONFIG.minFramesSmile;
        }
        return LIVENESS_CONFIG.minFramesDefault;
    }

    reset() {
        this.frames = [];
        this.lastSampleAt = 0;
        this.consecutivePass = 0;
        this.blinkTracker.reset();
        this.smileTracker.reset(this.baselineMouthWidth);
        this.capturedBaselineMouth = null;
    }

    shouldSample(now) {
        return now - this.lastSampleAt >= LIVENESS_CONFIG.sampleIntervalMs;
    }

    /**
     * @param {Landmark[]} landmarks
     * @param {Record<string, number>} blendshapes
     * @param {number} now
     * @param {number[]|null} [neuralEmbedding]
     * @param {{ humanPass?: boolean }} [options]
     */
    addSample(landmarks, blendshapes, now, neuralEmbedding = null, options = {}) {
        this.lastSampleAt = now;

        const faceSig = extractFaceSignature(landmarks, neuralEmbedding);
        const embedding = faceSig.embedding;
        const geometry = faceSig.geometry;
        let identitySimilarity = 1;

        const isFinalStep = this.challengeId === 'smile';
        const neuralThreshold = isFinalStep
            ? LIVENESS_CONFIG.neuralDistanceThresholdFinal
            : LIVENESS_CONFIG.neuralDistanceThreshold;

        if (this.chainedReferences?.length) {
            const embeddingThreshold = isFinalStep
                ? LIVENESS_CONFIG.smileIdentityThreshold
                : LIVENESS_CONFIG.chainIdentityThreshold;

            for (const ref of this.chainedReferences) {
                const cmp = compareFaceSignatures(faceSig, ref, {
                    embeddingThreshold,
                    geometryThreshold: LIVENESS_CONFIG.geometryIdentityThreshold,
                    structuralThreshold: LIVENESS_CONFIG.structuralIdentityThreshold,
                    neuralDistanceThreshold: neuralThreshold,
                    requireGeometry: isFinalStep,
                    requireNeural: Boolean(neuralEmbedding?.length && ref.neuralEmbedding?.length),
                });
                if (!cmp.matched) {
                    identitySimilarity = cmp.embeddingSim;
                    break;
                }
            }
        } else if (this.referenceSignature?.embedding?.length) {
            const cmp = compareFaceSignatures(faceSig, this.referenceSignature, {
                embeddingThreshold: LIVENESS_CONFIG.frameIdentityThreshold,
                geometryThreshold: LIVENESS_CONFIG.geometryIdentityThreshold,
                neuralDistanceThreshold: neuralThreshold,
                requireGeometry: this.challengeId === 'smile',
            });
            identitySimilarity = cmp.embeddingSim;
        }

        const mouthWidth = getNormalizedMouthWidth(landmarks);
        const ear = computeAverageEAR(landmarks);

        if (this.challengeId === 'blink') {
            this.blinkTracker.update(landmarks, now);
        }

        if (this.challengeId === 'smile') {
            this.smileTracker.update(landmarks, now, blendshapes);
        }

        if (this.challengeId === 'humanVerify' || this.challengeId === 'lookStraight') {
            if (!this.mouthWidthSamples) {
                this.mouthWidthSamples = [];
            }
            this.mouthWidthSamples.push(mouthWidth);
            if (this.mouthWidthSamples.length >= 4) {
                this.capturedBaselineMouth =
                    this.mouthWidthSamples.reduce((a, b) => a + b, 0) / this.mouthWidthSamples.length;
            }
        }

        this.frames.push({
            embedding,
            geometry,
            neuralEmbedding: neuralEmbedding || undefined,
            fingerprint: frameFingerprint(landmarks),
            yaw: getHeadYawOffset(landmarks),
            pitch: getHeadPitchOffset(landmarks),
            mouthWidth,
            ear,
            blendshapes,
            timestamp: now,
            identitySimilarity,
            noseZ: landmarks[1]?.z || 0,
            humanPass: options.humanPass !== false,
        });

        if (this.frames.length > LIVENESS_CONFIG.maxFramesPerStep) {
            this.frames.shift();
        }

        const passes = this.framePassesChallenge(this.frames[this.frames.length - 1]);
        this.consecutivePass = passes ? this.consecutivePass + 1 : 0;
    }

    framePassesChallenge(frame) {
        if (this.chainedReferences?.length && this.challengeId !== 'humanVerify') {
            const isFinalStep = this.challengeId === 'smile';
            const neuralThreshold = isFinalStep
                ? LIVENESS_CONFIG.neuralDistanceThresholdFinal
                : LIVENESS_CONFIG.neuralDistanceThreshold;

            for (const ref of this.chainedReferences) {
                const cmp = compareFaceSignatures(
                    {
                        embedding: frame.embedding,
                        geometry: frame.geometry || [],
                        neuralEmbedding: frame.neuralEmbedding,
                    },
                    ref,
                    {
                        embeddingThreshold: isFinalStep
                            ? LIVENESS_CONFIG.smileIdentityThreshold
                            : LIVENESS_CONFIG.chainIdentityThreshold,
                        geometryThreshold: LIVENESS_CONFIG.geometryIdentityThreshold,
                        structuralThreshold: LIVENESS_CONFIG.structuralIdentityThreshold,
                        neuralDistanceThreshold: neuralThreshold,
                        requireGeometry: isFinalStep,
                        requireNeural: Boolean(frame.neuralEmbedding?.length && ref.neuralEmbedding?.length),
                    }
                );
                if (!cmp.matched) {
                    return false;
                }
            }
        } else if (
            this.referenceSignature?.embedding?.length
            && this.challengeId !== 'humanVerify'
            && this.challengeId !== 'lookStraight'
        ) {
            const cmp = compareFaceSignatures(
                { embedding: frame.embedding, geometry: frame.geometry || [] },
                this.referenceSignature,
                {
                    embeddingThreshold: LIVENESS_CONFIG.frameIdentityThreshold,
                    geometryThreshold: LIVENESS_CONFIG.geometryIdentityThreshold,
                    structuralThreshold: LIVENESS_CONFIG.structuralIdentityThreshold,
                    requireGeometry: this.challengeId === 'smile',
                }
            );
            if (!cmp.matched) {
                return false;
            }
        }

        const { challengeId } = this;
        const { yaw } = frame;

        switch (challengeId) {
            case 'lookStraight':
                return Math.abs(yaw) <= LIVENESS_CONFIG.yawStraightThreshold;
            case 'lookRight':
                return yaw < -LIVENESS_CONFIG.yawTurnThreshold;
            case 'lookLeft':
                return yaw > LIVENESS_CONFIG.yawTurnThreshold;
            case 'humanVerify':
                return true;
            case 'smile':
                return this.smileTracker.smileSustained;
            case 'blink':
                return this.blinkTracker.blinkDetected;
            default:
                return false;
        }
    }

    hasEnoughFrames() {
        if (this.challengeId === 'blink' && this.blinkTracker.blinkDetected) {
            return this.frames.length >= LIVENESS_CONFIG.minFramesBlink;
        }
        return this.frames.length >= this.getMinFrames();
    }

    hasSustainedChallenge() {
        if (this.challengeId === 'humanVerify') {
            return this.frames.length >= LIVENESS_CONFIG.minFramesStraight;
        }
        if (this.challengeId === 'blink') {
            return this.blinkTracker.blinkDetected;
        }
        if (this.challengeId === 'smile') {
            return this.smileTracker.smileSustained;
        }
        return this.consecutivePass >= LIVENESS_CONFIG.sustainedChallengeFrames;
    }

    analyze(referenceSignature, options = {}) {
        if (!this.hasEnoughFrames()) {
            return { pass: false, reason: 'Collecting frames…', code: 'collecting' };
        }

        if (!this.hasSustainedChallenge()) {
            return { pass: false, reason: 'Keep following the instruction', code: 'challenge' };
        }

        const isBlinkStep = this.challengeId === 'blink';
        const isTurnStep = this.challengeId === 'lookRight' || this.challengeId === 'lookLeft';
        const isInitStep = this.challengeId === 'humanVerify';
        const isSmileStep = this.challengeId === 'smile';
        const isBaseStep = isInitStep || this.challengeId === 'lookStraight';
        /** @type {{ uniqueRatio?: number }} */
        let livenessResult = { uniqueRatio: 1 };

        if (isInitStep) {
            const initCheck = validateStep1InitFromFrames(this.frames);
            if (!initCheck.valid) {
                return {
                    pass: false,
                    reason: initCheck.reason,
                    code: initCheck.code === 'collecting' ? 'collecting' : initCheck.code,
                };
            }

            const humanPassRatio = this.frames.filter((f) => f.humanPass).length / this.frames.length;
            if (humanPassRatio < 0.78) {
                return {
                    pass: false,
                    reason: 'Live human face required — photos, masks, or non-human faces are not allowed.',
                    code: SECURITY_CODES.NOT_HUMAN,
                };
            }

            livenessResult = { uniqueRatio: initCheck.uniqueRatio ?? 1 };
        } else {
            const liveness = validateMultiFrameLiveness(this.frames, {
                isBlinkStep,
                isTurnStep,
                minFrames: this.getMinFrames(),
            });

            if (!liveness.valid) {
                return {
                    pass: false,
                    reason: liveness.reason,
                    code: liveness.code === 'collecting' ? 'collecting' : liveness.code,
                };
            }
            livenessResult = liveness;
        }

        if (isBlinkStep && !this.blinkTracker.blinkDetected) {
            return { pass: false, reason: 'Blink not detected yet', code: 'challenge' };
        }

        const stepEmbedding = averageEmbedding(this.frames);
        const stepSignature = {
            embedding: stepEmbedding,
            geometry: averageGeometry(this.frames),
            neuralEmbedding: pickStepNeuralEmbedding(this.frames) || undefined,
        };
        const checkGeometry = isSmileStep;
        const identityThreshold = isSmileStep
            ? LIVENESS_CONFIG.smileIdentityThreshold
            : LIVENESS_CONFIG.chainIdentityThreshold;
        const neuralThreshold = isSmileStep
            ? LIVENESS_CONFIG.neuralDistanceThresholdFinal
            : LIVENESS_CONFIG.neuralDistanceThreshold;
        const maxFailRatio = isSmileStep
            ? LIVENESS_CONFIG.smileMaxIdentityFailRatio
            : LIVENESS_CONFIG.maxIdentityFailRatio;

        const identityReferences = options.chainedReferences?.length
            ? options.chainedReferences
            : (referenceSignature?.embedding?.length && !isBaseStep ? [referenceSignature] : []);

        if (identityReferences.length && !isBaseStep) {
            for (const ref of identityReferences) {
                if (!ref?.embedding?.length) {
                    continue;
                }

                const failCount = this.frames.filter((f) => {
                    const cmp = compareFaceSignatures(
                        {
                            embedding: f.embedding,
                            geometry: f.geometry || [],
                            neuralEmbedding: f.neuralEmbedding,
                        },
                        ref,
                        {
                            embeddingThreshold: identityThreshold,
                            geometryThreshold: LIVENESS_CONFIG.geometryIdentityThreshold,
                            structuralThreshold: LIVENESS_CONFIG.structuralIdentityThreshold,
                            neuralDistanceThreshold: neuralThreshold,
                            requireGeometry: checkGeometry,
                            requireNeural: Boolean(f.neuralEmbedding?.length && ref.neuralEmbedding?.length),
                        }
                    );
                    return !cmp.matched;
                }).length;
                const failRatio = failCount / this.frames.length;

                if (failRatio > maxFailRatio) {
                    return {
                        pass: false,
                        reason: isSmileStep
                            ? 'Identity chain broken — face inconsistent with Steps 1–4.'
                            : 'Identity chain broken — same person must complete all steps.',
                        code: SECURITY_CODES.IDENTITY_MISMATCH,
                    };
                }

                const cmp = compareFaceSignatures(stepSignature, ref, {
                    embeddingThreshold: identityThreshold,
                    geometryThreshold: LIVENESS_CONFIG.geometryIdentityThreshold,
                    structuralThreshold: LIVENESS_CONFIG.structuralIdentityThreshold,
                    neuralDistanceThreshold: neuralThreshold,
                    requireGeometry: checkGeometry,
                    requireNeural: Boolean(stepSignature.neuralEmbedding?.length && ref.neuralEmbedding?.length),
                });

                if (!cmp.matched) {
                    return {
                        pass: false,
                        reason: isSmileStep
                            ? 'Final verification failed — face does not match Steps 1–4.'
                            : 'Identity chain broken — face does not match a prior step.',
                        code: SECURITY_CODES.IDENTITY_MISMATCH,
                        embeddingSim: cmp.embeddingSim,
                        geometrySim: cmp.geometrySim,
                    };
                }
            }
        }

        const baselineMouthWidth = (this.challengeId === 'humanVerify' || this.challengeId === 'lookStraight')
            ? this.frames.reduce((sum, f) => sum + f.mouthWidth, 0) / this.frames.length
            : this.capturedBaselineMouth;

        const baseSignature = isBaseStep
            ? stepSignature
            : null;

        return {
            pass: true,
            embedding: stepEmbedding,
            faceSignature: baseSignature || stepSignature,
            baselineMouthWidth,
            uniqueRatio: livenessResult.uniqueRatio,
            frameCount: this.frames.length,
        };
    }
}

export class LivenessSession {
    constructor() {
        this.challenges = buildSessionChallenges();
        this.currentIndex = 0;
        this.referenceEmbedding = null;
        /** @type {FaceSignature|null} */
        this.baseFaceSignature = null;
        /** @type {Array<{ stepId: string, signature: FaceSignature }>} */
        this.stepRecords = [];
        this.baselineMouthWidth = null;
        this.collector = null;
        this.completed = new Set();
    }

    get totalSteps() {
        return this.challenges.length;
    }

    get currentChallenge() {
        return this.challenges[this.currentIndex] || null;
    }

    get completedCount() {
        return this.completed.size;
    }

    get finalStepId() {
        return this.challenges[this.challenges.length - 1]?.id ?? null;
    }

    reset() {
        this.challenges = buildSessionChallenges();
        this.currentIndex = 0;
        this.referenceEmbedding = null;
        this.baseFaceSignature = null;
        this.stepRecords = [];
        this.baselineMouthWidth = null;
        this.collector = null;
        this.completed = new Set();
        this.ensureCollector();
    }

    ensureCollector() {
        const challenge = this.currentChallenge;
        if (!challenge) {
            this.collector = null;
            return;
        }

        if (!this.collector || this.collector.challengeId !== challenge.id) {
            const chain = this.getChainedReferencesForStep(challenge.stepNumber);
            this.collector = new StepFrameCollector(challenge.id, {
                baselineMouthWidth: this.baselineMouthWidth,
                referenceSignature: this.baseFaceSignature,
                chainedReferences: chain.refs,
                chainStepIndices: chain.indices,
                stepNumber: challenge.stepNumber,
            });
        }
    }

    completeCurrentStep(result) {
        const challenge = this.currentChallenge;
        if (!challenge || !result.pass) {
            return false;
        }

        this.completed.add(challenge.id);

        if (challenge.isBaseStep && result.faceSignature) {
            this.baseFaceSignature = result.faceSignature;
            this.referenceEmbedding = result.faceSignature.embedding;
            if (result.baselineMouthWidth != null && result.baselineMouthWidth > 0) {
                this.baselineMouthWidth = result.baselineMouthWidth;
            }
        } else if (challenge.isBaseStep && result.embedding) {
            this.referenceEmbedding = result.embedding;
            if (result.baselineMouthWidth != null && result.baselineMouthWidth > 0) {
                this.baselineMouthWidth = result.baselineMouthWidth;
            }
        }

        if (result.faceSignature?.embedding?.length && challenge.id !== 'smile') {
            this.stepRecords.push({
                stepId: challenge.id,
                signature: result.faceSignature,
            });
        }

        this.currentIndex += 1;
        this.collector = null;
        this.ensureCollector();
        return true;
    }

    isComplete() {
        return this.completed.size >= this.totalSteps;
    }

    /**
     * Chained biometric lock references per step:
     * Step 2 → Step 1 | Step 3 → Step 2 + Step 1 | Step 4 → Step 3 + Step 1 | Step 5 → Steps 1–4
     */
    getChainedReferencesForStep(stepNumber) {
        const records = this.stepRecords;
        if (stepNumber <= 1 || !records.length) {
            return { refs: [], indices: [] };
        }

        if (stepNumber === 2) {
            return {
                refs: [records[0]?.signature].filter((s) => s?.embedding?.length),
                indices: [1],
            };
        }

        if (stepNumber === 3) {
            return {
                refs: [records[1]?.signature, records[0]?.signature].filter((s) => s?.embedding?.length),
                indices: [2, 1],
            };
        }

        if (stepNumber === 4) {
            return {
                refs: [records[2]?.signature, records[0]?.signature].filter((s) => s?.embedding?.length),
                indices: [3, 1],
            };
        }

        return {
            refs: records.slice(0, 4).map((r) => r.signature).filter((s) => s?.embedding?.length),
            indices: [1, 2, 3, 4],
        };
    }

    /** @deprecated Use getChainedReferencesForStep(5).refs */
    getIdentityReferences() {
        return this.getChainedReferencesForStep(5).refs;
    }

    /** Re-open the last step after photo-hold failure (stay on Step 5, not Step 1). */
    reopenLastStep() {
        const lastIdx = this.challenges.length - 1;
        if (lastIdx < 0) {
            return;
        }

        const lastId = this.challenges[lastIdx].id;
        this.completed.delete(lastId);
        this.currentIndex = lastIdx;
        this.collector = null;
        this.ensureCollector();
    }
}

/** @deprecated Use isSmilingLandmarks */
export function isSmiling(blendshapes) {
    return ((blendshapes.mouthSmileLeft || 0) + (blendshapes.mouthSmileRight || 0)) / 2 >= 0.42;
}

export function checkFrameIdentity(currentSignature, referenceSignature) {
    if (!referenceSignature?.embedding?.length) {
        return { matched: true, similarity: 1, geometrySim: 1 };
    }

    const current = typeof currentSignature === 'object' && currentSignature.embedding
        ? currentSignature
        : { embedding: currentSignature, geometry: [] };

    const cmp = compareFaceSignatures(current, referenceSignature, {
        embeddingThreshold: LIVENESS_CONFIG.frameIdentityThreshold,
        geometryThreshold: LIVENESS_CONFIG.geometryIdentityThreshold,
        requireGeometry: Boolean(referenceSignature.geometry?.length),
    });

    return {
        matched: cmp.matched,
        similarity: cmp.embeddingSim,
        geometrySim: cmp.geometrySim,
    };
}

export { SECURITY_CODES };

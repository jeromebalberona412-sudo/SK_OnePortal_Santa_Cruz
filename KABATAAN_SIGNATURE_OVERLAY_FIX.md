# Kabataan Signature Overlay Fix

## Changes Made

### 1. Signature Display Structure
**New Layout:**
```
┌─────────────────────────────────┐
│   [Signature Image Overlay]     │ ← Centered, overlaid on name
├─────────────────────────────────┤
│     Auto-populated Name         │ ← Underlined
├─────────────────────────────────┤
│ Name and Signature of Participant│ ← Label below underline
└─────────────────────────────────┘
```

### 2. Key Features

**Signature Overlay:**
- Signature image is **overlaid/centered** on top of the name
- Smaller size (max 180px × 35px) to fit within name area
- Positioned absolutely above the name field
- Uses `z-index` to layer properly

**Name Field:**
- Auto-populated from respondent name fields
- Format: FirstName MiddleName LastName Suffix
- Centered text alignment
- Underlined with 1px solid black border

**Label Position:**
- "Name and Signature of Participant" appears **below** the underline
- Small font (9px), gray color (#666)
- Centered alignment

### 3. Files Modified

#### A. View File: `SK_Officials/app/modules/Kabataan/views/kabataan.blade.php`

**Manual Entry Form (Add/Edit):**
```html
<div class="kkf-sig-section">
    <div class="kkf-sig-container">
        <!-- Signature overlay (centered on top of name) -->
        <div class="kkf-sig-overlay" id="kabataanSignatureOverlay" style="display: none;">
            <img id="kabataanSignaturePreview" class="kkf-sig-overlay-img" alt="Signature">
        </div>
        
        <!-- Name field with underline -->
        <div class="kkf-sig-name-wrapper">
            <input type="text" id="kabataanSignatureName" readonly class="kkf-sig-name-input">
        </div>
        
        <!-- Label below the underline -->
        <div class="kkf-sig-label-bottom">Name and Signature of Participant</div>
        
        <!-- Sign button -->
        <button type="button" id="kabataanSignatureTrigger">Sign</button>
        
        <input type="hidden" id="kabataanSignature" value="">
    </div>
</div>
```

**View-Only Form:**
```html
<div class="kkf-sig-section">
    <div class="kkf-sig-container">
        <!-- Signature overlay -->
        <div class="kkf-sig-overlay" id="vSignatureOverlay" style="display: none;">
            <img id="vSignature" class="kkf-sig-overlay-img" alt="Signature">
        </div>
        
        <!-- Name field -->
        <div class="kkf-sig-name-wrapper">
            <span id="vSignatureText" class="kkf-sig-name-input"></span>
        </div>
        
        <!-- Label below -->
        <div class="kkf-sig-label-bottom">Name and Signature of Participant</div>
    </div>
</div>
```

#### B. CSS File: `SK_Officials/app/modules/Kabataan/assets/css/kabataan.css`

**Signature Container:**
```css
.kkf-sig-container {
    position: relative;
    display: inline-block;
    width: 280px;
}
```

**Signature Overlay (Centered on Name):**
```css
.kkf-sig-overlay {
    position: absolute;
    top: -25px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    pointer-events: none;
    z-index: 1;
}

.kkf-sig-overlay-img {
    max-width: 180px;
    max-height: 35px;
    height: auto;
    display: block;
}
```

**Name Field:**
```css
.kkf-sig-name-wrapper {
    position: relative;
    z-index: 0;
}

.kkf-sig-name-input {
    width: 100%;
    border: none;
    border-bottom: 1px solid #000;
    padding: 2px 4px;
    font-size: 11px;
    background: transparent;
    cursor: default;
    text-align: center;
    outline: none;
}
```

**Label Below Underline:**
```css
.kkf-sig-label-bottom {
    font-size: 9px;
    font-weight: 400;
    color: #666;
    margin-top: 4px;
    text-align: center;
    letter-spacing: 0.02em;
}
```

#### C. JavaScript File: `SK_Officials/app/modules/Kabataan/assets/js/kabataan.js`

**Updated Save Signature Function:**
```javascript
function saveSignature() {
    if (!hasSignature) {
        alert('Please provide a signature before saving.');
        return;
    }
    
    // Convert canvas to base64
    const signatureData = canvas.toDataURL('image/png');
    
    // Save to hidden input
    signatureInput.value = signatureData;
    
    // Show signature overlay (centered on top of name)
    const signatureOverlay = document.getElementById('kabataanSignatureOverlay');
    if (signaturePreview && signatureOverlay) {
        signaturePreview.src = signatureData;
        signatureOverlay.style.display = 'flex';
    }
    
    // Hide the sign button
    if (triggerBtn) {
        triggerBtn.style.display = 'none';
    }
    
    // Close modal
    closeSignaturePad();
}
```

## Visual Layout

### Before Signing:
```
┌─────────────────────────────────┐
│     Juan Dela Cruz Jr.          │ ← Auto-populated name (underlined)
├─────────────────────────────────┤
│ Name and Signature of Participant│ ← Label
├─────────────────────────────────┤
│         [Sign Button]            │
└─────────────────────────────────┘
```

### After Signing:
```
┌─────────────────────────────────┐
│    ~~~signature image~~~         │ ← Signature overlaid/centered
│     Juan Dela Cruz Jr.          │ ← Name (underlined)
├─────────────────────────────────┤
│ Name and Signature of Participant│ ← Label
└─────────────────────────────────┘
```

## Result

✅ **Signature Overlays on Name**
- Signature image centered on top of name field
- Smaller size (180px × 35px max) fits within name area
- Uses absolute positioning with z-index layering

✅ **Label Below Underline**
- "Name and Signature of Participant" appears below the name
- Small, subtle styling (9px, gray)
- Centered alignment

✅ **Clean Layout**
- Signature and name in same visual space
- Professional appearance
- Matches reference image format

✅ **Auto-Populate Still Works**
- Name automatically fills from respondent fields
- Format: FirstName MiddleName LastName Suffix
- Real-time updates

## Testing Checklist

- [ ] Signature overlays centered on name field
- [ ] Signature size is appropriate (fits within name area)
- [ ] Label "Name and Signature of Participant" appears below underline
- [ ] Name auto-populates from respondent fields
- [ ] Sign button shows when name is filled
- [ ] Sign button hides after signing
- [ ] Signature displays correctly in Add modal
- [ ] Signature displays correctly in Edit modal
- [ ] Signature displays correctly in View modal
- [ ] Layout is centered and aligned properly

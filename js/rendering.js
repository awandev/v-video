// Advanced rendering functions - LUT, camera, effects

let customCubeLut = null;

function resetCameraFraming() {
    const camZoom = document.getElementById('camZoom');
    const camPanX = document.getElementById('camPanX');
    const camPanY = document.getElementById('camPanY');
    
    if (camZoom) camZoom.value = 1.0;
    if (camPanX) camPanX.value = 0;
    if (camPanY) camPanY.value = 0;
    
    renderFrame();
}

function loadCubeLutFile(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const lines = e.target.result.split(/\r?\n/);
        let size = 0;
        const rgbValues = [];
        for (let line of lines) {
            line = line.trim();
            if (!line || line.startsWith('#')) continue;
            if (line.startsWith('LUT_3D_SIZE')) { 
                size = parseInt(line.split(/\s+/)[1], 10); 
                continue; 
            }
            if (/^[A-Za-z]/.test(line)) continue;
            const parts = line.split(/\s+/);
            if (parts.length >= 3) {
                const r = parseFloat(parts[0]), g = parseFloat(parts[1]), b = parseFloat(parts[2]);
                if (!isNaN(r) && !isNaN(g) && !isNaN(b)) rgbValues.push(r, g, b);
            }
        }
        if (size > 1 && rgbValues.length >= size * size * size * 3) {
            customCubeLut = { size, data: new Float32Array(rgbValues) };
            const lutSelect = document.getElementById('lutPresetSelect');
            if (lutSelect) lutSelect.value = 'custom_cube';
            renderFrame();
        } else {
            alert('Format file .CUBE tidak valid.');
        }
    };
    reader.readAsText(file);
}

function drawMediaWithLutAndPanZoom(ctx, media, mW, mH, cW, cH) {
    const lutPresetEl = document.getElementById('lutPresetSelect');
    const intensityEl = document.getElementById('lutIntensitySlider');
    const camZoomEl = document.getElementById('camZoom');
    const camPanXEl = document.getElementById('camPanX');
    const camPanYEl = document.getElementById('camPanY');
    
    const preset = lutPresetEl ? lutPresetEl.value : 'none';
    const intensity = intensityEl ? parseFloat(intensityEl.value || 0.85) : 0.85;
    const camZoom = camZoomEl ? parseFloat(camZoomEl.value || 1.0) : 1.0;
    const camPanX = camPanXEl ? parseFloat(camPanXEl.value || 0) : 0;
    const camPanY = camPanYEl ? parseFloat(camPanYEl.value || 0) : 0;

    ctx.save();
    
    // Apply LUT filters (CSS filters sebagai approximation)
    if (preset === 'teal_orange') {
        ctx.filter = `contrast(${100 + 22 * intensity}%) saturate(${100 + 30 * intensity}%) brightness(${100 - 4 * intensity}%)`;
    } else if (preset === 'moody_dark') {
        ctx.filter = `contrast(${100 + 32 * intensity}%) saturate(${100 - 28 * intensity}%) brightness(${100 - 14 * intensity}%)`;
    } else if (preset === 'bleach_grit') {
        ctx.filter = `contrast(${100 + 45 * intensity}%) saturate(${100 - 55 * intensity}%) brightness(${100 - 8 * intensity}%)`;
    } else if (preset === 'cold_steel') {
        ctx.filter = `contrast(${100 + 18 * intensity}%) saturate(${100 - 20 * intensity}%) hue-rotate(${-12 * intensity}deg)`;
    } else if (preset === 'golden_hour') {
        ctx.filter = `contrast(${100 + 12 * intensity}%) saturate(${100 + 35 * intensity}%) sepia(${28 * intensity}%)`;
    } else if (preset === 'kodachrome') {
        ctx.filter = `contrast(${100 + 20 * intensity}%) sepia(${35 * intensity}%) saturate(${100 + 15 * intensity}%)`;
    } else if (preset === 'bw_noir') {
        ctx.filter = `grayscale(${100 * intensity}%) contrast(${100 + 42 * intensity}%) brightness(${100 - 6 * intensity}%)`;
    } else if (preset === 'vibrant_pop') {
        ctx.filter = `contrast(${100 + 18 * intensity}%) saturate(${100 + 45 * intensity}%) brightness(${100 + 4 * intensity}%)`;
    }

    // Camera zoom & pan (scale media, translate)
    const baseScale = Math.max(cW / mW, cH / mH) * camZoom;
    const dW = mW * baseScale, dH = mH * baseScale;
    const dx = ((cW - dW) / 2) + camPanX, dy = ((cH - dH) / 2) + camPanY;

    ctx.drawImage(media, dx, dy, dW, dH);
    ctx.filter = 'none';

    // Apply custom CUBE LUT (pixel-by-pixel color mapping)
    if (preset === 'custom_cube' && customCubeLut && intensity > 0.01) {
        try {
            const imgData = ctx.getImageData(0, 0, cW, cH);
            const px = imgData.data, sz = customCubeLut.size, inv255 = (sz - 1) / 255, lut = customCubeLut.data;
            for (let i = 0; i < px.length; i += 4) {
                const rIdx = Math.round(px[i] * inv255), gIdx = Math.round(px[i+1] * inv255), bIdx = Math.round(px[i+2] * inv255);
                const off = ((bIdx * sz * sz) + (gIdx * sz) + rIdx) * 3;
                px[i]   += (lut[off]*255 - px[i]) * intensity;
                px[i+1] += (lut[off+1]*255 - px[i+1]) * intensity;
                px[i+2] += (lut[off+2]*255 - px[i+2]) * intensity;
            }
            ctx.putImageData(imgData, 0, 0);
        } catch (err) {
            console.warn('CUBE LUT apply error:', err);
        }
    }

    // Overlay LUT color grading (visual enhancement)
    if (preset !== 'none' && preset !== 'custom_cube' && intensity > 0) {
        ctx.save();
        if (preset === 'teal_orange') {
            ctx.globalCompositeOperation = 'overlay';
            const grad = ctx.createLinearGradient(0, 0, cW, cH);
            grad.addColorStop(0, `rgba(255, 135, 50, ${0.22 * intensity})`);
            grad.addColorStop(1, `rgba(0, 140, 175, ${0.28 * intensity})`);
            ctx.fillStyle = grad; ctx.fillRect(0, 0, cW, cH);
        } else if (preset === 'moody_dark' || preset === 'bleach_grit') {
            const rad = ctx.createRadialGradient(cW/2, cH/2, cW*0.25, cW/2, cH/2, cH*0.75);
            rad.addColorStop(0, 'rgba(0,0,0,0)'); 
            rad.addColorStop(1, `rgba(0,0,0,${0.55 * intensity})`);
            ctx.fillStyle = rad; ctx.fillRect(0, 0, cW, cH);
        } else if (preset === 'cold_steel') {
            ctx.globalCompositeOperation = 'soft-light';
            ctx.fillStyle = `rgba(30, 110, 220, ${0.32 * intensity})`; 
            ctx.fillRect(0, 0, cW, cH);
        } else if (preset === 'golden_hour') {
            ctx.globalCompositeOperation = 'soft-light';
            ctx.fillStyle = `rgba(255, 160, 40, ${0.30 * intensity})`; 
            ctx.fillRect(0, 0, cW, cH);
        }
        ctx.restore();
    }
    
    ctx.restore();
}

function setPlaybackSpeed(speed) {
    const exportSpeedEl = document.getElementById('exportSpeedSelect');
    if (exportSpeedEl) exportSpeedEl.value = speed;
    playbackSpeed = speed;
}

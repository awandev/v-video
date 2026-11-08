// Canvas & DOM elements
const canvas = document.getElementById('hudCanvas');
const ctx = canvas.getContext('2d');
const hiddenVideo = document.getElementById('hiddenVideo');

// State variables
let bgImage = null;
let useVideoBg = false;
let hrValues = [];
let currentVideoSec = 945;
let introOffsetSec = 0;
let workoutDurationSec = 1800;
let hrIntervalSec = 5;
let isPlayingSim = false;
let animFrameId = null;
let lastTick = 0;
let pulsePhase = 0;

let hudPos = { x: 56, y: 440 };
let isDragging = false;
let dragOffset = { x: 0, y: 0 };

function getSupportedMimeType() {
    const types = [
        'video/mp4;codecs=avc1.42E01E,mp4a.40.2',
        'video/mp4;codecs=avc1',
        'video/mp4',
        'video/webm;codecs=h264',
        'video/webm;codecs=vp9',
        'video/webm'
    ];
    for (const t of types) {
        if (MediaRecorder.isTypeSupported(t)) return t;
    }
    return '';
}

const bestMime = getSupportedMimeType();
const isNativeMp4 = bestMime.includes('mp4') || bestMime.includes('h264');

function changeAspectRatio(ratio) {
    if (ratio === '9:16') {
        canvas.width = 720;
        canvas.height = 1280;
        hudPos = { x: 40, y: 920 };
        document.getElementById('hudScale').value = 0.9;
    } else if (ratio === '1:1') {
        canvas.width = 1080;
        canvas.height = 1080;
        hudPos = { x: 50, y: 780 };
        document.getElementById('hudScale').value = 1.0;
    } else {
        canvas.width = 1280;
        canvas.height = 720;
        hudPos = { x: 56, y: 440 };
        document.getElementById('hudScale').value = 1.0;
    }
    renderFrame();
}

function drawMediaCover(ctx, media, mW, mH, cW, cH) {
    const scale = Math.max(cW / mW, cH / mH);
    const dW = mW * scale;
    const dH = mH * scale;
    const dx = (cW - dW) / 2;
    const dy = (cH - dH) / 2;
    ctx.drawImage(media, dx, dy, dW, dH);
}

function createDefaultBackground() {
    const grad = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
    grad.addColorStop(0, '#1e293b');
    grad.addColorStop(1, '#0f172a');
    ctx.fillStyle = grad;
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = 'rgba(255,255,255,0.14)';
    ctx.font = 'bold 24px Inter, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('AREA VIDEO WORKOUT ANDA', canvas.width / 2, canvas.height / 2 - 10);
    ctx.font = '15px Inter, sans-serif';
    ctx.fillText('Pilih Rasio 9:16 untuk Reels/TikTok atau 16:9 untuk YouTube', canvas.width / 2, canvas.height / 2 + 20);
    ctx.textAlign = 'left';
}

function parseHrInput() {
    const raw = document.getElementById('hrDataInput').value;
    const nums = raw.split(/[\s,;]+/).map(v => parseInt(v.trim(), 10)).filter(v => !isNaN(v) && v >= 35 && v <= 240);
    hrValues = nums.length > 0 ? nums : [145];
    document.getElementById('hrCountBadge').innerText = `Total data: ${hrValues.length} titik (${formatMMSS(hrValues.length * hrIntervalSec)})`;
    renderFrame();
}

function generateSampleWorkout() {
    const totalPoints = Math.ceil(workoutDurationSec / hrIntervalSec);
    let sample = [];
    for (let i = 0; i < totalPoints; i++) {
        const progress = i / totalPoints;
        let baseBpm = 105;
        if (progress < 0.15) baseBpm = 100 + (progress / 0.15) * 35;
        else if (progress < 0.85) baseBpm = 140 + Math.sin(i * 0.35) * 14 + (Math.random() * 4 - 2);
        else baseBpm = 145 - ((progress - 0.85) / 0.15) * 35;
        sample.push(Math.round(baseBpm));
    }
    const idx1545 = Math.floor(945 / hrIntervalSec);
    if (sample[idx1545] !== undefined) sample[idx1545] = 145;
    document.getElementById('hrDataInput').value = sample.join(', ');
    parseHrInput();
}

function formatMMSS(sec) {
    sec = Math.max(0, Math.floor(sec));
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
}

function updateTiming() {
    const trimStartMinEl = document.getElementById('trimStartMin');
    const trimStartSecEl = document.getElementById('trimStartSec');
    const trimEndMinEl = document.getElementById('trimEndMin');
    const trimEndSecEl = document.getElementById('trimEndSec');
    const hrIntervalEl = document.getElementById('hrInterval');

    const trimStartMin = trimStartMinEl ? parseInt(trimStartMinEl.value || 0, 10) : 0;
    const trimStartSec = trimStartSecEl ? parseInt(trimStartSecEl.value || 0, 10) : 0;
    introOffsetSec = (trimStartMin * 60) + trimStartSec;

    const trimEndMin = trimEndMinEl ? parseInt(trimEndMinEl.value || 20, 10) : 20;
    const trimEndSec = trimEndSecEl ? parseInt(trimEndSecEl.value || 0, 10) : 0;
    const trimEndTotalSec = (trimEndMin * 60) + trimEndSec;
    workoutDurationSec = Math.max(5, trimEndTotalSec - introOffsetSec);
    
    hrIntervalSec = hrIntervalEl ? Math.max(1, parseInt(hrIntervalEl.value || 5, 10)) : 5;

    const totalVideoMax = introOffsetSec + workoutDurationSec;
    const slider = document.getElementById('timelineSlider');
    if (slider) {
        slider.max = totalVideoMax;
        slider.value = Math.min(currentVideoSec, totalVideoMax);
    }
    
    const lblTime = document.getElementById('lblVideoTime');
    const lblStart = document.getElementById('lblTrimStartMarker');
    const lblEnd = document.getElementById('lblTrimEndMarker');
    if (lblTime) lblTime.innerText = formatMMSS(currentVideoSec);
    if (lblStart) lblStart.innerText = `✂️ Mulai: ${formatMMSS(introOffsetSec)}`;
    if (lblEnd) lblEnd.innerText = `✂️ Selesai: ${formatMMSS(trimEndTotalSec)}`;
    
    parseHrInput();
}

function getHeartRateZone(bpm) {
    const userAgeEl = document.getElementById('userAge');
    const age = userAgeEl ? parseInt(userAgeEl.value || 28, 10) : 28;
    const maxHr = 220 - age;
    const pct = (bpm / maxHr) * 100;
    if (pct >= 90) return { label: 'ZONE 5 (MAX EFFORT)', color: '#ef4444' };
    if (pct >= 80) return { label: 'ZONE 4 (ANAEROBIC)',  color: '#f97316' };
    if (pct >= 70) return { label: 'ZONE 3 (AEROBIC)',    color: '#eab308' };
    if (pct >= 60) return { label: 'ZONE 2 (FAT BURN)',   color: '#22c55e' };
    return { label: 'ZONE 1 (WARM UP)', color: '#38bdf8' };
}

function drawHeartIcon(ctx, cx, cy, size, color) {
    ctx.save();
    ctx.fillStyle = color;
    ctx.shadowColor = color;
    ctx.shadowBlur = 14;
    ctx.beginPath();
    const topCurveHeight = size * 0.3;
    ctx.moveTo(cx, cy + topCurveHeight);
    ctx.bezierCurveTo(cx, cy, cx - size / 2, cy, cx - size / 2, cy + topCurveHeight);
    ctx.bezierCurveTo(cx - size / 2, cy + (size + topCurveHeight) / 2, cx, cy + (size + topCurveHeight) / 1.8, cx, cy + size);
    ctx.bezierCurveTo(cx, cy + (size + topCurveHeight) / 1.8, cx + size / 2, cy + (size + topCurveHeight) / 2, cx + size / 2, cy + topCurveHeight);
    ctx.bezierCurveTo(cx + size / 2, cy, cx, cy, cx, cy + topCurveHeight);
    ctx.closePath();
    ctx.fill();
    ctx.restore();
}

function drawStopwatchIcon(ctx, cx, cy, radius) {
    ctx.save();
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 3;
    ctx.beginPath(); ctx.arc(cx, cy, radius, 0, Math.PI * 2); ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(cx - 5, cy - radius - 4); ctx.lineTo(cx + 5, cy - radius - 4);
    ctx.moveTo(cx, cy - radius - 4); ctx.lineTo(cx, cy - radius);
    ctx.moveTo(cx, cy); ctx.lineTo(cx, cy - radius * 0.55);
    ctx.moveTo(cx, cy); ctx.lineTo(cx + radius * 0.45, cy);
    ctx.stroke();
    ctx.restore();
}

function renderFrame() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const greenScreenEl = document.getElementById('greenScreenMode');
    const isGreenScreen = greenScreenEl ? greenScreenEl.checked : false;
    if (isGreenScreen) {
        ctx.fillStyle = '#00FF00';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    } else if (useVideoBg && hiddenVideo.readyState >= 2) {
        drawMediaWithLutAndPanZoom(ctx, hiddenVideo, hiddenVideo.videoWidth, hiddenVideo.videoHeight, canvas.width, canvas.height);
    } else if (bgImage) {
        drawMediaWithLutAndPanZoom(ctx, bgImage, bgImage.width, bgImage.height, canvas.width, canvas.height);
    } else {
        createDefaultBackground();
    }

    const workoutElapsedSec = currentVideoSec - introOffsetSec;

    if (workoutElapsedSec < 0) return;

    const clampedElapsed = Math.min(workoutElapsedSec, workoutDurationSec);
    const hrIndex = Math.floor(clampedElapsed / hrIntervalSec);
    const currentBpm = hrValues[hrIndex % hrValues.length] || 145;
    const zone = getHeartRateZone(currentBpm);

    const scale = parseFloat(document.getElementById('hudScale').value || 1.0);
    ctx.save();
    ctx.translate(hudPos.x, hudPos.y);
    ctx.scale(scale, scale);

    const boxW = 370, boxH = 215;
    ctx.beginPath();
    ctx.moveTo(16, 0); ctx.lineTo(225, 0); ctx.lineTo(240, 12); ctx.lineTo(boxW - 14, 12);
    ctx.quadraticCurveTo(boxW, 12, boxW, 26); ctx.lineTo(boxW, boxH - 16);
    ctx.quadraticCurveTo(boxW, boxH, boxW - 16, boxH); ctx.lineTo(16, boxH);
    ctx.quadraticCurveTo(0, boxH, 0, boxH - 16); ctx.lineTo(0, 16);
    ctx.quadraticCurveTo(0, 0, 16, 0); ctx.closePath();

    ctx.fillStyle = 'rgba(18, 22, 28, 0.78)';
    ctx.fill();
    ctx.lineWidth = 2;
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.35)';
    ctx.stroke();

    ctx.fillStyle = 'rgba(255, 255, 255, 0.45)';
    for (let i = 0; i < 5; i++) {
        const sx = 280 + (i * 13);
        ctx.beginPath();
        ctx.moveTo(sx, 20); ctx.lineTo(sx + 7, 20); ctx.lineTo(sx + 12, 28); ctx.lineTo(sx + 5, 28);
        ctx.closePath(); ctx.fill();
    }

    const beatScale = 1 + Math.sin(pulsePhase) * 0.08;
    ctx.save();
    ctx.translate(52, 42);
    ctx.scale(beatScale, beatScale);
    drawHeartIcon(ctx, 0, -18, 42, '#ff2a4b');
    ctx.restore();

    ctx.fillStyle = '#ffffff';
    ctx.font = '900 68px Inter, Arial, sans-serif';
    ctx.fillText(String(currentBpm), 95, 82);

    const bpmNumWidth = ctx.measureText(String(currentBpm)).width;
    ctx.fillStyle = '#cbd5e1';
    ctx.font = 'bold 28px Inter, Arial, sans-serif';
    ctx.fillText('BPM', 95 + bpmNumWidth + 10, 80);

    ctx.fillStyle = zone.color;
    ctx.font = 'bold 17px Inter, Arial, sans-serif';
    ctx.fillText(zone.label, 88, 110);

    ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
    ctx.lineWidth = 1.5;
    ctx.beginPath(); ctx.moveTo(24, 126); ctx.lineTo(boxW - 24, 126); ctx.stroke();

    drawStopwatchIcon(ctx, 48, 162, 16);

    const currentClockStr = formatMMSS(clampedElapsed);
    const totalClockStr = formatMMSS(workoutDurationSec);

    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 40px Inter, Arial, sans-serif';
    ctx.fillText(currentClockStr, 78, 176);

    const curClockWidth = ctx.measureText(currentClockStr).width;
    ctx.fillStyle = '#94a3b8';
    ctx.font = 'bold 30px Inter, Arial, sans-serif';
    ctx.fillText(` / ${totalClockStr}`, 78 + curClockWidth + 4, 175);

    const barX = 28, barY = 194, barW = boxW - 56, barH = 7;
    const progressRatio = Math.min(1, Math.max(0, clampedElapsed / workoutDurationSec));

    ctx.fillStyle = 'rgba(255, 255, 255, 0.22)';
    ctx.beginPath(); ctx.roundRect(barX, barY, barW, barH, 4); ctx.fill();

    ctx.fillStyle = '#ffffff';
    ctx.beginPath(); ctx.roundRect(barX, barY, Math.max(8, barW * progressRatio), barH, 4); ctx.fill();

    ctx.restore();
}

function animationLoop(timestamp) {
    if (!isPlayingSim) return;
    if (!lastTick) lastTick = timestamp;
    const deltaSec = (timestamp - lastTick) / 1000;
    lastTick = timestamp;

    const workoutElapsedSec = Math.max(0, currentVideoSec - introOffsetSec);
    const hrIndex = Math.floor(workoutElapsedSec / hrIntervalSec);
    const bpm = hrValues[hrIndex % hrValues.length] || 140;
    pulsePhase += deltaSec * (bpm / 60) * Math.PI * 2;

    if (useVideoBg && !hiddenVideo.paused) {
        currentVideoSec = hiddenVideo.currentTime;
        if (hiddenVideo.ended) {
            if (mediaRecorder && mediaRecorder.state === 'recording') toggleRecording();
            else togglePlaySim();
            return;
        }
    } else {
        currentVideoSec += deltaSec;
    }

    const maxVal = parseFloat(document.getElementById('timelineSlider').max);
    if (currentVideoSec > maxVal) {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            toggleRecording();
            return;
        }
        currentVideoSec = 0;
    }

    document.getElementById('timelineSlider').value = currentVideoSec;
    renderFrame();
    animFrameId = requestAnimationFrame(animationLoop);
}

window.addEventListener('load', () => {
    clearLegacyStorage();
    updateTiming();
    generateSampleWorkout();
    document.getElementById('timelineSlider').max = introOffsetSec + workoutDurationSec;
    document.getElementById('lblVideoTime').innerText = formatMMSS(currentVideoSec);
    document.getElementById('lblTrimStartMarker').innerText = `✂️ Mulai: ${formatMMSS(introOffsetSec)}`;
    document.getElementById('lblTrimEndMarker').innerText = `✂️ Selesai: ${formatMMSS(introOffsetSec + workoutDurationSec)}`;
    renderFrame();
});

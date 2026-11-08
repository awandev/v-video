let mediaRecorder = null;
let recordedChunks = [];

document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('hudCanvas');
    
    canvas.addEventListener('mousedown', function(e) {
        const rect = canvas.getBoundingClientRect();
        const mx = (e.clientX - rect.left) * (canvas.width / rect.width);
        const my = (e.clientY - rect.top) * (canvas.height / rect.height);
        isDragging = true;
        dragOffset.x = mx - hudPos.x;
        dragOffset.y = my - hudPos.y;
    });

    window.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        const rect = canvas.getBoundingClientRect();
        const mx = (e.clientX - rect.left) * (canvas.width / rect.width);
        const my = (e.clientY - rect.top) * (canvas.height / rect.height);
        hudPos.x = Math.max(0, Math.min(canvas.width - 200, mx - dragOffset.x));
        hudPos.y = Math.max(0, Math.min(canvas.height - 120, my - dragOffset.y));
        renderFrame();
    });

    window.addEventListener('mouseup', () => { isDragging = false; });
});

function extractFromGraphImage(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(evt) {
        const img = new Image();
        img.onload = function() {
            const offCanvas = document.createElement('canvas');
            offCanvas.width = img.width; 
            offCanvas.height = img.height;
            const octx = offCanvas.getContext('2d');
            octx.drawImage(img, 0, 0);
            const totalSteps = Math.ceil(workoutDurationSec / hrIntervalSec);
            const extracted = [];
            const imgData = octx.getImageData(0, 0, img.width, img.height).data;
            for (let i = 0; i < totalSteps; i++) {
                const x = Math.floor((i / totalSteps) * (img.width * 0.8) + (img.width * 0.1));
                let foundY = -1;
                for (let y = Math.floor(img.height * 0.15); y < Math.floor(img.height * 0.85); y++) {
                    const idx = (y * img.width + x) * 4;
                    const r = imgData[idx], g = imgData[idx+1], b = imgData[idx+2];
                    if (r > 180 && g < 110 && b < 110 && (r - g) > 80) { foundY = y; break; }
                }
                if (foundY !== -1) {
                    const norm = 1 - ((foundY - img.height * 0.15) / (img.height * 0.7));
                    extracted.push(Math.max(60, Math.min(200, Math.round(95 + norm * 85))));
                } else {
                    extracted.push(extracted.length > 0 ? extracted[extracted.length - 1] : 135);
                }
            }
            document.getElementById('hrDataInput').value = extracted.join(', ');
            parseHrInput();
            alert(`Berhasil mengekstrak ${extracted.length} titik data BPM dari screenshot!`);
        };
        img.src = evt.target.result;
    };
    reader.readAsDataURL(file);
}

function toggleRecording() {
    const btn = document.getElementById('btnRecord');
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
        btn.innerText = '⏺ Export (.MP4)';
        btn.className = 'bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold px-4 py-1.5 rounded-lg transition shadow-lg';
        if (isPlayingSim) togglePlaySim();
        return;
    }

    const canvasStream = canvas.captureStream(30);

    if (useVideoBg && hiddenVideo.captureStream) {
        try {
            const vidStream = hiddenVideo.captureStream();
            const audioTracks = vidStream.getAudioTracks();
            if (audioTracks.length > 0) {
                canvasStream.addTrack(audioTracks[0]);
            }
        } catch (err) {
            console.warn('Audio stream tidak dapat digabung otomatis:', err);
        }
    }

    const mimeType = getSupportedMimeType();
    const options = mimeType ? { mimeType, videoBitsPerSecond: 6000000 } : { videoBitsPerSecond: 6000000 };

    mediaRecorder = new MediaRecorder(canvasStream, options);
    recordedChunks = [];

    mediaRecorder.ondataavailable = e => {
        if (e.data && e.data.size > 0) recordedChunks.push(e.data);
    };

    mediaRecorder.onstop = () => {
        const ext = (mimeType.includes('mp4') || mimeType.includes('h264')) ? 'mp4' : 'mp4';
        const blob = new Blob(recordedChunks, { type: mimeType || 'video/mp4' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `workout_overlay_ready.${ext}`;
        a.click();
    };

    mediaRecorder.start(1000);
    if (!isPlayingSim) togglePlaySim();

    btn.innerText = '⏹ Stop & Simpan';
    btn.className = 'bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold px-4 py-1.5 rounded-lg transition shadow-lg animate-pulse';
}

window.addEventListener('keydown', function(e) {
    if (e.key === 'F5' || ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'r')) {
        if (hrValues.length > 0 && !confirm("⚠️ Refresh halaman? Data bisa hilang.")) {
            e.preventDefault();
            return;
        }
    }
    
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
        e.preventDefault();
        undoAction();
        return;
    }
    
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') {
        e.preventDefault();
        redoAction();
        return;
    }
});

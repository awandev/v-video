let currentActiveTab = 'workout';
let isModalOpen = true;

const modalTitles = {
    workout:   '💪 Pengaturan Workout & Spasi',
    hr:        '❤️ Pengaturan Heart Rate',
    timer:     '⏱️ Pengaturan Timer Durasi',
    title:     '🏷️ Pengaturan Judul Atas',
    watermark: '📱 Watermark Sosial Media',
    result:    '🏆 Kartu Result / Rekap',
    audio:     '🎵 Studio Audio Mixer',
    video:     '🎬 Video, LUT & Trim'
};

function openSideModal(tabName) {
    const container = document.getElementById('sideModalContainer');

    if (currentActiveTab === tabName && isModalOpen) {
        closeSideModal();
        return;
    }

    currentActiveTab = tabName;
    isModalOpen = true;
    container.classList.remove('hidden');

    document.querySelectorAll('.sidebar-btn').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById('nav_' + tabName);
    if (activeBtn) activeBtn.classList.add('active');

    document.querySelectorAll('.modal-pane').forEach(pane => pane.classList.add('hidden'));
    const targetPane = document.getElementById('tab_' + tabName);
    if (targetPane) targetPane.classList.remove('hidden');

    document.getElementById('sideModalTitle').innerText = modalTitles[tabName] || 'Pengaturan';
}

function closeSideModal() {
    const container = document.getElementById('sideModalContainer');
    container.classList.add('hidden');
    document.querySelectorAll('.sidebar-btn').forEach(btn => btn.classList.remove('active'));
    isModalOpen = false;
}

function onSliderChange(val) {
    currentVideoSec = parseFloat(val);
    if (useVideoBg && Math.abs(hiddenVideo.currentTime - currentVideoSec) > 0.2) {
        hiddenVideo.currentTime = currentVideoSec;
    }
    const lblTime = document.getElementById('lblVideoTime');
    const lblStart = document.getElementById('lblTrimStartMarker');
    const lblEnd = document.getElementById('lblTrimEndMarker');
    if (lblTime) lblTime.innerText = formatMMSS(currentVideoSec);
    if (lblStart && lblEnd) {
        const trimStartTotal = introOffsetSec;
        const trimEndTotal = introOffsetSec + workoutDurationSec;
        lblStart.innerText = `✂️ Mulai: ${formatMMSS(trimStartTotal)}`;
        lblEnd.innerText = `✂️ Selesai: ${formatMMSS(trimEndTotal)}`;
    }
    renderFrame();
}

function togglePlaySim() {
    const btn = document.getElementById('btnPlaySim');
    if (isPlayingSim) {
        isPlayingSim = false;
        cancelAnimationFrame(animFrameId);
        if (useVideoBg) hiddenVideo.pause();
        btn.innerText = '▶ Putar Video';
    } else {
        isPlayingSim = true;
        lastTick = 0;
        if (useVideoBg) {
            hiddenVideo.currentTime = currentVideoSec;
            hiddenVideo.play();
        }
        btn.innerText = '⏸ Pause';
        animFrameId = requestAnimationFrame(animationLoop);
    }
}

function downloadSnapshot() {
    renderFrame();
    if (canvas) {
        const link = document.createElement('a');
        link.download = `workout_hud_${formatMMSS(currentVideoSec).replace(':','-')}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    }
}

function jumpToTrimStart() {
    currentVideoSec = introOffsetSec;
    const slider = document.getElementById('timelineSlider');
    if (slider) {
        slider.value = currentVideoSec;
        onSliderChange(currentVideoSec);
    }
}

function toggleResultPreviewBtn() {
    renderFrame();
}

function resetWidgetPositions() {
    hudPos = { x: 56, y: 440 };
    renderFrame();
}

function saveSettingState() {
    const state = {
        hrValues: hrValues,
        introOffsetSec: introOffsetSec,
        workoutDurationSec: workoutDurationSec,
        hrIntervalSec: hrIntervalSec
    };
    localStorage.setItem('workoutAppState', JSON.stringify(state));
}

function loadSettingState() {
    const saved = localStorage.getItem('workoutAppState');
    if (saved) {
        const state = JSON.parse(saved);
        hrValues = state.hrValues || hrValues;
        introOffsetSec = state.introOffsetSec || 0;
        workoutDurationSec = state.workoutDurationSec || 1800;
        hrIntervalSec = state.hrIntervalSec || 5;
    }
}

function clearLegacyStorage() {
    localStorage.removeItem('videoTemplates');
}

function undoAction() {
    document.getElementById('undoStatusBadge').innerText = 'Fitur Undo sedang dikembangkan';
}

function redoAction() {
    document.getElementById('undoStatusBadge').innerText = 'Fitur Redo sedang dikembangkan';
}

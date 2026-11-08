<?php
/**
 * DAILY WORKOUT CONTENT STUDIO v3.0 (DJI OSMO TELEMETRY & CUSTOM STYLE EDITION)
 * Fitur Utama:
 * - 5 Style HUD: DJI Osmo Gauge, DJI ECG Wave, Minimalist Pill, Tactical Stack, Broadcast Card
 * - 5 Pilihan Font Sporty + Custom Color Picker & Background Opacity Slider
 * - Spacebar Tap-to-Count (+1 Rep otomatis tiap tekan Spasi)
 * - 4 Metode Latihan: Target Reps (0/200 + %), EMOM, AMRAP, Free Counter
 * - Export Direct .MP4 (Reels/TikTok 9:16 & YouTube 16:9)
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'parse_file' && isset($_FILES['hr_file'])) {
        header('Content-Type: application/json');
        $tmpName = $_FILES['hr_file']['tmp_name'];
        $fileName = strtolower($_FILES['hr_file']['name']);
        $bpmList = [];

        if (is_uploaded_file($tmpName)) {
            $content = file_get_contents($tmpName);
            if (str_ends_with($fileName, '.tcx') || str_contains($content, '<TrainingCenterDatabase')) {
                preg_match_all('/<HeartRateBpm>.*?<Value>\s*(\d+)\s*<\/Value>.*?<\/HeartRateBpm>/is', $content, $matches);
                if (!empty($matches[1])) $bpmList = array_map('intval', $matches[1]);
            } else {
                $lines = preg_split('/\r\n|\r|\n/', trim($content));
                foreach ($lines as $line) {
                    foreach (str_getcsv($line) as $col) {
                        $val = intval(trim($col));
                        if ($val >= 40 && $val <= 230) { $bpmList[] = $val; break; }
                    }
                }
            }
        }
        echo json_encode(['status' => count($bpmList) ? 'success' : 'empty', 'data' => $bpmList, 'count' => count($bpmList)]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Workout Studio - DJI Telemetry & Custom HUD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts untuk Variasi Tipografi HUD -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,600;0,700;1,700&family=JetBrains+Mono:wght@700;800&family=Montserrat:wght@700;900&family=Orbitron:wght@700;900&family=Oswald:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #090d16; color: #f8fafc; font-family: 'Montserrat', system-ui, sans-serif; }
        .glass-panel { background: rgba(19, 27, 46, 0.92); border: 1px solid rgba(255,255,255,0.08); }
        canvas { max-width: 100%; max-height: 70vh; width: auto; height: auto; border-radius: 0.75rem; box-shadow: 0 20px 30px -10px rgba(0,0,0,0.75); }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        input[type="color"] { -webkit-appearance: none; border: none; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; background: transparent; }
        input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
        input[type="color"]::-webkit-color-swatch { border: 1px solid rgba(255,255,255,0.3); border-radius: 6px; }
    </style>
</head>
<body class="min-h-screen p-3 md:p-5">

<div class="max-w-[1460px] mx-auto space-y-4">
    <!-- HEADER BAR -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-800 pb-3">
        <div>
            <h1 class="text-xl md:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                <span class="bg-gradient-to-r from-rose-600 to-orange-500 text-white px-2.5 py-0.5 rounded text-xs font-extrabold">DJI TELEMETRY</span>
                DAILY WORKOUT CONTENT STUDIO
            </h1>
            <p class="text-slate-400 text-xs mt-0.5">Tekan <b>SPASI</b> untuk tambah repetisi otomatis (+1) • 5 Style HUD • Kustomisasi Font & Warna Penuh</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select id="aspectRatioSelect" onchange="changeAspectRatio(this.value)" class="bg-slate-800 border border-slate-700 text-white text-xs font-semibold px-3 py-2 rounded-lg">
                <option value="16:9">📺 YouTube (16:9 - 1280x720)</option>
                <option value="9:16">📱 Reels / TikTok / Shorts (9:16 - 720x1280)</option>
                <option value="1:1">🟦 Feed Square (1:1 - 1080x1080)</option>
            </select>
            <button onclick="downloadSnapshot()" class="bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                📸 Snapshot (.PNG)
            </button>
            <button id="btnRecord" onclick="toggleRecording()" class="bg-rose-600 hover:bg-rose-500 text-white text-xs md:text-sm font-bold px-4 py-2 rounded-lg transition shadow-lg">
                ⏺ Export Video (.MP4)
            </button>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- PANEL KIRI: KONTROL & DESAIN (5 KOLOM) -->
        <div class="lg:col-span-5 space-y-4 max-h-[85vh] overflow-y-auto pr-1">

            <!-- 1. PILIHAN STYLE HUD, FONT, DAN WARNA -->
            <div class="glass-panel p-4 rounded-xl space-y-3 border-l-4 border-l-amber-500">
                <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">1. Style HUD (Ala DJI Osmo), Font & Warna</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] text-slate-300 font-semibold mb-1">Pilih Style HR & HUD (5 Model):</label>
                        <select id="hudStyleSelect" onchange="renderFrame()" class="w-full bg-slate-900 border border-amber-500/50 rounded px-2.5 py-1.5 text-xs text-amber-300 font-bold">
                            <option value="dji_gauge">1. DJI Osmo Action Gauge (2 Cincin Lingkaran)</option>
                            <option value="dji_ecg">2. DJI Telemetry Wave (Grafik ECG + 5 Zona)</option>
                            <option value="minimal_pill">3. Minimalist Floating Pill (Kapsul Modern)</option>
                            <option value="tactical_stack">4. Tactical Cyber Stack (Vertikal Sporty)</option>
                            <option value="broadcast_card">5. Broadcast Pro Box (Kotak Klasik + Bar)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-300 font-semibold mb-1">Pilih Jenis Font (Tipografi):</label>
                        <select id="fontFamilySelect" onchange="renderFrame()" class="w-full bg-slate-900 border border-slate-700 rounded px-2.5 py-1.5 text-xs text-white font-bold">
                            <option value="'Chakra Petch', sans-serif">Chakra Petch (DJI / Action Cam Style)</option>
                            <option value="'Orbitron', sans-serif">Orbitron (Sci-Fi Futuristic)</option>
                            <option value="'Oswald', sans-serif">Oswald (Tall Athletic / Nike Broadcast)</option>
                            <option value="'JetBrains Mono', monospace">JetBrains Mono (Digital Stopwatch)</option>
                            <option value="'Montserrat', sans-serif">Montserrat (Clean Apple Fitness)</option>
                        </select>
                    </div>
                </div>

                <!-- Pemilih Warna & Transparansi -->
                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-800/80 items-center">
                    <div class="flex items-center gap-2">
                        <input type="color" id="accentColorPicker" value="#f97316" oninput="renderFrame()">
                        <div>
                            <div class="text-[11px] font-bold text-slate-200">Warna Aksen</div>
                            <div class="text-[10px] text-slate-400">Bar / Gauge</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="color" id="heartColorPicker" value="#ff2a4b" oninput="renderFrame()">
                        <div>
                            <div class="text-[11px] font-bold text-slate-200">Warna HR</div>
                            <div class="text-[10px] text-slate-400">Ikon & ECG</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-400">Gelap Latar HUD: <b id="lblOpacity" class="text-white">65%</b></label>
                        <input type="range" id="bgOpacitySlider" min="0" max="0.95" step="0.05" value="0.65" oninput="document.getElementById('lblOpacity').innerText=Math.round(this.value*100)+'%'; renderFrame();" class="w-full accent-amber-500">
                    </div>
                </div>

                <!-- Ukuran HUD -->
                <div class="flex items-center justify-between gap-3 pt-1">
                    <span class="text-[11px] text-slate-400">Ukuran Skala HUD:</span>
                    <input type="range" id="hudScale" min="0.65" max="1.45" step="0.05" value="1.0" oninput="renderFrame()" class="w-48 accent-sky-500">
                </div>
            </div>

            <!-- 2. JUDUL VIDEO KIRI ATAS & METODE WORKOUT -->
            <div class="glass-panel p-4 rounded-xl space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-extrabold text-sky-400 uppercase tracking-wider">2. Judul Video (Kiri Atas) & Metode Latihan</h2>
                    <label class="flex items-center gap-1.5 text-xs text-slate-300 cursor-pointer">
                        <input type="checkbox" id="showTopTitle" checked onchange="renderFrame()" class="rounded bg-slate-800 text-sky-500">
                        <span>Tampilkan Judul</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] text-slate-400 mb-0.5">Tag Hari / Episode:</label>
                        <input type="text" id="titleTag" value="DAY 15 // DAILY GRIND" oninput="renderFrame()" class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-xs text-amber-300 font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-400 mb-0.5">Judul Utama:</label>
                        <input type="text" id="titleMain" value="200 NAVY SEAL BURPEES" oninput="renderFrame()" class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-xs text-white font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-400 mb-0.5">Sub-Judul / Target:</label>
                        <input type="text" id="titleSub" value="FOR TIME • NO EQUIPMENT" oninput="renderFrame()" class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-xs text-slate-300">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5 pt-2 border-t border-slate-800">
                    <div>
                        <label class="block text-[11px] text-sky-400 font-bold mb-1">Metode Workout:</label>
                        <select id="workoutMode" onchange="onModeChange()" class="w-full bg-slate-900 border border-sky-500/50 rounded px-2.5 py-1.5 text-xs text-white font-bold">
                            <option value="target_reps">🎯 Target Repetisi (0/200 + %)</option>
                            <option value="emom">⏱️ EMOM (Auto Reset Tiap Menit)</option>
                            <option value="amrap">🔄 AMRAP (Ronde + Repetisi)</option>
                            <option value="free_reps">💪 Free Counter (Repetisi Saja)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] text-emerald-400 font-bold mb-1" id="lblTargetInput">Target Total Repetisi:</label>
                        <input type="number" id="targetRepsInput" value="200" min="1" oninput="renderFrame()" class="w-full bg-slate-900 border border-emerald-500/50 rounded px-2.5 py-1.5 text-xs text-emerald-300 font-bold">
                    </div>
                </div>
            </div>

            <!-- 3. TOMBOL SPASI PENAMBAH REPETISI OTOMATIS (+1) -->
            <div class="glass-panel p-4 rounded-xl space-y-2.5 border-l-4 border-l-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-extrabold text-emerald-400 uppercase tracking-wider">3. Counter Repetisi Otomatis (Tekan Spasi)</h2>
                        <p class="text-[11px] text-slate-400">Cukup tekan <b>SPASI</b> 1x setiap gerakan selesai, angka otomatis naik <b>+1</b>!</p>
                    </div>
                    <span id="repTotalBadge" class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 px-2.5 py-1 rounded text-xs font-black">0 REPS</span>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="markRepNow()" class="col-span-2 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-black text-xs py-2.5 px-3 rounded-lg shadow transition">
                        ➕ TEKAN [SPASI] = +1 REP
                    </button>
                    <button type="button" onclick="markRoundNow()" class="bg-amber-600 hover:bg-amber-500 active:scale-95 text-white font-bold text-xs py-2.5 px-2 rounded-lg shadow transition">
                        🔄 +1 RONDE [R]
                    </button>
                </div>

                <div class="flex items-center justify-between gap-2 text-[11px]">
                    <button type="button" onclick="undoLastRep()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-2.5 py-1 rounded">
                        ↩ Batal 1 Rep [Backspace]
                    </button>
                    <button type="button" onclick="generateDemoReps()" class="bg-slate-800 hover:bg-slate-700 text-sky-300 px-2.5 py-1 rounded">
                        ⚡ Simulasi 200 Reps
                    </button>
                    <button type="button" onclick="clearAllReps()" class="bg-slate-800 hover:bg-rose-900/60 text-rose-300 px-2.5 py-1 rounded">
                        🗑 Reset Nol
                    </button>
                </div>
                <textarea id="repTimestampsInput" rows="1" oninput="parseRepTimestamps()" placeholder="Detik repetisi tercatat otomatis di sini..." class="w-full bg-slate-950 border border-slate-800 rounded p-1.5 text-[10px] font-mono text-emerald-400"></textarea>
            </div>

            <!-- 4. MEDIA VIDEO & HEART RATE HUAWEI BAND -->
            <div class="glass-panel p-4 rounded-xl space-y-3">
                <h2 class="text-xs font-extrabold text-rose-400 uppercase tracking-wider">4. Video, Waktu Mulai (00:00) & Data HR Huawei</h2>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] text-slate-300 mb-0.5">Upload Video Workout:</label>
                        <input type="file" id="bgVideoInput" accept="video/*" class="w-full text-[11px] text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-rose-600 file:text-white">
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-300 mb-0.5">Upload Foto Background:</label>
                        <input type="file" id="bgImageInput" accept="image/*" class="w-full text-[11px] text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-700 file:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] text-slate-400">Skip Intro (MM:SS):</label>
                        <div class="flex items-center gap-1">
                            <input type="number" id="introMin" value="0" min="0" onchange="updateTiming()" class="w-full bg-slate-900 border border-slate-700 rounded px-1.5 py-1 text-xs text-white">
                            <span>:</span>
                            <input type="number" id="introSec" value="0" min="0" max="59" onchange="updateTiming()" class="w-full bg-slate-900 border border-slate-700 rounded px-1.5 py-1 text-xs text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-400">Durasi (Menit):</label>
                        <input type="number" id="workoutMin" value="15" min="1" onchange="updateTiming()" class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-400">Usia (Zona HR):</label>
                        <input type="number" id="userAge" value="28" min="10" max="90" onchange="renderFrame()" class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-xs text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-800">
                    <div>
                        <span class="block text-[10px] text-amber-300 mb-0.5">📱 Screenshot Grafik Huawei:</span>
                        <input type="file" accept="image/*" onchange="extractFromGraphImage(this)" class="w-full text-[10px] text-slate-300 file:py-0.5 file:px-2 file:rounded file:border-0 file:bg-amber-600 file:text-white">
                    </div>
                    <div>
                        <span class="block text-[10px] text-sky-300 mb-0.5">📄 File .TCX / .CSV:</span>
                        <input type="file" accept=".tcx,.csv,.txt" onchange="uploadFileToPhp(this)" class="w-full text-[10px] text-slate-300 file:py-0.5 file:px-2 file:rounded file:border-0 file:bg-slate-700 file:text-white">
                    </div>
                </div>
                <textarea id="hrDataInput" rows="2" oninput="parseHrInput()" class="w-full bg-slate-950 border border-slate-800 rounded p-1.5 text-[10px] font-mono text-rose-400"></textarea>
            </div>

        </div>

        <!-- PANEL KANAN: LIVE CANVAS PREVIEW (7 KOLOM) -->
        <div class="lg:col-span-7 space-y-3">
            <div class="glass-panel p-4 rounded-xl space-y-3">
                
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <button onclick="togglePlaySim()" id="btnPlaySim" class="bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow">
                            ▶ Putar Video
                        </button>
                        <div class="flex items-center bg-slate-900 border border-slate-700 rounded-lg p-0.5 text-xs">
                            <span class="px-2 text-slate-400 text-[11px]">Speed:</span>
                            <button onclick="setPlaybackSpeed(1)" id="spd1" class="px-2 py-1 rounded bg-sky-600 text-white font-bold">1x</button>
                            <button onclick="setPlaybackSpeed(2)" id="spd2" class="px-2 py-1 rounded text-slate-300 hover:text-white">2x</button>
                            <button onclick="setPlaybackSpeed(3)" id="spd3" class="px-2 py-1 rounded text-slate-300 hover:text-white">3x</button>
                            <button onclick="setPlaybackSpeed(4)" id="spd4" class="px-2 py-1 rounded text-slate-300 hover:text-white">4x</button>
                        </div>
                        <label class="flex items-center gap-1.5 text-xs text-emerald-400 ml-2 cursor-pointer">
                            <input type="checkbox" id="greenScreenMode" onchange="renderFrame()" class="rounded bg-slate-900">
                            <span>Green Screen</span>
                        </label>
                    </div>
                    <span class="text-[11px] text-slate-400">🖱️ Klik & geser Judul atau HUD langsung di layar!</span>
                </div>

                <!-- KANVAS UTAMA -->
                <div class="relative flex justify-center items-center bg-slate-950 rounded-xl p-1.5 border border-slate-800 overflow-hidden">
                    <canvas id="hudCanvas" width="1280" height="720" class="cursor-move"></canvas>
                </div>

                <!-- TIMELINE SLIDER -->
                <div class="bg-slate-900/90 p-3.5 rounded-xl border border-slate-800 space-y-1.5">
                    <div class="flex flex-wrap justify-between text-xs font-semibold">
                        <span class="text-slate-300">Waktu Video: <b id="lblVideoTime" class="text-sky-400 font-mono">00:00</b></span>
                        <span id="lblStatusSummary" class="text-emerald-400 font-mono">REPS: 0 / 200 (0%) | HR: 142 BPM</span>
                    </div>
                    <input type="range" id="timelineSlider" min="0" max="900" step="0.1" value="0" oninput="onSliderChange(this.value)" class="w-full accent-rose-500 cursor-pointer">
                    <div class="flex justify-between text-[11px] text-slate-500 font-mono">
                        <span>00:00 (Awal Video)</span>
                        <span id="lblIntroMarker" class="text-amber-400">Mulai Olahraga: 00:00</span>
                        <span id="lblTotalEnd">15:00</span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<video id="hiddenVideo" class="hidden" playsinline crossorigin="anonymous"></video>

<script>
const canvas = document.getElementById('hudCanvas');
const ctx = canvas.getContext('2d');
const hiddenVideo = document.getElementById('hiddenVideo');

let bgImage = null;
let useVideoBg = false;
let hrValues = [];
let repTimestamps = [];
let roundTimestamps = [];

let currentVideoSec = 0;
let introOffsetSec = 0;
let workoutDurationSec = 900;
let hrIntervalSec = 5;
let playbackSpeed = 1.0;

let isPlayingSim = false;
let animFrameId = null;
let lastTick = 0;
let pulsePhase = 0;
let ecgOffset = 0;
let repPopScale = 1.0;

let titlePos = { x: 42, y: 38 };
let hudPos   = { x: 42, y: 470 };
let dragTarget = null;
let dragOffset = { x: 0, y: 0 };

// Keyboard Listener: Tekan SPASI otomatis tambah 1 repetisi
window.addEventListener('keydown', function(e) {
    const tag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
    if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

    if (e.code === 'Space') {
        e.preventDefault();
        markRepNow();
    } else if (e.code === 'KeyR') {
        e.preventDefault();
        markRoundNow();
    } else if (e.code === 'Backspace') {
        e.preventDefault();
        undoLastRep();
    }
});

function markRepNow() {
    const workoutSec = Math.max(0, currentVideoSec - introOffsetSec);
    // Jika video sedang di-pause di detik yang sama, tambahkan 0.01 detik agar tetap terhitung naik +1
    let newStamp = Number(workoutSec.toFixed(2));
    while (repTimestamps.includes(newStamp)) {
        newStamp = Number((newStamp + 0.01).toFixed(2));
    }
    repTimestamps.push(newStamp);
    repTimestamps.sort((a, b) => a - b);
    currentVideoSec = Math.max(currentVideoSec, introOffsetSec + newStamp);
    repPopScale = 1.35;
    syncRepTextarea();
    renderFrame();
}

function markRoundNow() {
    const workoutSec = Math.max(0, currentVideoSec - introOffsetSec);
    roundTimestamps.push(Number(workoutSec.toFixed(2)));
    roundTimestamps.sort((a, b) => a - b);
    repPopScale = 1.35;
    renderFrame();
}

function undoLastRep() {
    if (repTimestamps.length > 0) {
        repTimestamps.pop();
        syncRepTextarea();
        renderFrame();
    }
}

function clearAllReps() {
    repTimestamps = [];
    roundTimestamps = [];
    syncRepTextarea();
    renderFrame();
}

function syncRepTextarea() {
    document.getElementById('repTimestampsInput').value = repTimestamps.join(', ');
    document.getElementById('repTotalBadge').innerText = `${repTimestamps.length} REPS`;
}

function parseRepTimestamps() {
    const raw = document.getElementById('repTimestampsInput').value;
    repTimestamps = raw.split(/[\s,;]+/).map(v => parseFloat(v.trim())).filter(v => !isNaN(v) && v >= 0).sort((a, b) => a - b);
    document.getElementById('repTotalBadge').innerText = `${repTimestamps.length} REPS`;
    renderFrame();
}

function generateDemoReps() {
    const target = parseInt(document.getElementById('targetRepsInput').value || 200, 10);
    repTimestamps = [];
    const step = workoutDurationSec / target;
    for (let i = 1; i <= target; i++) {
        repTimestamps.push(Number((i * step).toFixed(1)));
    }
    syncRepTextarea();
    renderFrame();
}

function getCompletedRepsAt(workoutSec) {
    let count = 0;
    for (let i = 0; i < repTimestamps.length; i++) {
        if (workoutSec >= repTimestamps[i]) count++;
        else break;
    }
    return count;
}

function getCompletedRoundsAt(workoutSec) {
    let r = 1;
    for (let i = 0; i < roundTimestamps.length; i++) {
        if (workoutSec >= roundTimestamps[i]) r++;
        else break;
    }
    return r;
}

function setPlaybackSpeed(spd) {
    playbackSpeed = spd;
    if (useVideoBg) hiddenVideo.playbackRate = spd;
    [1, 2, 3, 4].forEach(s => {
        const btn = document.getElementById('spd' + s);
        btn.className = (s === spd)
            ? 'px-2 py-1 rounded bg-sky-600 text-white font-bold'
            : 'px-2 py-1 rounded text-slate-300 hover:text-white';
    });
}

function onModeChange() {
    const mode = document.getElementById('workoutMode').value;
    const lbl = document.getElementById('lblTargetInput');
    const input = document.getElementById('targetRepsInput');
    if (mode === 'target_reps') {
        lbl.innerText = 'Target Total Repetisi:';
        input.value = 200;
    } else if (mode === 'emom') {
        lbl.innerText = 'Total Menit EMOM:';
        input.value = 15;
    } else if (mode === 'amrap') {
        lbl.innerText = 'Target Reps/Ronde:';
        input.value = 30;
    } else {
        lbl.innerText = 'Target Referensi:';
    }
    renderFrame();
}

function changeAspectRatio(ratio) {
    if (ratio === '9:16') {
        canvas.width = 720;
        canvas.height = 1280;
        titlePos = { x: 36, y: 65 };
        hudPos   = { x: 36, y: 940 };
    } else if (ratio === '1:1') {
        canvas.width = 1080;
        canvas.height = 1080;
        titlePos = { x: 40, y: 40 };
        hudPos   = { x: 40, y: 780 };
    } else {
        canvas.width = 1280;
        canvas.height = 720;
        titlePos = { x: 42, y: 38 };
        hudPos   = { x: 42, y: 470 };
    }
    renderFrame();
}

function formatMMSS(sec) {
    sec = Math.max(0, Math.floor(sec));
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
}

function updateTiming() {
    const iMin = parseInt(document.getElementById('introMin').value || 0, 10);
    const iSec = parseInt(document.getElementById('introSec').value || 0, 10);
    introOffsetSec = (iMin * 60) + iSec;

    const wMin = parseInt(document.getElementById('workoutMin').value || 15, 10);
    workoutDurationSec = Math.max(10, wMin * 60);

    const totalMax = introOffsetSec + workoutDurationSec;
    document.getElementById('timelineSlider').max = totalMax;
    document.getElementById('lblIntroMarker').innerText = `Mulai 00:00 (${formatMMSS(introOffsetSec)})`;
    document.getElementById('lblTotalEnd').innerText = formatMMSS(totalMax);
    renderFrame();
}

function parseHrInput() {
    const raw = document.getElementById('hrDataInput').value;
    const nums = raw.split(/[\s,;]+/).map(v => parseInt(v.trim(), 10)).filter(v => !isNaN(v) && v >= 35 && v <= 240);
    hrValues = nums.length > 0 ? nums : [142];
    renderFrame();
}

function generateSampleHr() {
    const totalPoints = Math.ceil(workoutDurationSec / hrIntervalSec);
    let sample = [];
    for (let i = 0; i < totalPoints; i++) {
        const p = i / totalPoints;
        let bpm = 115 + Math.sin(i * 0.35) * 14 + (p * 35);
        sample.push(Math.min(185, Math.round(bpm)));
    }
    document.getElementById('hrDataInput').value = sample.join(', ');
    parseHrInput();
}

function getZoneInfo(bpm) {
    const age = parseInt(document.getElementById('userAge').value || 28, 10);
    const maxHr = 220 - age;
    const pct = (bpm / maxHr) * 100;
    if (pct >= 90) return { zone: 5, name: 'ZONE 5 • MAX', color: '#ef4444', ratio: Math.min(1, bpm / maxHr) };
    if (pct >= 80) return { zone: 4, name: 'ZONE 4 • ANAEROBIC', color: '#f97316', ratio: bpm / maxHr };
    if (pct >= 70) return { zone: 3, name: 'ZONE 3 • AEROBIC', color: '#eab308', ratio: bpm / maxHr };
    if (pct >= 60) return { zone: 2, name: 'ZONE 2 • FAT BURN', color: '#22c55e', ratio: bpm / maxHr };
    return { zone: 1, name: 'ZONE 1 • WARM UP', color: '#38bdf8', ratio: Math.max(0.25, bpm / maxHr) };
}

function drawMediaCover(ctx, media, mW, mH, cW, cH) {
    const scale = Math.max(cW / mW, cH / mH);
    const dW = mW * scale, dH = mH * scale;
    ctx.drawImage(media, (cW - dW) / 2, (cH - dH) / 2, dW, dH);
}

function drawHeartIcon(ctx, cx, cy, size, color) {
    ctx.save();
    ctx.fillStyle = color;
    ctx.shadowColor = color;
    ctx.shadowBlur = 10;
    ctx.beginPath();
    const t = size * 0.3;
    ctx.moveTo(cx, cy + t);
    ctx.bezierCurveTo(cx, cy, cx - size / 2, cy, cx - size / 2, cy + t);
    ctx.bezierCurveTo(cx - size / 2, cy + (size + t) / 2, cx, cy + (size + t) / 1.8, cx, cy + size);
    ctx.bezierCurveTo(cx, cy + (size + t) / 1.8, cx + size / 2, cy + (size + t) / 2, cx + size / 2, cy + t);
    ctx.bezierCurveTo(cx + size / 2, cy, cx, cy, cx, cy + t);
    ctx.closePath();
    ctx.fill();
    ctx.restore();
}

// Gambar Judul Kiri Atas (Mengikuti Font & Warna Pilihan User)
function drawTopLeftTitleCard(ctx, fontFam, accentColor, bgOpacity) {
    if (!document.getElementById('showTopTitle').checked) return;

    const tagText  = document.getElementById('titleTag').value.toUpperCase();
    const mainText = document.getElementById('titleMain').value.toUpperCase();
    const subText  = document.getElementById('titleSub').value.toUpperCase();

    ctx.save();
    ctx.translate(titlePos.x, titlePos.y);

    ctx.font = `900 28px ${fontFam}`;
    const mainW = ctx.measureText(mainText).width;
    ctx.font = `700 13px ${fontFam}`;
    const subW = ctx.measureText(subText).width;
    const cardW = Math.max(310, Math.max(mainW, subW) + 50);
    const cardH = 88;

    if (bgOpacity > 0.02) {
        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.lineTo(cardW, 0);
        ctx.lineTo(cardW - 16, cardH);
        ctx.lineTo(0, cardH);
        ctx.closePath();
        ctx.fillStyle = `rgba(12, 18, 32, ${bgOpacity})`;
        ctx.fill();
    }

    ctx.fillStyle = accentColor;
    ctx.fillRect(0, 0, 6, cardH);

    ctx.shadowColor = 'rgba(0,0,0,0.8)';
    ctx.shadowBlur = 6;

    ctx.fillStyle = accentColor;
    ctx.font = `700 12px ${fontFam}`;
    ctx.fillText(tagText, 18, 22);

    ctx.fillStyle = '#ffffff';
    ctx.font = `900 27px ${fontFam}`;
    ctx.fillText(mainText, 18, 53);

    ctx.fillStyle = '#cbd5e1';
    ctx.font = `600 13px ${fontFam}`;
    ctx.fillText(subText, 18, 74);

    ctx.restore();
}

// Ambil teks metrik kanan sesuai mode (Target Reps / EMOM / AMRAP / Free)
function getWorkoutMetrics(clampedElapsed, currentReps, currentRound, mode, targetVal) {
    if (mode === 'target_reps') {
        const pct = Math.min(100, Math.round((currentReps / targetVal) * 100));
        return {
            topLabel: 'REPETITIONS',
            bigPrimary: `${currentReps}`,
            bigSuffix: `/${targetVal}`,
            subBadge: `${pct}% DONE`,
            progress: Math.min(1, currentReps / targetVal)
        };
    } else if (mode === 'emom') {
        const curMin = Math.min(targetVal, Math.floor(clampedElapsed / 60) + 1);
        const secInMin = Math.floor(clampedElapsed % 60);
        const rem = 60 - secInMin;
        return {
            topLabel: `EMOM MIN ${curMin}/${targetVal}`,
            bigPrimary: `00:${String(rem === 60 ? 0 : rem).padStart(2, '0')}`,
            bigSuffix: '',
            subBadge: `REPS: ${currentReps}`,
            progress: secInMin / 60
        };
    } else if (mode === 'amrap') {
        return {
            topLabel: 'AMRAP ROUND',
            bigPrimary: `R${currentRound}`,
            bigSuffix: '',
            subBadge: `${currentReps} REPS`,
            progress: Math.min(1, clampedElapsed / workoutDurationSec)
        };
    } else {
        return {
            topLabel: 'TOTAL REPS',
            bigPrimary: `${currentReps}`,
            bigSuffix: '',
            subBadge: 'ACTIVE',
            progress: Math.min(1, clampedElapsed / workoutDurationSec)
        };
    }
}

// ============================================================================
// 5 STYLE RENDERER HUD (TERMASUK DJI OSMO ACTION GAUGE & DJI TELEMETRY WAVE)
// ============================================================================

// STYLE 1: DJI OSMO / ACTION MIMO GAUGE (2 Circular Gauges + Center Timer)
function drawStyleDjiGauge(ctx, data) {
    const { bpm, zInfo, clockStr, mInfo, fontFam, accentColor, heartColor, bgOpacity, beatScale } = data;
    const w = 510, h = 195;

    if (bgOpacity > 0.02) {
        ctx.fillStyle = `rgba(10, 15, 26, ${bgOpacity})`;
        ctx.beginPath(); ctx.roundRect(0, 0, w, h, 98); ctx.fill();
        ctx.strokeStyle = 'rgba(255,255,255,0.14)';
        ctx.lineWidth = 1.5; ctx.stroke();
    }

    ctx.shadowColor = 'rgba(0,0,0,0.85)';
    ctx.shadowBlur = 8;

    // --- GAUGE KIRI: HEART RATE RING (Mirip Tachometer DJI Mimo) ---
    const cx1 = 98, cy1 = 98, r1 = 68;
    const startAng = Math.PI * 0.75;
    const endAng   = Math.PI * 2.25;
    const totalArc = endAng - startAng;

    // Track Busur Belakang
    ctx.beginPath();
    ctx.arc(cx1, cy1, r1, startAng, endAng);
    ctx.strokeStyle = 'rgba(255,255,255,0.15)';
    ctx.lineWidth = 11;
    ctx.lineCap = 'round';
    ctx.stroke();

    // Busur Aktif HR
    ctx.beginPath();
    ctx.arc(cx1, cy1, r1, startAng, startAng + totalArc * Math.min(1, zInfo.ratio));
    ctx.strokeStyle = heartColor;
    ctx.lineWidth = 11;
    ctx.lineCap = 'round';
    ctx.stroke();

    // Angka BPM di Tengah Cincin Kiri
    ctx.textAlign = 'center';
    ctx.fillStyle = '#ffffff';
    ctx.font = `900 40px ${fontFam}`;
    ctx.fillText(String(bpm), cx1, cy1 + 8);

    ctx.fillStyle = '#cbd5e1';
    ctx.font = `700 12px ${fontFam}`;
    ctx.fillText('BPM', cx1, cy1 + 26);

    ctx.save();
    ctx.translate(cx1, cy1 - 36);
    ctx.scale(beatScale, beatScale);
    drawHeartIcon(ctx, 0, -10, 22, heartColor);
    ctx.restore();

    ctx.fillStyle = zInfo.color;
    ctx.font = `700 11px ${fontFam}`;
    ctx.fillText(`Z${zInfo.zone}`, cx1, cy1 + 58);

    // --- TENGAH: WORKOUT TIME & ZONE ---
    const midX = 255;
    ctx.fillStyle = accentColor;
    ctx.font = `700 12px ${fontFam}`;
    ctx.fillText('WORKOUT TIME', midX, 62);

    ctx.fillStyle = '#ffffff';
    ctx.font = `900 38px ${fontFam}`;
    ctx.fillText(clockStr, midX, 105);

    // 5 Titik Indikator Zona DJI
    const zColors = ['#38bdf8', '#22c55e', '#eab308', '#f97316', '#ef4444'];
    for (let z = 1; z <= 5; z++) {
        ctx.fillStyle = (z <= zInfo.zone) ? zColors[z - 1] : 'rgba(255,255,255,0.18)';
        ctx.beginPath();
        ctx.roundRect(midX - 55 + (z - 1) * 23, 122, 18, 6, 3);
        ctx.fill();
    }
    ctx.fillStyle = '#94a3b8';
    ctx.font = `700 11px ${fontFam}`;
    ctx.fillText(zInfo.name, midX, 148);

    // --- GAUGE KANAN: REPETISI / PROGRESS RING ---
    const cx2 = 412, cy2 = 98, r2 = 68;
    ctx.beginPath();
    ctx.arc(cx2, cy2, r2, startAng, endAng);
    ctx.strokeStyle = 'rgba(255,255,255,0.15)';
    ctx.lineWidth = 11;
    ctx.lineCap = 'round';
    ctx.stroke();

    ctx.beginPath();
    ctx.arc(cx2, cy2, r2, startAng, startAng + totalArc * Math.max(0.02, mInfo.progress));
    ctx.strokeStyle = accentColor;
    ctx.lineWidth = 11;
    ctx.lineCap = 'round';
    ctx.stroke();

    ctx.fillStyle = '#cbd5e1';
    ctx.font = `700 11px ${fontFam}`;
    ctx.fillText(mInfo.topLabel, cx2, cy2 - 26);

    ctx.save();
    ctx.translate(cx2, cy2 + 10);
    ctx.scale(repPopScale, repPopScale);
    ctx.fillStyle = '#ffffff';
    ctx.font = `900 32px ${fontFam}`;
    ctx.fillText(`${mInfo.bigPrimary}${mInfo.bigSuffix}`, 0, 0);
    ctx.restore();

    ctx.fillStyle = accentColor;
    ctx.font = `800 13px ${fontFam}`;
    ctx.fillText(mInfo.subBadge, cx2, cy2 + 48);

    ctx.textAlign = 'left';
}

// STYLE 2: DJI TELEMETRY WAVE (Live ECG Line + 5 Zone Blocks + Frameless Look)
function drawStyleDjiEcg(ctx, data) {
    const { bpm, zInfo, clockStr, mInfo, fontFam, accentColor, heartColor, bgOpacity, beatScale } = data;
    const w = 520, h = 165;

    if (bgOpacity > 0.02) {
        ctx.fillStyle = `rgba(10, 15, 26, ${bgOpacity})`;
        ctx.beginPath(); ctx.roundRect(0, 0, w, h, 14); ctx.fill();
    }

    ctx.shadowColor = 'rgba(0,0,0,0.9)';
    ctx.shadowBlur = 8;

    // Kiri: Heart Rate + 5 Zone Blocks
    ctx.save();
    ctx.translate(34, 42);
    ctx.scale(beatScale, beatScale);
    drawHeartIcon(ctx, 0, -14, 30, heartColor);
    ctx.restore();

    ctx.fillStyle = '#ffffff';
    ctx.font = `900 54px ${fontFam}`;
    ctx.fillText(String(bpm), 60, 62);
    const bw = ctx.measureText(String(bpm)).width;
    ctx.fillStyle = heartColor;
    ctx.font = `700 18px ${fontFam}`;
    ctx.fillText('BPM', 64 + bw, 42);
    ctx.fillStyle = '#cbd5e1';
    ctx.font = `700 12px ${fontFam}`;
    ctx.fillText(`Z${zInfo.zone}`, 64 + bw, 60);

    // Gambar Gelombang Live ECG Berjalan
    ctx.save();
    ctx.beginPath();
    const ecgX = 20, ecgY = 98, ecgW = 195;
    ctx.moveTo(ecgX, ecgY);
    for (let x = 0; x < ecgW; x += 3) {
        const phase = (x + ecgOffset) % 60;
        let yOffset = 0;
        if (phase > 20 && phase < 25) yOffset = -6;
        else if (phase >= 25 && phase < 29) yOffset = 8;
        else if (phase >= 29 && phase < 34) yOffset = -24; // Puncak R-wave ECG
        else if (phase >= 34 && phase < 38) yOffset = 12;
        else if (phase >= 38 && phase < 44) yOffset = -5;
        ctx.lineTo(ecgX + x, ecgY + yOffset);
    }
    ctx.strokeStyle = heartColor;
    ctx.lineWidth = 2.5;
    ctx.stroke();
    ctx.restore();

    // 5 Bar Zona di bawah ECG
    const zColors = ['#38bdf8', '#22c55e', '#eab308', '#f97316', '#ef4444'];
    for (let z = 1; z <= 5; z++) {
        ctx.fillStyle = (z === zInfo.zone) ? zColors[z - 1] : 'rgba(255,255,255,0.18)';
        ctx.fillRect(20 + (z - 1) * 39, 122, 34, (z === zInfo.zone) ? 10 : 5);
    }
    ctx.fillStyle = zInfo.color;
    ctx.font = `700 12px ${fontFam}`;
    ctx.fillText(zInfo.name, 20, 150);

    // Kanan: Timer & Repetisi
    ctx.fillStyle = '#94a3b8';
    ctx.font = `700 12px ${fontFam}`;
    ctx.fillText('TIME', 245, 32);
    ctx.fillStyle = '#ffffff';
    ctx.font = `900 36px ${fontFam}`;
    ctx.fillText(clockStr, 245, 68);

    ctx.fillStyle = accentColor;
    ctx.font = `700 12px ${fontFam}`;
    ctx.fillText(mInfo.topLabel, 375, 32);

    ctx.save();
    ctx.translate(375, 68);
    ctx.scale(repPopScale, repPopScale);
    ctx.fillStyle = '#ffffff';
    ctx.font = `900 36px ${fontFam}`;
    ctx.fillText(`${mInfo.bigPrimary}${mInfo.bigSuffix}`, 0, 0);
    ctx.restore();

    // Bar Progres Panjang di Kanan Bawah
    ctx.fillStyle = '#ffffff';
    ctx.font = `800 16px ${fontFam}`;
    ctx.fillText(mInfo.subBadge, 245, 112);

    ctx.fillStyle = 'rgba(255,255,255,0.18)';
    ctx.beginPath(); ctx.roundRect(245, 124, 250, 10, 5); ctx.fill();
    ctx.fillStyle = accentColor;
    ctx.beginPath(); ctx.roundRect(245, 124, Math.max(8, 250 * mInfo.progress), 10, 5); ctx.fill();
}

// STYLE 3: MINIMALIST FLOATING PILL (Kapsul Horizontal Ringkas)
function drawStyleMinimalPill(ctx, data) {
    const { bpm, zInfo, clockStr, mInfo, fontFam, accentColor, heartColor, bgOpacity, beatScale } = data;
    const w = 540, h = 92;

    if (bgOpacity > 0.02) {
        ctx.fillStyle = `rgba(12, 18, 30, ${bgOpacity})`;
        ctx.beginPath(); ctx.roundRect(0, 0, w, h, 46); ctx.fill();
        ctx.strokeStyle = 'rgba(255,255,255,0.2)';
        ctx.lineWidth = 1.5; ctx.stroke();
    }

    ctx.shadowColor = 'rgba(0,0,0,0.8)';
    ctx.shadowBlur = 6;

    // 1. HR
    ctx.save();
    ctx.translate(38, 42);
    ctx.scale(beatScale, beatScale);
    drawHeartIcon(ctx, 0, -14, 28, heartColor);
    ctx.restore();

    ctx.fillStyle = '#ffffff';
    ctx.font = `900 38px ${fontFam}`;
    ctx.fillText(String(bpm), 62, 54);
    const bw = ctx.measureText(String(bpm)).width;
    ctx.fillStyle = zInfo.color;
    ctx.font = `700 13px ${fontFam}`;
    ctx.fillText('BPM', 66 + bw, 40);
    ctx.fillText(`Z${zInfo.zone}`, 66 + bw, 56);

    // Pemisah 1
    ctx.strokeStyle = 'rgba(255,255,255,0.2)';
    ctx.beginPath(); ctx.moveTo(190, 20); ctx.lineTo(190, 72); ctx.stroke();

    // 2. Timer
    ctx.fillStyle = '#94a3b8';
    ctx.font = `700 11px ${fontFam}`;
    ctx.fillText('WORKOUT TIME', 210, 30);
    ctx.fillStyle = '#ffffff';
    ctx.font = `900 34px ${fontFam}`;
    ctx.fillText(clockStr, 210, 66);

    // Pemisah 2
    ctx.beginPath(); ctx.moveTo(335, 20); ctx.lineTo(335, 72); ctx.stroke();

    // 3. Reps & Progress
    ctx.fillStyle = accentColor;
    ctx.font = `700 11px ${fontFam}`;
    ctx.fillText(`${mInfo.topLabel} • ${mInfo.subBadge}`, 355, 30);

    ctx.save();
    ctx.translate(355, 66);
    ctx.scale(repPopScale, repPopScale);
    ctx.fillStyle = '#ffffff';
    ctx.font = `900 34px ${fontFam}`;
    ctx.fillText(`${mInfo.bigPrimary}${mInfo.bigSuffix}`, 0, 0);
    ctx.restore();
}

// STYLE 4: TACTICAL CYBER STACK (3 Modul Vertikal Bertumpuk — Cocok untuk 9:16)
function drawStyleTacticalStack(ctx, data) {
    const { bpm, zInfo, clockStr, mInfo, fontFam, accentColor, heartColor, bgOpacity, beatScale } = data;
    const cardW = 290, cardH = 68, gap = 8;

    ctx.shadowColor = 'rgba(0,0,0,0.85)';
    ctx.shadowBlur = 6;

    // Modul 1: Heart Rate
    if (bgOpacity > 0.02) {
        ctx.fillStyle = `rgba(12, 18, 30, ${bgOpacity})`;
        ctx.fillRect(0, 0, cardW, cardH);
    }
    ctx.fillStyle = heartColor;
    ctx.fillRect(0, 0, 6, cardH);

    ctx.save();
    ctx.translate(32, 30);
    ctx.scale(beatScale, beatScale);
    drawHeartIcon(ctx, 0, -12, 26, heartColor);
    ctx.restore();

    ctx.fillStyle = '#ffffff';
    ctx.font = `900 40px ${fontFam}`;
    ctx.fillText(`${bpm}`, 56, 48);
    ctx.fillStyle = zInfo.color;
    ctx.font = `700 13px ${fontFam}`;
    ctx.fillText(`BPM • ${zInfo.name}`, 140, 42);

    // Modul 2: Workout Time
    const y2 = cardH + gap;
    if (bgOpacity > 0.02) {
        ctx.fillStyle = `rgba(12, 18, 30, ${bgOpacity})`;
        ctx.fillRect(0, y2, cardW, cardH);
    }
    ctx.fillStyle = '#38bdf8';
    ctx.fillRect(0, y2, 6, cardH);

    ctx.fillStyle = '#94a3b8';
    ctx.font = `700 12px ${fontFam}`;
    ctx.fillText('DURATION', 20, y2 + 24);
    ctx.fillStyle = '#ffffff';
    ctx.font = `900 36px ${fontFam}`;
    ctx.fillText(clockStr, 125, y2 + 48);

    // Modul 3: Repetitions
    const y3 = (cardH + gap) * 2;
    if (bgOpacity > 0.02) {
        ctx.fillStyle = `rgba(12, 18, 30, ${bgOpacity})`;
        ctx.fillRect(0, y3, cardW, cardH + 14);
    }
    ctx.fillStyle = accentColor;
    ctx.fillRect(0, y3, 6, cardH + 14);

    ctx.fillStyle = accentColor;
    ctx.font = `700 12px ${fontFam}`;
    ctx.fillText(`${mInfo.topLabel} (${mInfo.subBadge})`, 20, y3 + 22);

    ctx.save();
    ctx.translate(20, y3 + 58);
    ctx.scale(repPopScale, repPopScale);
    ctx.fillStyle = '#ffffff';
    ctx.font = `900 34px ${fontFam}`;
    ctx.fillText(`${mInfo.bigPrimary}${mInfo.bigSuffix}`, 0, 0);
    ctx.restore();

    ctx.fillStyle = 'rgba(255,255,255,0.2)';
    ctx.fillRect(20, y3 + 68, cardW - 36, 6);
    ctx.fillStyle = accentColor;
    ctx.fillRect(20, y3 + 68, Math.max(6, (cardW - 36) * mInfo.progress), 6);
}

// STYLE 5: BROADCAST PRO CARD (Kotak Klasik + Bottom Progress Bar)
function drawStyleBroadcastCard(ctx, data) {
    const { bpm, zInfo, clockStr, mInfo, fontFam, accentColor, heartColor, bgOpacity, beatScale } = data;
    const boxW = 460, boxH = 215;

    if (bgOpacity > 0.02) {
        ctx.beginPath(); ctx.roundRect(0, 0, boxW, boxH, 16);
        ctx.fillStyle = `rgba(15, 23, 42, ${bgOpacity})`;
        ctx.fill();
        ctx.lineWidth = 2; ctx.strokeStyle = 'rgba(255,255,255,0.2)'; ctx.stroke();
    }

    ctx.fillStyle = accentColor;
    ctx.beginPath(); ctx.roundRect(0, 0, boxW, 6, [16, 16, 0, 0]); ctx.fill();

    ctx.shadowColor = 'rgba(0,0,0,0.8)';
    ctx.shadowBlur = 6;

    // Kiri: HR & Timer
    ctx.save();
    ctx.translate(40, 44);
    ctx.scale(beatScale, beatScale);
    drawHeartIcon(ctx, 0, -14, 32, heartColor);
    ctx.restore();

    ctx.fillStyle = '#ffffff';
    ctx.font = `900 50px ${fontFam}`;
    ctx.fillText(String(bpm), 72, 66);
    const bw = ctx.measureText(String(bpm)).width;
    ctx.fillStyle = '#94a3b8';
    ctx.font = `700 18px ${fontFam}`;
    ctx.fillText('BPM', 76 + bw, 64);

    ctx.fillStyle = zInfo.color;
    ctx.font = `700 13px ${fontFam}`;
    ctx.fillText(zInfo.name, 26, 92);

    ctx.fillStyle = '#94a3b8';
    ctx.font = `700 12px ${fontFam}`;
    ctx.fillText('WORKOUT TIME', 26, 126);
    ctx.fillStyle = '#ffffff';
    ctx.font = `900 38px ${fontFam}`;
    ctx.fillText(clockStr, 24, 166);

    ctx.strokeStyle = 'rgba(255,255,255,0.15)';
    ctx.beginPath(); ctx.moveTo(245, 22); ctx.lineTo(245, 175); ctx.stroke();

    // Kanan: Repetitions
    ctx.fillStyle = accentColor;
    ctx.font = `800 13px ${fontFam}`;
    ctx.fillText(mInfo.topLabel, 265, 42);

    ctx.save();
    ctx.translate(265, 95);
    ctx.scale(repPopScale, repPopScale);
    ctx.fillStyle = '#ffffff';
    ctx.font = `900 46px ${fontFam}`;
    ctx.fillText(`${mInfo.bigPrimary}${mInfo.bigSuffix}`, 0, 0);
    ctx.restore();

    ctx.fillStyle = '#10b981';
    ctx.font = `900 24px ${fontFam}`;
    ctx.fillText(mInfo.subBadge, 265, 145);

    // Bar Bawah
    const barX = 24, barY = 188, barW = boxW - 48, barH = 10;
    ctx.fillStyle = 'rgba(255, 255, 255, 0.15)';
    ctx.beginPath(); ctx.roundRect(barX, barY, barW, barH, 5); ctx.fill();
    ctx.fillStyle = accentColor;
    ctx.beginPath(); ctx.roundRect(barX, barY, Math.max(8, barW * mInfo.progress), barH, 5); ctx.fill();
}

// ============================================================================
// FUNGSI RENDER UTAMA
// ============================================================================
function renderFrame() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const isGreen     = document.getElementById('greenScreenMode').checked;
    const hudStyle    = document.getElementById('hudStyleSelect').value;
    const fontFam     = document.getElementById('fontFamilySelect').value;
    const accentColor = document.getElementById('accentColorPicker').value;
    const heartColor  = document.getElementById('heartColorPicker').value;
    const bgOpacity   = parseFloat(document.getElementById('bgOpacitySlider').value);
    const scale       = parseFloat(document.getElementById('hudScale').value || 1.0);

    if (isGreen) {
        ctx.fillStyle = '#00FF00';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    } else if (useVideoBg && hiddenVideo.readyState >= 2) {
        drawMediaCover(ctx, hiddenVideo, hiddenVideo.videoWidth, hiddenVideo.videoHeight, canvas.width, canvas.height);
    } else if (bgImage) {
        drawMediaCover(ctx, bgImage, bgImage.width, bgImage.height, canvas.width, canvas.height);
    } else {
        const grad = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
        grad.addColorStop(0, '#1e293b');
        grad.addColorStop(1, '#090d16');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = 'rgba(255,255,255,0.12)';
        ctx.font = `700 22px ${fontFam}`;
        ctx.textAlign = 'center';
        ctx.fillText('PREVIEW LAYAR WORKOUT (TEKAN SPASI = +1 REP)', canvas.width / 2, canvas.height / 2);
        ctx.textAlign = 'left';
    }

    // 1. Gambar Judul Kiri Atas
    drawTopLeftTitleCard(ctx, fontFam, accentColor, bgOpacity);

    document.getElementById('lblVideoTime').innerText = formatMMSS(currentVideoSec);
    const workoutElapsedSec = currentVideoSec - introOffsetSec;

    if (workoutElapsedSec < 0) {
        document.getElementById('lblStatusSummary').innerText = `INTRO VIDEO (Mulai 00:00 dalam ${Math.ceil(Math.abs(workoutElapsedSec))} dtk)`;
        return;
    }

    // 2. Siapkan Data Telemetry
    const clampedElapsed = Math.min(workoutElapsedSec, workoutDurationSec);
    const hrIndex    = Math.floor(clampedElapsed / hrIntervalSec);
    const currentBpm = hrValues[hrIndex % hrValues.length] || 142;
    const zInfo      = getZoneInfo(currentBpm);

    const currentReps  = getCompletedRepsAt(clampedElapsed);
    const currentRound = getCompletedRoundsAt(clampedElapsed);
    const mode         = document.getElementById('workoutMode').value;
    const targetVal    = Math.max(1, parseInt(document.getElementById('targetRepsInput').value || 200, 10));
    const mInfo        = getWorkoutMetrics(clampedElapsed, currentReps, currentRound, mode, targetVal);

    if (repPopScale > 1.0) repPopScale = Math.max(1.0, repPopScale - 0.04);
    const beatScale = 1 + Math.sin(pulsePhase) * 0.08;

    const renderData = {
        bpm: currentBpm,
        zInfo,
        clockStr: formatMMSS(clampedElapsed),
        mInfo,
        fontFam,
        accentColor,
        heartColor,
        bgOpacity,
        beatScale
    };

    // 3. Render HUD sesuai Style Pilihan User
    ctx.save();
    ctx.translate(hudPos.x, hudPos.y);
    ctx.scale(scale, scale);

    if (hudStyle === 'dji_gauge') {
        drawStyleDjiGauge(ctx, renderData);
    } else if (hudStyle === 'dji_ecg') {
        drawStyleDjiEcg(ctx, renderData);
    } else if (hudStyle === 'minimal_pill') {
        drawStyleMinimalPill(ctx, renderData);
    } else if (hudStyle === 'tactical_stack') {
        drawStyleTacticalStack(ctx, renderData);
    } else {
        drawStyleBroadcastCard(ctx, renderData);
    }

    ctx.restore();

    document.getElementById('lblStatusSummary').innerText = `${mInfo.topLabel}: ${mInfo.bigPrimary}${mInfo.bigSuffix} (${mInfo.subBadge}) | ♥ ${currentBpm} BPM`;
}

function onSliderChange(val) {
    currentVideoSec = parseFloat(val);
    if (useVideoBg && Math.abs(hiddenVideo.currentTime - currentVideoSec) > 0.2) {
        hiddenVideo.currentTime = currentVideoSec;
    }
    renderFrame();
}

function animationLoop(timestamp) {
    if (!isPlayingSim) return;
    if (!lastTick) lastTick = timestamp;
    const deltaSec = ((timestamp - lastTick) / 1000) * playbackSpeed;
    lastTick = timestamp;

    const workoutElapsedSec = Math.max(0, currentVideoSec - introOffsetSec);
    const hrIndex = Math.floor(workoutElapsedSec / hrIntervalSec);
    const bpm = hrValues[hrIndex % hrValues.length] || 140;
    pulsePhase += (deltaSec / playbackSpeed) * (bpm / 60) * Math.PI * 2;
    ecgOffset  += (deltaSec / playbackSpeed) * 90;

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
            hiddenVideo.playbackRate = playbackSpeed;
            hiddenVideo.currentTime = currentVideoSec;
            hiddenVideo.play();
        }
        btn.innerText = '⏸ Pause';
        animFrameId = requestAnimationFrame(animationLoop);
    }
}

document.getElementById('bgVideoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    hiddenVideo.src = URL.createObjectURL(file);
    hiddenVideo.onloadedmetadata = function() {
        useVideoBg = true;
        currentVideoSec = 0;
        if (hiddenVideo.duration && hiddenVideo.duration > 5) {
            document.getElementById('timelineSlider').max = Math.ceil(hiddenVideo.duration);
        }
        hiddenVideo.currentTime = 0;
    };
    hiddenVideo.onseeked = () => renderFrame();
});

document.getElementById('bgImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(evt) {
        const img = new Image();
        img.onload = function() { bgImage = img; useVideoBg = false; renderFrame(); };
        img.src = evt.target.result;
    };
    reader.readAsDataURL(file);
});

canvas.addEventListener('mousedown', function(e) {
    const rect = canvas.getBoundingClientRect();
    const mx = (e.clientX - rect.left) * (canvas.width / rect.width);
    const my = (e.clientY - rect.top) * (canvas.height / rect.height);

    if (my < canvas.height * 0.35) {
        dragTarget = 'title';
        dragOffset.x = mx - titlePos.x;
        dragOffset.y = my - titlePos.y;
    } else {
        dragTarget = 'hud';
        dragOffset.x = mx - hudPos.x;
        dragOffset.y = my - hudPos.y;
    }
});

window.addEventListener('mousemove', function(e) {
    if (!dragTarget) return;
    const rect = canvas.getBoundingClientRect();
    const mx = (e.clientX - rect.left) * (canvas.width / rect.width);
    const my = (e.clientY - rect.top) * (canvas.height / rect.height);

    if (dragTarget === 'title') {
        titlePos.x = Math.max(0, Math.min(canvas.width - 200, mx - dragOffset.x));
        titlePos.y = Math.max(0, Math.min(canvas.height - 80, my - dragOffset.y));
    } else {
        hudPos.x = Math.max(0, Math.min(canvas.width - 250, mx - dragOffset.x));
        hudPos.y = Math.max(0, Math.min(canvas.height - 100, my - dragOffset.y));
    }
    renderFrame();
});

window.addEventListener('mouseup', () => { dragTarget = null; });

function extractFromGraphImage(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(evt) {
        const img = new Image();
        img.onload = function() {
            const off = document.createElement('canvas');
            off.width = img.width; off.height = img.height;
            const octx = off.getContext('2d');
            octx.drawImage(img, 0, 0);
            const steps = Math.ceil(workoutDurationSec / hrIntervalSec);
            const extracted = [];
            const data = octx.getImageData(0, 0, img.width, img.height).data;
            for (let i = 0; i < steps; i++) {
                const x = Math.floor((i / steps) * (img.width * 0.8) + (img.width * 0.1));
                let foundY = -1;
                for (let y = Math.floor(img.height * 0.15); y < Math.floor(img.height * 0.85); y++) {
                    const idx = (y * img.width + x) * 4;
                    if (data[idx] > 180 && data[idx+1] < 110 && data[idx+2] < 110 && (data[idx] - data[idx+1]) > 80) {
                        foundY = y; break;
                    }
                }
                if (foundY !== -1) {
                    const norm = 1 - ((foundY - img.height * 0.15) / (img.height * 0.7));
                    extracted.push(Math.max(60, Math.min(200, Math.round(95 + norm * 85))));
                } else {
                    extracted.push(extracted.length ? extracted[extracted.length - 1] : 138);
                }
            }
            document.getElementById('hrDataInput').value = extracted.join(', ');
            parseHrInput();
        };
        img.src = evt.target.result;
    };
    reader.readAsDataURL(file);
}

function uploadFileToPhp(input) {
    const file = input.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('action', 'parse_file');
    fd.append('hr_file', file);
    fetch('', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                document.getElementById('hrDataInput').value = res.data.join(', ');
                parseHrInput();
            }
        });
}

function downloadSnapshot() {
    renderFrame();
    const a = document.createElement('a');
    a.download = `workout_dji_hud_${formatMMSS(currentVideoSec).replace(':','-')}.png`;
    a.href = canvas.toDataURL('image/png');
    a.click();
}

let mediaRecorder = null;
let recordedChunks = [];

function getSupportedMimeType() {
    const types = ['video/mp4;codecs=avc1.42E01E,mp4a.40.2', 'video/mp4;codecs=avc1', 'video/mp4', 'video/webm;codecs=h264', 'video/webm'];
    for (const t of types) { if (MediaRecorder.isTypeSupported(t)) return t; }
    return '';
}

function toggleRecording() {
    const btn = document.getElementById('btnRecord');
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
        btn.innerText = '⏺ Export Video (.MP4)';
        btn.className = 'bg-rose-600 hover:bg-rose-500 text-white text-xs md:text-sm font-bold px-4 py-2 rounded-lg transition shadow-lg';
        if (isPlayingSim) togglePlaySim();
        return;
    }

    setPlaybackSpeed(1);
    const stream = canvas.captureStream(30);
    if (useVideoBg && hiddenVideo.captureStream) {
        try {
            const audioTracks = hiddenVideo.captureStream().getAudioTracks();
            if (audioTracks.length > 0) stream.addTrack(audioTracks[0]);
        } catch (e) {}
    }

    const mime = getSupportedMimeType();
    mediaRecorder = new MediaRecorder(stream, mime ? { mimeType: mime, videoBitsPerSecond: 6000000 } : { videoBitsPerSecond: 6000000 });
    recordedChunks = [];
    mediaRecorder.ondataavailable = e => { if (e.data && e.data.size > 0) recordedChunks.push(e.data); };
    mediaRecorder.onstop = () => {
        const blob = new Blob(recordedChunks, { type: mime || 'video/mp4' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'workout_dji_telemetry.mp4';
        a.click();
    };

    mediaRecorder.start(1000);
    if (!isPlayingSim) togglePlaySim();
    btn.innerText = '⏹ Stop & Simpan (.MP4)';
    btn.className = 'bg-amber-600 hover:bg-amber-500 text-white text-xs md:text-sm font-bold px-4 py-2 rounded-lg transition shadow-lg animate-pulse';
}

updateTiming();
generateSampleHr();
renderFrame();
</script>
</body>
</html>
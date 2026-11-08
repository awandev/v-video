<?php
/**
 * DAILY WORKOUT CONTENT STUDIO v11.0 (SIDEBAR & SLIDE-OUT SIDE MODAL EDITION)
 * Fitur Utama UI Baru:
 * 1. Navigasi Sidebar Ikon di Kiri + Panel Modal Samping (Drawer) yang muncul sesuai menu yang diklik
 * 2. Setiap Modal Widget (Heart Rate, Timer, Judul, Workout, Watermark, Result) memiliki saklar Aktif/Nonaktif, Style, Font, Warna & Ukuran masing-masing
 * 3. Modal bisa ditutup (Collapse) agar area Preview Kanvas Video menjadi ekstra luas
 * 4. Mencakup seluruh fitur v10.0 (17 Style HR termasuk Heart+Angka, 24 Style Timer, 20 Template Result, 10 Watermark, Audio Mixer MP3, LUT .CUBE, Trim, Timelapse & Full Undo/Redo)
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
    <title>Daily Workout Studio v11.0 - Sidebar & Side-Modal UI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:ital,wght@0,700;0,900;1,800&family=Bebas+Neue&family=Black+Ops+One&family=Chakra+Petch:ital,wght@0,600;0,700;1,700&family=JetBrains+Mono:wght@700;800&family=Montserrat:wght@700;800;900&family=Orbitron:wght@700;900&family=Oswald:wght@600;700&family=Rajdhani:wght@700&family=Russo+One&family=Teko:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #070a12; color: #f8fafc; font-family: 'Montserrat', system-ui, sans-serif; overflow-x: hidden; }
        .sidebar-btn { transition: all 0.15s ease; border-left: 3px solid transparent; }
        .sidebar-btn.active { background: rgba(30, 41, 59, 0.95); border-left-color: #38bdf8; color: #ffffff; }
        .side-modal { background: rgba(15, 23, 42, 0.97); backdrop-filter: blur(16px); border-right: 1px solid rgba(255,255,255,0.09); transition: width 0.2s ease, opacity 0.15s ease; }
        canvas { max-width: 100%; max-height: 70vh; width: auto; height: auto; border-radius: 0.75rem; box-shadow: 0 20px 35px -10px rgba(0,0,0,0.85); }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        input[type="color"] { -webkit-appearance: none; border: none; width: 26px; height: 26px; border-radius: 6px; cursor: pointer; background: transparent; }
        input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
        input[type="color"]::-webkit-color-swatch { border: 1px solid rgba(255,255,255,0.35); border-radius: 6px; }
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden">

    <!-- HEADER BAR ATAS -->
    <header class="h-14 bg-slate-950 border-b border-slate-800/80 px-4 flex items-center justify-between shrink-0 z-30">
        <div class="flex items-center gap-3">
            <span class="bg-gradient-to-r from-sky-400 to-emerald-400 text-slate-950 px-2.5 py-0.5 rounded text-xs font-black tracking-wider">V11.0 STUDIO</span>
            <h1 class="text-sm md:text-base font-black tracking-tight text-white hidden sm:block">DAILY WORKOUT OVERLAY STUDIO</h1>
            <span id="undoStatusBadge" class="text-[10px] bg-slate-800 text-sky-300 border border-slate-700 px-2 py-0.5 rounded font-semibold">Auto-Save Aktif</span>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="undoAction()" id="btnUndo" disabled class="bg-slate-900 hover:bg-slate-800 disabled:opacity-40 text-amber-300 text-xs font-bold px-2.5 py-1.5 rounded-lg border border-slate-800 transition" title="Shortcut: Ctrl+Z / Backspace">
                ↩ Undo (0)
            </button>
            <button onclick="redoAction()" id="btnRedo" disabled class="bg-slate-900 hover:bg-slate-800 disabled:opacity-40 text-sky-300 text-xs font-bold px-2.5 py-1.5 rounded-lg border border-slate-800 transition" title="Shortcut: Ctrl+Y">
                ↪ Redo (0)
            </button>
            <select id="aspectRatioSelect" onchange="changeAspectRatio(this.value)" class="bg-slate-900 border border-slate-700 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg">
                <option value="9:16">📱 Reels / TikTok (9:16)</option>
                <option value="16:9">📺 YouTube (16:9)</option>
                <option value="1:1">🟦 Square (1:1)</option>
            </select>
            <button onclick="downloadSnapshot()" class="bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                📸 PNG
            </button>
            <button id="btnRecord" onclick="toggleRecording()" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold px-4 py-1.5 rounded-lg transition shadow-lg">
                ⏺ Export (.MP4)
            </button>
        </div>
    </header>

    <!-- WORKSPACE UTAMA: SIDEBAR KIRI + SIDE MODAL + AREA KANVAS -->
    <div class="flex-1 flex overflow-hidden relative">

        <!-- 1. SIDEBAR MENU IKON KIRI -->
        <aside class="w-20 md:w-24 bg-slate-950 border-r border-slate-800/80 flex flex-col justify-between py-2 shrink-0 z-20 select-none overflow-y-auto">
            <div class="space-y-1">
                <button onclick="openSideModal('workout')" id="nav_workout" class="sidebar-btn active w-full py-3 px-1 flex flex-col items-center gap-1 text-slate-400 hover:text-white">
                    <span class="text-xl">💪</span>
                    <span class="text-[10px] font-extrabold tracking-tight text-center leading-tight">Workout<br>& Spasi</span>
                </button>
                <button onclick="openSideModal('hr')" id="nav_hr" class="sidebar-btn w-full py-3 px-1 flex flex-col items-center gap-1 text-slate-400 hover:text-white">
                    <span class="text-xl">❤️</span>
                    <span class="text-[10px] font-extrabold tracking-tight text-center leading-tight">Heart<br>Rate</span>
                </button>
                <button onclick="openSideModal('timer')" id="nav_timer" class="sidebar-btn w-full py-3 px-1 flex flex-col items-center gap-1 text-slate-400 hover:text-white">
                    <span class="text-xl">⏱️</span>
                    <span class="text-[10px] font-extrabold tracking-tight text-center leading-tight">Timer<br>Durasi</span>
                </button>
                <button onclick="openSideModal('title')" id="nav_title" class="sidebar-btn w-full py-3 px-1 flex flex-col items-center gap-1 text-slate-400 hover:text-white">
                    <span class="text-xl">🏷️</span>
                    <span class="text-[10px] font-extrabold tracking-tight text-center leading-tight">Judul<br>Atas</span>
                </button>
                <button onclick="openSideModal('watermark')" id="nav_watermark" class="sidebar-btn w-full py-3 px-1 flex flex-col items-center gap-1 text-slate-400 hover:text-white">
                    <span class="text-xl">📱</span>
                    <span class="text-[10px] font-extrabold tracking-tight text-center leading-tight">Sosmed<br>Watermark</span>
                </button>
                <button onclick="openSideModal('result')" id="nav_result" class="sidebar-btn w-full py-3 px-1 flex flex-col items-center gap-1 text-slate-400 hover:text-white">
                    <span class="text-xl">🏆</span>
                    <span class="text-[10px] font-extrabold tracking-tight text-center leading-tight">Result<br>Rekap</span>
                </button>
                <button onclick="openSideModal('audio')" id="nav_audio" class="sidebar-btn w-full py-3 px-1 flex flex-col items-center gap-1 text-slate-400 hover:text-white">
                    <span class="text-xl">🎵</span>
                    <span class="text-[10px] font-extrabold tracking-tight text-center leading-tight">Audio &<br>Backsound</span>
                </button>
                <button onclick="openSideModal('video')" id="nav_video" class="sidebar-btn w-full py-3 px-1 flex flex-col items-center gap-1 text-slate-400 hover:text-white">
                    <span class="text-xl">🎬</span>
                    <span class="text-[10px] font-extrabold tracking-tight text-center leading-tight">Video,<br>LUT & Trim</span>
                </button>
            </div>

            <div class="px-2 pt-2 border-t border-slate-800/80 text-center">
                <button onclick="resetWidgetPositions()" class="w-full bg-slate-900 hover:bg-slate-800 text-[10px] text-slate-300 font-bold py-1.5 rounded border border-slate-800" title="Kembalikan posisi semua elemen ke tempat semula">
                    📐 Reset<br>Posisi
                </button>
            </div>
        </aside>

        <!-- 2. SIDE MODAL / DRAWER PANEL (MUNCUL DI SAMPING SIDEBAR SAAT MENU DIKLIK) -->
        <div id="sideModalContainer" class="side-modal w-80 sm:w-96 md:w-[420px] h-full flex flex-col shrink-0 z-10 overflow-hidden">
            
            <!-- Header Modal Samping -->
            <div class="h-12 px-4 border-b border-slate-800 flex items-center justify-between shrink-0 bg-slate-900/70">
                <span id="sideModalTitle" class="text-xs font-black uppercase tracking-wider text-sky-400 flex items-center gap-2">
                    💪 Pengaturan Workout & Spasi
                </span>
                <button onclick="closeSideModal()" class="text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold" title="Sembunyikan Panel Samping">
                    ✕
                </button>
            </div>

            <!-- Isi Modal Samping (Berisi 8 Tab Konten yang Berganti Sesuai Menu) -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">

                <!-- ==================== TAB 1: WORKOUT & SPASI ==================== -->
                <div id="tab_workout" class="modal-pane space-y-3.5">
                    <div class="flex items-center justify-between bg-slate-900 p-2.5 rounded-lg border border-emerald-500/40">
                        <div>
                            <div class="text-xs font-bold text-white">Status Tampilan Daftar Workout</div>
                            <div class="text-[10px] text-slate-400">Tampilkan daftar gerakan di tengah video</div>
                        </div>
                        <label class="flex items-center gap-1.5 bg-emerald-500/20 border border-emerald-500/40 px-3 py-1 rounded-lg text-xs font-extrabold text-emerald-300 cursor-pointer">
                            <input type="checkbox" id="showWorkoutList" checked onchange="saveSettingState(); renderFrame();" class="rounded bg-slate-950 text-emerald-500">
                            <span>Aktif</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-12 gap-2 bg-slate-900/90 p-2.5 rounded-lg border border-slate-800">
                        <div class="col-span-5">
                            <label class="block text-[10px] text-amber-300 font-extrabold mb-0.5">✏️ Header Daftar:</label>
                            <input type="text" id="wodHeader" value="10 ROUND" placeholder="10 ROUND / AMRAP 20" oninput="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-amber-500/50 rounded px-2 py-1 text-xs text-amber-300 font-black">
                        </div>
                        <div class="col-span-4">
                            <label class="block text-[10px] text-slate-300 font-bold mb-0.5">Format Angka:</label>
                            <select id="listDisplayFormat" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded px-1.5 py-1 text-[11px] text-white font-bold">
                                <option value="target_first_live">10 PUSH UPS (Tgt->Live)</option>
                                <option value="static_target">10 PUSH UPS (Statis)</option>
                                <option value="live_first">0 PUSH UPS (Live Depan)</option>
                                <option value="name_count">PUSH UPS 0 (Live Blkg)</option>
                                <option value="count_target">PUSH UPS 0/10</option>
                                <option value="front_slash">0/10 PUSH UPS</option>
                            </select>
                        </div>
                        <div class="col-span-3">
                            <label class="block text-[10px] text-slate-300 font-bold mb-0.5">Mode Spasi:</label>
                            <select id="spacebarMode" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-emerald-500/40 rounded px-1 py-1 text-[11px] text-emerald-300 font-bold">
                                <option value="manual_select">🎯 Key 1-4</option>
                                <option value="auto_cycle">🔄 Auto</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5" id="exerciseRowsContainer"></div>

                    <!-- Style Bungkus, Font, Warna & Opacity Daftar Workout -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-lg p-3 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-emerald-400 uppercase">🎨 Style Kotak Bungkus & Font Workout</span>
                            <select id="listAlignSelect" onchange="saveSettingState(); renderFrame();" class="bg-slate-950 border border-slate-700 rounded px-2 py-0.5 text-[10px] text-slate-200 font-bold">
                                <option value="center">☰ Rata Tengah</option>
                                <option value="left">☰ Rata Kiri</option>
                                <option value="right">☰ Rata Kanan</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <div>
                                <label class="block text-[10px] text-slate-400 mb-0.5">Pilih Style Bungkus (10 Model):</label>
                                <select id="listBoxStyleSelect" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-emerald-500/40 rounded px-2 py-1.5 text-xs text-amber-300 font-bold">
                                    <option value="glass_card">1. Glassmorphism Box (Kotak Halus)</option>
                                    <option value="header_banner">2. Header Banner Card (Pita "10 ROUND")</option>
                                    <option value="row_pills">3. Individual Row Pills (Kapsul per Baris)</option>
                                    <option value="left_bar_card">4. Left Neon Accent Bar (Garis Samping)</option>
                                    <option value="tactical_brackets">5. Tactical Corner Brackets (Siku Militer)</option>
                                    <option value="slanted_sport">6. Slanted Broadcast Panel (Kotak Miring)</option>
                                    <option value="neon_border">7. Neon Glow Frame (Bingkai Menyala)</option>
                                    <option value="crossfit_board">8. CrossFit Scoreboard (Tabel Garis)</option>
                                    <option value="stencil_stamp">9. Military Stencil Box (Kotak Tegas)</option>
                                    <option value="frameless_shadow">10. Frameless Clean (Tanpa Kotak Latar)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 mb-0.5">Pilih Font Daftar Workout (12 Font):</label>
                                <select id="listFontSelect" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-1.5 text-xs text-white font-bold">
                                    <option value="'Montserrat', sans-serif">1. Montserrat Black (Tebal Modern)</option>
                                    <option value="'Bebas Neue', sans-serif">2. Bebas Neue (Tinggi Sporty)</option>
                                    <option value="'Anton', sans-serif">3. Anton (Impact Tegas)</option>
                                    <option value="'Teko', sans-serif">4. Teko (Athletic HUD)</option>
                                    <option value="'Russo One', sans-serif">5. Russo One (Heavy Gym)</option>
                                    <option value="'Black Ops One', cursive">6. Black Ops One (Military Stencil)</option>
                                    <option value="'Barlow Condensed', sans-serif">7. Barlow Condensed (Broadcast)</option>
                                    <option value="'Oswald', sans-serif">8. Oswald (CrossFit Style)</option>
                                    <option value="'Chakra Petch', sans-serif">9. Chakra Petch (DJI Tactical)</option>
                                    <option value="'Rajdhani', sans-serif">10. Rajdhani (Tech Modern)</option>
                                    <option value="'Orbitron', sans-serif">11. Orbitron (Sci-Fi)</option>
                                    <option value="'JetBrains Mono', monospace">12. JetBrains Mono (Digital)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-1.5 pt-1">
                            <div class="flex items-center gap-1 bg-slate-950 p-1 rounded border border-slate-800">
                                <input type="color" id="listHeaderColor" value="#fbbf24" oninput="saveSettingState(); renderFrame();">
                                <span class="text-[9px] text-slate-300 leading-tight">Header</span>
                            </div>
                            <div class="flex items-center gap-1 bg-slate-950 p-1 rounded border border-slate-800">
                                <input type="color" id="listActiveColor" value="#10b981" oninput="saveSettingState(); renderFrame();">
                                <span class="text-[9px] text-slate-300 leading-tight">Aktif</span>
                            </div>
                            <div class="flex items-center gap-1 bg-slate-950 p-1 rounded border border-slate-800">
                                <input type="color" id="listTextColor" value="#ffffff" oninput="saveSettingState(); renderFrame();">
                                <span class="text-[9px] text-slate-300 leading-tight">Teks</span>
                            </div>
                            <div class="flex items-center gap-1 bg-slate-950 p-1 rounded border border-slate-800">
                                <input type="color" id="listBgColor" value="#0f172a" oninput="saveSettingState(); renderFrame();">
                                <span class="text-[9px] text-slate-300 leading-tight">Kotak</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div>
                                <div class="flex justify-between text-[10px] text-slate-400"><span>Opacity Kotak:</span><b id="lblListOpacity" class="text-emerald-300">70%</b></div>
                                <input type="range" id="listBgOpacity" min="0" max="0.95" step="0.05" value="0.70" oninput="document.getElementById('lblListOpacity').innerText=Math.round(this.value*100)+'%'; saveSettingState(); renderFrame();" class="w-full accent-emerald-500">
                            </div>
                            <div>
                                <div class="flex justify-between text-[10px] text-slate-400"><span>Ukuran Skala:</span><b id="lblListScale" class="text-sky-300">100%</b></div>
                                <input type="range" id="listScaleSlider" min="0.55" max="1.6" step="0.05" value="1.0" oninput="document.getElementById('lblListScale').innerText=Math.round(this.value*100)+'%'; saveSettingState(); renderFrame();" class="w-full accent-sky-500">
                            </div>
                        </div>
                    </div>

                    <!-- Log Ketukan Repetisi -->
                    <div class="bg-slate-950/90 border border-slate-800 rounded-lg p-2.5 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-sky-400">📋 Log Ketukan (Klik waktu utk lompat, ❌ utk hapus):</span>
                            <div class="flex items-center gap-1">
                                <select id="debounceMsSelect" class="bg-slate-900 border border-amber-500/40 rounded px-1 py-0.5 text-[9px] text-amber-300 font-bold" title="Anti-Double Tap">
                                    <option value="250">🛡️ 0.25s</option>
                                    <option value="500">🛡️ 0.50s</option>
                                    <option value="0">Off</option>
                                </select>
                                <button type="button" onclick="generateDemoMultiReps()" class="text-[10px] bg-slate-800 hover:bg-slate-700 text-sky-300 px-1.5 py-0.5 rounded">⚡ Demo</button>
                                <button type="button" onclick="clearAllReps()" class="text-[10px] bg-rose-950 hover:bg-rose-800 text-rose-300 px-1.5 py-0.5 rounded">🗑 Reset</button>
                            </div>
                        </div>
                        <div id="eventLogList" class="max-h-28 overflow-y-auto space-y-1 pr-1 text-[10px] font-mono"></div>
                    </div>
                </div>

                <!-- ==================== TAB 2: HEART RATE MODAL ==================== -->
                <div id="tab_hr" class="modal-pane hidden space-y-4">
                    <div class="flex items-center justify-between bg-slate-900 p-3 rounded-lg border border-rose-500/40">
                        <div>
                            <div class="text-xs font-bold text-white">Tampilkan Widget Heart Rate</div>
                            <div class="text-[10px] text-slate-400">Aktif/nonaktifkan indikator detak jantung di video</div>
                        </div>
                        <label class="flex items-center gap-1.5 bg-rose-500/20 border border-rose-500/40 px-3 py-1 rounded-lg text-xs font-extrabold text-rose-300 cursor-pointer">
                            <input type="checkbox" id="showHrWidget" checked onchange="saveSettingState(); renderFrame();" class="rounded bg-slate-950 text-rose-500">
                            <span>Aktif</span>
                        </label>
                    </div>

                    <div class="bg-slate-900/90 p-3 rounded-lg border border-slate-800 space-y-3">
                        <div>
                            <label class="block text-xs text-rose-300 font-bold mb-1">❤️ Pilih Style Heart Rate (17 Model):</label>
                            <select id="hudStyleSelect" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-rose-500/50 rounded-lg px-2.5 py-2 text-xs text-amber-300 font-bold">
                                <optgroup label="Simpel: Gambar Heart + Angka Saja">
                                    <option value="heart_num_side">1. Heart + Angka Saja (Samping Bersih)</option>
                                    <option value="heart_num_inside">2. Heart Besar + Angka di Dalam Hati</option>
                                </optgroup>
                                <optgroup label="Telemetri & Sport Watch">
                                    <option value="dji_ring">3. DJI Osmo Single Ring</option>
                                    <option value="dji_ecg">4. DJI Telemetry Wave (ECG)</option>
                                    <option value="apple_fitness">5. Apple Fitness+ Ring</option>
                                    <option value="garmin_arc">6. Garmin Fenix Arc (5 Zona)</option>
                                    <option value="whoop_strain">7. WHOOP Strain Bar</option>
                                    <option value="strava_minimal">8. Strava Clean Floating</option>
                                    <option value="coros_hex">9. COROS Tactical Hexagon</option>
                                    <option value="nike_volt">10. Nike Training Volt Slash</option>
                                    <option value="polar_beat">11. Polar H10 Monitor Badge</option>
                                    <option value="cyber_hud">12. Cyberpunk Corner Brackets</option>
                                    <option value="crossfit_tile">13. CrossFit Games Tile</option>
                                    <option value="heart_fill">14. Beating Heart + Label BPM</option>
                                    <option value="vertical_meter">15. Vertical Zone Meter</option>
                                    <option value="neon_glow">16. Neon Glow Cyber</option>
                                    <option value="glass_badge">17. Minimalist Glass Badge</option>
                                </optgroup>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-slate-300 font-bold mb-1">🔤 Pilih Font Angka Heart Rate & Timer:</label>
                            <select id="fontFamilySelect" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-2 text-xs text-white font-bold">
                                <option value="'Chakra Petch', sans-serif">1. Chakra Petch (DJI Action Cam)</option>
                                <option value="'Bebas Neue', sans-serif">2. Bebas Neue (Tall Sporty)</option>
                                <option value="'Teko', sans-serif">3. Teko (Square Athletic HUD)</option>
                                <option value="'Russo One', sans-serif">4. Russo One (Heavy Tank Gym)</option>
                                <option value="'Rajdhani', sans-serif">5. Rajdhani (Modern Telemetry)</option>
                                <option value="'Black Ops One', cursive">6. Black Ops One (Military)</option>
                                <option value="'Barlow Condensed', sans-serif">7. Barlow Condensed (Pro)</option>
                                <option value="'Anton', sans-serif">8. Anton (Thick Impact)</option>
                                <option value="'Montserrat', sans-serif">9. Montserrat Black (Clean)</option>
                                <option value="'JetBrains Mono', monospace">10. JetBrains Mono (Digital)</option>
                                <option value="'Orbitron', sans-serif">11. Orbitron (Sci-Fi)</option>
                                <option value="'Oswald', sans-serif">12. Oswald (CrossFit)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div class="flex items-center gap-2 bg-slate-950 p-2 rounded-lg border border-slate-800">
                                <input type="color" id="heartColorPicker" value="#ff2a4b" oninput="saveSettingState(); renderFrame();">
                                <span class="text-xs text-slate-300 font-semibold">Warna Hati / HR</span>
                            </div>
                            <div class="flex items-center gap-2 bg-slate-950 p-2 rounded-lg border border-slate-800">
                                <input type="color" id="accentColorPicker" value="#f97316" oninput="saveSettingState(); renderFrame();">
                                <span class="text-xs text-slate-300 font-semibold">Warna Aksen</span>
                            </div>
                        </div>

                        <div class="space-y-2 pt-1">
                            <div>
                                <span class="block text-[11px] text-slate-400 mb-0.5">Gelap Latar Belakang Widget (Opacity):</span>
                                <input type="range" id="bgOpacitySlider" min="0" max="0.95" step="0.05" value="0.60" oninput="renderFrame()" class="w-full accent-rose-500">
                            </div>
                            <div>
                                <span class="block text-[11px] text-slate-400 mb-0.5">Ukuran Skala Widget HR & Timer:</span>
                                <input type="range" id="hudScale" min="0.6" max="1.6" step="0.05" value="1.0" oninput="renderFrame()" class="w-full accent-sky-500">
                            </div>
                        </div>
                    </div>

                    <!-- Sumber Data Huawei Band -->
                    <div class="bg-slate-900/90 p-3 rounded-lg border border-slate-800 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-extrabold text-amber-300 uppercase">⌚ Data Detak Jantung (Tiap 5 Detik)</span>
                            <button type="button" onclick="generateSampleHr()" class="text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-200 px-2 py-0.5 rounded">⚡ Isi Contoh</button>
                        </div>
                        <div>
                            <label class="block text-[10px] text-amber-300 mb-1">📱 Ekstrak Otomatis dari Screenshot Grafik Huawei Health:</label>
                            <input type="file" accept="image/*" onchange="extractFromGraphImage(this)" class="w-full text-[11px] text-slate-300 file:py-1 file:px-2.5 file:rounded file:border-0 file:bg-amber-600 file:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] text-sky-300 mb-1">📄 Atau Upload File .TCX / .CSV:</label>
                            <input type="file" accept=".tcx,.csv,.txt" onchange="uploadFileToPhp(this)" class="w-full text-[11px] text-slate-300 file:py-1 file:px-2.5 file:rounded file:border-0 file:bg-slate-700 file:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-400 mb-1">Daftar Angka BPM (Bisa diedit manual):</label>
                            <textarea id="hrDataInput" rows="3" oninput="parseHrInput()" class="w-full bg-slate-950 border border-slate-800 rounded p-2 text-[11px] font-mono text-rose-400"></textarea>
                        </div>
                    </div>
                </div>

                <!-- ==================== TAB 3: TIMER DURASI MODAL ==================== -->
                <div id="tab_timer" class="modal-pane hidden space-y-4">
                    <div class="flex items-center justify-between bg-slate-900 p-3 rounded-lg border border-sky-500/40">
                        <div>
                            <div class="text-xs font-bold text-white">Tampilkan Widget Timer Durasi</div>
                            <div class="text-[10px] text-slate-400">Jam waktu olahraga berjalan di video</div>
                        </div>
                        <label class="flex items-center gap-1.5 bg-sky-500/20 border border-sky-500/40 px-3 py-1 rounded-lg text-xs font-extrabold text-sky-300 cursor-pointer">
                            <input type="checkbox" id="showTimerWidget" checked onchange="saveSettingState(); renderFrame();" class="rounded bg-slate-950 text-sky-500">
                            <span>Aktif</span>
                        </label>
                    </div>

                    <div class="bg-slate-900/90 p-3 rounded-lg border border-slate-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs text-sky-300 font-bold">⏱️ Pilih Style Timer (24 Model):</label>
                            <button type="button" onclick="syncTimerWithHrStyle()" class="text-[10px] bg-sky-600/30 hover:bg-sky-600/50 text-sky-300 border border-sky-500/40 px-2 py-1 rounded font-bold">
                                🔗 Samakan dgn Style HR
                            </button>
                        </div>

                        <select id="timerStyleSelect" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-sky-500/50 rounded-lg px-2.5 py-2 text-xs text-sky-300 font-bold">
                            <optgroup label="A. Gaya Teks Murni (Tanpa Kotak)">
                                <option value="text_pure">1. Pure Text Only (Angka Saja)</option>
                                <option value="clean_digital">2. Clean Digital + Label Atas</option>
                                <option value="text_slash_total">3. Text + Total (15:45 / 20:00 + Bar)</option>
                                <option value="text_outline">4. Hollow Outline Text (Garis Tepi)</option>
                                <option value="text_neon">5. Neon Glowing Text (Teks Menyala)</option>
                                <option value="text_stacked">6. Vertical Stacked (Menit Atas, Detik Bawah)</option>
                            </optgroup>
                            <optgroup label="B. Kembaran Style Heart Rate (Serasi)">
                                <option value="match_dji_ring">7. DJI Osmo Single Ring Timer (Bulat)</option>
                                <option value="match_dji_wave">8. DJI Telemetry Wave Timer</option>
                                <option value="match_apple_ring">9. Apple Fitness+ Activity Ring Timer</option>
                                <option value="match_garmin_arc">10. Garmin Fenix 5-Arc Speedometer</option>
                                <option value="match_whoop_bar">11. WHOOP Progress Strain Bar (% Waktu)</option>
                                <option value="match_strava_clean">12. Strava Orange Line Floating</option>
                                <option value="match_coros_hex">13. COROS Tactical Hexagon Timer</option>
                                <option value="match_nike_volt">14. Nike Training Volt Slash Timer</option>
                                <option value="match_polar_badge">15. Polar Red Header Card Timer</option>
                                <option value="match_cyber_brackets">16. Cyberpunk Corner Brackets (SYS.TIME)</option>
                                <option value="match_crossfit_tile">17. CrossFit Games Broadcast Tile</option>
                                <option value="match_stopwatch_analog">18. Analog Stopwatch Dial Fill</option>
                                <option value="match_vertical_meter">19. Vertical Thermometer Progress</option>
                                <option value="match_neon_box">20. Neon Glow Cyber Box</option>
                                <option value="match_glass_capsule">21. Minimalist Glass Capsule + Icon</option>
                            </optgroup>
                            <optgroup label="C. Papan Skor Gym & Broadcast">
                                <option value="dji_pill">22. DJI Dark Pill + Progress Bar</option>
                                <option value="broadcast_tag">23. Broadcast Left Accent Box</option>
                                <option value="match_split_flap">24. Gym Scoreboard (Kotak per Digit)</option>
                            </optgroup>
                        </select>

                        <div>
                            <label class="block text-xs text-slate-300 font-bold mb-1">🔄 Mode Hitungan Waktu:</label>
                            <select id="timerCountMode" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-2 text-xs text-emerald-300 font-bold">
                                <option value="count_up">⬆️ Hitung Maju (00:00 -> 20:00)</option>
                                <option value="count_down">⬇️ Hitung Mundur / Countdown (20:00 -> 00:00)</option>
                                <option value="with_total">➗ Tampilkan Total Durasi (15:45 / 20:00)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Widget Round Opsional -->
                    <div class="flex items-center justify-between bg-slate-900 p-3 rounded-lg border border-slate-800">
                        <div>
                            <div class="text-xs font-bold text-amber-300">Tampilkan Widget Round Terpisah</div>
                            <div class="text-[10px] text-slate-400">Kotak penghitung ronde & total repetisi</div>
                        </div>
                        <label class="flex items-center gap-1.5 bg-slate-800 px-3 py-1 rounded-lg text-xs font-bold text-amber-300 cursor-pointer">
                            <input type="checkbox" id="showRoundWidget" onchange="saveSettingState(); renderFrame();" class="rounded bg-slate-950 text-amber-500">
                            <span>Aktif</span>
                        </label>
                    </div>
                </div>

                <!-- ==================== TAB 4: JUDUL KIRI ATAS MODAL ==================== -->
                <div id="tab_title" class="modal-pane hidden space-y-4">
                    <div class="flex items-center justify-between bg-slate-900 p-3 rounded-lg border border-red-500/40">
                        <div>
                            <div class="text-xs font-bold text-white">Tampilkan Judul Pojok Kiri Atas</div>
                            <div class="text-[10px] text-slate-400">Contoh: DAY 26 / CINDY</div>
                        </div>
                        <label class="flex items-center gap-1.5 bg-red-500/20 border border-red-500/40 px-3 py-1 rounded-lg text-xs font-extrabold text-red-300 cursor-pointer">
                            <input type="checkbox" id="showTopTitle" checked onchange="saveSettingState(); renderFrame();" class="rounded bg-slate-950 text-red-500">
                            <span>Aktif</span>
                        </label>
                    </div>

                    <div class="bg-slate-900/90 p-3 rounded-lg border border-slate-800 space-y-3">
                        <div>
                            <label class="block text-xs text-amber-300 font-bold mb-1">🏷️ Pilih Style Judul (8 Model):</label>
                            <select id="titleStyleSelect" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-red-500/40 rounded-lg px-2.5 py-2 text-xs text-amber-300 font-bold">
                                <option value="cindy_redbar">1. Red Vertical Bar (DAY 26 / CINDY)</option>
                                <option value="broadcast_box">2. Broadcast Slanted Box</option>
                                <option value="solid_badge">3. Solid Tag Banner</option>
                                <option value="clean_outline">4. Modern Stroke Outline</option>
                                <option value="neon_underline">5. Neon Glowing Underline</option>
                                <option value="crossfit_bracket">6. CrossFit Games Brackets</option>
                                <option value="split_capsule">7. Two-Tone Split Pill</option>
                                <option value="stencil_stamp">8. Military Stencil Stamp</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-slate-300 font-bold mb-1">🔤 Pilih Font Judul (12 Font):</label>
                            <select id="titleFontSelect" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-2 text-xs text-white font-bold">
                                <option value="'Bebas Neue', sans-serif">1. Bebas Neue (Persis CINDY)</option>
                                <option value="'Anton', sans-serif">2. Anton (Thick Impact)</option>
                                <option value="'Teko', sans-serif">3. Teko (Tall Square Sporty)</option>
                                <option value="'Russo One', sans-serif">4. Russo One (Heavy Athletic)</option>
                                <option value="'Black Ops One', cursive">5. Black Ops One (Navy Seal)</option>
                                <option value="'Barlow Condensed', sans-serif">6. Barlow Condensed</option>
                                <option value="'Montserrat', sans-serif">7. Montserrat Black</option>
                                <option value="'Oswald', sans-serif">8. Oswald (CrossFit Style)</option>
                                <option value="'Chakra Petch', sans-serif">9. Chakra Petch (DJI Tactical)</option>
                                <option value="'Rajdhani', sans-serif">10. Rajdhani (Tech Modern)</option>
                                <option value="'Orbitron', sans-serif">11. Orbitron (Sci-Fi)</option>
                                <option value="'JetBrains Mono', monospace">12. JetBrains Mono</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] text-slate-400 mb-0.5">Baris Atas (DAY 26):</label>
                                <input type="text" id="titleTag" value="DAY 26" oninput="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded px-2.5 py-1.5 text-xs text-red-400 font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 mb-0.5">Judul Utama (CINDY):</label>
                                <input type="text" id="titleMain" value="CINDY" oninput="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded px-2.5 py-1.5 text-xs text-white font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <div class="flex items-center gap-2 bg-slate-950 p-2 rounded border border-slate-800">
                                <input type="color" id="titleColorPicker" value="#ef1111" oninput="saveSettingState(); renderFrame();">
                                <span class="text-xs text-slate-300">Warna Bar/Tag</span>
                            </div>
                            <div class="flex items-center gap-2 bg-slate-950 p-2 rounded border border-slate-800">
                                <input type="color" id="titleMainColor" value="#ffffff" oninput="saveSettingState(); renderFrame();">
                                <span class="text-xs text-slate-300">Warna Judul</span>
                            </div>
                        </div>

                        <div>
                            <span class="block text-[11px] text-slate-400 mb-0.5">Ukuran Skala Judul:</span>
                            <input type="range" id="titleScale" min="0.6" max="1.5" step="0.05" value="1.0" oninput="renderFrame()" class="w-full accent-red-500">
                        </div>
                    </div>
                </div>

                <!-- ==================== TAB 5: WATERMARK SOSIAL MEDIA MODAL ==================== -->
                <div id="tab_watermark" class="modal-pane hidden space-y-4">
                    <div class="flex items-center justify-between bg-slate-900 p-3 rounded-lg border border-cyan-500/40">
                        <div>
                            <div class="text-xs font-bold text-white">Tampilkan Watermark Sosial Media</div>
                            <div class="text-[10px] text-slate-400">Identitas akun IG / TikTok / YouTube Anda</div>
                        </div>
                        <label class="flex items-center gap-1.5 bg-cyan-500/20 border border-cyan-500/40 px-3 py-1 rounded-lg text-xs font-extrabold text-cyan-300 cursor-pointer">
                            <input type="checkbox" id="showWatermarkWidget" checked onchange="saveSettingState(); renderFrame();" class="rounded bg-slate-950 text-cyan-500">
                            <span>Aktif</span>
                        </label>
                    </div>

                    <div class="bg-slate-900/90 p-3 rounded-lg border border-slate-800 space-y-3">
                        <div>
                            <label class="block text-xs text-cyan-300 font-bold mb-1">📱 Pilih Style Watermark (10 Model):</label>
                            <select id="wmStyleSelect" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-cyan-500/50 rounded-lg px-2.5 py-2 text-xs text-cyan-300 font-bold">
                                <option value="glass_pill">1. Glassmorphism Pill (Kapsul Modern)</option>
                                <option value="ig_gradient">2. Instagram Gradient Badge</option>
                                <option value="tiktok_glitch">3. TikTok Cyber Neon Glitch</option>
                                <option value="yt_subscribe">4. YouTube Red Subscribe Box</option>
                                <option value="minimal_clean">5. Minimalist Clean (Tanpa Kotak)</option>
                                <option value="neon_border">6. Neon Glow Frame (Garis Menyala)</option>
                                <option value="slanted_sport">7. Slanted Sport Tag (Pita Miring)</option>
                                <option value="tactical_bracket">8. Tactical Corner Brackets (Siku)</option>
                                <option value="verified_card">9. Creator Card 2 Baris (Akun + Sub)</option>
                                <option value="vertical_side">10. Vertical Side Stamp (Berdiri 90°)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-slate-300 font-bold mb-1">🎯 Pilih Ikon Platform:</label>
                            <select id="wmIconSelect" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-2 text-xs text-white font-bold">
                                <option value="instagram">📸 Instagram Camera</option>
                                <option value="tiktok">🎵 TikTok Note</option>
                                <option value="youtube">▶️ YouTube Play</option>
                                <option value="verified">☑️ Verified Checkmark</option>
                                <option value="dumbbell">🏋️ Fitness Dumbbell</option>
                                <option value="none">🚫 Tanpa Ikon (Teks Saja)</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <div>
                                <label class="block text-[10px] text-slate-400 mb-0.5">Teks Akun / Username:</label>
                                <input type="text" id="watermarkText" value="@DAILYWORKOUT" oninput="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded px-2.5 py-1.5 text-xs text-white font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 mb-0.5">Teks Tambahan (Khusus Style #9):</label>
                                <input type="text" id="watermarkSub" value="FOLLOW FOR DAILY WOD" oninput="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded px-2.5 py-1.5 text-xs text-slate-300">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <div class="flex items-center gap-2 bg-slate-950 p-2 rounded border border-slate-800">
                                <input type="color" id="wmColorPicker" value="#06b6d4" oninput="saveSettingState(); renderFrame();">
                                <span class="text-xs text-slate-300">Warna Aksen</span>
                            </div>
                            <div class="flex items-center gap-2 bg-slate-950 p-2 rounded border border-slate-800">
                                <input type="color" id="wmTextColorPicker" value="#ffffff" oninput="saveSettingState(); renderFrame();">
                                <span class="text-xs text-slate-300">Warna Teks</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div>
                                <span class="block text-[10px] text-slate-400">Ukuran Watermark:</span>
                                <input type="range" id="wmScaleSlider" min="0.6" max="1.8" step="0.05" value="1.0" oninput="saveSettingState(); renderFrame();" class="w-full accent-cyan-400">
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400">Transparansi:</span>
                                <input type="range" id="wmOpacitySlider" min="0.2" max="1.0" step="0.05" value="0.90" oninput="saveSettingState(); renderFrame();" class="w-full accent-cyan-400">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== TAB 6: RESULT / KARTU REKAP MODAL ==================== -->
                <div id="tab_result" class="modal-pane hidden space-y-4">
                    <div class="flex items-center justify-between bg-slate-900 p-3 rounded-lg border border-yellow-500/40">
                        <div>
                            <div class="text-xs font-bold text-white">Kartu Result di Akhir Video</div>
                            <div class="text-[10px] text-slate-400">Otomatis muncul sebelum video selesai</div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="flex items-center gap-1 bg-yellow-500/20 border border-yellow-400/50 px-2.5 py-1 rounded text-[10px] font-extrabold text-yellow-300 cursor-pointer">
                                <input type="checkbox" id="forcePreviewResult" onchange="renderFrame()" class="rounded bg-slate-950 text-yellow-400">
                                <span>👁️ Preview</span>
                            </label>
                            <label class="flex items-center gap-1 bg-slate-800 px-2.5 py-1 rounded text-[10px] font-bold text-emerald-300 cursor-pointer">
                                <input type="checkbox" id="showFinisherCard" checked onchange="saveSettingState(); renderFrame();" class="rounded bg-slate-950 text-emerald-500">
                                <span>Aktif</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-slate-900/90 p-3 rounded-lg border border-slate-800 space-y-3">
                        <div>
                            <label class="block text-xs text-yellow-300 font-bold mb-1">🏆 Pilih Template Result (20 Model):</label>
                            <select id="resTemplateSelect" onchange="document.getElementById('forcePreviewResult').checked=true; saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-yellow-500/50 rounded-lg px-2.5 py-2 text-xs text-yellow-300 font-bold">
                                <option value="classic_pro">1. Classic Pro Card (3 Kolom + Rincian)</option>
                                <option value="strava_share">2. Strava Activity Share (Oranye Bersih)</option>
                                <option value="nike_volt">3. Nike Training Club (Slanted Volt Neon)</option>
                                <option value="apple_rings">4. Apple Fitness+ Activity Rings</option>
                                <option value="crossfit_board">5. CrossFit Games Scoreboard Table</option>
                                <option value="garmin_shield">6. Garmin Connect Hexagon Badge</option>
                                <option value="whoop_strain">7. WHOOP Strain Recovery Bar</option>
                                <option value="cyberpunk_hud">8. Cyberpunk 2077 Telemetry Glitch</option>
                                <option value="military_stamp">9. Military Mission Accomplished Stamp</option>
                                <option value="minimal_floating">10. Minimalist Floating (Teks Tanpa Kotak)</option>
                                <option value="ig_story_glass">11. IG Story Vertical Frosted Glass</option>
                                <option value="gold_champion">12. Gold Championship Trophy Card</option>
                                <option value="neon_arcade">13. Retro Neon Arcade High Score</option>
                                <option value="polar_red">14. Polar Beat Red Header Report</option>
                                <option value="split_diagonal">15. Dynamic Diagonal Two-Tone Split</option>
                                <option value="receipt_ticket">16. Workout Receipt (Struk Belanja Unik)</option>
                                <option value="magazine_cover">17. Fitness Magazine Cover Headline</option>
                                <option value="coros_hex_grid">18. COROS Tactical Hexagonal Grid</option>
                                <option value="brutalist_poster">19. Neo-Brutalist Bold Shadow Poster</option>
                                <option value="cinema_banner">20. Cinematic Widescreen Letterbox</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-slate-300 font-bold mb-1">🔤 Font Kartu Result:</label>
                            <select id="resFontSelect" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-2 text-xs text-white font-bold">
                                <option value="'Chakra Petch', sans-serif">1. Chakra Petch (DJI / Sporty)</option>
                                <option value="'Bebas Neue', sans-serif">2. Bebas Neue (Tall Headline)</option>
                                <option value="'Montserrat', sans-serif">3. Montserrat Black (Modern)</option>
                                <option value="'Anton', sans-serif">4. Anton (Heavy Impact)</option>
                                <option value="'Teko', sans-serif">5. Teko (Square Scoreboard)</option>
                                <option value="'Russo One', sans-serif">6. Russo One (Athletic)</option>
                                <option value="'Black Ops One', cursive">7. Black Ops One (Military)</option>
                                <option value="'Barlow Condensed', sans-serif">8. Barlow Condensed</option>
                                <option value="'Oswald', sans-serif">9. Oswald (CrossFit)</option>
                                <option value="'Rajdhani', sans-serif">10. Rajdhani (Tech)</option>
                                <option value="'Orbitron', sans-serif">11. Orbitron (Sci-Fi)</option>
                                <option value="'JetBrains Mono', monospace">12. JetBrains Mono (Struk/Code)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-12 gap-2">
                            <div class="col-span-8">
                                <label class="block text-[10px] text-slate-400 mb-0.5">Judul Atas Result:</label>
                                <input type="text" id="resTitleInput" value="WORKOUT COMPLETED" oninput="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-1.5 text-xs text-white font-bold">
                            </div>
                            <div class="col-span-4">
                                <label class="block text-[10px] text-slate-400 mb-0.5">Durasi Akhir (dtk):</label>
                                <input type="number" id="resDurationSec" value="3.5" min="1" max="15" step="0.5" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-1.5 text-xs text-yellow-300 font-bold text-center">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] text-slate-400 mb-0.5">Sub-Judul / Quote Bawah:</label>
                            <input type="text" id="resSubInput" value="GREAT SESSION • NO EXCUSES" oninput="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded px-2.5 py-1.5 text-xs text-slate-300">
                        </div>

                        <div class="grid grid-cols-4 gap-1.5 pt-1 items-center">
                            <div class="flex items-center gap-1 bg-slate-950 p-1.5 rounded border border-slate-800">
                                <input type="color" id="resAccentColor" value="#f59e0b" oninput="saveSettingState(); renderFrame();">
                                <span class="text-[9px] text-slate-300">Aksen</span>
                            </div>
                            <div class="flex items-center gap-1 bg-slate-950 p-1.5 rounded border border-slate-800">
                                <input type="color" id="resBgColor" value="#090d16" oninput="saveSettingState(); renderFrame();">
                                <span class="text-[9px] text-slate-300">Kotak</span>
                            </div>
                            <div class="flex items-center gap-1 bg-slate-950 p-1.5 rounded border border-slate-800">
                                <input type="color" id="resTextColor" value="#ffffff" oninput="saveSettingState(); renderFrame();">
                                <span class="text-[9px] text-slate-300">Teks</span>
                            </div>
                            <label class="flex items-center gap-1 bg-slate-950 p-1.5 rounded border border-slate-800 text-[9px] text-emerald-300 font-bold cursor-pointer">
                                <input type="checkbox" id="resShowBreakdown" checked onchange="saveSettingState(); renderFrame();" class="rounded bg-slate-900">
                                <span>Rincian</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div>
                                <span class="block text-[10px] text-slate-400">Gelap Latar Result:</span>
                                <input type="range" id="resOpacitySlider" min="0.1" max="1.0" step="0.05" value="0.92" oninput="saveSettingState(); renderFrame();" class="w-full accent-yellow-400">
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400">Ukuran Kartu Result:</span>
                                <input type="range" id="resScaleSlider" min="0.6" max="1.4" step="0.05" value="1.0" oninput="saveSettingState(); renderFrame();" class="w-full accent-yellow-400">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== TAB 7: AUDIO MIXER & BACKSOUND MODAL ==================== -->
                <div id="tab_audio" class="modal-pane hidden space-y-4">
                    <div class="bg-slate-900/90 p-3 rounded-lg border border-amber-500/40 space-y-3">
                        <div class="text-xs font-extrabold text-amber-300 uppercase">🎵 Upload Backsound Musik (.MP3 / .WAV)</div>
                        <p class="text-[10px] text-slate-400">Musik otomatis tetap berputar normal (1x) meskipun video dipercepat menjadi Timelapse!</p>
                        <input type="file" id="bgmFileInput" accept="audio/*" class="w-full text-xs text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-amber-600 file:text-white">
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs text-slate-300">Mulai Lagu dari Detik Ke- (Beat Drop):</span>
                            <input type="number" id="bgmOffsetSec" value="0" min="0" class="w-20 bg-slate-950 border border-slate-700 rounded px-2 py-1 text-xs text-white font-bold text-center">
                        </div>
                    </div>

                    <div class="bg-slate-900/90 p-3 rounded-lg border border-slate-800 space-y-3">
                        <div class="text-xs font-extrabold text-sky-300 uppercase">🎚️ Kontrol Volume Multi-Track</div>
                        <div>
                            <div class="flex justify-between text-xs mb-1"><span class="text-slate-300">🔊 Volume Suara Video Asli</span><b id="lblVidVol" class="text-sky-400">80%</b></div>
                            <input type="range" id="videoVolSlider" min="0" max="1.5" step="0.05" value="0.8" oninput="updateAudioVolumes()" class="w-full accent-sky-500">
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1"><span class="text-slate-300">🎵 Volume Musik Backsound</span><b id="lblBgmVol" class="text-amber-400">60%</b></div>
                            <input type="range" id="bgmVolSlider" min="0" max="1.5" step="0.05" value="0.6" oninput="updateAudioVolumes()" class="w-full accent-amber-500">
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1"><span class="text-slate-300">🔔 Volume Efek Bunyi Repetisi (SFX)</span><b id="lblSfxVol" class="text-emerald-400">50%</b></div>
                            <input type="range" id="sfxVolSlider" min="0" max="1.5" step="0.05" value="0.5" oninput="updateAudioVolumes()" class="w-full accent-emerald-500">
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                            <label class="flex items-center gap-1.5 text-xs text-emerald-300 font-semibold cursor-pointer">
                                <input type="checkbox" id="enableRepBeep" checked class="rounded bg-slate-950">
                                <span>Bunyi "Ding" tiap +1 Rep & "Bell" Ronde</span>
                            </label>
                            <button type="button" onclick="playSfxBeep(880, 0.12)" class="bg-slate-800 hover:bg-slate-700 text-xs text-white px-2.5 py-1 rounded">🔊 Tes Bunyi</button>
                        </div>
                    </div>
                </div>

                <!-- ==================== TAB 8: VIDEO, LUT & TRIM MODAL ==================== -->
                <div id="tab_video" class="modal-pane hidden space-y-4">
                    <div class="bg-slate-900/90 p-3 rounded-lg border border-sky-500/40 space-y-2.5">
                        <div class="text-xs font-extrabold text-sky-400 uppercase">📹 Sumber Video / Foto Workout</div>
                        <div>
                            <label class="block text-[10px] text-slate-300 mb-1">Upload Video Workout (.MP4 / .MOV):</label>
                            <input type="file" id="bgVideoInput" accept="video/*" class="w-full text-xs text-slate-300 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:bg-sky-600 file:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-300 mb-1">Atau Upload Foto Background:</label>
                            <input type="file" id="bgImageInput" accept="image/*" class="w-full text-xs text-slate-300 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:bg-slate-700 file:text-white">
                        </div>
                        <label class="flex items-center gap-2 text-xs text-emerald-400 pt-1 cursor-pointer">
                            <input type="checkbox" id="greenScreenMode" onchange="renderFrame()" class="rounded bg-slate-950">
                            <span>Aktifkan Mode Layar Hijau (Green Screen #00FF00)</span>
                        </label>
                    </div>

                    <!-- Trim & Timelapse -->
                    <div class="bg-slate-900/90 p-3 rounded-lg border border-slate-800 space-y-2.5">
                        <div class="text-xs font-extrabold text-amber-400 uppercase">✂️ Trim Video & Speed Timelapse</div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] text-slate-300 mb-0.5">Trim Mulai (MM:SS):</label>
                                <div class="flex items-center gap-1">
                                    <input type="number" id="trimStartMin" value="0" min="0" onchange="updateTiming()" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-1 text-xs text-white">
                                    <span>:</span>
                                    <input type="number" id="trimStartSec" value="0" min="0" max="59" onchange="updateTiming()" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-1 text-xs text-white">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-300 mb-0.5">Trim Akhir (MM:SS):</label>
                                <div class="flex items-center gap-1">
                                    <input type="number" id="trimEndMin" value="20" min="0" onchange="updateTiming()" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-1 text-xs text-white">
                                    <span>:</span>
                                    <input type="number" id="trimEndSec" value="0" min="0" max="59" onchange="updateTiming()" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-1 text-xs text-white">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] text-emerald-300 font-bold mb-0.5">⚡ Kecepatan Video / Timelapse Export:</label>
                            <select id="exportSpeedSelect" onchange="setPlaybackSpeed(parseFloat(this.value))" class="w-full bg-slate-950 border border-emerald-500/50 rounded px-2 py-1.5 text-xs text-emerald-300 font-bold">
                                <option value="1">1x (Normal Real-Time)</option>
                                <option value="2">2x (Fast Forward)</option>
                                <option value="4">4x (Timelapse Cepat)</option>
                                <option value="8">8x (Super Timelapse)</option>
                                <option value="10">10x (Hyper Timelapse)</option>
                                <option value="16">16x (Ultra Timelapse)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Kamera Pan & Zoom + LUT -->
                    <div class="bg-slate-900/90 p-3 rounded-lg border border-purple-500/40 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-extrabold text-purple-300 uppercase">🎨 LUT Color Grading & Kamera</span>
                            <button type="button" onclick="resetCameraFraming()" class="text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 px-2 py-0.5 rounded">Reset Kamera</button>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <span class="block text-[10px] text-sky-300">🔍 Zoom:</span>
                                <input type="range" id="camZoom" min="0.5" max="2.2" step="0.02" value="1.0" oninput="renderFrame()" class="w-full accent-sky-500">
                            </div>
                            <div>
                                <span class="block text-[10px] text-sky-300">↔️ Geser X:</span>
                                <input type="range" id="camPanX" min="-500" max="500" step="5" value="0" oninput="renderFrame()" class="w-full accent-sky-500">
                            </div>
                            <div>
                                <span class="block text-[10px] text-sky-300">↕️ Geser Y:</span>
                                <input type="range" id="camPanY" min="-500" max="500" step="5" value="0" oninput="renderFrame()" class="w-full accent-sky-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] text-purple-300 font-bold mb-0.5">Pilih Preset LUT Video:</label>
                            <select id="lutPresetSelect" onchange="renderFrame()" class="w-full bg-slate-950 border border-purple-500/40 rounded px-2 py-1.5 text-xs text-white font-bold">
                                <option value="none">0. Normal (Warna Asli Kamera)</option>
                                <option value="teal_orange">1. Teal & Orange (Cinematic Gym)</option>
                                <option value="moody_dark">2. Moody Dark Fitness (Dramatic)</option>
                                <option value="bleach_grit">3. Hardcore Grit (Bleach Bypass)</option>
                                <option value="cold_steel">4. Cold Steel (Blue Tactical)</option>
                                <option value="golden_hour">5. Warm Golden Hour (Sunset)</option>
                                <option value="kodachrome">6. Vintage Film Kodachrome</option>
                                <option value="bw_noir">7. Monochrome Grit (Hitam Putih)</option>
                                <option value="vibrant_pop">8. Vibrant Reels Pop (Cerah)</option>
                                <option value="custom_cube">9. File .CUBE Kustom</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] text-amber-300 mb-0.5">📂 Upload File .CUBE (Premiere/CapCut):</label>
                            <input type="file" id="cubeFileInput" accept=".cube" onchange="loadCubeLutFile(this)" class="w-full text-[10px] text-slate-300 file:py-0.5 file:px-2 file:rounded file:border-0 file:bg-purple-600 file:text-white">
                        </div>

                        <div>
                            <span class="block text-[10px] text-slate-400">Intensitas LUT:</span>
                            <input type="range" id="lutIntensitySlider" min="0" max="1" step="0.05" value="0.85" oninput="renderFrame()" class="w-full accent-purple-500">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- 3. AREA KANVAS UTAMA (KANAN) -->
        <main class="flex-1 flex flex-col justify-between p-3 md:p-5 bg-slate-950/60 overflow-y-auto">
            
            <!-- Bar Kontrol Cepat di Atas Kanvas -->
            <div class="flex flex-wrap items-center justify-between gap-2 bg-slate-900/90 px-3.5 py-2 rounded-xl border border-slate-800">
                <div class="flex flex-wrap items-center gap-1.5">
                    <button onclick="togglePlaySim()" id="btnPlaySim" class="bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold px-3.5 py-1.5 rounded-lg transition shadow">
                        ▶ Putar Video
                    </button>
                    <button onclick="markRepNow()" class="bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white text-xs font-black px-3.5 py-1.5 rounded-lg transition shadow" title="Atau tekan tombol SPASI di keyboard">
                        ➕ [SPASI] +1 REP
                    </button>
                    <button onclick="markRoundNow()" class="bg-amber-600 hover:bg-amber-500 active:scale-95 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg transition">
                        🔄 +1 RND [R]
                    </button>
                    <button onclick="jumpToTrimStart()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold px-2.5 py-1.5 rounded-lg">
                        ⏮ Awal
                    </button>
                    <button onclick="toggleResultPreviewBtn()" class="bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-300 border border-yellow-500/40 text-xs font-bold px-2.5 py-1.5 rounded-lg transition">
                        🏆 Preview Result
                    </button>
                </div>
                <div class="text-[11px] text-slate-400 hidden xl:block">
                    🖱️ Klik & geser langsung elemen di kanvas • <code class="text-emerald-300">[1-4]</code> Pilih Gerakan • <code class="text-emerald-300">[Spasi]</code> +1 Rep
                </div>
            </div>

            <!-- Kanvas Video -->
            <div class="flex-1 flex justify-center items-center my-2 overflow-hidden">
                <canvas id="hudCanvas" width="720" height="1280" class="cursor-move"></canvas>
            </div>

            <!-- Timeline Slider Bawah -->
            <div class="bg-slate-900/90 px-4 py-2.5 rounded-xl border border-slate-800 space-y-1">
                <div class="flex flex-wrap justify-between text-xs font-semibold">
                    <span class="text-slate-300">Posisi Video: <b id="lblVideoTime" class="text-sky-400 font-mono">00:00</b> <span id="lblSpeedInfo" class="text-amber-400 ml-1">(1x)</span></span>
                    <span id="lblStatusSummary" class="text-emerald-400 font-mono">AKTIF: PUSH UPS | TOTAL REPS: 0</span>
                </div>
                <input type="range" id="timelineSlider" min="0" max="1200" step="0.1" value="0" oninput="onSliderChange(this.value)" class="w-full accent-rose-500 cursor-pointer">
                <div class="flex justify-between text-[10px] text-slate-500 font-mono">
                    <span id="lblTrimStartMarker" class="text-amber-400">✂️ Mulai: 00:00</span>
                    <span class="text-slate-400">Klik menu di Sidebar Kiri untuk membuka/menutup pengaturan tiap item</span>
                    <span id="lblTrimEndMarker" class="text-rose-400">✂️ Selesai: 20:00</span>
                </div>
            </div>

        </main>

    </div>

<video id="hiddenVideo" class="hidden" playsinline crossorigin="anonymous"></video>
<audio id="bgmAudio" class="hidden" loop crossorigin="anonymous"></audio>

<script>
// ============================================================================
// KONTROL SIDEBAR & SLIDE-OUT SIDE MODAL
// ============================================================================
let currentActiveTab = 'workout';
let isModalOpen = true;

const modalTitles = {
    workout:   '💪 Pengaturan Workout & Spasi',
    hr:        '❤️ Pengaturan Heart Rate (17 Style)',
    timer:     '⏱️ Pengaturan Timer Durasi (24 Style)',
    title:     '🏷️ Pengaturan Judul Atas (8 Style)',
    watermark: '📱 Watermark Sosial Media (10 Style)',
    result:    '🏆 Kartu Result / Rekap Akhir (20 Template)',
    audio:     '🎵 Studio Audio Mixer & Backsound',
    video:     '🎬 Video, LUT Color Grading & Trim'
};

function openSideModal(tabName) {
    const container = document.getElementById('sideModalContainer');

    // Jika tombol yang sama diklik saat modal sedang terbuka -> Tutup modal agar kanvas lega
    if (currentActiveTab === tabName && isModalOpen) {
        closeSideModal();
        return;
    }

    currentActiveTab = tabName;
    isModalOpen = true;
    container.classList.remove('hidden');

    // Update tombol aktif di sidebar
    document.querySelectorAll('.sidebar-btn').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById('nav_' + tabName);
    if (activeBtn) activeBtn.classList.add('active');

    // Tampilkan isi tab yang sesuai
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

// ============================================================================
// ENGINE UTAMA KANVAS, AUDIO MIXER & TELEMETRI
// ============================================================================
const canvas = document.getElementById('hudCanvas');
const ctx = canvas.getContext('2d');
const hiddenVideo = document.getElementById('hiddenVideo');
const bgmAudio = document.getElementById('bgmAudio');

let audioCtx = null, audioDest = null, videoSourceNode = null, bgmSourceNode = null;
let videoGainNode = null, bgmGainNode = null, sfxGainNode = null, hasBgmLoaded = false;

function initAudioMixer() {
    if (audioCtx) {
        if (audioCtx.state === 'suspended') audioCtx.resume();
        return;
    }
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    audioCtx = new AudioContextClass();
    audioDest = audioCtx.createMediaStreamDestination();

    videoGainNode = audioCtx.createGain();
    bgmGainNode   = audioCtx.createGain();
    sfxGainNode   = audioCtx.createGain();
    updateAudioVolumes();

    try {
        videoSourceNode = audioCtx.createMediaElementSource(hiddenVideo);
        videoSourceNode.connect(videoGainNode);
        videoGainNode.connect(audioCtx.destination);
        videoGainNode.connect(audioDest);
    } catch (e) {}

    try {
        bgmSourceNode = audioCtx.createMediaElementSource(bgmAudio);
        bgmSourceNode.connect(bgmGainNode);
        bgmGainNode.connect(audioCtx.destination);
        bgmGainNode.connect(audioDest);
    } catch (e) {}

    sfxGainNode.connect(audioCtx.destination);
    sfxGainNode.connect(audioDest);
}

function updateAudioVolumes() {
    const vVol = parseFloat(document.getElementById('videoVolSlider').value || 0.8);
    const bVol = parseFloat(document.getElementById('bgmVolSlider').value || 0.6);
    const sVol = parseFloat(document.getElementById('sfxVolSlider').value || 0.5);

    document.getElementById('lblVidVol').innerText = Math.round(vVol * 100) + '%';
    document.getElementById('lblBgmVol').innerText = Math.round(bVol * 100) + '%';
    document.getElementById('lblSfxVol').innerText = Math.round(sVol * 100) + '%';

    if (videoGainNode && audioCtx) videoGainNode.gain.setValueAtTime(vVol, audioCtx.currentTime);
    if (bgmGainNode && audioCtx)   bgmGainNode.gain.setValueAtTime(bVol, audioCtx.currentTime);
    if (sfxGainNode && audioCtx)   sfxGainNode.gain.setValueAtTime(sVol, audioCtx.currentTime);
}

function playSfxBeep(freq = 880, duration = 0.12, type = 'sine') {
    if (!document.getElementById('enableRepBeep').checked) return;
    initAudioMixer();
    const osc = audioCtx.createOscillator();
    const env = audioCtx.createGain();
    osc.type = type;
    osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(freq * 1.25, audioCtx.currentTime + duration);

    env.gain.setValueAtTime(0.4, audioCtx.currentTime);
    env.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);

    osc.connect(env);
    env.connect(sfxGainNode);
    osc.start();
    osc.stop(audioCtx.currentTime + duration);
}

function playRoundBellSfx() {
    if (!document.getElementById('enableRepBeep').checked) return;
    playSfxBeep(587.33, 0.35, 'triangle');
    setTimeout(() => playSfxBeep(880, 0.45, 'triangle'), 120);
}

document.getElementById('bgmFileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    initAudioMixer();
    bgmAudio.src = URL.createObjectURL(file);
    hasBgmLoaded = true;
});

// State Gerakan & Posisi 7 Widget
let exercises = [
    { name: 'PUSH UPS', target: 10 },
    { name: 'PULL UPS', target: 10 },
    { name: 'SQUATS',   target: 10 },
    { name: '',         target: 10 }
];
let activeExerciseIdx = 0;
let repEvents = [];
let roundTimestamps = [];
let nextEventId = 1;
let lastSpacePressTime = 0;
let lastPlayedRepCount = 0;

let titlePos     = { x: 44,  y: 65 };
let listPos      = { x: 360, y: 620 };
let hrPos        = { x: 44,  y: 1040 };
let timerPos     = { x: 455, y: 1040 };
let roundPos     = { x: 455, y: 940 };
let watermarkPos = { x: 420, y: 75 };
let resPos       = { x: 360, y: 640 };

let dragTarget = null;
let dragOffset = { x: 0, y: 0 };
let customCubeLut = null;

function hexToRgba(hex, alpha = 1) {
    hex = (hex || '#090d16').replace('#', '');
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
    const r = parseInt(hex.substring(0, 2), 16) || 15;
    const g = parseInt(hex.substring(2, 4), 16) || 23;
    const b = parseInt(hex.substring(4, 6), 16) || 42;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function toggleResultPreviewBtn() {
    const cb = document.getElementById('forcePreviewResult');
    cb.checked = !cb.checked;
    if (cb.checked) openSideModal('result');
    renderFrame();
}

function syncTimerWithHrStyle() {
    const hrStyle = document.getElementById('hudStyleSelect').value;
    const map = {
        'heart_num_side':   'text_pure',
        'heart_num_inside': 'text_pure',
        'dji_ring':         'match_dji_ring',
        'dji_ecg':          'match_dji_wave',
        'apple_fitness':    'match_apple_ring',
        'garmin_arc':       'match_garmin_arc',
        'whoop_strain':     'match_whoop_bar',
        'strava_minimal':   'match_strava_clean',
        'coros_hex':        'match_coros_hex',
        'nike_volt':        'match_nike_volt',
        'polar_beat':       'match_polar_badge',
        'cyber_hud':        'match_cyber_brackets',
        'crossfit_tile':    'match_crossfit_tile',
        'heart_fill':       'match_stopwatch_analog',
        'vertical_meter':   'match_vertical_meter',
        'neon_glow':        'match_neon_box',
        'glass_badge':      'match_glass_capsule'
    };
    if (map[hrStyle]) {
        document.getElementById('timerStyleSelect').value = map[hrStyle];
        saveSettingState();
        renderFrame();
    }
}

// Undo & Redo Stack
let undoStack = [];
let redoStack = [];
const MAX_HISTORY = 100;

function cloneStateSnapshot() {
    return JSON.parse(JSON.stringify({
        exercises, activeExerciseIdx, repEvents, roundTimestamps,
        titlePos, listPos, hrPos, timerPos, roundPos, watermarkPos, resPos
    }));
}

function recordHistory(actionLabel = 'Aksi') {
    undoStack.push({ label: actionLabel, snapshot: cloneStateSnapshot() });
    if (undoStack.length > MAX_HISTORY) undoStack.shift();
    redoStack = [];
    updateUndoRedoButtons(actionLabel);
}

function applySnapshot(snap) {
    exercises         = snap.exercises;
    activeExerciseIdx = snap.activeExerciseIdx;
    repEvents         = snap.repEvents;
    roundTimestamps   = snap.roundTimestamps;
    titlePos          = snap.titlePos;
    listPos           = snap.listPos;
    hrPos             = snap.hrPos        || { x: 44, y: 1040 };
    timerPos          = snap.timerPos     || { x: 455, y: 1040 };
    roundPos          = snap.roundPos     || { x: 455, y: 940 };
    watermarkPos      = snap.watermarkPos || { x: 420, y: 75 };
    resPos            = snap.resPos       || { x: canvas.width/2, y: canvas.height/2 };
    saveSettingState();
    renderExerciseInputs();
    renderEventLog();
    renderFrame();
}

function undoAction() {
    if (undoStack.length === 0) return;
    const lastItem = undoStack.pop();
    redoStack.push({ label: lastItem.label, snapshot: cloneStateSnapshot() });
    applySnapshot(lastItem.snapshot);
    updateUndoRedoButtons(`Undo: ${lastItem.label}`);
}

function redoAction() {
    if (redoStack.length === 0) return;
    const nextItem = redoStack.pop();
    undoStack.push({ label: nextItem.label, snapshot: cloneStateSnapshot() });
    applySnapshot(nextItem.snapshot);
    updateUndoRedoButtons(`Redo: ${nextItem.label}`);
}

function updateUndoRedoButtons(statusText) {
    const btnU = document.getElementById('btnUndo');
    const btnR = document.getElementById('btnRedo');
    btnU.disabled = (undoStack.length === 0);
    btnR.disabled = (redoStack.length === 0);
    btnU.innerText = `↩ Undo (${undoStack.length})`;
    btnR.innerText = `↪ Redo (${redoStack.length})`;
    if (statusText) document.getElementById('undoStatusBadge').innerText = statusText;
}

let bgImage = null, useVideoBg = false, hrValues = [];
let currentVideoSec = 0, trimStartTotalSec = 0, trimEndTotalSec = 1200, hrIntervalSec = 5, playbackSpeed = 1.0;
let isPlayingSim = false, animFrameId = null, lastTick = 0, pulsePhase = 0, ecgOffset = 0, repPopScale = 1.0;

function resetCameraFraming() {
    document.getElementById('camZoom').value = 1.0;
    document.getElementById('camPanX').value = 0;
    document.getElementById('camPanY').value = 0;
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
            if (line.startsWith('LUT_3D_SIZE')) { size = parseInt(line.split(/\s+/)[1], 10); continue; }
            if (/^[A-Za-z]/.test(line)) continue;
            const parts = line.split(/\s+/);
            if (parts.length >= 3) {
                const r = parseFloat(parts[0]), g = parseFloat(parts[1]), b = parseFloat(parts[2]);
                if (!isNaN(r) && !isNaN(g) && !isNaN(b)) rgbValues.push(r, g, b);
            }
        }
        if (size > 1 && rgbValues.length >= size * size * size * 3) {
            customCubeLut = { size, data: new Float32Array(rgbValues) };
            document.getElementById('lutPresetSelect').value = 'custom_cube';
            renderFrame();
        } else alert('Format file .CUBE tidak valid.');
    };
    reader.readAsText(file);
}

function drawMediaWithLutAndPanZoom(ctx, media, mW, mH, cW, cH) {
    const preset    = document.getElementById('lutPresetSelect').value;
    const intensity = parseFloat(document.getElementById('lutIntensitySlider').value || 0.85);
    const camZoom   = parseFloat(document.getElementById('camZoom').value || 1.0);
    const camPanX   = parseFloat(document.getElementById('camPanX').value || 0);
    const camPanY   = parseFloat(document.getElementById('camPanY').value || 0);

    ctx.save();
    if (preset === 'teal_orange') ctx.filter = `contrast(${100 + 22 * intensity}%) saturate(${100 + 30 * intensity}%) brightness(${100 - 4 * intensity}%)`;
    else if (preset === 'moody_dark') ctx.filter = `contrast(${100 + 32 * intensity}%) saturate(${100 - 28 * intensity}%) brightness(${100 - 14 * intensity}%)`;
    else if (preset === 'bleach_grit') ctx.filter = `contrast(${100 + 45 * intensity}%) saturate(${100 - 55 * intensity}%) brightness(${100 - 8 * intensity}%)`;
    else if (preset === 'cold_steel') ctx.filter = `contrast(${100 + 18 * intensity}%) saturate(${100 - 20 * intensity}%) hue-rotate(${-12 * intensity}deg)`;
    else if (preset === 'golden_hour') ctx.filter = `contrast(${100 + 12 * intensity}%) saturate(${100 + 35 * intensity}%) sepia(${28 * intensity}%)`;
    else if (preset === 'kodachrome') ctx.filter = `contrast(${100 + 20 * intensity}%) sepia(${35 * intensity}%) saturate(${100 + 15 * intensity}%)`;
    else if (preset === 'bw_noir') ctx.filter = `grayscale(${100 * intensity}%) contrast(${100 + 42 * intensity}%) brightness(${100 - 6 * intensity}%)`;
    else if (preset === 'vibrant_pop') ctx.filter = `contrast(${100 + 18 * intensity}%) saturate(${100 + 45 * intensity}%) brightness(${100 + 4 * intensity}%)`;

    const baseScale = Math.max(cW / mW, cH / mH) * camZoom;
    const dW = mW * baseScale, dH = mH * baseScale;
    const dx = ((cW - dW) / 2) + camPanX, dy = ((cH - dH) / 2) + camPanY;

    ctx.drawImage(media, dx, dy, dW, dH);
    ctx.filter = 'none';

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
        } catch (err) {}
    }

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
            rad.addColorStop(0, 'rgba(0,0,0,0)'); rad.addColorStop(1, `rgba(0,0,0,${0.55 * intensity})`);
            ctx.fillStyle = rad; ctx.fillRect(0, 0, cW, cH);
        } else if (preset === 'cold_steel') {
            ctx.globalCompositeOperation = 'soft-light';
            ctx.fillStyle = `rgba(30, 110, 220, ${0.32 * intensity})`; ctx.fillRect(0, 0, cW, cH);
        } else if (preset === 'golden_hour') {
            ctx.globalCompositeOperation = 'soft-light';
            ctx.fillStyle = `rgba(255, 160, 40, ${0.30 * intensity})`; ctx.fillRect(0, 0, cW, cH);
        }
        ctx.restore();
    }
    ctx.restore();
}

// Keyboard Shortcuts
window.addEventListener('keydown', function(e) {
    if (e.key === 'F5' || ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'r')) {
        if (repEvents.length > 0 && !confirm("⚠️ Anda menekan Refresh (F5). Yakin ingin memuat ulang halaman?")) {
            e.preventDefault(); return;
        }
    }
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
        e.preventDefault(); if (e.shiftKey) redoAction(); else undoAction(); return;
    }
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') {
        e.preventDefault(); redoAction(); return;
    }

    const tag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
    if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

    if (['Digit1', 'Digit2', 'Digit3', 'Digit4'].includes(e.code)) {
        const idx = parseInt(e.code.replace('Digit', ''), 10) - 1;
        if (exercises[idx] && exercises[idx].name.trim() !== '') {
            e.preventDefault(); setActiveExercise(idx);
        }
    } else if (e.code === 'Space') {
        e.preventDefault(); markRepNow();
    } else if (e.code === 'KeyR') {
        e.preventDefault(); markRoundNow();
    } else if (e.code === 'Backspace') {
        e.preventDefault(); undoAction();
    }
});

// Repetitions Management
function markRepNow(forcedExIdx = null) {
    const nowMs = performance.now();
    const debounceLimit = parseInt(document.getElementById('debounceMsSelect').value || 0, 10);
    if (forcedExIdx === null && debounceLimit > 0 && (nowMs - lastSpacePressTime) < debounceLimit) {
        document.getElementById('undoStatusBadge').innerText = '🛡️ Anti-Double Tap!';
        return;
    }
    lastSpacePressTime = nowMs;

    const workoutSec = Math.max(0, currentVideoSec - trimStartTotalSec);
    const spaceMode = document.getElementById('spacebarMode').value;
    let targetExIdx = (forcedExIdx !== null) ? forcedExIdx : activeExerciseIdx;

    if (forcedExIdx === null && spaceMode === 'auto_cycle') {
        const counts = getCountsAtTime(workoutSec);
        const validExercises = exercises.map((e, i) => ({ ...e, idx: i })).filter(e => e.name.trim() !== '');
        if (validExercises.length > 0) {
            const totalPerRound = validExercises.reduce((acc, e) => acc + Math.max(1, e.target), 0);
            const posInRound = counts.totalReps % totalPerRound;
            let accTarget = 0;
            for (const ve of validExercises) {
                accTarget += Math.max(1, ve.target);
                if (posInRound < accTarget) {
                    targetExIdx = ve.idx; activeExerciseIdx = ve.idx; break;
                }
            }
        }
    }

    const exName = exercises[targetExIdx] ? exercises[targetExIdx].name : 'REP';
    recordHistory(`+1 ${exName}`);

    let newStamp = Number(workoutSec.toFixed(2));
    while (repEvents.some(ev => ev.time === newStamp)) {
        newStamp = Number((newStamp + 0.01).toFixed(2));
    }

    repEvents.push({ id: nextEventId++, time: newStamp, exIdx: targetExIdx });
    repEvents.sort((a, b) => a.time - b.time);

    let roundUp = false;
    if (forcedExIdx === null && spaceMode === 'auto_cycle') {
        const validExercises = exercises.filter(e => e.name.trim() !== '');
        const totalPerRound = validExercises.reduce((acc, e) => acc + Math.max(1, e.target), 0);
        if (totalPerRound > 0 && (repEvents.length % totalPerRound === 0)) {
            roundTimestamps.push(Number((newStamp + 0.01).toFixed(2)));
            roundUp = true;
        }
    }

    if (roundUp) playRoundBellSfx();
    else playSfxBeep(880, 0.11);

    currentVideoSec = Math.max(currentVideoSec, trimStartTotalSec + newStamp);
    repPopScale = 1.32;
    saveSettingState();
    renderExerciseInputs();
    renderEventLog();
    renderFrame();
}

function decrementSpecificExercise(exIdx) {
    const workoutSec = Math.max(0, currentVideoSec - trimStartTotalSec);
    let targetIndexInArr = -1;
    for (let i = repEvents.length - 1; i >= 0; i--) {
        if (repEvents[i].exIdx === exIdx && repEvents[i].time <= workoutSec + 0.5) {
            targetIndexInArr = i; break;
        }
    }
    if (targetIndexInArr === -1) {
        for (let i = repEvents.length - 1; i >= 0; i--) {
            if (repEvents[i].exIdx === exIdx) { targetIndexInArr = i; break; }
        }
    }
    if (targetIndexInArr !== -1) {
        const exName = exercises[exIdx] ? exercises[exIdx].name : 'REP';
        recordHistory(`-1 ${exName}`);
        repEvents.splice(targetIndexInArr, 1);
        saveSettingState();
        renderExerciseInputs();
        renderEventLog();
        renderFrame();
    }
}

function deleteRepEventById(evId) {
    const idx = repEvents.findIndex(e => e.id === evId);
    if (idx !== -1) {
        recordHistory(`Hapus Ketukan`);
        repEvents.splice(idx, 1);
        saveSettingState();
        renderExerciseInputs();
        renderEventLog();
        renderFrame();
    }
}

function changeRepEventExercise(evId, newExIdx) {
    const ev = repEvents.find(e => e.id === evId);
    if (ev) {
        recordHistory(`Ubah Gerakan`);
        ev.exIdx = parseInt(newExIdx, 10);
        saveSettingState();
        renderExerciseInputs();
        renderEventLog();
        renderFrame();
    }
}

function markRoundNow() {
    recordHistory('+1 Ronde');
    const workoutSec = Math.max(0, currentVideoSec - trimStartTotalSec);
    roundTimestamps.push(Number(workoutSec.toFixed(2)));
    roundTimestamps.sort((a, b) => a - b);
    playRoundBellSfx();
    repPopScale = 1.32;
    saveSettingState();
    renderEventLog();
    renderFrame();
}

function clearAllReps() {
    if (repEvents.length === 0 && roundTimestamps.length === 0) return;
    if (!confirm("Reset semua hitungan repetisi kembali ke 0?")) return;
    recordHistory('Reset Semua');
    repEvents = [];
    roundTimestamps = [];
    saveSettingState();
    renderExerciseInputs();
    renderEventLog();
    renderFrame();
}

function renderExerciseInputs() {
    const container = document.getElementById('exerciseRowsContainer');
    container.innerHTML = '';
    const workoutSec = Math.max(0, currentVideoSec - trimStartTotalSec);
    const counts = getCountsAtTime(workoutSec);

    exercises.forEach((ex, idx) => {
        const isActive = (idx === activeExerciseIdx);
        const curVal = counts.perEx[idx] || 0;
        const row = document.createElement('div');
        row.className = `flex items-center gap-1.5 p-1 rounded-lg border transition ${isActive ? 'bg-emerald-950/60 border-emerald-500' : 'bg-slate-900/70 border-slate-800'}`;
        row.innerHTML = `
            <button type="button" onclick="setActiveExercise(${idx})" class="px-2 py-1 rounded text-[10px] font-black ${isActive ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-400 hover:text-white'}">
                [${idx + 1}]
            </button>
            <input type="text" value="${ex.name}" placeholder="Gerakan ${idx+1}" oninput="exercises[${idx}].name=this.value; saveSettingState(); renderFrame();" class="w-24 sm:flex-1 bg-slate-950 border border-slate-700 rounded px-2 py-0.5 text-xs text-white font-bold">
            <div class="flex items-center gap-1 bg-slate-950 px-1 py-0.5 rounded border border-slate-800">
                <button type="button" onclick="decrementSpecificExercise(${idx})" class="bg-rose-600/80 hover:bg-rose-500 text-white font-black text-[10px] px-1.5 py-0.5 rounded">-1</button>
                <span class="text-xs font-mono font-extrabold text-emerald-400 w-7 text-center">${curVal}</span>
                <button type="button" onclick="markRepNow(${idx})" class="bg-emerald-600/80 hover:bg-emerald-500 text-white font-black text-[10px] px-1.5 py-0.5 rounded">+1</button>
            </div>
            <div class="flex items-center gap-1">
                <input type="number" value="${ex.target}" min="1" oninput="exercises[${idx}].target=parseInt(this.value||1,10); saveSettingState(); renderFrame();" class="w-11 bg-slate-950 border border-slate-700 rounded px-1 py-0.5 text-xs text-amber-300 font-bold text-center" title="Angka Target (Misal: 10)">
            </div>
        `;
        container.appendChild(row);
    });
}

function renderEventLog() {
    const logContainer = document.getElementById('eventLogList');
    if (repEvents.length === 0) {
        logContainer.innerHTML = `<div class="text-slate-500 text-center py-1">Belum ada ketukan. Tekan [SPASI] saat video berjalan!</div>`;
        return;
    }
    const reversed = [...repEvents].reverse().slice(0, 35);
    logContainer.innerHTML = reversed.map((ev, i) => {
        const repNum = repEvents.length - i;
        const vidTime = trimStartTotalSec + ev.time;
        const opts = exercises.map((ex, idx) => ex.name.trim() ? `<option value="${idx}" ${idx===ev.exIdx?'selected':''}>${ex.name}</option>` : '').join('');
        return `
            <div class="flex items-center justify-between bg-slate-900/90 px-2 py-0.5 rounded border border-slate-800/80">
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-500">#${repNum}</span>
                    <button type="button" onclick="jumpToTime(${vidTime})" class="text-sky-400 hover:underline font-bold">⏱ ${formatMMSS(ev.time)}</button>
                </div>
                <div class="flex items-center gap-1">
                    <select onchange="changeRepEventExercise(${ev.id}, this.value)" class="bg-slate-950 border border-slate-700 rounded px-1 py-0.5 text-[10px] text-emerald-300 font-bold">${opts}</select>
                    <button type="button" onclick="deleteRepEventById(${ev.id})" class="bg-rose-900/60 hover:bg-rose-600 text-rose-200 px-1.5 py-0.5 rounded text-[10px] font-bold">❌</button>
                </div>
            </div>
        `;
    }).join('');
}

function jumpToTime(sec) {
    currentVideoSec = sec;
    document.getElementById('timelineSlider').value = sec;
    if (useVideoBg) hiddenVideo.currentTime = sec;
    renderExerciseInputs();
    renderFrame();
}

function setActiveExercise(idx) {
    activeExerciseIdx = idx;
    renderExerciseInputs();
    renderFrame();
}

function generateDemoMultiReps() {
    recordHistory('Isi Demo');
    repEvents = [];
    roundTimestamps = [];
    const duration = Math.max(60, trimEndTotalSec - trimStartTotalSec);
    const validExercises = exercises.map((e, i) => ({ ...e, idx: i })).filter(e => e.name.trim() !== '');
    if (validExercises.length === 0) return;

    let t = 3.0;
    while (t < duration - 2) {
        for (const ve of validExercises) {
            for (let r = 0; r < ve.target; r++) {
                if (t >= duration - 2) break;
                repEvents.push({ id: nextEventId++, time: Number(t.toFixed(1)), exIdx: ve.idx });
                t += 2.2;
            }
        }
        if (t < duration - 2) {
            roundTimestamps.push(Number(t.toFixed(1)));
            t += 4.0;
        }
    }
    saveSettingState();
    renderExerciseInputs();
    renderEventLog();
    renderFrame();
}

function getCountsAtTime(workoutSec) {
    const perEx = [0, 0, 0, 0];
    let totalReps = 0;
    for (let i = 0; i < repEvents.length; i++) {
        if (workoutSec >= repEvents[i].time) {
            const idx = repEvents[i].exIdx;
            if (perEx[idx] !== undefined) perEx[idx]++;
            totalReps++;
        } else break;
    }
    let currentRound = 1;
    for (let i = 0; i < roundTimestamps.length; i++) {
        if (workoutSec >= roundTimestamps[i]) currentRound++;
        else break;
    }
    return { perEx, totalReps, currentRound };
}

function setPlaybackSpeed(spd) {
    playbackSpeed = spd;
    document.getElementById('exportSpeedSelect').value = String(spd);
    document.getElementById('lblSpeedInfo').innerText = `(${spd}x Speed)`;
    if (useVideoBg) hiddenVideo.playbackRate = Math.min(16, spd);
}

function resetWidgetPositions() {
    recordHistory('Reset Posisi Widget');
    changeAspectRatio(document.getElementById('aspectRatioSelect').value);
}

function changeAspectRatio(ratio) {
    if (ratio === '9:16') {
        canvas.width = 720; canvas.height = 1280;
        titlePos     = { x: 44,  y: 65 };
        listPos      = { x: 360, y: 620 };
        hrPos        = { x: 44,  y: 1040 };
        timerPos     = { x: 455, y: 1040 };
        roundPos     = { x: 455, y: 940 };
        watermarkPos = { x: 420, y: 75 };
        resPos       = { x: 360, y: 640 };
    } else if (ratio === '1:1') {
        canvas.width = 1080; canvas.height = 1080;
        titlePos     = { x: 45,  y: 45 };
        listPos      = { x: 540, y: 500 };
        hrPos        = { x: 45,  y: 840 };
        timerPos     = { x: 760, y: 840 };
        roundPos     = { x: 760, y: 740 };
        watermarkPos = { x: 750, y: 55 };
        resPos       = { x: 540, y: 540 };
    } else {
        canvas.width = 1280; canvas.height = 720;
        titlePos     = { x: 48,   y: 42 };
        listPos      = { x: 1020, y: 240 };
        hrPos        = { x: 48,   y: 495 };
        timerPos     = { x: 960,  y: 495 };
        roundPos     = { x: 960,  y: 410 };
        watermarkPos = { x: 940,  y: 50 };
        resPos       = { x: 640,  y: 360 };
    }
    saveSettingState();
    renderFrame();
}

function formatMMSS(sec) {
    sec = Math.max(0, Math.floor(sec));
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
}

function updateTiming() {
    const sMin = parseInt(document.getElementById('trimStartMin').value || 0, 10);
    const sSec = parseInt(document.getElementById('trimStartSec').value || 0, 10);
    trimStartTotalSec = (sMin * 60) + sSec;

    const eMin = parseInt(document.getElementById('trimEndMin').value || 20, 10);
    const eSec = parseInt(document.getElementById('trimEndSec').value || 0, 10);
    trimEndTotalSec = Math.max(trimStartTotalSec + 5, (eMin * 60) + eSec);

    const slider = document.getElementById('timelineSlider');
    slider.min = 0; slider.max = trimEndTotalSec;
    document.getElementById('lblTrimStartMarker').innerText = `✂️ Mulai: ${formatMMSS(trimStartTotalSec)}`;
    document.getElementById('lblTrimEndMarker').innerText   = `✂️ Selesai: ${formatMMSS(trimEndTotalSec)}`;

    if (currentVideoSec < trimStartTotalSec) {
        currentVideoSec = trimStartTotalSec;
        slider.value = currentVideoSec;
    }
    saveSettingState();
    renderFrame();
}

function jumpToTrimStart() {
    currentVideoSec = trimStartTotalSec;
    document.getElementById('timelineSlider').value = currentVideoSec;
    if (useVideoBg) hiddenVideo.currentTime = currentVideoSec;
    renderExerciseInputs();
    renderFrame();
}

function parseHrInput() {
    const raw = document.getElementById('hrDataInput').value;
    const nums = raw.split(/[\s,;]+/).map(v => parseInt(v.trim(), 10)).filter(v => !isNaN(v) && v >= 35 && v <= 240);
    hrValues = nums.length > 0 ? nums : [145];
    renderFrame();
}

function generateSampleHr() {
    const workoutLen = Math.max(60, trimEndTotalSec - trimStartTotalSec);
    const totalPoints = Math.ceil(workoutLen / hrIntervalSec);
    let sample = [];
    for (let i = 0; i < totalPoints; i++) {
        const p = i / totalPoints;
        let bpm = 118 + Math.sin(i * 0.35) * 14 + (p * 32);
        sample.push(Math.min(185, Math.round(bpm)));
    }
    document.getElementById('hrDataInput').value = sample.join(', ');
    parseHrInput();
}

function getZoneInfo(bpm) {
    const maxHr = 192;
    const pct = Math.min(100, Math.round((bpm / maxHr) * 100));
    if (pct >= 90) return { zone: 5, name: 'ZONE 5 • MAX', short: 'MAX', color: '#ef4444', pct, ratio: Math.min(1, bpm / maxHr) };
    if (pct >= 80) return { zone: 4, name: 'ZONE 4 • ANAEROBIC', short: 'ANAEROBIC', color: '#f97316', pct, ratio: bpm / maxHr };
    if (pct >= 70) return { zone: 3, name: 'ZONE 3 • AEROBIC', short: 'AEROBIC', color: '#eab308', pct, ratio: bpm / maxHr };
    if (pct >= 60) return { zone: 2, name: 'ZONE 2 • FAT BURN', short: 'FAT BURN', color: '#22c55e', pct, ratio: bpm / maxHr };
    return { zone: 1, name: 'ZONE 1 • WARM UP', short: 'WARM UP', color: '#38bdf8', pct, ratio: Math.max(0.25, bpm / maxHr) };
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

function drawStopwatchVector(ctx, cx, cy, r, color, secAngle = Math.PI * 0.4) {
    ctx.save();
    ctx.strokeStyle = color;
    ctx.lineWidth = 2.6;
    ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI * 2); ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(cx - 4, cy - r - 4); ctx.lineTo(cx + 4, cy - r - 4);
    ctx.moveTo(cx, cy - r - 4); ctx.lineTo(cx, cy - r);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.lineTo(cx + Math.sin(secAngle) * (r * 0.68), cy - Math.cos(secAngle) * (r * 0.68));
    ctx.stroke();
    ctx.restore();
}

// 1. RENDER 8 STYLE JUDUL KIRI ATAS
function drawTopLeftTitle(ctx) {
    if (!document.getElementById('showTopTitle').checked) return;

    const style     = document.getElementById('titleStyleSelect').value;
    const fontFam   = document.getElementById('titleFontSelect').value;
    const tagText   = document.getElementById('titleTag').value.toUpperCase();
    const mainText  = document.getElementById('titleMain').value.toUpperCase();
    const barColor  = document.getElementById('titleColorPicker').value;
    const mainColor = document.getElementById('titleMainColor').value;
    const scale     = parseFloat(document.getElementById('titleScale').value || 1.0);

    ctx.save();
    ctx.translate(titlePos.x, titlePos.y);
    ctx.scale(scale, scale);

    if (style === 'cindy_redbar') {
        ctx.shadowColor = 'rgba(0,0,0,0.8)'; ctx.shadowBlur = 10; ctx.shadowOffsetY = 3;
        ctx.fillStyle = barColor; ctx.fillRect(0, 0, 16, 132);
        ctx.font = `900 48px ${fontFam}`; ctx.fillText(tagText, 30, 42);
        ctx.fillStyle = mainColor; ctx.font = `900 96px ${fontFam}`; ctx.fillText(mainText, 28, 128);
    } else if (style === 'broadcast_box') {
        ctx.font = `900 64px ${fontFam}`;
        const w = Math.max(260, ctx.measureText(mainText).width + 55), h = 110;
        ctx.fillStyle = 'rgba(12, 18, 30, 0.85)';
        ctx.beginPath(); ctx.moveTo(0, 0); ctx.lineTo(w, 0); ctx.lineTo(w - 18, h); ctx.lineTo(0, h); ctx.closePath(); ctx.fill();
        ctx.fillStyle = barColor; ctx.fillRect(0, 0, 10, h);
        ctx.font = `800 28px ${fontFam}`; ctx.fillText(tagText, 24, 34);
        ctx.fillStyle = mainColor; ctx.font = `900 64px ${fontFam}`; ctx.fillText(mainText, 24, 94);
    } else if (style === 'solid_badge') {
        ctx.shadowColor = 'rgba(0,0,0,0.8)'; ctx.shadowBlur = 8;
        ctx.font = `800 32px ${fontFam}`;
        const tagW = ctx.measureText(tagText).width + 28;
        ctx.fillStyle = barColor; ctx.beginPath(); ctx.roundRect(0, 0, tagW, 42, 6); ctx.fill();
        ctx.fillStyle = '#ffffff'; ctx.fillText(tagText, 14, 32);
        ctx.fillStyle = mainColor; ctx.font = `900 88px ${fontFam}`; ctx.fillText(mainText, 0, 122);
    } else if (style === 'clean_outline') {
        ctx.shadowColor = 'rgba(0,0,0,0.85)'; ctx.shadowBlur = 8;
        ctx.fillStyle = barColor; ctx.font = `900 42px ${fontFam}`; ctx.fillText(tagText, 0, 38);
        ctx.font = `900 92px ${fontFam}`; ctx.lineWidth = 4; ctx.strokeStyle = mainColor;
        ctx.strokeText(mainText, 0, 120);
        ctx.fillStyle = 'rgba(255,255,255,0.15)'; ctx.fillText(mainText, 0, 120);
    } else if (style === 'neon_underline') {
        ctx.shadowColor = barColor; ctx.shadowBlur = 14;
        ctx.fillStyle = barColor; ctx.font = `800 32px ${fontFam}`; ctx.fillText(`// ${tagText}`, 0, 28);
        ctx.fillStyle = mainColor; ctx.font = `900 84px ${fontFam}`; ctx.fillText(mainText, 0, 104);
        const mw = Math.max(180, ctx.measureText(mainText).width);
        ctx.fillStyle = barColor; ctx.fillRect(0, 116, mw, 8);
    } else if (style === 'crossfit_bracket') {
        ctx.font = `900 72px ${fontFam}`;
        const mw = Math.max(220, ctx.measureText(mainText).width + 44), mh = 115;
        ctx.fillStyle = 'rgba(10, 15, 25, 0.72)'; ctx.fillRect(0, 0, mw, mh);
        ctx.strokeStyle = barColor; ctx.lineWidth = 5;
        ctx.beginPath(); ctx.moveTo(0, 28); ctx.lineTo(0, 0); ctx.lineTo(28, 0); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(mw - 28, mh); ctx.lineTo(mw, mh); ctx.lineTo(mw, mh - 28); ctx.stroke();
        ctx.fillStyle = barColor; ctx.font = `800 26px ${fontFam}`; ctx.fillText(tagText, 20, 34);
        ctx.fillStyle = mainColor; ctx.font = `900 72px ${fontFam}`; ctx.fillText(mainText, 20, 98);
    } else if (style === 'split_capsule') {
        ctx.font = `800 44px ${fontFam}`;
        const w1 = ctx.measureText(tagText).width + 36;
        ctx.font = `900 48px ${fontFam}`;
        const w2 = ctx.measureText(mainText).width + 44, h = 68;
        ctx.fillStyle = barColor; ctx.beginPath(); ctx.roundRect(0, 0, w1, h, [34, 0, 0, 34]); ctx.fill();
        ctx.fillStyle = 'rgba(15, 23, 42, 0.9)'; ctx.beginPath(); ctx.roundRect(w1, 0, w2, h, [0, 34, 34, 0]); ctx.fill();
        ctx.fillStyle = '#ffffff'; ctx.font = `800 44px ${fontFam}`; ctx.fillText(tagText, 18, 49);
        ctx.fillStyle = mainColor; ctx.font = `900 48px ${fontFam}`; ctx.fillText(mainText, w1 + 20, 51);
    } else {
        ctx.font = `900 74px ${fontFam}`;
        const mw = Math.max(240, ctx.measureText(mainText).width + 48), mh = 118;
        ctx.strokeStyle = barColor; ctx.lineWidth = 6; ctx.strokeRect(0, 0, mw, mh);
        ctx.fillStyle = barColor; ctx.fillRect(0, 0, mw, 36);
        ctx.fillStyle = '#090d16'; ctx.font = `900 26px ${fontFam}`; ctx.fillText(tagText, 16, 27);
        ctx.shadowColor = 'rgba(0,0,0,0.85)'; ctx.shadowBlur = 8;
        ctx.fillStyle = mainColor; ctx.font = `900 74px ${fontFam}`; ctx.fillText(mainText, 18, 102);
    }

    ctx.restore();
}

// 2. RENDER DAFTAR WORKOUT DENGAN 10 STYLE BUNGKUS
function drawCenterWorkoutList(ctx, counts) {
    if (!document.getElementById('showWorkoutList').checked) return;

    const boxStyle    = document.getElementById('listBoxStyleSelect').value;
    const fontFam     = document.getElementById('listFontSelect').value;
    const align       = document.getElementById('listAlignSelect').value;
    const wodHeader   = document.getElementById('wodHeader').value.trim().toUpperCase();
    const listFormat  = document.getElementById('listDisplayFormat').value;

    const headerColor = document.getElementById('listHeaderColor').value;
    const activeColor = document.getElementById('listActiveColor').value;
    const textColor   = document.getElementById('listTextColor').value;
    const bgHex       = document.getElementById('listBgColor').value;
    const bgOpacity   = parseFloat(document.getElementById('listBgOpacity').value);
    const listScale   = parseFloat(document.getElementById('listScaleSlider').value || 1.0);

    const lines = [];
    exercises.forEach((ex, idx) => {
        if (!ex.name || ex.name.trim() === '') return;
        const nameUpper = ex.name.trim().toUpperCase();
        const curVal    = counts.perEx[idx] || 0;
        const isAct     = (idx === activeExerciseIdx);

        let text = '';
        if (listFormat === 'target_first_live') {
            const num = (repEvents.length === 0) ? ex.target : curVal;
            text = `${num} ${nameUpper}`;
        } else if (listFormat === 'static_target') {
            text = `${ex.target} ${nameUpper}`;
        } else if (listFormat === 'live_first') {
            text = `${curVal} ${nameUpper}`;
        } else if (listFormat === 'count_target') {
            text = `${nameUpper} ${curVal}/${ex.target}`;
        } else if (listFormat === 'front_slash') {
            text = `${curVal}/${ex.target} ${nameUpper}`;
        } else {
            text = `${nameUpper} ${curVal}`;
        }
        lines.push({ text, isAct, idx });
    });

    if (lines.length === 0 && !wodHeader) return;

    ctx.save();
    ctx.translate(listPos.x, listPos.y);
    ctx.scale(listScale, listScale);

    let maxW = 240;
    if (wodHeader) {
        ctx.font = `900 50px ${fontFam}`;
        maxW = Math.max(maxW, ctx.measureText(wodHeader).width);
    }
    ctx.font = `900 42px ${fontFam}`;
    lines.forEach(l => {
        maxW = Math.max(maxW, ctx.measureText(l.text).width + 36);
    });

    const padX = 38, padY = 26;
    const headerH = wodHeader ? 68 : 0;
    const rowH = 54;
    const boxW = maxW + padX * 2;
    const boxH = padY * 2 + headerH + (lines.length * rowH);
    const leftX = -boxW / 2, topY = -45;
    const bgRgba = hexToRgba(bgHex, bgOpacity);

    if (boxStyle === 'glass_card' && bgOpacity > 0.01) {
        ctx.fillStyle = bgRgba;
        ctx.beginPath(); ctx.roundRect(leftX, topY, boxW, boxH, 20); ctx.fill();
        ctx.strokeStyle = 'rgba(255,255,255,0.22)'; ctx.lineWidth = 2; ctx.stroke();
    } else if (boxStyle === 'header_banner') {
        if (bgOpacity > 0.01) {
            ctx.fillStyle = bgRgba;
            ctx.beginPath(); ctx.roundRect(leftX, topY, boxW, boxH, 16); ctx.fill();
        }
        if (wodHeader) {
            ctx.fillStyle = headerColor;
            ctx.beginPath(); ctx.roundRect(leftX, topY, boxW, headerH + 10, [16, 16, 0, 0]); ctx.fill();
        }
        ctx.strokeStyle = headerColor; ctx.lineWidth = 2.5;
        ctx.beginPath(); ctx.roundRect(leftX, topY, boxW, boxH, 16); ctx.stroke();
    } else if (boxStyle === 'left_bar_card') {
        if (bgOpacity > 0.01) {
            ctx.fillStyle = bgRgba;
            ctx.beginPath(); ctx.roundRect(leftX, topY, boxW, boxH, [4, 16, 16, 4]); ctx.fill();
        }
        ctx.fillStyle = headerColor; ctx.fillRect(leftX, topY, 10, boxH);
    } else if (boxStyle === 'tactical_brackets') {
        if (bgOpacity > 0.01) { ctx.fillStyle = bgRgba; ctx.fillRect(leftX, topY, boxW, boxH); }
        ctx.strokeStyle = headerColor; ctx.lineWidth = 4;
        const bLen = 24;
        ctx.beginPath();
        ctx.moveTo(leftX, topY + bLen); ctx.lineTo(leftX, topY); ctx.lineTo(leftX + bLen, topY);
        ctx.moveTo(leftX + boxW - bLen, topY); ctx.lineTo(leftX + boxW, topY); ctx.lineTo(leftX + boxW, topY + bLen);
        ctx.moveTo(leftX, topY + boxH - bLen); ctx.lineTo(leftX, topY + boxH); ctx.lineTo(leftX + bLen, topY + boxH);
        ctx.moveTo(leftX + boxW - bLen, topY + boxH); ctx.lineTo(leftX + boxW, topY + boxH); ctx.lineTo(leftX + boxW, topY + boxH - bLen);
        ctx.stroke();
    } else if (boxStyle === 'slanted_sport') {
        ctx.beginPath();
        ctx.moveTo(leftX + 18, topY); ctx.lineTo(leftX + boxW, topY);
        ctx.lineTo(leftX + boxW - 18, topY + boxH); ctx.lineTo(leftX, topY + boxH);
        ctx.closePath();
        if (bgOpacity > 0.01) { ctx.fillStyle = bgRgba; ctx.fill(); }
        ctx.strokeStyle = headerColor; ctx.lineWidth = 3; ctx.stroke();
    } else if (boxStyle === 'neon_border') {
        ctx.save();
        if (bgOpacity > 0.01) {
            ctx.fillStyle = bgRgba;
            ctx.beginPath(); ctx.roundRect(leftX, topY, boxW, boxH, 18); ctx.fill();
        }
        ctx.shadowColor = headerColor; ctx.shadowBlur = 18;
        ctx.strokeStyle = headerColor; ctx.lineWidth = 3.5;
        ctx.beginPath(); ctx.roundRect(leftX, topY, boxW, boxH, 18); ctx.stroke();
        ctx.restore();
    } else if (boxStyle === 'crossfit_board') {
        if (bgOpacity > 0.01) { ctx.fillStyle = bgRgba; ctx.fillRect(leftX, topY, boxW, boxH); }
        ctx.fillStyle = headerColor; ctx.fillRect(leftX, topY, boxW, 6);
        ctx.strokeStyle = 'rgba(255,255,255,0.18)'; ctx.lineWidth = 1.5; ctx.strokeRect(leftX, topY, boxW, boxH);
    } else if (boxStyle === 'stencil_stamp') {
        if (bgOpacity > 0.01) { ctx.fillStyle = bgRgba; ctx.fillRect(leftX, topY, boxW, boxH); }
        ctx.strokeStyle = headerColor; ctx.lineWidth = 5; ctx.strokeRect(leftX, topY, boxW, boxH);
    }

    ctx.textAlign = align;
    let textAnchorX = 0;
    if (align === 'left') textAnchorX = leftX + padX;
    else if (align === 'right') textAnchorX = leftX + boxW - padX;

    ctx.shadowColor = 'rgba(0, 0, 0, 0.85)'; ctx.shadowBlur = 10; ctx.shadowOffsetY = 2;

    let curY = topY + padY + 38;
    if (wodHeader) {
        ctx.fillStyle = (boxStyle === 'header_banner') ? '#090d16' : headerColor;
        ctx.font = `900 50px ${fontFam}`;
        ctx.fillText(wodHeader, textAnchorX, curY);

        if (boxStyle === 'crossfit_board' || boxStyle === 'glass_card') {
            ctx.strokeStyle = 'rgba(255,255,255,0.16)'; ctx.lineWidth = 1.5;
            ctx.beginPath(); ctx.moveTo(leftX + 20, curY + 18); ctx.lineTo(leftX + boxW - 20, curY + 18); ctx.stroke();
        }
        curY += headerH;
    }

    lines.forEach((item, i) => {
        ctx.save();
        ctx.translate(0, curY + (i * rowH));

        if (boxStyle === 'row_pills' && bgOpacity > 0.01) {
            ctx.fillStyle = item.isAct ? hexToRgba(activeColor, Math.min(0.95, bgOpacity + 0.15)) : bgRgba;
            ctx.beginPath(); ctx.roundRect(leftX + 10, -38, boxW - 20, 48, 24); ctx.fill();
            if (item.isAct) { ctx.strokeStyle = '#ffffff'; ctx.lineWidth = 2; ctx.stroke(); }
        } else if (boxStyle === 'crossfit_board' && i > 0) {
            ctx.strokeStyle = 'rgba(255,255,255,0.1)'; ctx.lineWidth = 1;
            ctx.beginPath(); ctx.moveTo(leftX + 16, -42); ctx.lineTo(leftX + boxW - 16, -42); ctx.stroke();
        }

        if (item.isAct && repPopScale > 1.0) ctx.scale(repPopScale, repPopScale);

        ctx.font = `900 42px ${fontFam}`;
        ctx.fillStyle = (boxStyle === 'row_pills' && item.isAct) ? '#090d16' : (item.isAct ? activeColor : textColor);
        ctx.fillText(item.text, textAnchorX, 0);

        if (item.isAct && boxStyle !== 'row_pills') {
            const tw = ctx.measureText(item.text).width;
            let dotX = -tw / 2 - 16;
            if (align === 'left') dotX = textAnchorX - 16;
            else if (align === 'right') dotX = textAnchorX - tw - 16;
            ctx.fillStyle = activeColor;
            ctx.beginPath(); ctx.arc(dotX, -14, 6, 0, Math.PI * 2); ctx.fill();
        }
        ctx.restore();
    });

    ctx.textAlign = 'left';
    ctx.restore();
}

// 3. RENDER 17 STYLE HEART RATE
function drawStandaloneHrWidget(ctx, data) {
    if (!document.getElementById('showHrWidget').checked) return;

    const style = document.getElementById('hudStyleSelect').value;
    const scale = parseFloat(document.getElementById('hudScale').value || 1.0);
    const { bpm, zInfo, fontFam, accentColor, heartColor, bgOpacity, beatScale } = data;

    ctx.save();
    ctx.translate(hrPos.x, hrPos.y);
    ctx.scale(scale, scale);
    ctx.shadowColor = 'rgba(0,0,0,0.85)'; ctx.shadowBlur = 8;

    if (style === 'heart_num_side') {
        ctx.save(); ctx.translate(32, 38); ctx.scale(beatScale, beatScale);
        drawHeartIcon(ctx, 0, -22, 48, heartColor); ctx.restore();
        ctx.fillStyle = '#ffffff'; ctx.font = `900 66px ${fontFam}`; ctx.fillText(String(bpm), 68, 62);
    } else if (style === 'heart_num_inside') {
        ctx.save(); ctx.translate(68, 12); ctx.scale(beatScale, beatScale);
        drawHeartIcon(ctx, 0, 0, 108, heartColor); ctx.restore();
        ctx.textAlign = 'center'; ctx.fillStyle = '#ffffff'; ctx.font = `900 44px ${fontFam}`;
        ctx.fillText(String(bpm), 68, 78); ctx.textAlign = 'left';
    } else if (style === 'dji_ring') {
        const r = 74, cx = 82, cy = 82;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(10, 15, 26, ${bgOpacity})`;
            ctx.beginPath(); ctx.arc(cx, cy, r + 10, 0, Math.PI * 2); ctx.fill();
        }
        const sAng = Math.PI * 0.75, eAng = Math.PI * 2.25, tot = eAng - sAng;
        ctx.beginPath(); ctx.arc(cx, cy, r, sAng, eAng);
        ctx.strokeStyle = 'rgba(255,255,255,0.18)'; ctx.lineWidth = 11; ctx.lineCap = 'round'; ctx.stroke();
        ctx.beginPath(); ctx.arc(cx, cy, r, sAng, sAng + tot * Math.min(1, zInfo.ratio));
        ctx.strokeStyle = heartColor; ctx.lineWidth = 11; ctx.lineCap = 'round'; ctx.stroke();
        ctx.textAlign = 'center';
        ctx.save(); ctx.translate(cx, cy - 36); ctx.scale(beatScale, beatScale);
        drawHeartIcon(ctx, 0, -10, 22, heartColor); ctx.restore();
        ctx.fillStyle = '#ffffff'; ctx.font = `900 44px ${fontFam}`; ctx.fillText(String(bpm), cx, cy + 12);
        ctx.fillStyle = '#cbd5e1'; ctx.font = `700 13px ${fontFam}`; ctx.fillText('BPM', cx, cy + 30);
        ctx.fillStyle = zInfo.color; ctx.font = `800 12px ${fontFam}`; ctx.fillText(`Z${zInfo.zone} ${zInfo.short}`, cx, cy + 60);
        ctx.textAlign = 'left';
    } else if (style === 'dji_ecg') {
        const w = 235, h = 125;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(10, 15, 26, ${bgOpacity})`;
            ctx.beginPath(); ctx.roundRect(0, 0, w, h, 14); ctx.fill();
        }
        ctx.save(); ctx.translate(30, 36); ctx.scale(beatScale, beatScale);
        drawHeartIcon(ctx, 0, -12, 26, heartColor); ctx.restore();
        ctx.fillStyle = '#ffffff'; ctx.font = `900 48px ${fontFam}`; ctx.fillText(String(bpm), 54, 52);
        ctx.fillStyle = zInfo.color; ctx.font = `700 14px ${fontFam}`; ctx.fillText(`BPM • Z${zInfo.zone}`, 142, 46);
        ctx.beginPath();
        for (let x = 0; x < 195; x += 3) {
            const ph = (x + ecgOffset) % 60;
            let yo = (ph > 25 && ph < 30) ? -20 : (ph >= 30 && ph < 35 ? 12 : 0);
            if (x === 0) ctx.moveTo(20 + x, 92 + yo); else ctx.lineTo(20 + x, 92 + yo);
        }
        ctx.strokeStyle = heartColor; ctx.lineWidth = 2.5; ctx.stroke();
    } else if (style === 'apple_fitness') {
        const w = 230, h = 100;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(14, 18, 26, ${bgOpacity})`;
            ctx.beginPath(); ctx.roundRect(0, 0, w, h, 24); ctx.fill();
        }
        const cx = 50, cy = 50, r = 30;
        ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(255,42,75,0.25)'; ctx.lineWidth = 9; ctx.stroke();
        ctx.beginPath(); ctx.arc(cx, cy, r, -Math.PI/2, -Math.PI/2 + (Math.PI*2 * zInfo.ratio));
        ctx.strokeStyle = heartColor; ctx.lineWidth = 9; ctx.lineCap = 'round'; ctx.stroke();
        ctx.save(); ctx.translate(cx, cy + 2); ctx.scale(beatScale, beatScale);
        drawHeartIcon(ctx, 0, -10, 20, heartColor); ctx.restore();
        ctx.fillStyle = '#ffffff'; ctx.font = `900 44px ${fontFam}`; ctx.fillText(`${bpm}`, 96, 56);
        ctx.fillStyle = '#94a3b8'; ctx.font = `700 14px ${fontFam}`; ctx.fillText('BPM', 178, 54);
        ctx.fillStyle = zInfo.color; ctx.font = `800 12px ${fontFam}`; ctx.fillText(zInfo.name, 96, 78);
    } else if (style === 'garmin_arc') {
        const cx = 105, cy = 105, r = 78;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(10, 15, 26, ${bgOpacity})`;
            ctx.beginPath(); ctx.roundRect(10, 15, 190, 135, 18); ctx.fill();
        }
        const zCols = ['#38bdf8', '#22c55e', '#eab308', '#f97316', '#ef4444'];
        for (let z = 0; z < 5; z++) {
            const a1 = Math.PI + (z * 0.2 * Math.PI) + 0.03;
            const a2 = Math.PI + ((z + 1) * 0.2 * Math.PI) - 0.03;
            ctx.beginPath(); ctx.arc(cx, cy, r, a1, a2);
            ctx.strokeStyle = (z + 1 <= zInfo.zone) ? zCols[z] : 'rgba(255,255,255,0.15)';
            ctx.lineWidth = (z + 1 === zInfo.zone) ? 14 : 9;
            ctx.stroke();
        }
        ctx.textAlign = 'center';
        ctx.fillStyle = '#ffffff'; ctx.font = `900 48px ${fontFam}`; ctx.fillText(`${bpm}`, cx, cy + 2);
        ctx.fillStyle = zInfo.color; ctx.font = `800 13px ${fontFam}`; ctx.fillText(`♥ ${zInfo.short}`, cx, cy + 26);
        ctx.textAlign = 'left';
    } else if (style === 'whoop_strain') {
        const w = 220, h = 95;
        if (bgOpacity > 0.02) { ctx.fillStyle = `rgba(12, 16, 24, ${bgOpacity})`; ctx.fillRect(0, 0, w, h); }
        ctx.fillStyle = '#ffffff'; ctx.font = `900 46px ${fontFam}`; ctx.fillText(`${bpm}`, 16, 50);
        ctx.fillStyle = heartColor; ctx.font = `800 14px ${fontFam}`; ctx.fillText(`♥ ${zInfo.pct}% MAX`, 105, 34);
        ctx.fillStyle = '#94a3b8'; ctx.font = `700 12px ${fontFam}`; ctx.fillText(zInfo.short, 105, 52);
        ctx.fillStyle = 'rgba(255,255,255,0.18)'; ctx.fillRect(16, 68, w - 32, 10);
        ctx.fillStyle = zInfo.color; ctx.fillRect(16, 68, (w - 32) * zInfo.ratio, 10);
    } else if (style === 'strava_minimal') {
        ctx.fillStyle = '#fc4c02'; ctx.fillRect(0, 8, 6, 78);
        ctx.fillStyle = '#cbd5e1'; ctx.font = `800 13px ${fontFam}`; ctx.fillText('HEART RATE', 18, 22);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 58px ${fontFam}`; ctx.fillText(`${bpm}`, 16, 74);
        const bw = ctx.measureText(`${bpm}`).width;
        ctx.fillStyle = heartColor; ctx.font = `800 18px ${fontFam}`; ctx.fillText('BPM', 22 + bw, 72);
    } else if (style === 'coros_hex') {
        const cx = 90, cy = 80, r = 72;
        ctx.beginPath();
        for (let i = 0; i < 6; i++) {
            const ang = (Math.PI / 3) * i - Math.PI / 6;
            const hx = cx + r * Math.cos(ang), hy = cy + r * Math.sin(ang);
            if (i === 0) ctx.moveTo(hx, hy); else ctx.lineTo(hx, hy);
        }
        ctx.closePath();
        if (bgOpacity > 0.02) { ctx.fillStyle = `rgba(12, 19, 32, ${bgOpacity})`; ctx.fill(); }
        ctx.strokeStyle = zInfo.color; ctx.lineWidth = 4; ctx.stroke();
        ctx.textAlign = 'center';
        ctx.fillStyle = heartColor; ctx.font = `800 13px ${fontFam}`; ctx.fillText(`♥ Z${zInfo.zone}`, cx, cy - 24);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 44px ${fontFam}`; ctx.fillText(`${bpm}`, cx, cy + 14);
        ctx.fillStyle = '#94a3b8'; ctx.font = `700 12px ${fontFam}`; ctx.fillText('BPM', cx, cy + 34);
        ctx.textAlign = 'left';
    } else if (style === 'nike_volt') {
        const w = 225, h = 92;
        ctx.beginPath(); ctx.moveTo(14, 0); ctx.lineTo(w, 0); ctx.lineTo(w - 14, h); ctx.lineTo(0, h); ctx.closePath();
        ctx.fillStyle = `rgba(10, 12, 16, ${Math.max(0.75, bgOpacity)})`; ctx.fill();
        ctx.fillStyle = '#d4ff00';
        ctx.beginPath(); ctx.moveTo(14, 0); ctx.lineTo(26, 0); ctx.lineTo(12, h); ctx.lineTo(0, h); ctx.closePath(); ctx.fill();
        ctx.fillStyle = '#d4ff00'; ctx.font = `900 13px ${fontFam}`; ctx.fillText(`♥ LIVE BPM // Z${zInfo.zone}`, 34, 26);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 52px ${fontFam}`; ctx.fillText(`${bpm}`, 32, 76);
    } else if (style === 'polar_beat') {
        const w = 185, h = 110;
        ctx.fillStyle = '#d90429';
        ctx.beginPath(); ctx.roundRect(0, 0, w, 30, [12, 12, 0, 0]); ctx.fill();
        ctx.fillStyle = `rgba(15, 20, 30, ${Math.max(0.7, bgOpacity)})`;
        ctx.beginPath(); ctx.roundRect(0, 30, w, h - 30, [0, 0, 12, 12]); ctx.fill();
        ctx.fillStyle = '#ffffff'; ctx.font = `800 13px ${fontFam}`; ctx.fillText('♥ POLAR HR ZONE ' + zInfo.zone, 14, 20);
        ctx.font = `900 52px ${fontFam}`; ctx.fillText(`${bpm}`, 20, 86);
        ctx.fillStyle = '#94a3b8'; ctx.font = `700 16px ${fontFam}`; ctx.fillText('BPM', 118, 84);
    } else if (style === 'cyber_hud') {
        const w = 215, h = 95;
        if (bgOpacity > 0.02) { ctx.fillStyle = `rgba(8, 16, 28, ${bgOpacity})`; ctx.fillRect(0, 0, w, h); }
        ctx.strokeStyle = '#00f5d4'; ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(0, 20); ctx.lineTo(0, 0); ctx.lineTo(20, 0);
        ctx.moveTo(w - 20, 0); ctx.lineTo(w, 0); ctx.lineTo(w, 20);
        ctx.moveTo(0, h - 20); ctx.lineTo(0, h); ctx.lineTo(20, h);
        ctx.moveTo(w - 20, h); ctx.lineTo(w, h); ctx.lineTo(w, h - 20);
        ctx.stroke();
        ctx.fillStyle = '#00f5d4'; ctx.font = `700 12px ${fontFam}`; ctx.fillText(`SYS.HR // ${zInfo.short}`, 16, 24);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 48px ${fontFam}`; ctx.fillText(`♥ ${bpm}`, 16, 74);
    } else if (style === 'crossfit_tile') {
        const w = 155, h = 120;
        ctx.fillStyle = `rgba(18, 22, 28, ${Math.max(0.75, bgOpacity)})`; ctx.fillRect(0, 0, w, h);
        ctx.fillStyle = accentColor; ctx.fillRect(0, 0, w, 6);
        ctx.textAlign = 'center';
        ctx.fillStyle = '#94a3b8'; ctx.font = `800 13px ${fontFam}`; ctx.fillText('HEART RATE', w/2, 28);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 54px ${fontFam}`; ctx.fillText(`${bpm}`, w/2, 80);
        ctx.fillStyle = zInfo.color; ctx.font = `800 13px ${fontFam}`; ctx.fillText(`♥ BPM (Z${zInfo.zone})`, w/2, 106);
        ctx.textAlign = 'left';
    } else if (style === 'heart_fill') {
        ctx.save(); ctx.translate(65, 20); ctx.scale(beatScale, beatScale);
        drawHeartIcon(ctx, 0, 0, 95, 'rgba(255, 42, 75, 0.32)');
        drawHeartIcon(ctx, 0, 0, 82, heartColor);
        ctx.restore();
        ctx.textAlign = 'center';
        ctx.fillStyle = '#ffffff'; ctx.font = `900 38px ${fontFam}`; ctx.fillText(`${bpm}`, 65, 76);
        ctx.font = `800 12px ${fontFam}`; ctx.fillText('BPM', 65, 94);
        ctx.textAlign = 'left';
    } else if (style === 'vertical_meter') {
        const barH = 135;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(12, 18, 28, ${bgOpacity})`;
            ctx.beginPath(); ctx.roundRect(0, 0, 175, barH + 20, 12); ctx.fill();
        }
        const grad = ctx.createLinearGradient(0, barH + 10, 0, 10);
        grad.addColorStop(0, '#38bdf8'); grad.addColorStop(0.5, '#eab308'); grad.addColorStop(1, '#ef4444');
        ctx.fillStyle = 'rgba(255,255,255,0.15)'; ctx.fillRect(16, 10, 12, barH);
        const fillH = barH * zInfo.ratio;
        ctx.fillStyle = grad; ctx.fillRect(16, 10 + (barH - fillH), 12, fillH);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 48px ${fontFam}`; ctx.fillText(`${bpm}`, 42, 72);
        ctx.fillStyle = heartColor; ctx.font = `800 15px ${fontFam}`; ctx.fillText('♥ BPM', 42, 96);
        ctx.fillStyle = zInfo.color; ctx.font = `700 12px ${fontFam}`; ctx.fillText(zInfo.short, 42, 118);
    } else if (style === 'neon_glow') {
        const w = 210, h = 90;
        ctx.save();
        ctx.shadowColor = heartColor; ctx.shadowBlur = 18;
        ctx.strokeStyle = heartColor; ctx.lineWidth = 3;
        ctx.beginPath(); ctx.roundRect(0, 0, w, h, 16); ctx.stroke();
        ctx.fillStyle = '#ffffff'; ctx.font = `900 50px ${fontFam}`; ctx.fillText(`♥ ${bpm}`, 20, 62);
        ctx.restore();
        ctx.fillStyle = heartColor; ctx.font = `800 14px ${fontFam}`; ctx.fillText('BPM', 155, 60);
    } else {
        const w = 215, h = 82;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(15, 23, 42, ${bgOpacity})`;
            ctx.beginPath(); ctx.roundRect(0, 0, w, h, 41); ctx.fill();
            ctx.strokeStyle = 'rgba(255,255,255,0.2)'; ctx.lineWidth = 1.5; ctx.stroke();
        }
        ctx.save(); ctx.translate(36, 38); ctx.scale(beatScale, beatScale);
        drawHeartIcon(ctx, 0, -12, 26, heartColor); ctx.restore();
        ctx.fillStyle = '#ffffff'; ctx.font = `900 42px ${fontFam}`; ctx.fillText(`${bpm}`, 62, 54);
        ctx.fillStyle = zInfo.color; ctx.font = `700 13px ${fontFam}`; ctx.fillText(`BPM • Z${zInfo.zone}`, 142, 50);
    }

    ctx.restore();
}

// 4. RENDER 24 STYLE WIDGET TIMER INDEPENDEN
function drawStandaloneTimerWidget(ctx, data) {
    if (!document.getElementById('showTimerWidget').checked) return;

    const tStyle    = document.getElementById('timerStyleSelect').value;
    const countMode = document.getElementById('timerCountMode').value;
    const scale     = parseFloat(document.getElementById('hudScale').value || 1.0);
    const { clampedElapsed, workoutMaxDuration, progressRatio, fontFam, accentColor, bgOpacity } = data;

    const upStr    = formatMMSS(clampedElapsed);
    const downStr  = formatMMSS(Math.max(0, workoutMaxDuration - clampedElapsed));
    const totalStr = formatMMSS(workoutMaxDuration);

    let mainClock = upStr;
    if (countMode === 'count_down') mainClock = downStr;
    else if (countMode === 'with_total') mainClock = `${upStr} / ${totalStr}`;

    const shortClock = (countMode === 'count_down') ? downStr : upStr;
    const pctDone    = Math.min(100, Math.round(progressRatio * 100));
    const secHandAng = ((clampedElapsed % 60) / 60) * Math.PI * 2;

    ctx.save();
    ctx.translate(timerPos.x, timerPos.y);
    ctx.scale(scale, scale);
    ctx.shadowColor = 'rgba(0,0,0,0.85)';
    ctx.shadowBlur = 8;

    if (tStyle === 'text_pure') {
        ctx.fillStyle = '#ffffff'; ctx.font = `900 62px ${fontFam}`; ctx.fillText(mainClock, 0, 58);
    } else if (tStyle === 'clean_digital') {
        ctx.fillStyle = accentColor; ctx.font = `800 13px ${fontFam}`;
        ctx.fillText(countMode === 'count_down' ? '⏱ TIME REMAINING' : '⏱ WORKOUT TIME', 0, 18);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 54px ${fontFam}`; ctx.fillText(mainClock, 0, 68);
    } else if (tStyle === 'text_slash_total') {
        drawStopwatchVector(ctx, 18, 36, 15, '#e2e8f0', secHandAng);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 44px ${fontFam}`; ctx.fillText(shortClock, 44, 52);
        const w1 = ctx.measureText(shortClock).width;
        ctx.fillStyle = '#94a3b8'; ctx.font = `700 32px ${fontFam}`; ctx.fillText(` / ${totalStr}`, 44 + w1 + 4, 50);
        const barW = Math.max(220, 44 + w1 + ctx.measureText(` / ${totalStr}`).width);
        ctx.fillStyle = 'rgba(255,255,255,0.22)'; ctx.beginPath(); ctx.roundRect(4, 66, barW, 7, 4); ctx.fill();
        ctx.fillStyle = '#ffffff'; ctx.beginPath(); ctx.roundRect(4, 66, Math.max(8, barW * progressRatio), 7, 4); ctx.fill();
    } else if (tStyle === 'text_outline') {
        ctx.font = `900 64px ${fontFam}`; ctx.lineWidth = 3.5; ctx.strokeStyle = accentColor;
        ctx.strokeText(mainClock, 0, 60); ctx.fillStyle = 'rgba(255,255,255,0.15)'; ctx.fillText(mainClock, 0, 60);
    } else if (tStyle === 'text_neon') {
        ctx.save(); ctx.shadowColor = accentColor; ctx.shadowBlur = 20;
        ctx.fillStyle = '#ffffff'; ctx.font = `900 60px ${fontFam}`; ctx.fillText(mainClock, 0, 58); ctx.restore();
        ctx.fillStyle = accentColor; ctx.font = `800 13px ${fontFam}`; ctx.fillText(`// ${pctDone}% COMPLETED`, 2, 78);
    } else if (tStyle === 'text_stacked') {
        const parts = shortClock.split(':');
        ctx.fillStyle = accentColor; ctx.fillRect(0, 4, 6, 108);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 54px ${fontFam}`; ctx.fillText(parts[0] || '00', 16, 50);
        ctx.fillStyle = accentColor; ctx.font = `900 54px ${fontFam}`; ctx.fillText(parts[1] || '00', 16, 106);
        ctx.fillStyle = '#94a3b8'; ctx.font = `700 12px ${fontFam}`; ctx.fillText('MIN', 92, 34); ctx.fillText('SEC', 92, 90);
    } else if (tStyle === 'match_dji_ring') {
        const r = 74, cx = 82, cy = 82;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(10, 15, 26, ${bgOpacity})`;
            ctx.beginPath(); ctx.arc(cx, cy, r + 10, 0, Math.PI * 2); ctx.fill();
        }
        const sAng = Math.PI * 0.75, eAng = Math.PI * 2.25, tot = eAng - sAng;
        ctx.beginPath(); ctx.arc(cx, cy, r, sAng, eAng);
        ctx.strokeStyle = 'rgba(255,255,255,0.18)'; ctx.lineWidth = 11; ctx.lineCap = 'round'; ctx.stroke();
        ctx.beginPath(); ctx.arc(cx, cy, r, sAng, sAng + tot * Math.max(0.02, progressRatio));
        ctx.strokeStyle = accentColor; ctx.lineWidth = 11; ctx.lineCap = 'round'; ctx.stroke();
        drawStopwatchVector(ctx, cx, cy - 38, 11, accentColor, secHandAng);
        ctx.textAlign = 'center';
        ctx.fillStyle = '#ffffff'; ctx.font = `900 36px ${fontFam}`; ctx.fillText(shortClock, cx, cy + 10);
        ctx.fillStyle = '#cbd5e1'; ctx.font = `700 12px ${fontFam}`; ctx.fillText('TIME', cx, cy + 28);
        ctx.fillStyle = accentColor; ctx.font = `800 12px ${fontFam}`; ctx.fillText(`${pctDone}% DONE`, cx, cy + 58);
        ctx.textAlign = 'left';
    } else if (tStyle === 'match_dji_wave') {
        const w = 235, h = 125;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(10, 15, 26, ${bgOpacity})`;
            ctx.beginPath(); ctx.roundRect(0, 0, w, h, 14); ctx.fill();
        }
        drawStopwatchVector(ctx, 28, 36, 13, accentColor, secHandAng);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 44px ${fontFam}`; ctx.fillText(shortClock, 50, 52);
        ctx.fillStyle = accentColor; ctx.font = `700 13px ${fontFam}`; ctx.fillText(`${pctDone}%`, 178, 46);
        ctx.beginPath();
        for (let x = 0; x < 195; x += 3) {
            const yo = Math.sin((x + ecgOffset) * 0.08) * 10;
            if (x === 0) ctx.moveTo(20 + x, 92 + yo); else ctx.lineTo(20 + x, 92 + yo);
        }
        ctx.strokeStyle = accentColor; ctx.lineWidth = 2.5; ctx.stroke();
    } else if (tStyle === 'match_apple_ring') {
        const w = 235, h = 100;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(14, 18, 26, ${bgOpacity})`;
            ctx.beginPath(); ctx.roundRect(0, 0, w, h, 24); ctx.fill();
        }
        const cx = 50, cy = 50, r = 30;
        ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(255,255,255,0.18)'; ctx.lineWidth = 9; ctx.stroke();
        ctx.beginPath(); ctx.arc(cx, cy, r, -Math.PI/2, -Math.PI/2 + (Math.PI * 2 * progressRatio));
        ctx.strokeStyle = accentColor; ctx.lineWidth = 9; ctx.lineCap = 'round'; ctx.stroke();
        drawStopwatchVector(ctx, cx, cy + 2, 11, accentColor, secHandAng);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 40px ${fontFam}`; ctx.fillText(shortClock, 94, 54);
        ctx.fillStyle = accentColor; ctx.font = `800 12px ${fontFam}`; ctx.fillText(`TOTAL ${totalStr}`, 94, 76);
    } else if (tStyle === 'match_garmin_arc') {
        const cx = 105, cy = 105, r = 78;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(10, 15, 26, ${bgOpacity})`;
            ctx.beginPath(); ctx.roundRect(10, 15, 190, 135, 18); ctx.fill();
        }
        const activeSeg = Math.ceil(progressRatio * 5);
        const zCols = ['#38bdf8', '#22c55e', '#eab308', '#f97316', '#ef4444'];
        for (let z = 0; z < 5; z++) {
            const a1 = Math.PI + (z * 0.2 * Math.PI) + 0.03;
            const a2 = Math.PI + ((z + 1) * 0.2 * Math.PI) - 0.03;
            ctx.beginPath(); ctx.arc(cx, cy, r, a1, a2);
            ctx.strokeStyle = (z + 1 <= activeSeg) ? zCols[z] : 'rgba(255,255,255,0.15)';
            ctx.lineWidth = (z + 1 === activeSeg) ? 14 : 9;
            ctx.stroke();
        }
        ctx.textAlign = 'center';
        ctx.fillStyle = '#ffffff'; ctx.font = `900 42px ${fontFam}`; ctx.fillText(shortClock, cx, cy + 2);
        ctx.fillStyle = accentColor; ctx.font = `800 13px ${fontFam}`; ctx.fillText(`⏱ ${pctDone}% TIME`, cx, cy + 26);
        ctx.textAlign = 'left';
    } else if (tStyle === 'match_whoop_bar') {
        const w = 220, h = 95;
        if (bgOpacity > 0.02) { ctx.fillStyle = `rgba(12, 16, 24, ${bgOpacity})`; ctx.fillRect(0, 0, w, h); }
        ctx.fillStyle = '#ffffff'; ctx.font = `900 42px ${fontFam}`; ctx.fillText(shortClock, 16, 50);
        ctx.fillStyle = accentColor; ctx.font = `800 13px ${fontFam}`; ctx.fillText(`⏱ ${pctDone}%`, 145, 34);
        ctx.fillStyle = '#94a3b8'; ctx.font = `700 11px ${fontFam}`; ctx.fillText(`/${totalStr}`, 145, 52);
        ctx.fillStyle = 'rgba(255,255,255,0.18)'; ctx.fillRect(16, 68, w - 32, 10);
        ctx.fillStyle = accentColor; ctx.fillRect(16, 68, (w - 32) * progressRatio, 10);
    } else if (tStyle === 'match_strava_clean') {
        ctx.fillStyle = '#fc4c02'; ctx.fillRect(0, 8, 6, 78);
        ctx.fillStyle = '#cbd5e1'; ctx.font = `800 13px ${fontFam}`; ctx.fillText('ELAPSED TIME', 18, 22);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 54px ${fontFam}`; ctx.fillText(shortClock, 16, 74);
    } else if (tStyle === 'match_coros_hex') {
        const cx = 90, cy = 80, r = 72;
        ctx.beginPath();
        for (let i = 0; i < 6; i++) {
            const ang = (Math.PI / 3) * i - Math.PI / 6;
            const hx = cx + r * Math.cos(ang), hy = cy + r * Math.sin(ang);
            if (i === 0) ctx.moveTo(hx, hy); else ctx.lineTo(hx, hy);
        }
        ctx.closePath();
        if (bgOpacity > 0.02) { ctx.fillStyle = `rgba(12, 19, 32, ${bgOpacity})`; ctx.fill(); }
        ctx.strokeStyle = accentColor; ctx.lineWidth = 4; ctx.stroke();
        ctx.textAlign = 'center';
        ctx.fillStyle = accentColor; ctx.font = `800 12px ${fontFam}`; ctx.fillText('⏱ TIMER', cx, cy - 24);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 36px ${fontFam}`; ctx.fillText(shortClock, cx, cy + 12);
        ctx.fillStyle = '#94a3b8'; ctx.font = `700 11px ${fontFam}`; ctx.fillText(`${pctDone}%`, cx, cy + 34);
        ctx.textAlign = 'left';
    } else if (tStyle === 'match_nike_volt') {
        const w = 225, h = 92;
        ctx.beginPath(); ctx.moveTo(14, 0); ctx.lineTo(w, 0); ctx.lineTo(w - 14, h); ctx.lineTo(0, h); ctx.closePath();
        ctx.fillStyle = `rgba(10, 12, 16, ${Math.max(0.75, bgOpacity)})`; ctx.fill();
        ctx.fillStyle = '#d4ff00';
        ctx.beginPath(); ctx.moveTo(14, 0); ctx.lineTo(26, 0); ctx.lineTo(12, h); ctx.lineTo(0, h); ctx.closePath(); ctx.fill();
        ctx.fillStyle = '#d4ff00'; ctx.font = `900 13px ${fontFam}`; ctx.fillText(`⏱ WORKOUT // ${pctDone}%`, 34, 26);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 48px ${fontFam}`; ctx.fillText(shortClock, 32, 76);
    } else if (tStyle === 'match_polar_badge') {
        const w = 185, h = 110;
        ctx.fillStyle = '#d90429';
        ctx.beginPath(); ctx.roundRect(0, 0, w, 30, [12, 12, 0, 0]); ctx.fill();
        ctx.fillStyle = `rgba(15, 20, 30, ${Math.max(0.7, bgOpacity)})`;
        ctx.beginPath(); ctx.roundRect(0, 30, w, h - 30, [0, 0, 12, 12]); ctx.fill();
        ctx.fillStyle = '#ffffff'; ctx.font = `800 13px ${fontFam}`; ctx.fillText('⏱ SESSION TIME', 14, 20);
        ctx.font = `900 46px ${fontFam}`; ctx.fillText(shortClock, 18, 84);
    } else if (tStyle === 'match_cyber_brackets') {
        const w = 215, h = 95;
        if (bgOpacity > 0.02) { ctx.fillStyle = `rgba(8, 16, 28, ${bgOpacity})`; ctx.fillRect(0, 0, w, h); }
        ctx.strokeStyle = '#00f5d4'; ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(0, 20); ctx.lineTo(0, 0); ctx.lineTo(20, 0);
        ctx.moveTo(w - 20, 0); ctx.lineTo(w, 0); ctx.lineTo(w, 20);
        ctx.moveTo(0, h - 20); ctx.lineTo(0, h); ctx.lineTo(20, h);
        ctx.moveTo(w - 20, h); ctx.lineTo(w, h); ctx.lineTo(w, h - 20);
        ctx.stroke();
        ctx.fillStyle = '#00f5d4'; ctx.font = `700 12px ${fontFam}`; ctx.fillText(`SYS.TIME // ${pctDone}%`, 16, 24);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 44px ${fontFam}`; ctx.fillText(`⏱ ${shortClock}`, 16, 74);
    } else if (tStyle === 'match_crossfit_tile') {
        const w = 165, h = 120;
        ctx.fillStyle = `rgba(18, 22, 28, ${Math.max(0.75, bgOpacity)})`; ctx.fillRect(0, 0, w, h);
        ctx.fillStyle = accentColor; ctx.fillRect(0, 0, w, 6);
        ctx.textAlign = 'center';
        ctx.fillStyle = '#94a3b8'; ctx.font = `800 13px ${fontFam}`; ctx.fillText('DURATION', w/2, 28);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 46px ${fontFam}`; ctx.fillText(shortClock, w/2, 78);
        ctx.fillStyle = accentColor; ctx.font = `800 12px ${fontFam}`; ctx.fillText(`TARGET ${totalStr}`, w/2, 106);
        ctx.textAlign = 'left';
    } else if (tStyle === 'match_stopwatch_analog') {
        const cx = 65, cy = 65, r = 52;
        ctx.fillStyle = `rgba(15, 23, 42, ${Math.max(0.6, bgOpacity)})`;
        ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI * 2); ctx.fill();
        ctx.strokeStyle = accentColor; ctx.lineWidth = 4; ctx.stroke();
        ctx.fillStyle = hexToRgba(accentColor, 0.35);
        ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, r - 4, -Math.PI/2, -Math.PI/2 + secHandAng); ctx.closePath(); ctx.fill();
        ctx.textAlign = 'center';
        ctx.fillStyle = '#ffffff'; ctx.font = `900 32px ${fontFam}`; ctx.fillText(shortClock, cx, cy + 10);
        ctx.font = `800 11px ${fontFam}`; ctx.fillText('TIME', cx, cy + 28);
        ctx.textAlign = 'left';
    } else if (tStyle === 'match_vertical_meter') {
        const barH = 135;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(12, 18, 28, ${bgOpacity})`;
            ctx.beginPath(); ctx.roundRect(0, 0, 185, barH + 20, 12); ctx.fill();
        }
        ctx.fillStyle = 'rgba(255,255,255,0.15)'; ctx.fillRect(16, 10, 12, barH);
        const fillH = barH * progressRatio;
        ctx.fillStyle = accentColor; ctx.fillRect(16, 10 + (barH - fillH), 12, fillH);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 42px ${fontFam}`; ctx.fillText(shortClock, 40, 72);
        ctx.fillStyle = accentColor; ctx.font = `800 14px ${fontFam}`; ctx.fillText(`⏱ ${pctDone}%`, 40, 96);
        ctx.fillStyle = '#94a3b8'; ctx.font = `700 12px ${fontFam}`; ctx.fillText(`/${totalStr}`, 40, 118);
    } else if (tStyle === 'match_neon_box') {
        const w = 215, h = 90;
        ctx.save();
        ctx.shadowColor = accentColor; ctx.shadowBlur = 18;
        ctx.strokeStyle = accentColor; ctx.lineWidth = 3;
        ctx.beginPath(); ctx.roundRect(0, 0, w, h, 16); ctx.stroke();
        ctx.fillStyle = '#ffffff'; ctx.font = `900 46px ${fontFam}`; ctx.fillText(shortClock, 22, 60);
        ctx.restore();
        ctx.fillStyle = accentColor; ctx.font = `800 13px ${fontFam}`; ctx.fillText('TIME', 162, 58);
    } else if (tStyle === 'match_glass_capsule') {
        const w = 220, h = 82;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(15, 23, 42, ${bgOpacity})`;
            ctx.beginPath(); ctx.roundRect(0, 0, w, h, 41); ctx.fill();
            ctx.strokeStyle = 'rgba(255,255,255,0.2)'; ctx.lineWidth = 1.5; ctx.stroke();
        }
        drawStopwatchVector(ctx, 36, 42, 15, accentColor, secHandAng);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 40px ${fontFam}`; ctx.fillText(shortClock, 64, 54);
        ctx.fillStyle = accentColor; ctx.font = `700 12px ${fontFam}`; ctx.fillText(`${pctDone}%`, 175, 50);
    } else if (tStyle === 'dji_pill') {
        const w = 215, h = 88;
        if (bgOpacity > 0.02) {
            ctx.fillStyle = `rgba(10, 15, 26, ${bgOpacity})`;
            ctx.beginPath(); ctx.roundRect(0, 0, w, h, 20); ctx.fill();
        }
        ctx.fillStyle = '#94a3b8'; ctx.font = `700 12px ${fontFam}`; ctx.fillText('DURATION', 20, 24);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 42px ${fontFam}`; ctx.fillText(shortClock, 20, 64);
        ctx.fillStyle = 'rgba(255,255,255,0.2)'; ctx.fillRect(20, 74, w - 40, 5);
        ctx.fillStyle = accentColor; ctx.fillRect(20, 74, (w - 40) * progressRatio, 5);
    } else if (tStyle === 'broadcast_tag') {
        const w = 200, h = 82;
        ctx.fillStyle = `rgba(15, 23, 42, ${Math.max(0.7, bgOpacity)})`; ctx.fillRect(0, 0, w, h);
        ctx.fillStyle = accentColor; ctx.fillRect(0, 0, 6, h);
        ctx.fillStyle = accentColor; ctx.font = `800 12px ${fontFam}`; ctx.fillText('TIME ELAPSED', 18, 24);
        ctx.fillStyle = '#ffffff'; ctx.font = `900 44px ${fontFam}`; ctx.fillText(shortClock, 18, 68);
    } else {
        const chars = shortClock.split('');
        let cxOff = 0;
        chars.forEach(ch => {
            if (ch === ':') {
                ctx.fillStyle = accentColor; ctx.font = `900 44px ${fontFam}`;
                ctx.fillText(':', cxOff + 4, 52); cxOff += 20;
            } else {
                ctx.fillStyle = `rgba(15, 23, 42, ${Math.max(0.8, bgOpacity)})`;
                ctx.beginPath(); ctx.roundRect(cxOff, 6, 40, 60, 8); ctx.fill();
                ctx.strokeStyle = 'rgba(255,255,255,0.2)'; ctx.lineWidth = 1.5; ctx.stroke();
                ctx.textAlign = 'center';
                ctx.fillStyle = '#ffffff'; ctx.font = `900 44px ${fontFam}`;
                ctx.fillText(ch, cxOff + 20, 52);
                ctx.textAlign = 'left'; cxOff += 45;
            }
        });
    }

    ctx.restore();
}

// 5. RENDER WIDGET ROUND INDEPENDEN
function drawStandaloneRoundWidget(ctx, data) {
    if (!document.getElementById('showRoundWidget').checked) return;

    const scale = parseFloat(document.getElementById('hudScale').value || 1.0);
    const { counts, fontFam, accentColor, bgOpacity } = data;

    ctx.save();
    ctx.translate(roundPos.x, roundPos.y);
    ctx.scale(scale, scale);
    ctx.shadowColor = 'rgba(0,0,0,0.85)'; ctx.shadowBlur = 8;

    const w = 210, h = 72;
    if (bgOpacity > 0.02) {
        ctx.fillStyle = `rgba(12, 18, 30, ${bgOpacity})`;
        ctx.beginPath(); ctx.roundRect(0, 0, w, h, 14); ctx.fill();
    }
    ctx.fillStyle = accentColor; ctx.font = `800 12px ${fontFam}`; ctx.fillText('ROUND & TOTAL REPS', 16, 22);
    ctx.fillStyle = '#ffffff'; ctx.font = `900 32px ${fontFam}`;
    ctx.fillText(`RND ${counts.currentRound} • ${counts.totalReps}`, 16, 56);

    ctx.restore();
}

// 6. PENGGAMBAR IKON VEKTOR SOSIAL MEDIA & 10 STYLE WATERMARK
function drawSocialPlatformIcon(ctx, iconType, cx, cy, color) {
    if (iconType === 'none') return;
    ctx.save();
    ctx.strokeStyle = color; ctx.fillStyle = color; ctx.lineWidth = 2.2;

    if (iconType === 'instagram') {
        ctx.beginPath(); ctx.roundRect(cx - 11, cy - 11, 22, 22, 6); ctx.stroke();
        ctx.beginPath(); ctx.arc(cx, cy, 5, 0, Math.PI * 2); ctx.stroke();
        ctx.beginPath(); ctx.arc(cx + 6, cy - 6, 1.5, 0, Math.PI * 2); ctx.fill();
    } else if (iconType === 'tiktok') {
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.arc(cx - 3, cy + 4, 5, 0, Math.PI * 1.5);
        ctx.moveTo(cx + 2, cy + 4); ctx.lineTo(cx + 2, cy - 10);
        ctx.quadraticCurveTo(cx + 6, cy - 6, cx + 10, cy - 6);
        ctx.stroke();
    } else if (iconType === 'youtube') {
        ctx.beginPath(); ctx.roundRect(cx - 13, cy - 9, 26, 18, 5); ctx.fill();
        ctx.fillStyle = '#090d16';
        ctx.beginPath(); ctx.moveTo(cx - 3, cy - 5); ctx.lineTo(cx + 5, cy); ctx.lineTo(cx - 3, cy + 5); ctx.closePath(); ctx.fill();
    } else if (iconType === 'verified') {
        ctx.beginPath(); ctx.arc(cx, cy, 11, 0, Math.PI * 2); ctx.fill();
        ctx.strokeStyle = '#090d16'; ctx.lineWidth = 2.5;
        ctx.beginPath(); ctx.moveTo(cx - 5, cy); ctx.lineTo(cx - 1, cy + 4); ctx.lineTo(cx + 6, cy - 4); ctx.stroke();
    } else if (iconType === 'dumbbell') {
        ctx.lineWidth = 3;
        ctx.beginPath(); ctx.moveTo(cx - 8, cy); ctx.lineTo(cx + 8, cy); ctx.stroke();
        ctx.fillRect(cx - 11, cy - 6, 4, 12);
        ctx.fillRect(cx + 7, cy - 6, 4, 12);
    }
    ctx.restore();
}

function drawWatermarkWidget(ctx, fontFam) {
    if (!document.getElementById('showWatermarkWidget').checked) return;
    const wmText = document.getElementById('watermarkText').value.trim();
    if (!wmText) return;

    const wmSub     = document.getElementById('watermarkSub').value.trim().toUpperCase();
    const wmStyle   = document.getElementById('wmStyleSelect').value;
    const wmIcon    = document.getElementById('wmIconSelect').value;
    const wmColor   = document.getElementById('wmColorPicker').value;
    const textColor = document.getElementById('wmTextColorPicker').value;
    const wmScale   = parseFloat(document.getElementById('wmScaleSlider').value || 1.0);
    const wmOpacity = parseFloat(document.getElementById('wmOpacitySlider').value || 0.9);

    ctx.save();
    ctx.translate(watermarkPos.x, watermarkPos.y);
    ctx.scale(wmScale, wmScale);
    ctx.globalAlpha = wmOpacity;

    ctx.font = `900 22px ${fontFam}`;
    const textW = ctx.measureText(wmText).width;
    const hasIcon = (wmIcon !== 'none');
    const iconPad = hasIcon ? 36 : 12;

    if (wmStyle === 'glass_pill') {
        const w = textW + iconPad + 28, h = 44;
        ctx.fillStyle = 'rgba(12, 18, 30, 0.78)';
        ctx.beginPath(); ctx.roundRect(0, 0, w, h, 22); ctx.fill();
        ctx.strokeStyle = 'rgba(255,255,255,0.22)'; ctx.lineWidth = 1.5; ctx.stroke();
        if (hasIcon) drawSocialPlatformIcon(ctx, wmIcon, 24, 22, wmColor);
        ctx.fillStyle = textColor; ctx.fillText(wmText, hasIcon ? 44 : 18, 29);
    } else if (wmStyle === 'ig_gradient') {
        const w = textW + iconPad + 30, h = 46;
        const grad = ctx.createLinearGradient(0, 0, w, h);
        grad.addColorStop(0, '#833ab4'); grad.addColorStop(0.5, '#fd1d1d'); grad.addColorStop(1, '#fcb045');
        ctx.fillStyle = grad; ctx.beginPath(); ctx.roundRect(0, 0, w, h, 23); ctx.fill();
        if (hasIcon) drawSocialPlatformIcon(ctx, wmIcon, 25, 23, '#ffffff');
        ctx.fillStyle = '#ffffff'; ctx.fillText(wmText, hasIcon ? 46 : 18, 30);
    } else if (wmStyle === 'tiktok_glitch') {
        const w = textW + iconPad + 30, h = 46;
        ctx.fillStyle = '#00f2fe'; ctx.beginPath(); ctx.roundRect(-3, -2, w, h, 10); ctx.fill();
        ctx.fillStyle = '#fe0979'; ctx.beginPath(); ctx.roundRect(3, 2, w, h, 10); ctx.fill();
        ctx.fillStyle = '#090d16'; ctx.beginPath(); ctx.roundRect(0, 0, w, h, 10); ctx.fill();
        if (hasIcon) drawSocialPlatformIcon(ctx, wmIcon, 25, 23, '#00f2fe');
        ctx.fillStyle = textColor; ctx.fillText(wmText, hasIcon ? 46 : 18, 30);
    } else if (wmStyle === 'yt_subscribe') {
        const w = textW + iconPad + 32, h = 46;
        ctx.fillStyle = '#ef4444'; ctx.beginPath(); ctx.roundRect(0, 0, w, h, 8); ctx.fill();
        if (hasIcon) drawSocialPlatformIcon(ctx, wmIcon, 25, 23, '#ffffff');
        ctx.fillStyle = '#ffffff'; ctx.fillText(wmText, hasIcon ? 46 : 18, 30);
    } else if (wmStyle === 'minimal_clean') {
        ctx.shadowColor = 'rgba(0,0,0,0.9)'; ctx.shadowBlur = 8;
        if (hasIcon) drawSocialPlatformIcon(ctx, wmIcon, 16, 20, wmColor);
        ctx.fillStyle = textColor; ctx.font = `900 26px ${fontFam}`;
        ctx.fillText(wmText, hasIcon ? 36 : 4, 28);
    } else if (wmStyle === 'neon_border') {
        const w = textW + iconPad + 30, h = 46;
        ctx.save();
        ctx.shadowColor = wmColor; ctx.shadowBlur = 14;
        ctx.strokeStyle = wmColor; ctx.lineWidth = 2.5;
        ctx.fillStyle = 'rgba(9, 13, 22, 0.65)';
        ctx.beginPath(); ctx.roundRect(0, 0, w, h, 12); ctx.fill(); ctx.stroke();
        ctx.restore();
        if (hasIcon) drawSocialPlatformIcon(ctx, wmIcon, 25, 23, wmColor);
        ctx.fillStyle = textColor; ctx.fillText(wmText, hasIcon ? 46 : 18, 30);
    } else if (wmStyle === 'slanted_sport') {
        const w = textW + iconPad + 38, h = 44;
        ctx.beginPath(); ctx.moveTo(12, 0); ctx.lineTo(w, 0); ctx.lineTo(w - 12, h); ctx.lineTo(0, h); ctx.closePath();
        ctx.fillStyle = 'rgba(15, 23, 42, 0.88)'; ctx.fill();
        ctx.fillStyle = wmColor;
        ctx.beginPath(); ctx.moveTo(12, 0); ctx.lineTo(22, 0); ctx.lineTo(10, h); ctx.lineTo(0, h); ctx.closePath(); ctx.fill();
        if (hasIcon) drawSocialPlatformIcon(ctx, wmIcon, 34, 22, wmColor);
        ctx.fillStyle = textColor; ctx.fillText(wmText, hasIcon ? 54 : 26, 29);
    } else if (wmStyle === 'tactical_bracket') {
        const w = textW + iconPad + 30, h = 46;
        ctx.fillStyle = 'rgba(10, 15, 26, 0.55)'; ctx.fillRect(0, 0, w, h);
        ctx.strokeStyle = wmColor; ctx.lineWidth = 2.5;
        ctx.beginPath();
        ctx.moveTo(0, 12); ctx.lineTo(0, 0); ctx.lineTo(12, 0);
        ctx.moveTo(w - 12, 0); ctx.lineTo(w, 0); ctx.lineTo(w, 12);
        ctx.moveTo(0, h - 12); ctx.lineTo(0, h); ctx.lineTo(12, h);
        ctx.moveTo(w - 12, h); ctx.lineTo(w, h); ctx.lineTo(w, h - 12);
        ctx.stroke();
        if (hasIcon) drawSocialPlatformIcon(ctx, wmIcon, 24, 23, wmColor);
        ctx.fillStyle = textColor; ctx.fillText(wmText, hasIcon ? 45 : 18, 30);
    } else if (wmStyle === 'verified_card') {
        ctx.font = `700 11px ${fontFam}`;
        const subW = ctx.measureText(wmSub).width;
        ctx.font = `900 22px ${fontFam}`;
        const w = Math.max(textW, subW) + iconPad + 36, h = 58;
        ctx.fillStyle = 'rgba(12, 18, 30, 0.85)';
        ctx.beginPath(); ctx.roundRect(0, 0, w, h, 14); ctx.fill();
        ctx.fillStyle = wmColor; ctx.fillRect(0, 10, 5, h - 20);
        if (hasIcon) drawSocialPlatformIcon(ctx, wmIcon, 26, 29, wmColor);
        const tx = hasIcon ? 48 : 18;
        ctx.fillStyle = textColor; ctx.font = `900 22px ${fontFam}`; ctx.fillText(wmText, tx, 28);
        ctx.fillStyle = wmColor; ctx.font = `800 11px ${fontFam}`; ctx.fillText(wmSub || 'FOLLOW FOR MORE', tx, 46);
    } else if (wmStyle === 'vertical_side') {
        ctx.save();
        ctx.rotate(Math.PI / 2);
        const w = textW + iconPad + 28, h = 40;
        ctx.fillStyle = 'rgba(12, 18, 30, 0.72)';
        ctx.beginPath(); ctx.roundRect(0, -h, w, h, 20); ctx.fill();
        if (hasIcon) drawSocialPlatformIcon(ctx, wmIcon, 22, -h/2, wmColor);
        ctx.fillStyle = textColor; ctx.font = `900 20px ${fontFam}`;
        ctx.fillText(wmText, hasIcon ? 42 : 16, -13);
        ctx.restore();
    }

    ctx.restore();
}

// 7. RENDER 20 CUSTOMIZABLE RESULT TEMPLATES
function drawFinisherSummaryCard(ctx, data) {
    const forcePreview = document.getElementById('forcePreviewResult').checked;
    const autoShow     = document.getElementById('showFinisherCard').checked;
    const resDur       = parseFloat(document.getElementById('resDurationSec').value || 3.5);
    const remainingSec = trimEndTotalSec - currentVideoSec;

    if (!forcePreview && (!autoShow || remainingSec > resDur || remainingSec < 0)) return;

    const tpl           = document.getElementById('resTemplateSelect').value;
    const resFont       = document.getElementById('resFontSelect').value;
    const resTitle      = document.getElementById('resTitleInput').value.toUpperCase();
    const resSub        = document.getElementById('resSubInput').value.toUpperCase();
    const resAccent     = document.getElementById('resAccentColor').value;
    const resBgHex      = document.getElementById('resBgColor').value;
    const resTextCol    = document.getElementById('resTextColor').value;
    const resOpacity    = parseFloat(document.getElementById('resOpacitySlider').value || 0.92);
    const resScale      = parseFloat(document.getElementById('resScaleSlider').value || 1.0);
    const showBreakdown = document.getElementById('resShowBreakdown').checked;

    const { clockStr, counts } = data;
    const avgHr = hrValues.length ? Math.round(hrValues.reduce((a,b)=>a+b,0) / hrValues.length) : 142;
    const maxHr = hrValues.length ? Math.max(...hrValues) : 168;
    const wodMainTitle = `${document.getElementById('titleTag').value} • ${document.getElementById('titleMain').value}`.toUpperCase();

    const exSummary = [];
    exercises.forEach((ex, i) => {
        if (ex.name && ex.name.trim() !== '') {
            const val = counts.perEx[i] || (repEvents.length === 0 ? ex.target : 0);
            exSummary.push(`${ex.name.trim().toUpperCase()}: ${val}`);
        }
    });
    const breakdownLine = exSummary.join('   •   ');

    ctx.save();
    ctx.translate(resPos.x, resPos.y);
    ctx.scale(resScale, resScale);

    const w = 560;
    const h = showBreakdown ? 310 : 260;
    const lx = -w / 2, ty = -h / 2;
    const bgRgba = hexToRgba(resBgHex, resOpacity);

    ctx.shadowColor = 'rgba(0,0,0,0.85)';
    ctx.shadowBlur = 18;
    ctx.textAlign = 'center';

    if (tpl === 'classic_pro') {
        ctx.fillStyle = bgRgba; ctx.beginPath(); ctx.roundRect(lx, ty, w, h, 22); ctx.fill();
        ctx.strokeStyle = resAccent; ctx.lineWidth = 3; ctx.stroke();
        ctx.fillStyle = resAccent; ctx.font = `900 20px ${resFont}`; ctx.fillText(`🏁 ${resTitle}`, 0, ty + 42);
    } else if (tpl === 'strava_share') {
        ctx.fillStyle = bgRgba; ctx.beginPath(); ctx.roundRect(lx, ty, w, h, 12); ctx.fill();
        ctx.fillStyle = '#fc4c02'; ctx.fillRect(lx, ty, w, 10);
        ctx.fillStyle = '#fc4c02'; ctx.font = `900 22px ${resFont}`; ctx.fillText(resTitle, 0, ty + 46);
    } else if (tpl === 'nike_volt') {
        ctx.beginPath(); ctx.moveTo(lx + 24, ty); ctx.lineTo(lx + w, ty); ctx.lineTo(lx + w - 24, ty + h); ctx.lineTo(lx, ty + h); ctx.closePath();
        ctx.fillStyle = bgRgba; ctx.fill();
        ctx.fillStyle = '#d4ff00'; ctx.beginPath(); ctx.moveTo(lx + 24, ty); ctx.lineTo(lx + 40, ty); ctx.lineTo(lx + 16, ty + h); ctx.lineTo(lx, ty + h); ctx.closePath(); ctx.fill();
        ctx.fillStyle = '#d4ff00'; ctx.font = `900 22px ${resFont}`; ctx.fillText(`⚡ ${resTitle}`, 0, ty + 44);
    } else if (tpl === 'apple_rings') {
        ctx.fillStyle = bgRgba; ctx.beginPath(); ctx.roundRect(lx, ty, w, h, 32); ctx.fill();
        ctx.strokeStyle = 'rgba(255,255,255,0.18)'; ctx.lineWidth = 2; ctx.stroke();
        ['#fa114f', '#92e82a', '#00d9ff'].forEach((c, idx) => {
            ctx.beginPath(); ctx.arc(0, ty + 34, 18 - idx * 4, 0, Math.PI * 1.65);
            ctx.strokeStyle = c; ctx.lineWidth = 3; ctx.stroke();
        });
        ctx.fillStyle = resAccent; ctx.font = `800 18px ${resFont}`; ctx.fillText(resTitle, 0, ty + 68);
    } else if (tpl === 'crossfit_board') {
        ctx.fillStyle = bgRgba; ctx.fillRect(lx, ty, w, h);
        ctx.strokeStyle = '#ffffff'; ctx.lineWidth = 3; ctx.strokeRect(lx, ty, w, h);
        ctx.fillStyle = resAccent; ctx.fillRect(lx, ty, w, 46);
        ctx.fillStyle = '#090d16'; ctx.font = `900 24px ${resFont}`; ctx.fillText(resTitle, 0, ty + 32);
    } else if (tpl === 'garmin_shield') {
        ctx.fillStyle = bgRgba; ctx.beginPath(); ctx.roundRect(lx, ty, w, h, 18); ctx.fill();
        ctx.strokeStyle = '#38bdf8'; ctx.lineWidth = 3; ctx.stroke();
        ctx.fillStyle = '#38bdf8'; ctx.font = `900 20px ${resFont}`; ctx.fillText(`🛡️ ${resTitle}`, 0, ty + 42);
    } else if (tpl === 'whoop_strain') {
        ctx.fillStyle = bgRgba; ctx.fillRect(lx, ty, w, h);
        ctx.fillStyle = resAccent; ctx.fillRect(lx + 40, ty + 16, w - 80, 6);
        ctx.fillStyle = resTextCol; ctx.font = `900 22px ${resFont}`; ctx.fillText(`DAY STRAIN // ${resTitle}`, 0, ty + 48);
    } else if (tpl === 'cyberpunk_hud') {
        ctx.fillStyle = bgRgba; ctx.fillRect(lx, ty, w, h);
        ctx.strokeStyle = '#00f5d4'; ctx.lineWidth = 4;
        const b = 28;
        ctx.beginPath();
        ctx.moveTo(lx, ty+b); ctx.lineTo(lx, ty); ctx.lineTo(lx+b, ty);
        ctx.moveTo(lx+w-b, ty); ctx.lineTo(lx+w, ty); ctx.lineTo(lx+w, ty+b);
        ctx.moveTo(lx, ty+h-b); ctx.lineTo(lx, ty+h); ctx.lineTo(lx+b, ty+h);
        ctx.moveTo(lx+w-b, ty+h); ctx.lineTo(lx+w, ty+h); ctx.lineTo(lx+w, ty+h-b);
        ctx.stroke();
        ctx.fillStyle = '#00f5d4'; ctx.font = `900 20px ${resFont}`; ctx.fillText(`SYS.RESULT // ${resTitle}`, 0, ty + 42);
    } else if (tpl === 'military_stamp') {
        ctx.fillStyle = bgRgba; ctx.fillRect(lx, ty, w, h);
        ctx.strokeStyle = resAccent; ctx.lineWidth = 6; ctx.strokeRect(lx + 6, ty + 6, w - 12, h - 12);
        ctx.fillStyle = resAccent; ctx.font = `900 24px ${resFont}`; ctx.fillText(`★ ${resTitle} ★`, 0, ty + 44);
    } else if (tpl === 'minimal_floating') {
        ctx.shadowColor = 'rgba(0,0,0,0.95)'; ctx.shadowBlur = 16;
        ctx.fillStyle = resAccent; ctx.font = `900 24px ${resFont}`; ctx.fillText(resTitle, 0, ty + 42);
    } else if (tpl === 'ig_story_glass') {
        ctx.fillStyle = hexToRgba('#1e293b', Math.min(0.85, resOpacity));
        ctx.beginPath(); ctx.roundRect(lx + 20, ty, w - 40, h, 36); ctx.fill();
        ctx.strokeStyle = 'rgba(255,255,255,0.3)'; ctx.lineWidth = 1.5; ctx.stroke();
        ctx.fillStyle = resAccent; ctx.font = `800 20px ${resFont}`; ctx.fillText(resTitle, 0, ty + 44);
    } else if (tpl === 'gold_champion') {
        ctx.fillStyle = bgRgba; ctx.beginPath(); ctx.roundRect(lx, ty, w, h, 14); ctx.fill();
        ctx.strokeStyle = '#fbbf24'; ctx.lineWidth = 4; ctx.stroke();
        ctx.strokeStyle = 'rgba(251,191,36,0.4)'; ctx.lineWidth = 1.5; ctx.strokeRect(lx + 10, ty + 10, w - 20, h - 20);
        ctx.fillStyle = '#fbbf24'; ctx.font = `900 22px ${resFont}`; ctx.fillText(`🏆 ${resTitle} 🏆`, 0, ty + 44);
    } else if (tpl === 'neon_arcade') {
        ctx.save();
        ctx.fillStyle = bgRgba; ctx.beginPath(); ctx.roundRect(lx, ty, w, h, 18); ctx.fill();
        ctx.shadowColor = resAccent; ctx.shadowBlur = 24;
        ctx.strokeStyle = resAccent; ctx.lineWidth = 4; ctx.stroke();
        ctx.restore();
        ctx.fillStyle = resAccent; ctx.font = `900 22px ${resFont}`; ctx.fillText(resTitle, 0, ty + 44);
    } else if (tpl === 'polar_red') {
        ctx.fillStyle = bgRgba; ctx.beginPath(); ctx.roundRect(lx, ty, w, h, 16); ctx.fill();
        ctx.fillStyle = '#d90429'; ctx.beginPath(); ctx.roundRect(lx, ty, w, 46, [16, 16, 0, 0]); ctx.fill();
        ctx.fillStyle = '#ffffff'; ctx.font = `900 22px ${resFont}`; ctx.fillText(`♥ ${resTitle}`, 0, ty + 31);
    } else if (tpl === 'split_diagonal') {
        ctx.fillStyle = bgRgba; ctx.beginPath(); ctx.roundRect(lx, ty, w, h, 18); ctx.fill();
        ctx.save();
        ctx.beginPath(); ctx.moveTo(lx, ty); ctx.lineTo(lx + w * 0.45, ty); ctx.lineTo(lx + w * 0.25, ty + h); ctx.lineTo(lx, ty + h); ctx.closePath();
        ctx.fillStyle = hexToRgba(resAccent, 0.22); ctx.fill();
        ctx.restore();
        ctx.fillStyle = resAccent; ctx.font = `900 22px ${resFont}`; ctx.fillText(resTitle, 0, ty + 44);
    } else if (tpl === 'receipt_ticket') {
        ctx.fillStyle = hexToRgba('#f8fafc', resOpacity);
        ctx.beginPath(); ctx.roundRect(lx + 40, ty, w - 80, h, 6); ctx.fill();
        ctx.fillStyle = '#0f172a'; ctx.font = `900 22px ${resFont}`; ctx.fillText(`*** ${resTitle} ***`, 0, ty + 38);
        ctx.strokeStyle = '#64748b'; ctx.setLineDash([6, 4]);
        ctx.beginPath(); ctx.moveTo(lx + 60, ty + 50); ctx.lineTo(lx + w - 60, ty + 50); ctx.stroke();
        ctx.setLineDash([]);
    } else if (tpl === 'magazine_cover') {
        ctx.fillStyle = bgRgba; ctx.fillRect(lx, ty, w, h);
        ctx.fillStyle = resAccent; ctx.fillRect(lx, ty + h - 12, w, 12);
        ctx.fillStyle = resAccent; ctx.font = `900 26px ${resFont}`; ctx.fillText(resTitle, 0, ty + 44);
    } else if (tpl === 'coros_hex_grid') {
        ctx.fillStyle = bgRgba; ctx.beginPath(); ctx.roundRect(lx, ty, w, h, 20); ctx.fill();
        ctx.strokeStyle = resAccent; ctx.lineWidth = 2.5; ctx.stroke();
        ctx.fillStyle = resAccent; ctx.font = `900 20px ${resFont}`; ctx.fillText(`⬢ ${resTitle} ⬢`, 0, ty + 42);
    } else if (tpl === 'brutalist_poster') {
        ctx.fillStyle = '#000000'; ctx.fillRect(lx + 10, ty + 10, w, h);
        ctx.fillStyle = bgRgba; ctx.fillRect(lx, ty, w, h);
        ctx.strokeStyle = '#ffffff'; ctx.lineWidth = 4; ctx.strokeRect(lx, ty, w, h);
        ctx.fillStyle = resAccent; ctx.font = `900 24px ${resFont}`; ctx.fillText(resTitle, 0, ty + 44);
    } else {
        ctx.fillStyle = bgRgba; ctx.fillRect(lx - 40, ty + 10, w + 80, h - 20);
        ctx.fillStyle = resAccent; ctx.fillRect(lx - 40, ty + 10, w + 80, 4);
        ctx.fillRect(lx - 40, ty + h - 14, w + 80, 4);
        ctx.fillStyle = resAccent; ctx.font = `900 20px ${resFont}`; ctx.fillText(resTitle, 0, ty + 44);
    }

    const isReceipt  = (tpl === 'receipt_ticket');
    const mainTxtCol = isReceipt ? '#0f172a' : resTextCol;
    const subTxtCol  = isReceipt ? '#475569' : '#94a3b8';

    ctx.fillStyle = mainTxtCol;
    ctx.font = `900 38px ${resFont}`;
    ctx.fillText(wodMainTitle, 0, ty + 92);

    const statY = ty + 162;
    ctx.font = `800 13px ${resFont}`;
    ctx.fillStyle = subTxtCol;
    ctx.fillText('TOTAL TIME', -165, statY - 30);
    ctx.fillText('TOTAL REPS', 0, statY - 30);
    ctx.fillText('AVG / MAX HR', 165, statY - 30);

    ctx.font = `900 36px ${resFont}`;
    ctx.fillStyle = mainTxtCol;
    ctx.fillText(clockStr, -165, statY + 10);

    ctx.fillStyle = isReceipt ? '#059669' : '#10b981';
    ctx.fillText(`${counts.totalReps}`, 0, statY + 10);

    ctx.fillStyle = '#ff2a4b';
    ctx.fillText(`${avgHr}/${maxHr}`, 165, statY + 10);

    let subOffsetY = ty + 225;
    if (showBreakdown && breakdownLine) {
        ctx.fillStyle = isReceipt ? 'rgba(15,23,42,0.08)' : 'rgba(255,255,255,0.08)';
        ctx.beginPath(); ctx.roundRect(lx + 28, ty + 192, w - 56, 40, 8); ctx.fill();
        ctx.fillStyle = isReceipt ? '#0f172a' : resAccent;
        ctx.font = `800 16px ${resFont}`;
        ctx.fillText(breakdownLine, 0, ty + 218);
        subOffsetY = ty + 272;
    }

    if (resSub) {
        ctx.fillStyle = subTxtCol;
        ctx.font = `800 14px ${resFont}`;
        ctx.fillText(resSub, 0, subOffsetY);
    }

    ctx.textAlign = 'left';
    ctx.restore();
}

// Render Frame Utama
function renderFrame() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const isGreen     = document.getElementById('greenScreenMode').checked;
    const fontFam     = document.getElementById('fontFamilySelect').value;
    const accentColor = document.getElementById('accentColorPicker').value;
    const heartColor  = document.getElementById('heartColorPicker').value;
    const bgOpacity   = parseFloat(document.getElementById('bgOpacitySlider').value);

    if (isGreen) {
        ctx.fillStyle = '#00FF00';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    } else if (useVideoBg && hiddenVideo.readyState >= 2) {
        drawMediaWithLutAndPanZoom(ctx, hiddenVideo, hiddenVideo.videoWidth, hiddenVideo.videoHeight, canvas.width, canvas.height);
    } else if (bgImage) {
        drawMediaWithLutAndPanZoom(ctx, bgImage, bgImage.width, bgImage.height, canvas.width, canvas.height);
    } else {
        const grad = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
        grad.addColorStop(0, '#262936'); grad.addColorStop(1, '#11131a');
        ctx.fillStyle = grad; ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    document.getElementById('lblVideoTime').innerText = formatMMSS(currentVideoSec);
    const workoutElapsedSec  = Math.max(0, currentVideoSec - trimStartTotalSec);
    const workoutMaxDuration = Math.max(10, trimEndTotalSec - trimStartTotalSec);
    const clampedElapsed     = Math.min(workoutElapsedSec, workoutMaxDuration);

    const hrIndex    = Math.floor(clampedElapsed / hrIntervalSec);
    const currentBpm = hrValues[hrIndex % hrValues.length] || 145;
    const zInfo      = getZoneInfo(currentBpm);
    const counts     = getCountsAtTime(clampedElapsed);

    if (isPlayingSim && counts.totalReps > lastPlayedRepCount) {
        playSfxBeep(880, 0.10);
    }
    lastPlayedRepCount = counts.totalReps;

    if (repPopScale > 1.0) repPopScale = Math.max(1.0, repPopScale - 0.04);
    const beatScale = 1 + Math.sin(pulsePhase) * 0.08;

    const widgetData = {
        bpm: currentBpm,
        zInfo,
        clampedElapsed,
        workoutMaxDuration,
        clockStr: formatMMSS(clampedElapsed),
        progressRatio: Math.min(1, clampedElapsed / workoutMaxDuration),
        counts,
        fontFam,
        accentColor,
        heartColor,
        bgOpacity,
        beatScale
    };

    drawTopLeftTitle(ctx);
    drawCenterWorkoutList(ctx, counts);
    drawStandaloneHrWidget(ctx, widgetData);
    drawStandaloneTimerWidget(ctx, widgetData);
    drawStandaloneRoundWidget(ctx, widgetData);
    drawWatermarkWidget(ctx, fontFam);
    drawFinisherSummaryCard(ctx, widgetData);

    const activeName = exercises[activeExerciseIdx] ? exercises[activeExerciseIdx].name : '-';
    document.getElementById('lblStatusSummary').innerText = `AKTIF [${activeExerciseIdx+1}]: ${activeName} | TOTAL: ${counts.totalReps} REPS | ♥ ${currentBpm} BPM`;
}

function onSliderChange(val) {
    currentVideoSec = parseFloat(val);
    if (useVideoBg && Math.abs(hiddenVideo.currentTime - currentVideoSec) > 0.2) {
        hiddenVideo.currentTime = currentVideoSec;
    }
    renderExerciseInputs();
    renderFrame();
}

function animationLoop(timestamp) {
    if (!isPlayingSim) return;
    if (!lastTick) lastTick = timestamp;
    const realDeltaSec = (timestamp - lastTick) / 1000;
    const scaledDelta  = realDeltaSec * playbackSpeed;
    lastTick = timestamp;

    const workoutElapsedSec = Math.max(0, currentVideoSec - trimStartTotalSec);
    const hrIndex = Math.floor(workoutElapsedSec / hrIntervalSec);
    const bpm = hrValues[hrIndex % hrValues.length] || 142;
    pulsePhase += realDeltaSec * (bpm / 60) * Math.PI * 2;
    ecgOffset  += realDeltaSec * 90;

    if (useVideoBg && !hiddenVideo.paused) {
        currentVideoSec = hiddenVideo.currentTime;
    } else {
        currentVideoSec += scaledDelta;
    }

    if (currentVideoSec >= trimEndTotalSec || (useVideoBg && hiddenVideo.ended)) {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            toggleRecording(); return;
        }
        currentVideoSec = trimStartTotalSec;
        if (useVideoBg) hiddenVideo.currentTime = trimStartTotalSec;
    }

    document.getElementById('timelineSlider').value = currentVideoSec;
    renderExerciseInputs();
    renderFrame();
    animFrameId = requestAnimationFrame(animationLoop);
}

function togglePlaySim() {
    initAudioMixer();
    const btn = document.getElementById('btnPlaySim');
    if (isPlayingSim) {
        isPlayingSim = false;
        cancelAnimationFrame(animFrameId);
        if (useVideoBg) hiddenVideo.pause();
        if (hasBgmLoaded) bgmAudio.pause();
        btn.innerText = '▶ Putar Video';
    } else {
        if (currentVideoSec < trimStartTotalSec || currentVideoSec >= trimEndTotalSec) {
            currentVideoSec = trimStartTotalSec;
        }
        isPlayingSim = true;
        lastTick = 0;
        if (useVideoBg) {
            hiddenVideo.playbackRate = Math.min(16, playbackSpeed);
            hiddenVideo.currentTime = currentVideoSec;
            hiddenVideo.play();
        }
        if (hasBgmLoaded) {
            const bgmStart = parseFloat(document.getElementById('bgmOffsetSec').value || 0);
            if (bgmAudio.paused) {
                bgmAudio.currentTime = bgmStart;
                bgmAudio.play().catch(()=>{});
            }
        }
        btn.innerText = '⏸ Pause';
        animFrameId = requestAnimationFrame(animationLoop);
    }
}

// Drag & Drop 7 Widget di Kanvas (+ Auto Buka Modal yang Sesuai Saat Diklik!)
canvas.addEventListener('mousedown', function(e) {
    const rect = canvas.getBoundingClientRect();
    const mx = (e.clientX - rect.left) * (canvas.width / rect.width);
    const my = (e.clientY - rect.top) * (canvas.height / rect.height);

    recordHistory('Geser Posisi Widget');

    const isResVisible = document.getElementById('forcePreviewResult').checked ||
        (document.getElementById('showFinisherCard').checked && (trimEndTotalSec - currentVideoSec) <= parseFloat(document.getElementById('resDurationSec').value || 3.5));

    if (isResVisible && Math.hypot(mx - resPos.x, my - resPos.y) < 220) {
        dragTarget = 'result';
        dragOffset = { x: mx - resPos.x, y: my - resPos.y };
        return;
    }

    const candidates = [];
    if (document.getElementById('showTopTitle').checked) {
        candidates.push({ name: 'title', pos: titlePos, cx: titlePos.x + 110, cy: titlePos.y + 55 });
    }
    if (document.getElementById('showWorkoutList').checked) {
        candidates.push({ name: 'list', pos: listPos, cx: listPos.x, cy: listPos.y + 90 });
    }
    if (document.getElementById('showHrWidget').checked) {
        candidates.push({ name: 'hr', pos: hrPos, cx: hrPos.x + 90, cy: hrPos.y + 60 });
    }
    if (document.getElementById('showTimerWidget').checked) {
        candidates.push({ name: 'timer', pos: timerPos, cx: timerPos.x + 90, cy: timerPos.y + 55 });
    }
    if (document.getElementById('showRoundWidget').checked) {
        candidates.push({ name: 'round', pos: roundPos, cx: roundPos.x + 90, cy: roundPos.y + 35 });
    }
    if (document.getElementById('showWatermarkWidget').checked && document.getElementById('watermarkText').value.trim() !== '') {
        candidates.push({ name: 'watermark', pos: watermarkPos, cx: watermarkPos.x + 90, cy: watermarkPos.y + 24 });
    }

    let best = null, minDist = Infinity;
    for (const c of candidates) {
        const d = Math.hypot(mx - c.cx, my - c.cy);
        if (d < minDist) { minDist = d; best = c; }
    }

    if (best) {
        dragTarget = best.name;
        dragOffset = { x: mx - best.pos.x, y: my - best.pos.y };
    }
});

window.addEventListener('mousemove', function(e) {
    if (!dragTarget) return;
    const rect = canvas.getBoundingClientRect();
    const mx = (e.clientX - rect.left) * (canvas.width / rect.width);
    const my = (e.clientY - rect.top) * (canvas.height / rect.height);

    const newPos = { x: mx - dragOffset.x, y: my - dragOffset.y };
    if (dragTarget === 'result') resPos = newPos;
    else if (dragTarget === 'title') titlePos = newPos;
    else if (dragTarget === 'list') listPos = newPos;
    else if (dragTarget === 'hr') hrPos = newPos;
    else if (dragTarget === 'timer') timerPos = newPos;
    else if (dragTarget === 'round') roundPos = newPos;
    else if (dragTarget === 'watermark') watermarkPos = newPos;

    renderFrame();
});

window.addEventListener('mouseup', () => {
    if (dragTarget) { dragTarget = null; saveSettingState(); }
});

document.getElementById('bgVideoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    initAudioMixer();
    hiddenVideo.src = URL.createObjectURL(file);
    hiddenVideo.onloadedmetadata = function() {
        useVideoBg = true;
        if (hiddenVideo.duration && hiddenVideo.duration > 1) {
            const dur = Math.floor(hiddenVideo.duration);
            document.getElementById('trimEndMin').value = Math.floor(dur / 60);
            document.getElementById('trimEndSec').value = dur % 60;
            updateTiming();
        }
        currentVideoSec = trimStartTotalSec;
        hiddenVideo.currentTime = trimStartTotalSec;
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

// Auto-Save ke LocalStorage
function saveSettingState() {
    const state = {
        wodHeader: document.getElementById('wodHeader').value,
        listFormat: document.getElementById('listDisplayFormat').value,
        listBoxStyle: document.getElementById('listBoxStyleSelect').value,
        listFont: document.getElementById('listFontSelect').value,
        listAlign: document.getElementById('listAlignSelect').value,
        listHeaderColor: document.getElementById('listHeaderColor').value,
        listActiveColor: document.getElementById('listActiveColor').value,
        listTextColor: document.getElementById('listTextColor').value,
        listBgColor: document.getElementById('listBgColor').value,
        listBgOpacity: document.getElementById('listBgOpacity').value,
        listScale: document.getElementById('listScaleSlider').value,
        resTemplate: document.getElementById('resTemplateSelect').value,
        resFont: document.getElementById('resFontSelect').value,
        resTitle: document.getElementById('resTitleInput').value,
        resSub: document.getElementById('resSubInput').value,
        resDuration: document.getElementById('resDurationSec').value,
        resAccentColor: document.getElementById('resAccentColor').value,
        resBgColor: document.getElementById('resBgColor').value,
        resTextColor: document.getElementById('resTextColor').value,
        resShowBreakdown: document.getElementById('resShowBreakdown').checked,
        resOpacity: document.getElementById('resOpacitySlider').value,
        resScale: document.getElementById('resScaleSlider').value,
        titleStyle: document.getElementById('titleStyleSelect').value,
        titleFont: document.getElementById('titleFontSelect').value,
        hudStyle: document.getElementById('hudStyleSelect').value,
        timerStyle: document.getElementById('timerStyleSelect').value,
        timerCountMode: document.getElementById('timerCountMode').value,
        fontFamily: document.getElementById('fontFamilySelect').value,
        titleTag: document.getElementById('titleTag').value,
        titleMain: document.getElementById('titleMain').value,
        showWatermark: document.getElementById('showWatermarkWidget').checked,
        wmStyle: document.getElementById('wmStyleSelect').value,
        wmIcon: document.getElementById('wmIconSelect').value,
        watermarkText: document.getElementById('watermarkText').value,
        watermarkSub: document.getElementById('watermarkSub').value,
        wmColor: document.getElementById('wmColorPicker').value,
        wmTextColor: document.getElementById('wmTextColorPicker').value,
        wmScale: document.getElementById('wmScaleSlider').value,
        wmOpacity: document.getElementById('wmOpacitySlider').value,
        titleColor: document.getElementById('titleColorPicker').value,
        showHr: document.getElementById('showHrWidget').checked,
        showTimer: document.getElementById('showTimerWidget').checked,
        showRound: document.getElementById('showRoundWidget').checked,
        showFinisher: document.getElementById('showFinisherCard').checked,
        exercises, repEvents, roundTimestamps, nextEventId,
        titlePos, listPos, hrPos, timerPos, roundPos, watermarkPos, resPos
    };
    localStorage.setItem('workout_v110_autosave', JSON.stringify(state));
}

function loadSettingState() {
    const raw = localStorage.getItem('workout_v110_autosave') || localStorage.getItem('workout_v100_autosave');
    if (!raw) return;
    try {
        const s = JSON.parse(raw);
        if (s.wodHeader !== undefined) document.getElementById('wodHeader').value = s.wodHeader;
        if (s.listFormat) document.getElementById('listDisplayFormat').value = s.listFormat;
        if (s.listBoxStyle) document.getElementById('listBoxStyleSelect').value = s.listBoxStyle;
        if (s.listFont) document.getElementById('listFontSelect').value = s.listFont;
        if (s.listAlign) document.getElementById('listAlignSelect').value = s.listAlign;
        if (s.listHeaderColor) document.getElementById('listHeaderColor').value = s.listHeaderColor;
        if (s.listActiveColor) document.getElementById('listActiveColor').value = s.listActiveColor;
        if (s.listTextColor) document.getElementById('listTextColor').value = s.listTextColor;
        if (s.listBgColor) document.getElementById('listBgColor').value = s.listBgColor;
        if (s.listBgOpacity !== undefined) {
            document.getElementById('listBgOpacity').value = s.listBgOpacity;
            document.getElementById('lblListOpacity').innerText = Math.round(s.listBgOpacity * 100) + '%';
        }
        if (s.listScale !== undefined) {
            document.getElementById('listScaleSlider').value = s.listScale;
            document.getElementById('lblListScale').innerText = Math.round(s.listScale * 100) + '%';
        }
        if (s.resTemplate) document.getElementById('resTemplateSelect').value = s.resTemplate;
        if (s.resFont) document.getElementById('resFontSelect').value = s.resFont;
        if (s.resTitle !== undefined) document.getElementById('resTitleInput').value = s.resTitle;
        if (s.resSub !== undefined) document.getElementById('resSubInput').value = s.resSub;
        if (s.resDuration) document.getElementById('resDurationSec').value = s.resDuration;
        if (s.resAccentColor) document.getElementById('resAccentColor').value = s.resAccentColor;
        if (s.resBgColor) document.getElementById('resBgColor').value = s.resBgColor;
        if (s.resTextColor) document.getElementById('resTextColor').value = s.resTextColor;
        if (s.resShowBreakdown !== undefined) document.getElementById('resShowBreakdown').checked = s.resShowBreakdown;
        if (s.resOpacity) document.getElementById('resOpacitySlider').value = s.resOpacity;
        if (s.resScale) document.getElementById('resScaleSlider').value = s.resScale;
        if (s.titleStyle) document.getElementById('titleStyleSelect').value = s.titleStyle;
        if (s.titleFont) document.getElementById('titleFontSelect').value = s.titleFont;
        if (s.hudStyle) document.getElementById('hudStyleSelect').value = s.hudStyle;
        if (s.timerStyle) document.getElementById('timerStyleSelect').value = s.timerStyle;
        if (s.timerCountMode) document.getElementById('timerCountMode').value = s.timerCountMode;
        if (s.fontFamily) document.getElementById('fontFamilySelect').value = s.fontFamily;
        if (s.titleTag) document.getElementById('titleTag').value = s.titleTag;
        if (s.titleMain) document.getElementById('titleMain').value = s.titleMain;
        if (s.showWatermark !== undefined) document.getElementById('showWatermarkWidget').checked = s.showWatermark;
        if (s.wmStyle) document.getElementById('wmStyleSelect').value = s.wmStyle;
        if (s.wmIcon) document.getElementById('wmIconSelect').value = s.wmIcon;
        if (s.watermarkText !== undefined) document.getElementById('watermarkText').value = s.watermarkText;
        if (s.watermarkSub !== undefined) document.getElementById('watermarkSub').value = s.watermarkSub;
        if (s.wmColor) document.getElementById('wmColorPicker').value = s.wmColor;
        if (s.wmTextColor) document.getElementById('wmTextColorPicker').value = s.wmTextColor;
        if (s.wmScale) document.getElementById('wmScaleSlider').value = s.wmScale;
        if (s.wmOpacity) document.getElementById('wmOpacitySlider').value = s.wmOpacity;
        if (s.titleColor) document.getElementById('titleColorPicker').value = s.titleColor;
        if (s.showHr !== undefined) document.getElementById('showHrWidget').checked = s.showHr;
        if (s.showTimer !== undefined) document.getElementById('showTimerWidget').checked = s.showTimer;
        if (s.showRound !== undefined) document.getElementById('showRoundWidget').checked = s.showRound;
        if (s.showFinisher !== undefined) document.getElementById('showFinisherCard').checked = s.showFinisher;
        if (s.exercises) exercises = s.exercises;
        if (s.repEvents) repEvents = s.repEvents;
        if (s.roundTimestamps) roundTimestamps = s.roundTimestamps;
        if (s.nextEventId) nextEventId = s.nextEventId;
        if (s.titlePos) titlePos = s.titlePos;
        if (s.listPos) listPos = s.listPos;
        if (s.hrPos) hrPos = s.hrPos;
        if (s.timerPos) timerPos = s.timerPos;
        if (s.roundPos) roundPos = s.roundPos;
        if (s.watermarkPos) watermarkPos = s.watermarkPos;
        if (s.resPos) resPos = s.resPos;
    } catch (e) {}
}

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
            const steps = Math.ceil((trimEndTotalSec - trimStartTotalSec) / hrIntervalSec);
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
                    extracted.push(extracted.length ? extracted[extracted.length - 1] : 140);
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
    a.download = `workout_v11_${formatMMSS(currentVideoSec).replace(':','-')}.png`;
    a.href = canvas.toDataURL('image/png');
    a.click();
}

// Export Video (.MP4)
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
        btn.innerText = '⏺ Export (.MP4)';
        btn.className = 'bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold px-4 py-1.5 rounded-lg transition shadow-lg';
        if (isPlayingSim) togglePlaySim();
        return;
    }

    document.getElementById('forcePreviewResult').checked = false;
    initAudioMixer();
    const chosenSpeed = parseFloat(document.getElementById('exportSpeedSelect').value || 1);
    setPlaybackSpeed(chosenSpeed);

    if (chosenSpeed > 1 && videoGainNode) {
        videoGainNode.gain.setValueAtTime(0, audioCtx.currentTime);
    } else {
        updateAudioVolumes();
    }

    currentVideoSec = trimStartTotalSec;
    if (useVideoBg) hiddenVideo.currentTime = trimStartTotalSec;
    if (hasBgmLoaded) {
        bgmAudio.currentTime = parseFloat(document.getElementById('bgmOffsetSec').value || 0);
    }

    const stream = canvas.captureStream(30);
    if (audioDest) {
        const mixedAudioTracks = audioDest.stream.getAudioTracks();
        if (mixedAudioTracks.length > 0) stream.addTrack(mixedAudioTracks[0]);
    }

    const mime = getSupportedMimeType();
    mediaRecorder = new MediaRecorder(stream, mime ? { mimeType: mime, videoBitsPerSecond: 6000000 } : { videoBitsPerSecond: 6000000 });
    recordedChunks = [];
    mediaRecorder.ondataavailable = e => { if (e.data && e.data.size > 0) recordedChunks.push(e.data); };
    mediaRecorder.onstop = () => {
        updateAudioVolumes();
        const blob = new Blob(recordedChunks, { type: mime || 'video/mp4' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `workout_v11_${chosenSpeed}x.mp4`;
        a.click();
    };

    mediaRecorder.start(1000);
    if (!isPlayingSim) togglePlaySim();
    btn.innerText = `⏹ Stop (${chosenSpeed}x .MP4)`;
    btn.className = 'bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold px-4 py-1.5 rounded-lg transition shadow-lg animate-pulse';
}

// Inisialisasi
loadSettingState();
renderExerciseInputs();
renderEventLog();
updateTiming();
generateSampleHr();
renderFrame();
</script>
</body>
</html>
<div id="tab_video" class="modal-pane hidden space-y-4">
    <!-- Upload Media Section -->
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
                    <span class="text-slate-400">:</span>
                    <input type="number" id="trimStartSec" value="0" min="0" max="59" onchange="updateTiming()" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-1 text-xs text-white">
                </div>
            </div>
            <div>
                <label class="block text-[10px] text-slate-300 mb-0.5">Trim Akhir (MM:SS):</label>
                <div class="flex items-center gap-1">
                    <input type="number" id="trimEndMin" value="20" min="0" onchange="updateTiming()" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-1 text-xs text-white">
                    <span class="text-slate-400">:</span>
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

    <!-- Camera Pan & Zoom + LUT -->
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

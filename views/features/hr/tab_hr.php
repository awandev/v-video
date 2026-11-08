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

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

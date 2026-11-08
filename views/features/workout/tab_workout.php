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

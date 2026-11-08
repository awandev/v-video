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

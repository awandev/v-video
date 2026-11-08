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

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

<main class="flex-1 flex flex-col justify-between p-3 md:p-5 bg-slate-950/60 overflow-y-auto">
    
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

    <div class="flex-1 flex justify-center items-center my-2 overflow-hidden">
        <canvas id="hudCanvas" width="720" height="1280" class="cursor-move"></canvas>
    </div>

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

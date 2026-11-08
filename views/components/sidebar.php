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

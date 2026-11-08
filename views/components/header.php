<header class="h-14 bg-slate-950 border-b border-slate-800/80 px-4 flex items-center justify-between shrink-0 z-30">
    <div class="flex items-center gap-3">
        <span class="bg-gradient-to-r from-sky-400 to-emerald-400 text-slate-950 px-2.5 py-0.5 rounded text-xs font-black tracking-wider">V11.1 STUDIO</span>
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

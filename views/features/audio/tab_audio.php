<div id="tab_audio" class="modal-pane hidden space-y-4">
    <div class="bg-slate-900/90 p-3 rounded-lg border border-amber-500/40 space-y-3">
        <div class="text-xs font-extrabold text-amber-300 uppercase">🎵 Upload Backsound Musik (.MP3 / .WAV)</div>
        <p class="text-[10px] text-slate-400">Musik otomatis tetap berputar normal (1x) meskipun video dipercepat menjadi Timelapse!</p>
        <input type="file" id="bgmFileInput" accept="audio/*" class="w-full text-xs text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-amber-600 file:text-white">
        <div class="flex items-center justify-between pt-1">
            <span class="text-xs text-slate-300">Mulai Lagu dari Detik Ke- (Beat Drop):</span>
            <input type="number" id="bgmOffsetSec" value="0" min="0" class="w-20 bg-slate-950 border border-slate-700 rounded px-2 py-1 text-xs text-white font-bold text-center">
        </div>
    </div>

    <div class="bg-slate-900/90 p-3 rounded-lg border border-slate-800 space-y-3">
        <div class="text-xs font-extrabold text-sky-300 uppercase">🎚️ Kontrol Volume Multi-Track</div>
        <div>
            <div class="flex justify-between text-xs mb-1"><span class="text-slate-300">🔊 Volume Suara Video Asli</span><b id="lblVidVol" class="text-sky-400">80%</b></div>
            <input type="range" id="videoVolSlider" min="0" max="1.5" step="0.05" value="0.8" oninput="updateAudioVolumes()" class="w-full accent-sky-500">
        </div>
        <div>
            <div class="flex justify-between text-xs mb-1"><span class="text-slate-300">🎵 Volume Musik Backsound</span><b id="lblBgmVol" class="text-amber-400">60%</b></div>
            <input type="range" id="bgmVolSlider" min="0" max="1.5" step="0.05" value="0.6" oninput="updateAudioVolumes()" class="w-full accent-amber-500">
        </div>
        <div>
            <div class="flex justify-between text-xs mb-1"><span class="text-slate-300">🔔 Volume Efek Bunyi Repetisi (SFX)</span><b id="lblSfxVol" class="text-emerald-400">50%</b></div>
            <input type="range" id="sfxVolSlider" min="0" max="1.5" step="0.05" value="0.5" oninput="updateAudioVolumes()" class="w-full accent-emerald-500">
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-slate-800">
            <label class="flex items-center gap-1.5 text-xs text-emerald-300 font-semibold cursor-pointer">
                <input type="checkbox" id="enableRepBeep" checked class="rounded bg-slate-950">
                <span>Bunyi "Ding" tiap +1 Rep & "Bell" Ronde</span>
            </label>
            <button type="button" onclick="playSfxBeep(880, 0.12)" class="bg-slate-800 hover:bg-slate-700 text-xs text-white px-2.5 py-1 rounded">🔊 Tes Bunyi</button>
        </div>
    </div>
</div>

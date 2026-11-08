<div id="sideModalContainer" class="side-modal w-80 sm:w-96 md:w-[420px] h-full flex flex-col shrink-0 z-10 overflow-hidden">
    
    <div class="h-12 px-4 border-b border-slate-800 flex items-center justify-between shrink-0 bg-slate-900/70">
        <span id="sideModalTitle" class="text-xs font-black uppercase tracking-wider text-sky-400 flex items-center gap-2">
            💪 Pengaturan Workout & Spasi
        </span>
        <button onclick="closeSideModal()" class="text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold" title="Sembunyikan Panel Samping">
            ✕
        </button>
    </div>

    <div class="flex-1 overflow-y-auto p-4 space-y-4">
        <?php include 'views/features/workout/tab_workout.php'; ?>
        <?php include 'views/features/hr/tab_hr.php'; ?>
        <?php include 'views/features/timer/tab_timer.php'; ?>
        <?php include 'views/features/title/tab_title.php'; ?>
        <?php include 'views/features/watermark/tab_watermark.php'; ?>
        <?php include 'views/features/result/tab_result.php'; ?>
        <?php include 'views/features/audio/tab_audio.php'; ?>
        <?php include 'views/features/video/tab_video.php'; ?>
    </div>
</div>

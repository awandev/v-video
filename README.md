# Daily Workout Studio v11.1 - Modular Edition

Browser-based video editing tool untuk Instagram Reels & TikTok workout content.

## Struktur Folder

```
v-workout/
├── index.php                          # Main entry point
├── css/
│   └── style.css                      # Custom styling (Tailwind + custom)
├── js/
│   ├── main.js                        # Core canvas engine & state
│   ├── ui.js                          # Modal/sidebar controls
│   ├── audio.js                       # File upload handlers
│   ├── handlers.js                    # Event listeners & recording
│   └── rendering.js                   # Future rendering functions
└── views/
    ├── components/
    │   ├── header.php                 # Top navigation bar
    │   ├── sidebar.php                # Left sidebar with icons
    │   ├── modal_container.php        # Side modal wrapper
    │   └── canvas_area.php            # Canvas preview & timeline
    └── features/
        ├── workout/tab_workout.php    # Rep logging & exercise list
        ├── hr/tab_hr.php              # Heart rate data & styles
        ├── timer/tab_timer.php        # Timer & duration controls
        ├── title/tab_title.php        # Top title/watermark
        ├── watermark/tab_watermark.php # Social media badges
        ├── result/tab_result.php      # End card templates
        ├── audio/tab_audio.php        # BGM & audio mixer
        └── video/tab_video.php        # Video upload, LUT, trim
```

## Fitur Utama

### 1. **Workout & Rep Logging** 💪
- Daftar 4 gerakan custom dengan target repetisi
- Log ketukan dengan timestamp
- Anti-double-tap (0.25s - 0.5s debounce)
- 10 style kotak workout (glassmorphism, banner, pills, dll)
- 12 pilihan font custom
- Live counter per gerakan

### 2. **Heart Rate Monitor** ❤️
- 17 style HR display (DJI, Apple Rings, Garmin, Nike, dll)
- Input manual, CSV, TCX, screenshot extraction
- 5 heart rate zones dengan warna
- Live pulse animation
- Zona HR otomatis berdasarkan usia

### 3. **Timer & Duration** ⏱️
- 24 style timer (text, analog, ring, bar, dll)
- Mode: count-up, count-down, dengan total durasi
- Link style dengan Heart Rate untuk konsistensi
- Round widget terpisah (opsional)

### 4. **Title & Branding** 🏷️
- 8 style judul (red bar, broadcast box, neon, dll)
- Custom text dengan 12 font options
- Color customization (bar + main title)

### 5. **Watermark Social Media** 📱
- 10 style watermark (pill, gradient, glitch, dll)
- Ikon platform (IG, TikTok, YouTube, verified)
- Scale & opacity controls
- 2-line text support

### 6. **Result / End Card** 🏆
- 20 template hasil (Pro Card, Strava, Nike, Apple, dll)
- Auto-display sebelum video selesai
- Breakdown stats (opsional)
- Durasi tampilan adjustable

### 7. **Audio Mixer** 🎵
- BGM upload + volume control
- Video original sound mixer
- SFX beep untuk rep counter & round bell
- Multi-track audio control

### 8. **Video & Color Grading** 🎬
- Video/image upload (MP4, MOV, JPG, PNG)
- Green screen mode support
- Video trim (MM:SS start/end)
- Timelapse/speed export (1x - 16x)
- 9 preset LUT (teal/orange, moody, neon, dll)
- Custom .CUBE file support
- Camera zoom & pan controls

## Penggunaan

1. **Akses aplikasi**: `http://localhost/v-workout/`
2. **Upload video**: Tab Video → upload file MP4/MOV
3. **Input HR data**: Tab Heart Rate → manual input atau file upload
4. **Setup workout**: Tab Workout → daftar gerakan, atur reps
5. **Customize UI**: Setiap tab punya style, font, warna options
6. **Preview**: Klik Play, geser timeline, atau klik elemen di canvas
7. **Export**: Tombol PNG (snapshot) atau MP4 (video recording)

## Teknologi

- **Frontend**: Vanilla JS (ES6+), Tailwind CSS, HTML5 Canvas
- **Backend**: PHP (minimal - file upload handler saja)
- **Canvas Rendering**: CanvasRenderingContext2D
- **Audio**: Web Audio API
- **Video Recording**: MediaRecorder API
- **Storage**: LocalStorage (auto-save settings)

## Fitur Interaktif

- ✅ Drag-drop elemen di canvas
- ✅ Keyboard shortcuts: Space (rep), R (round), Ctrl+Z (undo)
- ✅ Multi-aspect ratio (9:16, 16:9, 1:1)
- ✅ Real-time preview
- ✅ Anti-double-tap protection
- ✅ Auto-save ke LocalStorage
- ✅ Export PNG snapshot
- ✅ Export MP4 video (native H.264 support di browser)

## Catatan Pengembangan

- **Undo/Redo**: Infrastructure siap, tinggal lengkapi logic
- **Advanced Rendering**: Placeholder di `js/rendering.js`
- **Custom LUT**: Support .CUBE format (CapCut/Premiere compatible)
- **Performance**: Canvas optimized untuk real-time (30fps)

## Browser Requirements

- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ⚠️ Limited MediaRecorder (WebM fallback)
- Mobile: ⚠️ Canvas OK, MediaRecorder limited

---

**Version**: 11.1 Modular Edition  
**Last Updated**: 2026-10-28

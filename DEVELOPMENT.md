# Development Guide - Workout Studio v11.1

## Quick Start

```bash
# Struktur sudah siap, tinggal mulai develop
cd C:\laragon\www\v-workout

# Test syntax PHP
php -l index.php
php -l views/components/*.php
php -l views/features/*/*.php

# Akses via browser
http://localhost/v-workout/
```

## Adding New Features

### Tambah Tab Baru (Contoh: Efek Visual)

1. **Buat folder & view**:
   ```
   views/features/effects/
   └── tab_effects.php
   ```

2. **Edit `views/components/modal_container.php`**:
   ```php
   <?php include 'views/features/effects/tab_effects.php'; ?>
   ```

3. **Edit `views/components/sidebar.php`** - tambah button:
   ```html
   <button onclick="openSideModal('effects')" id="nav_effects" class="sidebar-btn w-full...">
       <span class="text-xl">✨</span>
       <span>Effect</span>
   </button>
   ```

4. **Edit `js/ui.js`** - tambah modal title:
   ```javascript
   const modalTitles = {
       ...
       effects: '✨ Pengaturan Efek Visual'
   };
   ```

5. **Tab HTML structure** (minimal):
   ```html
   <div id="tab_effects" class="modal-pane hidden space-y-4">
       <!-- Isi sini -->
   </div>
   ```

### Extend Canvas Rendering

Tambah fungsi ke `js/rendering.js`:

```javascript
function drawCustomEffect(ctx, x, y, scale) {
    // Custom drawing code
    ctx.save();
    ctx.translate(x, y);
    ctx.scale(scale, scale);
    // ... gambar
    ctx.restore();
}
```

Panggil dari `renderFrame()` di `js/main.js`:

```javascript
function renderFrame() {
    // ... existing code
    drawCustomEffect(ctx, 100, 100, 1.0);
}
```

## File Organization Rules

### `views/components/`
- **Purpose**: Main layout components
- **Naming**: `header.php`, `sidebar.php`, `modal_container.php`, `canvas_area.php`
- **Size**: ~50-200 lines (pure HTML/layout)
- **Logic**: NONE - hanya HTML

### `views/features/*/`
- **Purpose**: Feature-specific UI tabs
- **Naming**: `tab_[feature].php` (exact match dengan ID)
- **Size**: ~100-400 lines (form controls, inputs)
- **Logic**: NONE - hanya HTML + inline `oninput=` handlers

### `js/`
- **main.js**: Canvas, state, rendering engine (~400 lines)
- **ui.js**: Modal/tab switching, localStorage (~150 lines)
- **audio.js**: File handlers, audio setup (~50 lines)
- **handlers.js**: Events, keyboard, recording (~200 lines)
- **rendering.js**: Future advanced rendering (placeholder)

## Coding Conventions

### HTML in PHP
```php
<!-- Contoh structure -->
<div class="bg-slate-900/90 p-3 rounded-lg border border-slate-800 space-y-2">
    <label class="block text-xs text-slate-300 font-bold mb-1">Label:</label>
    <input type="text" id="uniqueId" oninput="saveSettingState(); renderFrame();" class="...">
</div>
```

**Rules**:
- ID selalu unique & descriptive: `id="wodHeader"`, `id="hudScale"`
- Event handler selalu include `saveSettingState()` + `renderFrame()`
- Class: Tailwind utility classes, custom CSS di `css/style.css`
- Accessibility: Always use `<label>`

### JavaScript
```javascript
// 1. State variables di top-level (main.js)
let hudPos = { x: 56, y: 440 };

// 2. Handler functions (beri prefix)
function updateTiming() { ... }
function onSliderChange(val) { ... }

// 3. Rendering functions
function renderFrame() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    // ... draw
}

// 4. Event listeners (dalam DOMContentLoaded di handlers.js)
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('id').addEventListener('change', function() { ... });
});
```

**Rules**:
- Tidak ada inline script di HTML (kecuali `oninput=` yang trivial)
- DOM queries simpan di top, jangan repeat
- Avoid jQuery - vanilla DOM API saja
- Cache DOM element: `const canvas = document.getElementById('hudCanvas');`

## State Management Pattern

Current approach: **Mixed State + LocalStorage**

```javascript
// 1. Global state variables
let hudPos = { x: 56, y: 440 };
let hrValues = [];
let currentVideoSec = 0;

// 2. Save to localStorage
function saveSettingState() {
    const state = {
        hudPos, hrValues, currentVideoSec, // ... semua state
    };
    localStorage.setItem('workoutAppState', JSON.stringify(state));
}

// 3. Restore dari localStorage
function loadSettingState() {
    const saved = localStorage.getItem('workoutAppState');
    if (saved) {
        const state = JSON.parse(saved);
        Object.assign(this, state);
    }
}

// 4. Call pada load
window.addEventListener('load', () => {
    loadSettingState();
    updateTiming();
    renderFrame();
});
```

## Common Tasks

### Update Canvas Size
```javascript
canvas.width = 720;   // Reels
canvas.height = 1280; // 9:16
renderFrame();        // Re-render
```

### Add Color Picker to Tab
```html
<div class="flex items-center gap-2 bg-slate-950 p-2 rounded border border-slate-800">
    <input type="color" id="newColor" value="#ffffff" oninput="saveSettingState(); renderFrame();">
    <span class="text-xs text-slate-300">Label</span>
</div>
```

JavaScript:
```javascript
// Di renderFrame()
const color = document.getElementById('newColor').value;
ctx.fillStyle = color;
ctx.fillRect(0, 0, 100, 100);
```

### Add Slider Control
```html
<div>
    <div class="flex justify-between text-[10px] text-slate-400">
        <span>Scale:</span>
        <b id="lblScale" class="text-sky-300">100%</b>
    </div>
    <input type="range" id="scaleSlider" min="0.5" max="1.5" step="0.05" value="1.0" 
           oninput="document.getElementById('lblScale').innerText=Math.round(this.value*100)+'%'; saveSettingState(); renderFrame();" 
           class="w-full accent-sky-500">
</div>
```

### Add Select Dropdown
```html
<select id="styleSelect" onchange="saveSettingState(); renderFrame();" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-1.5 text-xs">
    <option value="style1">Style 1</option>
    <option value="style2">Style 2</option>
</select>
```

JavaScript:
```javascript
// Di renderFrame()
const style = document.getElementById('styleSelect').value;
if (style === 'style1') {
    // render style 1
} else if (style === 'style2') {
    // render style 2
}
```

## Performance Tips

1. **Cache DOM queries**:
   ```javascript
   // ❌ Bad
   for (let i = 0; i < 1000; i++) {
       document.getElementById('canvas').something();
   }

   // ✅ Good
   const canvas = document.getElementById('canvas');
   for (let i = 0; i < 1000; i++) {
       canvas.something();
   }
   ```

2. **Use ctx.save()/restore()** untuk isolated transforms:
   ```javascript
   ctx.save();
   ctx.translate(x, y);
   ctx.scale(s, s);
   drawSomething(ctx);
   ctx.restore(); // Kembali ke state sebelumnya
   ```

3. **Batch DOM updates**:
   ```javascript
   // Gather semua perubahan dulu
   const updates = {};
   // ... hitung updates
   // Apply sekaligus
   Object.keys(updates).forEach(id => {
       document.getElementById(id).value = updates[id];
   });
   ```

4. **Throttle renderFrame()** untuk high-frequency inputs:
   ```javascript
   let renderScheduled = false;
   function scheduleRender() {
       if (!renderScheduled) {
           renderScheduled = true;
           requestAnimationFrame(() => {
               renderFrame();
               renderScheduled = false;
           });
       }
   }
   ```

## Debugging

### Browser DevTools
1. **Console**: `console.log(hudPos)` → inspect state
2. **Elements**: Inspect DOM structure
3. **Network**: Check file loads
4. **Application**: LocalStorage inspection

### PHP Debugging
```php
<?php
error_log("Debug msg: " . print_r($data, true));
?>
```

Check `C:\laragon\logs\php\error.log`

### Canvas Debugging
```javascript
// Visualize hitbox/bounds
ctx.strokeStyle = 'rgba(255, 0, 0, 0.5)';
ctx.lineWidth = 2;
ctx.strokeRect(x, y, w, h);
```

## Testing

### Manual Testing Checklist
- [ ] Sidebar buttons toggle tabs correctly
- [ ] Color pickers work + preview updates
- [ ] Range sliders update labels live
- [ ] Canvas drag-drop works
- [ ] Keyboard shortcuts (Space, R, Ctrl+Z)
- [ ] Multiple aspect ratios render correctly
- [ ] LocalStorage saves & restores state
- [ ] PNG export works
- [ ] MP4 export starts/stops
- [ ] File uploads (video, image, audio, TCX)

### Browser Compatibility
- Chrome/Edge: ✅ Full
- Firefox: ✅ Full
- Safari: ⚠️ MediaRecorder fallback
- Mobile: ⚠️ Touch events not yet optimized

## Common Bugs & Fixes

### Canvas rendering jadi blank
- Check: `canvas.width/height` set?
- Check: `ctx.clearRect()` called?
- Fix: Re-render pada window resize

### File upload tidak work
- Check: File input `accept` attribute
- Check: PHP handler di `index.php`
- Fix: Verify form `enctype="multipart/form-data"`

### Modal tidak scroll
- Check: Parent `.flex flex-col` + `overflow-y-auto`?
- Fix: Add `max-h-[calc(100vh-...)]`

### Performance lag
- Profile di DevTools Performance tab
- Check: `renderFrame()` frequency?
- Fix: Use `requestAnimationFrame()` throttling

---

**Last Updated**: 2026-10-28

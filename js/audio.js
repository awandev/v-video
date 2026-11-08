function uploadFileToPhp(input) {
    const file = input.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('action', 'parse_file');
    formData.append('hr_file', file);
    fetch('', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                document.getElementById('hrDataInput').value = res.data.join(', ');
                parseHrInput();
                alert(`Berhasil membaca ${res.count} data Heart Rate!`);
            } else alert('Data Heart Rate tidak ditemukan.');
        });
}

document.addEventListener('DOMContentLoaded', function() {
    const bgImageInput = document.getElementById('bgImageInput');
    if (bgImageInput) {
        bgImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(evt) {
                const img = new Image();
                img.onload = function() { 
                    bgImage = img; 
                    useVideoBg = false; 
                    renderFrame(); 
                };
                img.src = evt.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    const bgVideoInput = document.getElementById('bgVideoInput');
    if (bgVideoInput) {
        bgVideoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            hiddenVideo.src = URL.createObjectURL(file);
            hiddenVideo.onloadedmetadata = function() {
                useVideoBg = true;
                currentVideoSec = 0;
                const slider = document.getElementById('timelineSlider');
                if (slider) slider.value = 0;
                hiddenVideo.currentTime = 0;
                renderFrame();
            };
            hiddenVideo.onseeked = () => renderFrame();
        });
    }
    
    const cubeFileInput = document.getElementById('cubeFileInput');
    if (cubeFileInput) {
        cubeFileInput.addEventListener('change', function(e) {
            loadCubeLutFile(this);
        });
    }
});

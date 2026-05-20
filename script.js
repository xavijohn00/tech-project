// UI interactivity only — no backend logic here

// Profile dropdown toggle
function toggleMenu() {
    const menu = document.getElementById('dropdown');
    if (menu) menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
}

// Close dropdown when clicking outside
window.onclick = function(e) {
    if (!e.target.matches('.profile-circle')) {
        const menu = document.getElementById('dropdown');
        if (menu && menu.style.display === 'block') menu.style.display = 'none';
    }
}

// Fan manual control — show/hide slider
function toggleFanMode() {
    const btn       = document.getElementById('fan-mode-btn');
    const manual    = document.getElementById('fan-manual');
    const status    = document.getElementById('fan-status');
    const isAuto    = btn.dataset.mode === 'auto';

    if (isAuto) {
        btn.dataset.mode  = 'manual';
        btn.innerText     = 'OFF';
        btn.classList.add('secondary');
        manual.style.display = 'block';
        status.innerText  = 'MANUAL: 40%';
    } else {
        btn.dataset.mode  = 'auto';
        btn.innerText     = 'ON';
        btn.classList.remove('secondary');
        manual.style.display = 'none';
        status.innerText  = 'AUTO: 40%';
    }
}

function setFanSpeed(val) {
    const status = document.getElementById('fan-status');
    if (status) status.innerText = 'MANUAL: ' + val + '%';
}

// Heating manual control
function toggleHeatMode() {
    const btn    = document.getElementById('heat-mode-btn');
    const manual = document.getElementById('heat-manual');
    const status = document.getElementById('heat-status');
    const isAuto = btn.dataset.mode === 'auto';

    if (isAuto) {
        btn.dataset.mode  = 'manual';
        btn.innerText     = 'OFF';
        btn.classList.add('secondary');
        manual.style.display = 'block';
        status.innerText  = 'MANUAL: ON';
    } else {
        btn.dataset.mode  = 'auto';
        btn.innerText     = 'ON';
        btn.classList.remove('secondary');
        manual.style.display = 'none';
        status.innerText  = 'AUTO: ON';
    }
}

function toggleHeatPower() {
    const btn    = document.getElementById('heat-power-btn');
    const status = document.getElementById('heat-status');
    const isOn   = btn.dataset.state === 'on';

    if (isOn) {
        btn.dataset.state = 'off';
        btn.innerText     = 'POWER OFF';
        btn.classList.add('secondary');
        status.innerText  = 'MANUAL: OFF';
    } else {
        btn.dataset.state = 'on';
        btn.innerText     = 'POWER ON';
        btn.classList.remove('secondary');
        status.innerText  = 'MANUAL: ON';
    }
}

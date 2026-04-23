(function() {
    const player = document.getElementById('player');
    if (!player) return;

    const key = 'watch_pos_' + window.location.pathname;
    const saved = parseFloat(localStorage.getItem(key));

    if (saved > 5) {
        player.addEventListener('loadedmetadata', () => {
            player.currentTime = saved;
        }, { once: true });
    }

    player.addEventListener('timeupdate', () => {
        if (Math.floor(player.currentTime) % 5 === 0) {
            localStorage.setItem(key, player.currentTime);
        }
    });

    player.addEventListener('ended', () => {
        localStorage.removeItem(key);
        const next = document.querySelector('.btn-ep-next');
        if (next) setTimeout(() => { window.location = next.href; }, 2000);
    });
})();
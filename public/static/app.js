let currentTrackId = null;
let actions = {
    enqueueAlbum: null,
    setCurrentTrack: null,
    clearQueue: null
};
let isTrackInfoScrollPositionResetScheduled = false;

function playNextTrack() {
    if (!currentTrackId) {
        return;
    }

    const allPlaylistItems = document.querySelectorAll('.playlist__item');
    let nextTrackId = null;
    for (let i = 0; i < allPlaylistItems.length - 1; i++) {
        if (parseInt(allPlaylistItems[i].dataset.id) === currentTrackId) {
            nextTrackId = parseInt(allPlaylistItems[i + 1].dataset.id);
            break;
        }
    }

    nextTrackId && playTrack(nextTrackId);
}

function playPreviousTrack(trackId = null) {
    if (!currentTrackId) {
        return;
    }

    const allPlaylistItems = document.querySelectorAll('.playlist__item');
    let previousTrackId = null;
    for (let i = 1; i < allPlaylistItems.length; i++) {
        if (parseInt(allPlaylistItems[i].dataset.id) === currentTrackId) {
            previousTrackId = parseInt(allPlaylistItems[i - 1].dataset.id);
            break;
        }
    }

    previousTrackId && playTrack(previousTrackId);
}

function stopMusic() {
    const audio = document.getElementById('audio');
    audio.pause();
    audio.currentTime = 0;

    document.querySelector('.player__progress').value = 0;
}

function playTrack(trackId = null) {
    trackId = trackId || currentTrackId;
    if (!trackId) {
        return;
    }

    stopMusic();
    setCurrentTrack(trackId);
    unpauseTrack();
}

function setCurrentTrack(trackId = null) {
    currentTrackId = trackId;
    if (!currentTrackId) {
        return;
    }

    const playlistItem = document.getElementById(`playlistItem${trackId}`);
    setActivePlaylistItem(trackId);
    setPlayingTrackInformation(
        playlistItem.querySelector('.track-info__artist').textContent,
        playlistItem.querySelector('.track-info__track').textContent,
        playlistItem.dataset.duration,
        playlistItem.dataset.src
    );

    fetch(`/?action=${actions.setCurrentTrack}&trackId=${currentTrackId}`, {method: 'POST'});
}

function setActivePlaylistItem(trackId) {
    if (!trackId) {
        return;
    }

    const playlistItem = document.getElementById(`playlistItem${trackId}`);
    const currentPlaylistItem = document.querySelector('.playlist__item--active');
    if (currentPlaylistItem) {
        currentPlaylistItem.className = currentPlaylistItem.className.replace(/\s*playlist__item--active/, '');
    }

    playlistItem.className = `${playlistItem.className} playlist__item--active`;
}

function stopPlaylist() {
    if (!currentTrackId) {
        return;
    }

    const resetTrackId = parseInt(/(\d+)$/.exec(document.querySelector('.playlist__item').id)[1]);
    setCurrentTrack(resetTrackId);
    stopMusic();
}

function pauseTrack() {
    if (!currentTrackId) {
        return;
    }

    document.getElementById(`audio`).pause();
}

function unpauseTrack() {
    if (!currentTrackId) {
        return;
    }

    document.getElementById(`audio`).play();
}

function updateTrackProgress(trackId = null) {
    trackId = trackId || currentTrackId;
    if (!trackId) {
        return;
    }

    const audio = document.getElementById(`audio`);
    document.querySelector('.player__progress').value = parseInt(audio.currentTime);
}

function setPlayingTrackInformation(
    artist,
    track,
    duration,
    src = null
) {
    document.querySelector('.track-info__artist--player').textContent = artist;
    document.querySelector('.track-info__track--player').textContent = track;
    document.querySelector('.player__progress').max = duration;
    document.querySelector('.player__progress').value = 0;
    document.querySelector('.track-info--player').scrollLeft = 0;
    if (null !== src) {
        document.getElementById('audio').src = src;
    }

    document.title = null !== src
        ? `${artist} — ${track}`
        : 'Bandamp 2.9';
}

function enqueueAlbum() {
    const url = document.querySelector('.playlist-controls__album-url');
    if (!url.checkValidity()) {
        return;
    }

    const controls = document.querySelectorAll('button');
    controls.forEach((button) => button.disabled = true);

    fetch(`/?action=${actions.enqueueAlbum}&albumUrl=${encodeURIComponent(url.value)}`, {method: 'POST'})
        .then((response) => response.text())
        .then((playlistHtml) => {
            document.querySelector('.playlist').innerHTML = playlistHtml;
            controls.forEach((button) => button.disabled = false);
            url.value = '';
            if (!currentTrackId) {
                setCurrentTrack(parseInt(document.querySelector('.playlist__item').dataset.id));
            }
            setActivePlaylistItem(currentTrackId);
        });
}

function scrollTrackInfo() {
    const trackInfo = document.querySelector('.track-info--player');
    const targetScroll = trackInfo.scrollLeft += 10;
    if (trackInfo.scrollLeft < targetScroll) {
        if (isTrackInfoScrollPositionResetScheduled) {
            trackInfo.scrollLeft = 0;
            isTrackInfoScrollPositionResetScheduled = false;
            return;
        }

        isTrackInfoScrollPositionResetScheduled = true;
    }
}

function clearPlaylist() {
    stopMusic();
    setCurrentTrack(null);

    document.querySelector('.playlist').innerHTML = '';
    setPlayingTrackInformation('', '', 1);

    fetch(`/?action=${actions.clearQueue}`, {method: 'POST'});
}

function seekTrack(percentage) {
    if (!currentTrackId) {
        return;
    }

    document.getElementById('audio').currentTime = document.querySelector('.player__progress').max * percentage;
}

function setup(actionNames, trackId) {
    actions.enqueueAlbum = actionNames.enqueueAlbum;
    actions.setCurrentTrack = actionNames.setCurrentTrack;
    actions.clearQueue = actionNames.clearQueue;

    navigator.mediaSession.setActionHandler('previoustrack', function() {
        playPreviousTrack();
    });
    navigator.mediaSession.setActionHandler('nexttrack', function() {
        playNextTrack();
    });

    document.querySelector('.player__progress').onclick = (event) => {
        let seeker = event.target.getBoundingClientRect();
        seekTrack((event.clientX - seeker.left) / seeker.width);
    };

    setCurrentTrack(trackId);
    setInterval(scrollTrackInfo, 1100);
}

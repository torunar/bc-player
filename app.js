let currentTrackId = null;
let playlist = [];
let isTrackInfoScrollPositionResetScheduled = false;

function playNextTrack(isAutoPlay = false) {
    if (!currentTrackId) {
        return;
    }

    let nextTrackId = null;
    for (let i = 0; i < playlist.length - 1; i++) {
        if (playlist[i].id === currentTrackId) {
            nextTrackId = playlist[i + 1].id;
            break;
        }
    }

    if (nextTrackId) {
        playTrack(nextTrackId);
        return;
    }

    isAutoPlay && pauseTrack();
}

function playPreviousTrack() {
    if (!currentTrackId) {
        return;
    }

    let previousTrackId = null;
    for (let i = 1; i < playlist.length; i++) {
        if (playlist[i].id === currentTrackId) {
            previousTrackId = playlist[i - 1].id;
            break;
        }
    }

    previousTrackId && playTrack(previousTrackId);
}

function stopMusic() {
    pauseTrack();

    document.getElementById('audio').currentTime = 0;
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
    sessionStorage.setItem('currentTrackId', currentTrackId);
    if (!currentTrackId) {
        return;
    }

    const playlistItem = getTrack(trackId);
    setActivePlaylistItem(trackId);
    setPlayingTrackInformation(playlistItem.artist, playlistItem.title, playlistItem.duration, playlistItem.src);
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

    setCurrentTrack(playlist[0].id);
    stopMusic();
}

function pauseTrack() {
    if (!currentTrackId) {
        return;
    }

    document.getElementById(`audio`).pause();

    document.querySelector('.player').className = document.querySelector('.player').className.replace(/\s*player--paused/, '');
    document.querySelector('.player').className = `${document.querySelector('.player').className} player--paused`;
}

function unpauseTrack() {
    if (!currentTrackId) {
        return;
    }

    document.getElementById(`audio`).play();

    document.querySelector('.player').className = document.querySelector('.player').className.replace(/\s*player--paused/, '');
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
        : 'BC Player';
}

function enqueueAlbum() {
    const url = document.querySelector('.playlist-controls__album-url');
    if (!url.checkValidity()) {
        return;
    }

    const controls = document.querySelectorAll('button');
    controls.forEach((button) => button.disabled = true);

    fetch(`https://corsproxy.io/?url=${encodeURIComponent(url.value)}`)
        .then((response) => response.text())
        .then((body) => {
            const doc = (new DOMParser()).parseFromString(body, 'text/html');
            const albumInfo = JSON.parse(doc.querySelector('script[data-tralbum]').dataset.tralbum);
            const tracks = albumInfo.trackinfo
                .filter((track) => !!track.file && !!track.file['mp3-128'])
                .map((track) => {
                    return {
                        id: track.id,
                        duration: track.duration | 0,
                        src: track.file['mp3-128'],
                        artist: albumInfo.artist,
                        title: track.title
                    };
                });

            setPlaylist([...playlist, ...tracks]);
            document.querySelector('.playlist').innerHTML += renderTracks(tracks);

            if (!currentTrackId) {
                setCurrentTrack(playlist[0]?.id);
            }
            setActivePlaylistItem(currentTrackId);
        })
        .finally(() => {
            controls.forEach((button) => button.disabled = false);
            url.value = '';
        });
}

function renderTracks(tracks = [])
{
    return tracks.reduce((html, track) =>
        `${html}
        <div id="playlistItem${track.id}"
            class="playlist__item"
            ondblclick="playTrack(${track.id})"
            data-src="${track.src}"
            data-duration="${track.duration}"
            data-id="${track.id}"
        >
            <div class="track-info track-info--playlist" title="${track.artist} — ${track.title}">
                <div class="track-info__number"></div>
                <div class="track-info__artist track-info__artist--playlist">${track.artist}</div>
                <div class="track-info__track track-info__track--playlist">${track.title}</div>
                <div class="track-info__duration">${String((track.duration / 60) | 0).padStart(2, '0')}:${String(track.duration % 60).padStart(2, '0')}</div>
            </div>
        </div>`,
        ''
    );
}

function clearPlaylist() {
    stopMusic();
    setCurrentTrack(null);
    setPlaylist([]);

    document.querySelector('.playlist').innerHTML = '';
    setPlayingTrackInformation('', '', 1);
}

function seekTrack(percentage) {
    if (!currentTrackId) {
        return;
    }

    document.getElementById('audio').currentTime = document.querySelector('.player__progress').max * percentage;
}

function setPlaylist(newPlaylist = [])
{
    playlist = newPlaylist;
    sessionStorage.setItem('playlist', JSON.stringify(newPlaylist));
}

function getTrack(trackId)
{
    for (let track of playlist) {
        if (track.id === trackId) {
            return track;
        }
    }

    return null;
}

function setup(playlist, currentTrackId) {
    navigator.mediaSession.setActionHandler('play', unpauseTrack);
    navigator.mediaSession.setActionHandler('pause', pauseTrack);
    navigator.mediaSession.setActionHandler('previoustrack', playPreviousTrack);
    navigator.mediaSession.setActionHandler('nexttrack', playNextTrack);

    document.querySelector('.player__progress').onclick = (event) => {
        let seeker = event.target.getBoundingClientRect();
        seekTrack((event.clientX - seeker.left) / seeker.width);
    };

    setPlaylist(playlist);
    document.querySelector('.playlist').innerHTML = renderTracks(playlist);

    if (playlist.length === 0) {
        currentTrackId = null;
    }
    setCurrentTrack(currentTrackId);
}

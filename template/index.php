<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BandAmp 2.9</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: monospace;
            font-size: 18px;
            line-height: 1;
        }
        button, input, progress {
            color: black;
            border-radius: 0;
        }
        button {
            cursor: pointer;
            background: whitesmoke;
            min-height: 45px;
            min-width: 45px;
        }
        button:disabled {
            cursor: not-allowed;
        }
        .bandamp {
            padding: 0 0.5em;
            max-width: 600px;
            margin: 0 auto;
        }
        .player__progress {
            width: 100%;
            margin-bottom: 1em;
            background: whitesmoke;
            height: 0.25em;
        }
        .player__controls {
            display: flex;
            gap: 0.5em;
            margin-bottom: 1em;
        }
        .player__controls button {
            flex: 20%;
            font-size: 150%;
        }
        .playlist {
            max-height: calc(100vh - 14em);
            overflow: auto;
            padding-bottom: 1em;
            margin: 1em 0;
        }
        .track-info {
            display: flex;
            align-items: baseline;
        }
        .track-info--player {
            padding: 1em 0;
            overflow-x: auto;
        }
        .track-info__artist {
            flex-shrink: 0;
        }
        .track-info__artist:after {
            content: ' — ';
        }
        .track-info__artist:empty:after {
            display: none;
        }
        .track-info__artist--player {
            min-height: 18px;
        }
        .track-info__track {
            flex-shrink: 0;
        }
        .playlist__item--active {
            font-weight: bold;
        }
        .track-info__number {
            min-width: 4ch;
            text-align: right;
        }
        .track-info__number:before {
            content: counter(playlist) ".";
            padding-right: 1ch;
        }
        .playlist__item {
            counter-increment: playlist;
            padding: 0.125em 0;
            cursor: pointer;
        }
        .playlist__item:hover {
            background: whitesmoke;
        }
        .playlist-controls {
            display: flex;
            gap: 0.5em;
        }
        .playlist-controls__album-url {
            flex: 1;
            padding: 0.5em;
        }
        .playlist-controls__add {
            flex: 0;
        }
        .playlist-controls__clear {
            flex: 0;
        }
        .project-link {
            position: absolute;
            bottom: 1em;
            right: 1em;
        }
    </style>
</head>
<body>
<div class="bandamp">
    <div class="player">
        <div class="track-info track-info--player">
            <audio id="audio"
                   onended="playNextTrack()"
                   ontimeupdate="updateTrackProgress()"
            ></audio>
            <div class="track-info__artist track-info__artist--player"></div>
            <div class="track-info__track track-info__track--player"></div>
        </div>
        <progress class="player__progress" max="1" value="0"></progress>
        <div class="player__controls">
            <button onclick="playPreviousTrack()">⏮️</button>
            <button onclick="pauseTrack()">⏸️</button>
            <button onclick="unpauseTrack()">▶️️</button>
            <button onclick="stopPlaylist()">⏹️</button>
            <button onclick="playNextTrack()">⏭️️</button>
        </div>
    </div>
    <div class="playlist-controls">
        <input class="playlist-controls__album-url" type="url" required value="" placeholder="Bandcamp album URL">
        <button class="playlist-controls__add" onclick="enqueueAlbum()">➕</button>
        <button class="playlist-controls__clear" onclick="clearPlaylist()">🗑️</button>
    </div>
    <div class="playlist">
        <?php require_once __DIR__ . '/player.php' ?>
    </div>
</div>
<a class="project-link" target="_blank" href="https://github.com/torunar/bandamp-2.9">BandAmp 2.9</a>
<script>
    let currentTrackId;

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
        audio.fastSeek(0);

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

        fetch(`/?action=<?= Action::SET_CURRENT_TRACK->value ?>&trackId=${currentTrackId}`, {method: 'POST'});
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

        fetch(`/?action=<?= Action::ENQUEUE_ALBUM->value ?>&albumUrl=${encodeURIComponent(url.value)}`, {method: 'POST'})
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

    function clearPlaylist() {
        stopMusic();
        setCurrentTrack(null);

        document.querySelector('.playlist').innerHTML = '';
        setPlayingTrackInformation('', '', 1);

        fetch(`/?action=<?= Action::CLEAR_QUEUE->value ?>`, {method: 'POST'});
    }

    (() => {
        navigator.mediaSession.setActionHandler('previoustrack', function() {
            playPreviousTrack();
        });
        navigator.mediaSession.setActionHandler('nexttrack', function() {
            playNextTrack();
        });
        setCurrentTrack(<?= $currentTrackId ?? 'null' ?>);
    })();
</script>
</body>
</html>

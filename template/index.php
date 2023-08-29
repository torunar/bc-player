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
            border: 1px solid;
            color: black;
            border-radius: 0;
        }
        button {
            cursor: pointer;
            background: whitesmoke;
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
        }
        .player__controls {
            display: flex;
            gap: 0.5em;
            margin-bottom: 1em;
        }
        .player__controls button {
            flex: 20%;
            padding: 0.25em;
            font-size: 150%;
        }
        .playlist {
            max-height: calc(100vh - 230px);
            overflow: auto;
            padding-bottom: 1em;
            margin-bottom: 1em;
        }
        .track-info {
            display: flex;
            align-items: baseline;
        }
        .track-info--player {
            padding: 1em 0;
            min-height: 18px;
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
            padding: 0 0.5em;
        }
        .playlist-controls__clear {
            flex: 0;
            padding: 0 0.5em;
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
            <div class="track-info__artist track-info__artist--player"></div>
            <div class="track-info__track track-info__track--player"></div>
        </div>
        <progress class="player__progress" max="100" value="0"></progress>
        <div class="player__controls">
            <button onclick="playPreviousTrack()">⏮️</button>
            <button onclick="pauseTrack()">⏸️</button>
            <button onclick="unpauseTrack()">▶️️</button>
            <button onclick="stopPlaylist()">⏹️</button>
            <button onclick="playNextTrack()">⏭️️</button>
        </div>
    </div>
    <div class="playlist">
        <?php foreach (($queue ?? []) as $number => $track): ?>
            <?php require __DIR__ . '/playlistItem.php' ?>
        <?php endforeach; ?>
    </div>
    <form class="playlist-controls" action="/" method="post">
        <input class="playlist-controls__album-url" type="url" name="albumUrl" value="" placeholder="Bandcamp album URL">
        <button class="playlist-controls__add" type="submit" name="action" value="<?= Action::ENQUEUE_ALBUM->value ?>">➕</button>
        <button class="playlist-controls__clear" type="submit" name="action" value="<?= Action::CLEAR_QUEUE->value ?>">🗑️</button>
    </form>
</div>
<a class="project-link" target="_blank" href="https://github.com/torunar/bandamp-2.9">BandAmp 2.9</a>
<script>
    let currentTrackId = <?= $currentTrackId ?? 'null' ?>;

    function playNextTrack(trackId = null) {
        trackId = trackId || currentTrackId;
        if (!trackId) {
            return;
        }

        const allPlaylistItems = document.querySelectorAll('.playlist__item');
        let nextTrackId = null;
        for (let i = 0; i < allPlaylistItems.length - 1; i++) {
            if (allPlaylistItems[i].id === `playlistItem${trackId}`) {
                nextTrackId = parseInt(/(\d+)$/.exec(allPlaylistItems[i + 1].id)[1]);
                break;
            }
        }

        nextTrackId && playTrack(nextTrackId);
    }

    function playPreviousTrack(trackId = null) {
        trackId = trackId || currentTrackId;
        if (!trackId) {
            return;
        }

        const allPlaylistItems = document.querySelectorAll('.playlist__item');
        let previousTrackId = null;
        for (let i = 1; i < allPlaylistItems.length; i++) {
            if (allPlaylistItems[i].id === `playlistItem${trackId}`) {
                previousTrackId = parseInt(/(\d+)$/.exec(allPlaylistItems[i - 1].id)[1]);
                break;
            }
        }

        previousTrackId && playTrack(previousTrackId);
    }

    function stopMusic() {
        document.querySelectorAll('audio').forEach((audio) => {
            audio.fastSeek(0);
            audio.pause();
        });

        document.querySelector('.player__progress').value = 0;
    }

    function playTrack(trackId = null) {
        trackId = trackId || currentTrackId;
        if (!trackId) {
            return;
        }

        setCurrentTrack(trackId);
        stopMusic();

        document.getElementById(`audio${trackId}`).play();
    }

    function setCurrentTrack(trackId = null) {
        trackId = trackId || currentTrackId;
        if (!trackId) {
            return;
        }

        currentTrackId = trackId;

        const playlistItem = document.getElementById(`playlistItem${trackId}`);
        const currentPlaylistItem = document.querySelector('.playlist__item--active');
        if (currentPlaylistItem) {
            currentPlaylistItem.className = currentPlaylistItem.className.replace(/\s*playlist__item--active/, '');
        }

        playlistItem.className = `${playlistItem.className} playlist__item--active`;

        document.querySelector('.track-info__artist--player').textContent = playlistItem.querySelector('.track-info__artist').textContent;
        document.querySelector('.track-info__track--player').textContent = playlistItem.querySelector('.track-info__track').textContent;
        document.querySelector('.player__progress').max = document.getElementById(`audio${trackId}`).dataset.duration;

        fetch(`/?action=<?= Action::SET_CURRENT_TRACK->value ?>&trackId=${currentTrackId}`, {method: 'POST'});
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

        document.getElementById(`audio${currentTrackId}`).pause();
    }

    function unpauseTrack() {
        if (!currentTrackId) {
            return;
        }

        document.getElementById(`audio${currentTrackId}`).play();
    }

    function updateTrackProgress(trackId = null) {
        trackId = trackId || currentTrackId;
        if (!trackId) {
            return;
        }

        const audio = document.getElementById(`audio${trackId}`);
        document.querySelector('.player__progress').value = parseInt(audio.currentTime);
    }

    (() => {
        navigator.mediaSession.setActionHandler('previoustrack', function() {
            playPreviousTrack();
        });
        navigator.mediaSession.setActionHandler('nexttrack', function() {
            playNextTrack();
        });
        setCurrentTrack();
    })();
</script>
</body>
</html>

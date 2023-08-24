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
        }
        button, input {
            border: 1px solid;
        }
        button {
            cursor: pointer;
        }
        .bandamp {
            max-width: 600px;
            margin: 0 auto;
        }
        .player__controls {
            display: flex;
            gap: 0.5em;
            margin: 1em 0;
        }
        .player__controls button {
            flex: 20%;
            padding: 0.25em;
            font-size: 200%;
        }
        .playlist {
            max-height: 600px;
            overflow-y: scroll;
            margin: 1em 0;
        }
        .track-info {
            display: flex;
            align-items: baseline;
        }
        .track-info--player {
            margin: 1em 0;
            padding: 0 0.25em;
        }
        .track-info__artist {
            flex-shrink: 0;
        }
        .track-info__track {
            flex-shrink: 0;
        }
        .track-info__artist:after {
            content: ' — ';
        }
        .track-info__artist:empty:after {
            display: none;
        }
        .track-info__play {
            background: none;
            border: none;
            margin: 0 0.25em 0 0;
            opacity: 0;
            font-size: 125%;
        }
        .playlist__item:hover .track-info__play {
            opacity: 1;
        }
        .playlist__item {
            margin-left: 3em;
        }
        .playlist__item--active {
            font-weight: bold;
        }
        .playlist-controls {
            display: flex;
            gap: 0.5em;
            margin: 0.5em 0;
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
            top: 100vh;
            margin-top: -2em;
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
        <div class="player__controls">
            <button onclick="playPreviousTrack()">⏮️</button>
            <button onclick="pauseTrack()">⏸️</button>
            <button onclick="unpauseTrack()">▶️️</button>
            <button onclick="stopPlaylist()">⏹️</button>
            <button onclick="playNextTrack()">⏭️️</button>
        </div>
    </div>
    <ol class="playlist">
        <?php foreach (($queue ?? []) as $track): ?>
            <?php require __DIR__ . '/playlistItem.php' ?>
        <?php endforeach; ?>
    </ol>
    <form class="playlist-controls" action="/" method="post">
        <input class="playlist-controls__album-url" type="url" name="albumUrl" value="" placeholder="Bandcamp album URL">
        <button class="playlist-controls__add" type="submit" name="action" value="<?= Action::ENQUEUE_ALBUM->value ?>">add</button>
        <button class="playlist-controls__clear" type="submit" name="action" value="<?= Action::CLEAR_QUEUE->value ?>">clear</button>
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

        fetch(`/?action=<?= Action::SET_CURRENT_TRACK->value ?>&trackId=${currentTrackId}`, {method: 'POST'});
    }

    function stopPlaylist() {
        const resetTrackId = parseInt(/(\d+)$/.exec(document.querySelector('.playlist__item').id)[1]);
        setCurrentTrack(resetTrackId);
        stopMusic();
    }

    function pauseTrack() {
        document.getElementById(`audio${currentTrackId}`).pause();
    }

    function unpauseTrack() {
        document.getElementById(`audio${currentTrackId}`).play();
    }

    (() => setCurrentTrack())();
</script>
</body>
</html>

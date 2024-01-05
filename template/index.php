<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BandAmp 2.9</title>
    <link rel="stylesheet" href="/static/app.css" />
    <link rel="manifest" href="/static/manifest.json" />
</head>
<body>
<div class="bandamp">
    <div class="player player--paused">
        <div class="player__track-wrapper">
            <div class="equalizer">
                <span class="equalizer-bar" style="--duration: 0.5s"></span>
                <span class="equalizer-bar" style="--duration: 0.6s"></span>
                <span class="equalizer-bar" style="--duration: 1.3s"></span>
                <span class="equalizer-bar" style="--duration: 0.9s"></span>
                <span class="equalizer-bar" style="--duration: 0.7s"></span>
            </div>
            <div class="track-info track-info--player">
                <audio id="audio" onended="playNextTrack(true)" ontimeupdate="updateTrackProgress()"></audio>
                <div class="track-info__artist track-info__artist--player"></div>
                <div class="track-info__track track-info__track--player"></div>
            </div>
        </div>
        <progress class="player__progress" max="1" value="0"></progress>
        <div class="player__controls">
            <button onclick="playPreviousTrack()">⏮</button>
            <button onclick="pauseTrack()">⏸</button>
            <button onclick="unpauseTrack()">▶</button>
            <button onclick="stopPlaylist()">⏹</button>
            <button onclick="playNextTrack()">⏭</button>
        </div>
    </div>
    <div class="playlist-controls">
        <input class="playlist-controls__album-url" type="url" required value="" placeholder="Bandcamp album URL">
        <button class="playlist-controls__add" onclick="enqueueAlbum()">➕</button>
        <button class="playlist-controls__clear" onclick="clearPlaylist()" title="Clear">⏏</button>
    </div>
    <div class="playlist">
        <?php require_once __DIR__ . '/player.php' ?>
    </div>
</div>
<a class="project-link" target="_blank" href="https://github.com/torunar/bandamp-2.9">BandAmp 2.9</a>
<script src="/static/app.js"></script>
<script>
    (() => {
        setup(
            {
                enqueueAlbum: '<?= Action::ENQUEUE_ALBUM->value ?>',
                setCurrentTrack: '<?= Action::SET_CURRENT_TRACK->value ?>',
                clearQueue: '<?= Action::CLEAR_QUEUE->value ?>'
            },
            <?= $currentTrackId ?? 'null' ?>
        );
    })();
</script>
</body>
</html>

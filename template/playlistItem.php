<div id="playlistItem<?= $track->id ?>" class="playlist__item" ondblclick="playTrack(<?= $track->id ?>)">
    <audio id="audio<?= $track->id ?>"
           src="<?= $track->url ?>"
           onended="playNextTrack(<?= $track->id ?>);"
           ontimeupdate="updateTrackProgress(<?= $track->id ?>)"
           data-duration="<?= $track->duration ?>"
    ></audio>
    <div class="track-info track-info__playlist">
        <div class="track-info__number"></div>
        <div class="track-info__artist track-info__artist--playlist"><?= $track->artist ?></div>
        <div class="track-info__track track-info__track--playlist"><?= $track->title ?></div>
    </div>
</div>

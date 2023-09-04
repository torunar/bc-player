<div id="playlistItem<?= $track->id ?>"
     class="playlist__item"
     ondblclick="playTrack(<?= $track->id ?>)"
     data-src="<?= $track->url ?>"
     data-duration="<?= $track->duration ?>"
     data-id="<?= $track->id ?>"
>
    <div class="track-info track-info--playlist" title="<?= $track->artist ?> — <?= $track->title ?>">
        <div class="track-info__number"></div>
        <div class="track-info__artist track-info__artist--playlist"><?= $track->artist ?></div>
        <div class="track-info__track track-info__track--playlist"><?= $track->title ?></div>
        <div class="track-info__duration"><?= sprintf('%02d', $track->duration / 60) ?>:<?= sprintf('%02d', $track->duration % 60) ?></div>
    </div>
</div>

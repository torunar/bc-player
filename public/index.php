<?php

require_once __DIR__ . '/../src/functions.php';

enum Action: string {
    case ENQUEUE_ALBUM = 'enqueueAlbum';
    case CLEAR_QUEUE = 'clearQueue';
    CASE SET_CURRENT_TRACK = 'setCurrentTrack';
}

initSession();

$action = Action::tryFrom($_REQUEST['action'] ?? '');

try {
    match ($action) {
        Action::ENQUEUE_ALBUM => enqueue($_SESSION, getAlbumContents($_REQUEST['albumUrl'])),
        Action::CLEAR_QUEUE => clearQueue($_SESSION),
        Action::SET_CURRENT_TRACK => setCurrentTrackId($_SESSION, (int) $_REQUEST['trackId']),
        default => null,
    };
} catch (Throwable) {}

match ($action) {
    Action::ENQUEUE_ALBUM, Action::CLEAR_QUEUE => header('Location: /'),
    Action::SET_CURRENT_TRACK => null,
    default => renderPlayer($_SESSION),
};

exit(0);

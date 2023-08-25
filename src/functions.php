<?php declare(strict_types=1);

const DEFAULT_SESSION = [
    'queue' => [],
    'currentTrackId' => null,
];

function initSession(): void
{
    session_start();

    $_SESSION = [...DEFAULT_SESSION, ...$_SESSION];

    initCurrentTrack($_SESSION);

    register_shutdown_function('session_write_close');
}

function getAlbumContents(string $url): array
{
    $client = curl_init();
    curl_setopt_array($client, [
        CURLOPT_URL => $url,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/116.0',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
    ]);

    $html = new DOMDocument();
    $html->loadHTML(curl_exec($client), LIBXML_NOERROR | LIBXML_NOWARNING);
    $script = (new DOMXPath($html))->query('//script[@data-tralbum]');

    $albumInfo = json_decode($script->item(0)->attributes->getNamedItem('data-tralbum')->nodeValue);
    $tracksWithAudio = array_filter(
        $albumInfo->trackinfo,
        fn(stdClass $track): bool => !empty($track->file->{'mp3-128'})
    );

    return array_map(fn(stdClass $track): stdClass => (object) [
        'id' => $track->id,
        'title' => $track->title,
        'artist' => $albumInfo->artist,
        'url' => $track->file->{'mp3-128'},
        'duration' => (int) $track->duration,
    ], $tracksWithAudio);
}

function enqueue(array &$storage, array $tracks): void
{
    $storage['queue'] = [...$storage['queue'], ...$tracks];

    initCurrentTrack($storage);
}

function setCurrentTrackId(array &$storage, ?int $currentTrackId): void
{
    $storage['currentTrackId'] = $currentTrackId;
}

function clearQueue(array &$storage): void
{
    $storage = DEFAULT_SESSION;
}

function initCurrentTrack(array &$storage): void
{
    if ($storage['queue'] && !$storage['currentTrackId']) {
        $storage['currentTrackId'] = reset($storage['queue'])->id;
    }
    if (!$storage['queue']) {
        $storage['currentTrackId'] = null;
    }
}

function renderPlayer(array $storage): void
{
    $queue = $storage['queue'];
    $currentTrackId = $storage['currentTrackId'];

    require_once __DIR__ . '/../template/index.php';
}

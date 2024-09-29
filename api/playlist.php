<?php
header("Cache-Control: max-age=84000, public");
header('Content-Type: audio/x-mpegurl');
header('Content-Disposition: attachment; filename="playlist.m3u"');
function getAllChannelInfo(): array {
    $json = @file_get_contents('https://raw.githubusercontent.com/Skkings001/denkodrmai3/refs/heads/main/Channels.json');
    if ($json === false) {
        header("HTTP/1.1 500 Internal Server Error");
        exit;
    }
    $channels = json_decode($json, true);
    if ($channels === null) {
        header("HTTP/1.1 500 Internal Server Error");
        exit;
    }
    return $channels;
}
$channels = getAllChannelInfo();
$serverAddress = $_SERVER['HTTP_HOST'] ?? 'default.server.address';
$serverPort = $_SERVER['SERVER_PORT'] ?? '80';
$serverScheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$dirPath = dirname($requestUri);
$portPart = ($serverPort != '80' && $serverPort != '443') ? ":$serverPort" : '';
$m3u8PlaylistFile = "#EXTM3U x-tvg-url=\"https://www.tsepg.cf/epg.xml.gz\"\n";
foreach ($channels as $channel) {
    $id = $channel['id'];
    $dashUrl = $channel['streamData']['MPD='] ?? null;
    if ($dashUrl === null) {
        continue;
    }
    $extension = pathinfo(parse_url($dashUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    $playlistUrl = "https://$serverAddress/{$id}.$extension|X-Forwarded-For=59.178.72.184";
    $m3u8PlaylistFile .= "#EXTINF:-1 tvg-id=\"{$id}\" tvg-logo=\"https://mediaready.videoready.tv/tatasky-epg/image/fetch/f_auto,fl_lossy,q_auto,h_250,w_250/{$channel['channel_logo']}\" group-title=\"{$channel['channel_genre'][0]}\",{$channel['channel_name']}\n";
    $m3u8PlaylistFile .= "#KODIPROP:inputstream.adaptive.license_type=clearkey\n";
    $m3u8PlaylistFile .= "#KODIPROP:inputstream.adaptive.license_key=https://tpck.drmlive-01.workers.dev/?id={$id}\n";
    $m3u8PlaylistFile .= "#EXTVLCOPT:http-user-agent=third-party\n";
    $m3u8PlaylistFile .= "$playlistUrl\n\n";

<?php

function perform_handshake(Socket $client_socket, string $request): void
{
    // Récupération de la clé WebSocket envoyée par le client
    if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $request, $matches)) {
        $key = trim($matches[1]);

        // Création de la clé de réponse WebSocket
        $accept_key = base64_encode(pack('H*', sha1($key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11")));

        // Construction de la réponse HTTP de la poignée de main
        $response = "HTTP/1.1 101 Switching Protocols\r\n";
        $response .= "Upgrade: websocket\r\n";
        $response .= "Connection: Upgrade\r\n";
        $response .= "Sec-WebSocket-Accept: $accept_key\r\n\r\n";

        // Envoi de la réponse au client
        socket_write($client_socket, $response, strlen($response));
    }
}

function encode(string $text): string
{
    $b1 = 0x81;
    $length = strlen($text);
    $header = "";

    if ($length <= 125) {
        $header = pack('CC', $b1, $length);
    } elseif ($length > 125 && $length < 65536) {
        $header = pack('CCn', $b1, 126, $length);
    } else {
        $header = pack('CCNN', $b1, 127, $length);
    }

    return $header . $text;
}

function decode(string $payload): string
{
    $length = ord($payload[1]) & 127;
    if ($length == 126) {
        $masks = substr($payload, 4, 4);
        $data = substr($payload, 8);
    } elseif ($length == 127) {
        $masks = substr($payload, 10, 4);
        $data = substr($payload, 14);
    } else {
        $masks = substr($payload, 2, 4);
        $data = substr($payload, 6);
    }

    // Décodage des données en appliquant XOR
    $decoded = "";
    for ($i = 0; $i < strlen($data); $i++) {
        $decoded .= $data[$i] ^ $masks[$i % 4];
    }

    return $decoded;
}

<?php

/**
 * Effectue la poignée de main WebSocket avec un client.
 * 
 * Cette fonction analyse la requête HTTP envoyée par le client, extrait la clé WebSocket,
 * génère une clé d'acceptation et envoie la réponse HTTP nécessaire pour établir
 * la connexion WebSocket.
 * 
 * @param Socket $client_socket Socket du client WebSocket.
 * @param string $request       Requête HTTP de connexion WebSocket envoyée par le client.
 * @return void
 */
function handshake(Socket $client_socket, string $request): void
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

/**
 * Fonction pour encoder un message avec un masque de sécurité
 * 
 * Cette fonction est utilisée pour encoder un message à envoyer via WebSocket.
 * Le message est masqué selon les règles du protocole WebSocket, où un en-tête est ajouté
 * avant le texte et le texte est encodé avec un masque de sécurité.
 * 
 * L'en-tête contient les informations suivantes :
 * - Le premier octet (0x81) indique qu'il s'agit d'un message texte avec un masque appliqué.
 * - La longueur du message est encodée selon différentes tailles en fonction de la longueur réelle du texte.
 * 
 * Les longueurs possibles sont :
 * - Si la longueur est inférieure ou égale à 125, la longueur est codée sur un octet.
 * - Si la longueur est supérieure à 125 mais inférieure à 65536, elle est codée sur 2 octets.
 * - Si la longueur est supérieure à 65535, elle est codée sur 8 octets.
 * 
 * @param string $text Message texte à encoder.
 * @return string      Message encodé avec un en-tête et le texte masqué.
 */
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

/**
 * Fonction pour décoder un message WebSocket masqué
 * 
 * Cette fonction est utilisée pour décoder un message WebSocket qui a été masqué selon les règles du protocole WebSocket.
 * Le message est d'abord extrait de la chaîne binaire, puis les octets sont décodés en utilisant le masque de sécurité
 * fourni dans l'en-tête du message.
 * 
 * @param string $payload Message WebSocket masqué sous forme binaire.
 * @return string         Message texte décodé.
 */
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

/**
 * Fonction pour enregistrer un événement dans un fichier de Log.
 * 
 * @param string $message Message à enregistrer dans le Log.
 * @param string $type    Type d'événement.
 * @return void 
 */
function log_event(string $message, string $type = 'info'): void
{
    $logFile = __DIR__ . "/server.log";
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[" . $timestamp . "] [" . $type . "]" . $message . "\n";

    // Ajoute le message au fichier log, sans écraser les précédents
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

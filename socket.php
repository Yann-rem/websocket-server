<?php

require_once __DIR__ . "/functions.php";

// Définition de l'adresse et du port du serveur
$host = "0.0.0.0"; // Écoute sur toutes les interfaces réseaux
$port = 8080;

// Création de la socket principale
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    die("Erreur lors de la création de la socket : " . socket_strerror(socket_last_error()) . "\n");
}

// Liaison de la socket à l'adresse et au port définis
if (socket_bind($socket, $host, $port) === false) {
    die("Erreur lors de la liaison de la socket : " . socket_strerror(socket_last_error($socket)) . "\n");
}

// Mise en écoute de la socket
if (socket_listen($socket) === false) {
    die("Erreur lors de l'écoute de la socket : " . socket_strerror(socket_last_error($socket)) . "\n");
}

echo "Serveur WebSocket démarré sur ws://$host:$port\n";

// Définition du nombre maximum de clients autorisés
define('MAX_CLIENTS', 50);
$clients = []; // Tableau des clients connectés

// Boucle principale du serveur
while (true) {
    // Préparation des sockets à surveiller
    $changed_sockets = $clients;
    $changed_sockets[] = $socket;

    socket_select($changed_sockets, $null, $null, 0, 10);

    // Vérification des nouvelles connexions
    if (in_array($socket, $changed_sockets)) {
        if (count($clients) >= MAX_CLIENTS) {
            $temp_socket = socket_accept($socket);

            if ($temp_socket) {
                $msg = "Trop de connexions, réessayez plus tard.";
                socket_write($temp_socket, encode($msg));
                socket_close($temp_socket);
            }
        } else {
            $new_socket = socket_accept($socket);

            if ($new_socket === false) {
                echo "Erreur lors de l'acceptation d'un client : " . socket_strerror(socket_last_error($socket)) . "\n";
            } else {
                $clients[] = $new_socket;
                $request = socket_read($new_socket, 1024);

                if ($request === false) {
                    echo "Erreur lors de la lecture de la requête de handshake : " . socket_strerror(socket_last_error($new_socket)) . "\n";
                    socket_close($new_socket);
                } else {
                    perform_handshake($new_socket, $request);
                    echo "Nouveau client connecté !\n";
                }
            }
        }

        // Suppression du socket traité de la liste des sockets modifiés
        unset($changed_sockets[array_search($socket, $changed_sockets)]);
    }

    // Lecture des messages des clients existants
    foreach ($changed_sockets as $client_socket) {
        $message = @socket_read($client_socket, 1024);

        // Vérification si le client s'est déconnecté ou s'il y a une erreur
        if ($message === false || $message === "") {
            echo "Client déconnecté ou erreur de lecture : " . socket_strerror(socket_last_error($client_socket)) . "\n";
            $key = array_search($client_socket, $clients);
            socket_close($client_socket);
            unset($clients[$key]);
            continue;
        }

        // Décodage du message
        $decoded_message = decode(trim($message));
        echo "Message reçu : $decoded_message\n";

        // Réponse au client en encodant le message
        $response = encode("Message bien reçu !");
        if (socket_write($client_socket, $response, strlen($response)) === false) {
            echo "Erreur lors de l'écriture du message : " . socket_strerror(socket_last_error($client_socket)) . "\n";
        }
    }
}

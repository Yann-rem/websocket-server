# WebSocket Server en PHP

Ce projet est un serveur WebSocket simple en PHP permettant de gérer les connexions des clients via le protocole WebSocket. Il inclut la gestion de la poignée de main (handshake) et l'échange de messages entre le serveur et les clients.

## Fonctionnalités

- **Serveur WebSocket de base** : Écoute sur un port spécifié et accepte les connexions WebSocket entrantes.
- **Poignée de main WebSocket** : Effectue la mise à niveau du protocole HTTP vers WebSocket et génère la réponse appropriée pour valider la connexion.
- **Gestion des messages** : Envoi et réception de messages entre le serveur et les clients connectés.
- **Support de plusieurs clients** : Gère plusieurs connexions simultanées tout en envoyant des messages aux clients.

## Prérequis

Pour exécuter ce projet, vous devez avoir installé PHP sur votre machine.

- PHP 7.4 ou version supérieure (la version 8.0 est recommandée).
- Accès à une machine ou un environnement local pour tester le serveur WebSocket.

## Installation

1. Clonez ce dépôt ou téléchargez-le sur votre machine locale.

```bash
git clone https://github.com/Yann-rem/websocket-server.git
```

2. Allez dans le répertoire du projet.

```bash
cd websocket-server
```

3. Vous pouvez démarrer le serveur en exécutant le fichier `socket.php` avec PHP.

```bash
php socket.php
```

Cela démarrera le serveur WebSocket qui écoutera sur `ws://0.0.0.0:8080`.

## Utilisation

### Serveur

- Le serveur écoute les connexions WebSocket sur l'adresse `ws://0.0.0.0:8080`.
- Lorsqu'un client se connecte, la poignée de main est effectuée et une communication bidirectionnelle est établie.
- Le serveur reçoit les messages des clients, les décode et renvoie une réponse.

### Client WebSocket

Vous pouvez utiliser un client WebSocket, soit via un navigateur, soit via un script client en JavaScript pour vous connecter à ce serveur.

#### Exemple de code JavaScript pour un client :

```javascript
const socket = new WebSocket("ws://localhost:8080");

socket.onopen = () => {
  console.log("Connecté au serveur WebSocket");
  socket.send("Salut, serveur !");
};

socket.onmessage = (event) => {
  console.log("Message du serveur:", event.data);
};

socket.onclose = () => {
  console.log("Déconnexion du serveur");
};
```

Ce code crée un client WebSocket simple, qui se connecte au serveur WebSocket et envoie un message.

### Tester avec le terminal

Vous pouvez aussi utiliser un client WebSocket via un fichier PHP ou tout autre outil comme [websocat](https://github.com/vi/websocat).

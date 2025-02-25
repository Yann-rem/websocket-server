const socket = new WebSocket("ws://localhost:8080");

// Connexion ouverte
socket.onopen = () => {
  logMessage("🟢 Connecté au serveur WebSocket !");
};

// Réception de messages
socket.onmessage = (event) => {
  try {
    const data = JSON.parse(event.data);
    logMessage(`📩 Message reçu : ${data.message}`);
  } catch (error) {
    logMessage("⚠️ Erreur : Réception de données invalides.");
  }
};

// Gestion des erreurs
socket.onerror = () => {
  logMessage("❌ Erreur WebSocket détectée.");
};

// Fermeture de connexion
socket.onclose = () => {
  logMessage("🔴 Connexion fermée.");
};

const messageInput = document.querySelector(".message-input");
const messagesDiv = document.querySelector(".messages");

// Fonction pour envoyer un message
const sendMessage = () => {
  const message = messageInput.value.trim();

  if (message) {
    const jsonMessage = JSON.stringify({ message: message });
    socket.send(jsonMessage);
    logMessage(`📤 Envoyé : ${message}`);
    messageInput.value = "";
  }
};

// Fonction pour afficher les messages
const logMessage = (message) => {
  const newMessage = document.createElement("p");
  newMessage.textContent = message;
  messagesDiv.appendChild(newMessage);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
};

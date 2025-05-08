import express from 'express';
import { createServer } from 'http';
import { Server } from 'socket.io';

const app = express();
const server = createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

const PORT = 3000;

// Servir les fichiers statiques
app.use(express.static('public'));

// WebSocket : Gestion des connexions
io.on('connection', (socket) => {
    console.log(`✅ Un utilisateur est connecté : ${socket.id}`);

    socket.on('new_post', (post) => {
        console.log("📢 Nouveau post reçu :", post);
        io.emit('new_post', post);
    });

    socket.on('disconnect', () => {
        console.log(`❌ Utilisateur déconnecté : ${socket.id}`);
    });
});

// Démarrer le serveur
server.listen(PORT, () => {
    console.log(`🚀 Serveur WebSocket actif sur http://localhost:${PORT}`);
});

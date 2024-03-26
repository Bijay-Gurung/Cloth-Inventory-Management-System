const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = socketIo(server);

let inventory = [];

io.on('connection', (socket) => {
    console.log('A client connected');

    // Send initial inventory data to the client
    socket.emit('initialInventory', inventory);

    // Handle adding items
    socket.on('addItem', (item) => {
        inventory.push(item);
        io.emit('updateInventory', inventory);
    });

    // Handle deleting items
    socket.on('deleteItem', (itemId) => {
        inventory = inventory.filter(item => item.id !== itemId);
        io.emit('updateInventory', inventory);
    });

    // Handle client disconnections
    socket.on('disconnect', () => {
        console.log('A client disconnected');
    });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});

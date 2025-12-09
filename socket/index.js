const express = require('express');
const { createServer } = require('http');
const { join } = require('path');
const {Server} = require('socket.io');
const cors = require('cors');

const app = express();
const server = createServer(app);
const io = new Server(server,{
    cors: {
        origin: '*',
    }
});
// env
require('dotenv').config();

app.use(cors({
    origin: '*'
}));
app.get('/', (req, res) => {
    res.sendFile(join(__dirname, 'index.html'));
});
// ruta cors mandar get
app.get('/send', (req, res) => {
    io.emit('reservas', 'Hola desde el servidor');
    res.send('Mensaje enviado');
});
app.get('/silSolicitud', (req, res) => {
    io.emit('silSolicitud', 'Hola desde el servidor');
    res.send('Mensaje enviado');
});
io.on('connection', (socket) => {
    // console.log('a user connected');
    socket.on('disconnect', () => {
        console.log('user disconnected');
    });
    socket.on('reservas-Ayacucho', (msg) => {
        console.log('message: ' + msg);
        io.emit('reservas-ayacucho', msg);
    });
    socket.on('reservas-Oquendo', (msg) => {
        console.log('message: ' + msg);
        io.emit('reservas-oquendo', msg);
    });
    // reservas
    socket.on('reservas', (msg) => {
        console.log('reservas: ' + msg);
        io.emit('reservas', msg);
    });
    socket.broadcast.emit('hi');
});

port=process.env.PORT || 3000
server.listen(port, () => {
    console.log('http://localhost:'+port);
});
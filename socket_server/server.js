/**
 * SOLARIS REAL-TIME WEBSOCKET & SOCKET.IO SERVER
 * Phục vụ đồng bộ dữ liệu thời gian thực và tự động làm mới mượt mà
 * Port: 3001
 */

const http = require('http');
const { Server } = require('socket.io');

const PORT = process.env.PORT || 3001;

const server = http.createServer((req, res) => {
    // CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    const url = new URL(req.url, `http://localhost:${PORT}`);

    // Health check endpoint
    if (url.pathname === '/health' || url.pathname === '/') {
        res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
        res.end(JSON.stringify({
            status: 'online',
            service: 'Solaris Realtime Gateway',
            clients: io.engine.clientsCount,
            timestamp: new Date().toISOString()
        }));
        return;
    }

    // HTTP Broadcast endpoint: cho phép PHP hoặc cURL kích hoạt sự kiện realtime
    if (url.pathname === '/broadcast') {
        const eventName = url.searchParams.get('event') || 'solar:action';
        const actionType = url.searchParams.get('type') || 'generic_action';
        const message = url.searchParams.get('message') || 'Thao tác dữ liệu thành công';
        const payload = {
            event: eventName,
            type: actionType,
            message: message,
            timestamp: Date.now()
        };

        io.emit(eventName, payload);
        console.log(`[HTTP Broadcast] Emitted '${eventName}' with type: '${actionType}' to ${io.engine.clientsCount} clients`);

        res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
        res.end(JSON.stringify({ success: true, emitted: payload, clients: io.engine.clientsCount }));
        return;
    }

    res.writeHead(404, { 'Content-Type': 'text/plain' });
    res.end('Not Found');
});

// Thiết lập Socket.IO với cấu hình CORS toàn diện
const io = new Server(server, {
    cors: {
        origin: '*',
        methods: ['GET', 'POST']
    },
    transports: ['websocket', 'polling']
});

io.on('connection', (socket) => {
    console.log(`[Socket.io] Client connected: ${socket.id} (Total: ${io.engine.clientsCount})`);

    // Gửi lời chào chào đón và xác nhận kết nối thành công
    socket.emit('solar:connected', {
        clientId: socket.id,
        serverTime: Date.now(),
        message: 'Kết nối mạng lưới điều khiển năng lượng mặt trời Solaris thành công'
    });

    // Lắng nghe sự kiện người dùng thực hiện thao tác (chuyển slide, thêm giỏ hàng, đổi vai trò, v.v.)
    socket.on('solar:action', (data) => {
        console.log(`[Action Received from ${socket.id}]:`, data);
        // Phát lại sự kiện cho tất cả các client khác (hoặc cả chính nó nếu yêu cầu reload)
        io.emit('solar:action', {
            ...data,
            senderId: socket.id,
            timestamp: Date.now()
        });
    });

    // Lắng nghe sự kiện chuyển slide trên Home
    socket.on('solar:slide_change', (data) => {
        socket.broadcast.emit('solar:slide_change', data);
    });

    // Lắng nghe sự kiện cập nhật Kế hoạch thiết bị dự định
    socket.on('solar:plan_update', (data) => {
        io.emit('solar:plan_update', data);
    });

    socket.on('disconnect', (reason) => {
        console.log(`[Socket.io] Client disconnected: ${socket.id} (Reason: ${reason})`);
    });
});

server.listen(PORT, () => {
    console.log(`=======================================================`);
    console.log(`⚡ SOLARIS REALTIME SOCKET.IO SERVER RUNNING ON PORT ${PORT}`);
    console.log(`   Health check: http://localhost:${PORT}/health`);
    console.log(`   HTTP broadcast: http://localhost:${PORT}/broadcast?event=solar:action&type=test`);
    console.log(`=======================================================`);
});

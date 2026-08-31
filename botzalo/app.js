require('dotenv').config();
const express = require('express');
const cors = require('cors');

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(cors());

// ================= CẤU HÌNH BẢO MẬT & MÔI TRƯỜNG =================
const API_KEY = process.env.API_KEY;
const PHP_WEBHOOK_URL = process.env.ZALO_BOT_WEBHOOK_URL;
const PORT = process.env.PORT || 3000;

// ================= ENDPOINTS BOT ZALO MICROSERVICE =================

// 1. API Gửi thông báo tới học sinh qua Bot Zalo
app.post(['/send-bot', '/api/send-bot'], async (req, res) => {
    const { api_key, chat_id, message, title } = req.body;

    if (api_key && api_key !== API_KEY) {
        return res.status(401).json({ status: "error", message: "Sai API Key." });
    }

    if (!chat_id || !message) {
        return res.status(400).json({ status: "error", message: "Thiếu chat_id hoặc message." });
    }

    try {
        console.log(`[Bot Zalo] Đã gửi thông báo tới ChatID: ${chat_id} | Tiêu đề: ${title || 'Thông báo'}`);
        res.json({
            status: "success",
            message: "Đã gửi thông báo qua Bot Zalo thành công.",
            chat_id: chat_id
        });
    } catch (error) {
        console.error(`[Bot Zalo Error] ${error.message}`);
        res.status(500).json({ status: "error", message: error.message });
    }
});

// 2. Endpoint Webhook tiếp nhận tin nhắn từ Zalo (học sinh nhắn số CCCD) và chuyển tiếp tới PHP Backend
app.post(['/zalo-webhook', '/api/zalo-webhook'], async (req, res) => {
    try {
        const response = await fetch(PHP_WEBHOOK_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(req.body)
        });

        const data = await response.json();
        return res.json(data);
    } catch (err) {
        console.error("[Webhook Error]:", err.message);
        return res.status(500).json({ 
            success: false, 
            status: "error", 
            message: "Lỗi kết nối máy chủ xác thực: " + err.message 
        });
    }
});

// 3. Health Check
app.get(['/', '/status'], (req, res) => {
    res.json({
        status: "online",
        service: "Zalo Bot Microservice THPT Bình Sơn",
        time: new Date().toISOString()
    });
});

app.listen(PORT, () => console.log(`[Bot Zalo Service] Đang chạy trên cổng ${PORT}`));
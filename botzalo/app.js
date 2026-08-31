require('dotenv').config();
const express = require('express');
const cors = require('cors');

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(cors());

// ================= CẤU HÌNH BẢO MẬT & MÔI TRƯỜNG =================
const API_KEY = process.env.API_KEY;
const ZALO_BOT_TOKEN = process.env.ZALO_BOT_TOKEN || '528220222251220927:cfSCnPkmesSRlprCpQgdphHYlzbKjojSajCzxdKXaMESSDMexlvHSRCGvUQllPyx';
const PHP_WEBHOOK_URL = process.env.ZALO_BOT_WEBHOOK_URL || 'https://c3binhson.edu.vn/thidua/api/zalo-bot-webhook';
const PORT = process.env.PORT || 3000;

// ================= ENDPOINTS BOT ZALO MICROSERVICE =================

// 1. API Gửi thông báo tới học sinh qua Zalo Bot Platform
app.post(['/send-bot', '/api/send-bot'], async (req, res) => {
    const { api_key, chat_id, message, title } = req.body;

    if (api_key && api_key !== API_KEY) {
        return res.status(401).json({ status: "error", message: "Sai API Key." });
    }

    if (!chat_id || !message) {
        return res.status(400).json({ status: "error", message: "Thiếu chat_id hoặc message." });
    }

    try {
        const fullText = title ? `[THPT BÌNH SƠN - ${title.toUpperCase()}]\n\n${message}` : message;

        // Gửi trực tiếp tới Zalo Bot Platform
        const botApiUrl = `https://bot-api.zaloplatforms.com/bot${ZALO_BOT_TOKEN}/sendMessage`;
        const botResponse = await fetch(botApiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                chat_id: String(chat_id),
                text: fullText
            })
        });

        const botData = await botResponse.json().catch(() => ({}));
        console.log(`[Bot Zalo] Đã gửi tới ChatID: ${chat_id} | Kết quả:`, botData);

        res.json({
            status: "success",
            message: "Đã gửi thông báo qua Bot Zalo thành công.",
            chat_id: chat_id,
            result: botData
        });
    } catch (error) {
        console.error(`[Bot Zalo Error] ${error.message}`);
        res.status(500).json({ status: "error", message: error.message });
    }
});

// 2. Endpoint Webhook tiếp nhận tin nhắn từ Zalo (học sinh nhắn số CCCD) và chuyển tiếp tới PHP Backend
app.post(['/zalo-webhook', '/api/zalo-webhook'], async (req, res) => {
    try {
        console.log(`[Bot Webhook] Nhận request:`, JSON.stringify(req.body));
        const response = await fetch(PHP_WEBHOOK_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(req.body)
        });

        const data = await response.json().catch(() => ({ ok: true }));
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
        webhook_target: PHP_WEBHOOK_URL,
        time: new Date().toISOString()
    });
});

app.listen(PORT, () => console.log(`[Bot Zalo Service] Đang chạy trên cổng ${PORT} | Kết nối Webhook: ${PHP_WEBHOOK_URL}`));
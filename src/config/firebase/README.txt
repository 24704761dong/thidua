Vui lòng đặt tệp service-account.json của Firebase vào thư mục này để kích hoạt tính năng thông báo Push FCM v1.

Tệp của bạn phải có tên chính xác là: service-account.json
Và chứa nội dung giống thế này:
{
  "type": "service_account",
  "project_id": "...",
  "private_key_id": "...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
  "client_email": "...",
  "client_id": "...",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "..."
}

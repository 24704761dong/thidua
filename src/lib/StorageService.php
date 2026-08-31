<?php
// File: src/lib/StorageService.php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/bootstrap.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class StorageService {
    private $client;
    private $bucket;

    public function __construct() {
        // Đọc cấu hình từ file .env
        $this->bucket = $_ENV['R2_BUCKET_NAME'];

        // Một số cấu hình cũ dùng endpoint dạng "{bucket}.{account}.r2.cloudflarestorage.com"
        // làm AWS SDK cố tạo host con nữa và DNS sẽ thất bại. Chuẩn hóa endpoint về host account
        // và ép dùng path-style để tránh thêm subdomain.
        $rawEndpoint = rtrim($_ENV['R2_ENDPOINT_URL'], '/');
        $endpointHost = parse_url($rawEndpoint, PHP_URL_HOST) ?: '';
        $endpointScheme = parse_url($rawEndpoint, PHP_URL_SCHEME) ?: 'https';
        $endpointPath = parse_url($rawEndpoint, PHP_URL_PATH) ?: '';

        if (stripos($endpointHost, $this->bucket . '.') === 0) {
            // Bỏ tiền tố bucket. để còn lại host tài khoản R2
            $endpointHost = substr($endpointHost, strlen($this->bucket) + 1);
        }

        $normalizedEndpoint = $endpointScheme . '://' . $endpointHost . $endpointPath;

        $this->client = new S3Client([
            'version' => 'latest',
            'region'  => 'auto', // R2 yêu cầu 'auto'
            'endpoint' => $normalizedEndpoint,
            'use_path_style_endpoint' => true, // tránh DNS subdomain bucket
            'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4], // ép IPv4 để tránh lỗi DNS/IPV6
            'credentials' => [
                'key'    => $_ENV['R2_ACCESS_KEY_ID'],
                'secret' => $_ENV['R2_SECRET_ACCESS_KEY'],
            ],
        ]);
    }

    /**
     * Tải một file từ server local lên mây (R2).
     * @param string $localPath Đường dẫn file trên hosting (ví dụ: /var/www/...)
     * @param string $cloudKey Tên file trên mây (ví dụ: 'nhatky/file.jpg')
     */
    public function upload($localPath, $cloudKey) {
        return $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $cloudKey,
            'SourceFile' => $localPath,
        ]);
    }

    /**
     * Tạo một link tạm thời (chỉ có hiệu lực 10 phút) để xem file
     * @param string $cloudKey Tên file trên mây
     * @param string $expiry Thời gian hết hạn (ví dụ: '+10 minutes')
     * @return string URL đầy đủ để truy cập file
     */
    public function getTemporaryUrl($cloudKey, $expiry = '+10 minutes') {
        $cmd = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key'    => $cloudKey
        ]);
        $request = $this->client->createPresignedRequest($cmd, $expiry);
        return (string) $request->getUri();
    }

    /**
     * Tải file trên mây về một đường dẫn local.
     */
    public function downloadToPath($cloudKey, $localPath) {
        return $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $cloudKey,
            'SaveAs' => $localPath,
        ]);
    }

    /**
     * Lấy nội dung file từ mây dưới dạng chuỗi binary.
     */
    public function getFileContent($cloudKey) {
        $result = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $cloudKey,
        ]);
        return (string) $result['Body'];
    }

    /**
     * Xóa file khỏi mây.
     */
    public function delete($cloudKey) {
        return $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key'    => $cloudKey,
        ]);
    }
}
?>
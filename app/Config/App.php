<?php
namespace App\Config;

class App {
    public static function getConfig(): array {
        return [
            'name'         => 'PrintMaster ERP',
            'version'      => '1.0.0',
            'base_url'     => getenv('APP_URL') ?: '',
            'timezone'     => 'Asia/Jakarta',
            'upload_path'  => dirname(__DIR__, 2) . '/storage/uploads',
            'max_upload_size' => 25 * 1024 * 1024, // 25 MB
            'allowed_upload_mimes' => [
                'image/jpeg', 'image/png', 'image/webp',
                'application/pdf', 'application/zip',
                'application/x-zip-compressed',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]
        ];
    }
}

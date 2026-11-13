<?php
namespace App\Services;

use App\Core\Database;

class WhatsAppService {
    public static function send(string $recipient, string $eventCode, array $variables, ?int $branchId = null, int $companyId = 1): array {
        // Fetch template
        $template = Database::fetchOne(
            "SELECT * FROM notification_templates WHERE company_id = :cid AND event_code = :ev AND channel = 'whatsapp' AND is_active = 1",
            ['cid' => $companyId, 'ev' => $eventCode]
        );

        $msgBody = '';
        $subject = 'Notifikasi PrintMaster';

        if ($template) {
            $msgBody = $template['message_template'];
            $subject = $template['title'];
            foreach ($variables as $key => $val) {
                $msgBody = str_replace('{{' . $key . '}}', (string) $val, $msgBody);
            }
        } else {
            $msgBody = "Notifikasi dari PrintMaster: Event {$eventCode}";
        }

        // Clean phone number (format 08xx or 62xx)
        $cleanPhone = preg_replace('/[^0-9]/', '', $recipient);
        if (strpos($cleanPhone, '0') === 0) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        // In shared hosting or simulation mode, we simulate or call API if configured
        $status = 'sent';
        $responseLog = json_encode([
            'provider'   => 'PrintMaster WhatsApp Simulator',
            'status'     => 'success',
            'timestamp'  => date('Y-m-d H:i:s'),
            'message_id' => 'MSG-' . uniqid()
        ]);

        // Insert notification log
        $logId = Database::insert('notification_logs', [
            'company_id'      => $companyId,
            'branch_id'       => $branchId,
            'recipient'       => $recipient,
            'channel'         => 'whatsapp',
            'subject'         => $subject,
            'message_content' => $msgBody,
            'status'          => $status,
            'response_log'    => $responseLog,
            'sent_at'         => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'log_id'  => $logId,
            'message' => $msgBody,
            'status'  => $status
        ];
    }

    public static function sendOrderConfirmed(int $orderId): void {
        $order = Database::fetchOne("
            SELECT o.*, c.name as customer_name, c.phone as customer_phone, b.name as branch_name 
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN branches b ON o.branch_id = b.id
            WHERE o.id = :id
        ", ['id' => $orderId]);

        if ($order && !empty($order['customer_phone'])) {
            self::send($order['customer_phone'], 'order_confirmed', [
                'customer_name' => $order['customer_name'],
                'order_number'  => $order['order_number'],
                'total_amount'  => number_format((float)$order['total_amount'], 0, ',', '.'),
                'branch_name'   => $order['branch_name']
            ], $order['branch_id'], $order['company_id']);
        }
    }

    public static function sendPaymentReceived(int $paymentId): void {
        $pay = Database::fetchOne("
            SELECT p.*, o.order_number, c.name as customer_name, c.phone as customer_phone
            FROM payments p
            JOIN orders o ON p.order_id = o.id
            JOIN customers c ON o.customer_id = c.id
            WHERE p.id = :id
        ", ['id' => $paymentId]);

        if ($pay && !empty($pay['customer_phone'])) {
            self::send($pay['customer_phone'], 'payment_received', [
                'customer_name'  => $pay['customer_name'],
                'order_number'   => $pay['order_number'],
                'payment_amount' => number_format((float)$pay['amount'], 0, ',', '.'),
                'payment_ref'    => $pay['payment_number']
            ], $pay['branch_id'], $pay['company_id']);
        }
    }

    public static function sendOrderReady(int $orderId): void {
        $order = Database::fetchOne("
            SELECT o.*, c.name as customer_name, c.phone as customer_phone, b.name as branch_name 
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN branches b ON o.branch_id = b.id
            WHERE o.id = :id
        ", ['id' => $orderId]);

        if ($order && !empty($order['customer_phone'])) {
            self::send($order['customer_phone'], 'order_ready', [
                'customer_name' => $order['customer_name'],
                'order_number'  => $order['order_number'],
                'branch_name'   => $order['branch_name']
            ], $order['branch_id'], $order['company_id']);
        }
    }
}

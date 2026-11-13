<?php
namespace App\Services;

use App\Core\Database;
use Exception;

class OrderService {
    /**
     * Creates an order with items, payments, jobs, and journals in an ACID transaction.
     */
    public static function createOrder(array $data, int $userId): array {
        $companyId   = (int) ($data['company_id'] ?? 1);
        $branchId    = (int) ($data['branch_id'] ?? 1);
        $customerId  = (int) ($data['customer_id'] ?? 1);
        $orderDate   = $data['order_date'] ?? date('Y-m-d');
        $deadline    = $data['deadline_date'] ?? date('Y-m-d 18:00:00', strtotime('+1 day'));
        $pickupType  = $data['pickup_delivery_type'] ?? 'pickup';
        $shippingAddr= $data['shipping_address'] ?? null;
        $voucherCode = $data['voucher_code'] ?? null;
        $notes       = $data['notes'] ?? null;
        $items       = $data['items'] ?? [];
        $initialPay  = (float) ($data['initial_payment'] ?? 0);
        $payMethod   = $data['payment_method'] ?? 'cash';
        $bankRef     = $data['bank_reference'] ?? null;

        if (empty($items)) {
            throw new Exception("Pesanan harus memiliki minimal satu item.");
        }

        // Branch info for invoice prefix
        $branch = Database::fetchOne("SELECT * FROM branches WHERE id = :id", ['id' => $branchId]);
        $branchCode = $branch['code'] ?? 'JKT01';

        // Unique Order Number ORD-YYYYMM-XXXX
        $ym = date('Ym');
        $orderCount = (int) Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE order_number LIKE 'ORD-{$ym}-%'");
        $orderSeq = str_pad($orderCount + 1, 4, '0', STR_PAD_LEFT);
        $orderNumber = "ORD-{$ym}-{$orderSeq}";

        // Invoice Number INV-{BRANCH}-{YYMM}-XXXX
        $yymm = date('ym');
        $invCount = (int) Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE invoice_number LIKE 'INV-{$branchCode}-{$yymm}-%'");
        $invSeq = str_pad($invCount + 1, 4, '0', STR_PAD_LEFT);
        $invoiceNumber = "INV-{$branchCode}-{$yymm}-{$invSeq}";

        // Calculate totals
        $subtotal = 0.0;
        $totalCost = 0.0;
        foreach ($items as $item) {
            $subtotal += (float) ($item['subtotal'] ?? 0);
            $totalCost += (float) ($item['cost_estimate'] ?? 0);
        }

        $discountAmount = (float) ($data['discount_amount'] ?? 0);
        
        // Check voucher if provided
        if (!empty($voucherCode)) {
            $promo = Database::fetchOne("SELECT * FROM promotions WHERE code = :code AND is_active = 1 AND CURDATE() BETWEEN start_date AND end_date", ['code' => $voucherCode]);
            if ($promo) {
                if ($promo['discount_type'] === 'percentage') {
                    $disc = ($subtotal * ((float)$promo['discount_value'] / 100));
                    if ((float)$promo['max_discount'] > 0) {
                        $disc = min($disc, (float)$promo['max_discount']);
                    }
                    $discountAmount += $disc;
                } else {
                    $discountAmount += (float)$promo['discount_value'];
                }
                Database::query("UPDATE promotions SET used_count = used_count + 1 WHERE id = :id", ['id' => $promo['id']]);
            }
        }

        $taxAmount = (float) ($data['tax_amount'] ?? 0);
        $totalAmount = max(0.0, ($subtotal - $discountAmount) + $taxAmount);

        $initialPay = min($initialPay, $totalAmount);
        $remainingAmount = max(0.0, $totalAmount - $initialPay);
        
        $paymentStatus = 'unpaid';
        if ($initialPay >= $totalAmount && $totalAmount > 0) {
            $paymentStatus = 'paid';
        } elseif ($initialPay > 0) {
            $paymentStatus = 'partial';
        }

        $initialOrderStatus = ($paymentStatus === 'paid' || $initialPay > 0) ? 'queued' : 'awaiting_payment';

        Database::beginTransaction();
        try {
            // 1. Insert Order
            $orderId = Database::insert('orders', [
                'company_id'           => $companyId,
                'branch_id'            => $branchId,
                'order_number'         => $orderNumber,
                'invoice_number'       => $invoiceNumber,
                'customer_id'          => $customerId,
                'order_date'           => $orderDate,
                'deadline_date'        => $deadline,
                'pickup_delivery_type' => $pickupType,
                'shipping_address'     => $shippingAddr,
                'subtotal'             => $subtotal,
                'discount_amount'      => $discountAmount,
                'voucher_code'         => $voucherCode,
                'tax_amount'           => $taxAmount,
                'total_amount'         => $totalAmount,
                'paid_amount'          => $initialPay,
                'remaining_amount'     => $remainingAmount,
                'payment_status'       => $paymentStatus,
                'status'               => $initialOrderStatus,
                'notes'                => $notes,
                'created_by'           => $userId
            ]);

            // 2. Insert Items and Production Jobs
            foreach ($items as $idx => $item) {
                $itemName = $item['item_name'] ?? (Database::fetchColumn("SELECT name FROM products WHERE id = :id", ['id' => (int)$item['product_id']]) ?: 'Item Percetakan');
                $width = (float) ($item['width_cm'] ?? ($item['width'] ?? 0));
                $length = (float) ($item['height_cm'] ?? ($item['length'] ?? 0));
                $area = (float) ($item['area_sqm'] ?? (($width * $length) / 10000));
                $qty = (float) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $itemSubtotal = (float) ($item['subtotal'] ?? ($unitPrice * $qty));

                $orderItemId = Database::insert('order_items', [
                    'order_id'            => $orderId,
                    'product_id'          => (int) $item['product_id'],
                    'item_name'           => $itemName,
                    'description'         => $item['description'] ?? null,
                    'specs_json'          => isset($item['specs_json']) ? (is_string($item['specs_json']) ? $item['specs_json'] : json_encode($item['specs_json'])) : null,
                    'width_cm'            => $width,
                    'height_cm'           => $length,
                    'area_sqm'            => $area,
                    'quantity'            => $qty,
                    'unit'                => $item['unit'] ?? 'pcs',
                    'unit_price'          => $unitPrice,
                    'cost_estimate'       => (float) ($item['cost_estimate'] ?? 0),
                    'subtotal'            => $itemSubtotal,
                    'discount'            => (float) ($item['discount'] ?? 0),
                    'tax'                 => (float) ($item['tax'] ?? 0),
                    'total'               => (float) ($item['total'] ?? $itemSubtotal),
                    'finishing_details'   => $item['finishing_details'] ?? null,
                    'price_snapshot_json' => isset($item['price_snapshot']) ? json_encode($item['price_snapshot']) : null
                ]);

                // Create Production Job for each item
                $jobNumber = "JOB-{$yymm}-" . str_pad($orderId, 4, '0', STR_PAD_LEFT) . "-" . ($idx + 1);
                $jobId = Database::insert('production_jobs', [
                    'company_id'       => $companyId,
                    'source_branch_id' => $branchId,
                    'target_branch_id' => $branchId,
                    'order_id'         => $orderId,
                    'order_item_id'    => $orderItemId,
                    'job_number'       => $jobNumber,
                    'priority'         => $data['priority'] ?? 'normal',
                    'status'           => 'queued',
                    'scheduled_date'   => date('Y-m-d'),
                    'notes'            => "Job produksi untuk item #{$orderItemId}: {$itemName}"
                ]);

                // Create standard steps (prepress, printing, finishing, packing)
                $steps = ['prepress', 'printing', 'finishing', 'packing'];
                foreach ($steps as $seq => $st) {
                    Database::insert('production_steps', [
                        'production_job_id' => $jobId,
                        'step_name'         => $st,
                        'sequence'          => $seq + 1,
                        'status'            => 'pending'
                    ]);
                }
            }

            // 3. Status History
            Database::insert('order_status_history', [
                'order_id'   => $orderId,
                'old_status' => null,
                'new_status' => $initialOrderStatus,
                'changed_by' => $userId,
                'notes'      => 'Order baru dibuat melalui sistem'
            ]);

            // 4. Initial Payment if provided
            if ($initialPay > 0) {
                $payYm = date('ym');
                $payCount = (int) Database::fetchColumn("SELECT COUNT(*) FROM payments WHERE payment_number LIKE 'PAY-{$branchCode}-{$payYm}-%'");
                $paySeq = str_pad($payCount + 1, 4, '0', STR_PAD_LEFT);
                $paymentNumber = "PAY-{$branchCode}-{$payYm}-{$paySeq}";

                Database::insert('payments', [
                    'company_id'       => $companyId,
                    'branch_id'        => $branchId,
                    'order_id'         => $orderId,
                    'payment_number'   => $paymentNumber,
                    'payment_type'     => 'order_payment',
                    'payment_method'   => $payMethod,
                    'amount'           => $initialPay,
                    'payment_date'     => date('Y-m-d H:i:s'),
                    'reference_number' => $bankRef ?: ('PAY-' . time()),
                    'verified_by'      => $userId,
                    'status'           => 'verified',
                    'idempotency_key'  => 'INIT-' . $orderId . '-' . time(),
                    'notes'            => 'Pembayaran awal saat pembuatan order',
                    'created_by'       => $userId
                ]);

                // Update shift cash if payment is cash
                if ($payMethod === 'cash') {
                    Database::query(
                        "UPDATE cash_shifts SET cash_sales = cash_sales + :amt, expected_balance = expected_balance + :amt 
                         WHERE branch_id = :bid AND status = 'open' LIMIT 1",
                        ['amt' => $initialPay, 'bid' => $branchId]
                    );
                }
            }

            // 5. Accounting Journal Posting
            $cashAccId = ($branchId === 2) ? 2 : (($branchId === 3) ? 3 : 1);
            $payAccId = ($payMethod === 'cash') ? $cashAccId : 4; // BCA for transfer/qris

            $journalEntries = [];
            if ($initialPay > 0) {
                $journalEntries[] = [
                    'account_id' => $payAccId,
                    'debit'      => $initialPay,
                    'credit'     => 0.00,
                    'memo'       => 'Penerimaan pembayaran order ' . $orderNumber
                ];
            }
            if ($remainingAmount > 0) {
                $journalEntries[] = [
                    'account_id' => 6, // 1130 Piutang Usaha
                    'debit'      => $remainingAmount,
                    'credit'     => 0.00,
                    'memo'       => 'Piutang sisa order ' . $orderNumber
                ];
            }
            if ($totalAmount > 0) {
                $journalEntries[] = [
                    'account_id' => 20, // 4110 Pendapatan Cetak
                    'debit'      => 0.00,
                    'credit'     => $totalAmount,
                    'memo'       => 'Pendapatan penjualan order ' . $orderNumber
                ];
            }

            if (!empty($journalEntries) && ($initialPay > 0 || $remainingAmount > 0)) {
                AccountingService::postJournal(
                    $branchId,
                    $orderDate,
                    'order',
                    $orderId,
                    "Penjualan order {$orderNumber} ({$invoiceNumber})",
                    $journalEntries,
                    $userId,
                    $companyId
                );
            }

            // Update customer loyalty points (1 point per Rp 10.000)
            $pts = (int) floor($totalAmount / 10000);
            if ($pts > 0) {
                Database::query("UPDATE customers SET loyalty_points = loyalty_points + :pts, current_receivable = current_receivable + :rec WHERE id = :cid", [
                    'pts' => $pts,
                    'rec' => $remainingAmount,
                    'cid' => $customerId
                ]);
            }

            Database::commit();

            // WhatsApp Notification Trigger
            WhatsAppService::sendOrderConfirmed($orderId);

            return [
                'order_id'       => $orderId,
                'order_number'   => $orderNumber,
                'invoice_number' => $invoiceNumber,
                'total_amount'   => $totalAmount,
                'status'         => $initialOrderStatus,
                'payment_status' => $paymentStatus
            ];
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Records additional payment for an order (settlement / installment)
     */
    public static function addPayment(array $data, int $userId): array {
        $orderId    = (int) $data['order_id'];
        $amount     = (float) $data['amount'];
        $method     = $data['payment_method'] ?? 'cash';
        $refNumber  = $data['reference_number'] ?? null;
        $idempKey   = $data['idempotency_key'] ?? ('PAY-' . $orderId . '-' . time());

        Database::beginTransaction();
        try {
            $order = Database::fetchOne("SELECT * FROM orders WHERE id = :id FOR UPDATE", ['id' => $orderId]);
            if (!$order) {
                throw new Exception("Order #{$orderId} tidak ditemukan.");
            }

            if ($amount <= 0) {
                throw new Exception("Nominal pembayaran harus lebih besar dari Rp 0.");
            }

            $currentRemaining = (float) $order['remaining_amount'];
            if ($amount > $currentRemaining) {
                throw new Exception("Nominal pembayaran (Rp " . number_format($amount, 0, ',', '.') . ") melebihi sisa tagihan (Rp " . number_format($currentRemaining, 0, ',', '.') . ").");
            }

            $newPaid = (float) $order['paid_amount'] + $amount;
            $newRemaining = (float) $order['total_amount'] - $newPaid;
            $newPayStatus = ($newRemaining <= 0) ? 'paid' : 'partial';

            $branch = Database::fetchOne("SELECT * FROM branches WHERE id = :id", ['id' => $order['branch_id']]);
            $branchCode = $branch['code'] ?? 'JKT01';

            $payYm = date('ym');
            $payCount = (int) Database::fetchColumn("SELECT COUNT(*) FROM payments WHERE payment_number LIKE 'PAY-{$branchCode}-{$payYm}-%'");
            $paySeq = str_pad($payCount + 1, 4, '0', STR_PAD_LEFT);
            $paymentNumber = "PAY-{$branchCode}-{$payYm}-{$paySeq}";

            $paymentId = Database::insert('payments', [
                'company_id'       => $order['company_id'],
                'branch_id'        => $order['branch_id'],
                'order_id'         => $orderId,
                'payment_number'   => $paymentNumber,
                'payment_type'     => 'order_payment',
                'payment_method'   => $method,
                'amount'           => $amount,
                'payment_date'     => date('Y-m-d H:i:s'),
                'reference_number' => $refNumber ?: ('PAY-' . time()),
                'verified_by'      => $userId,
                'status'           => 'verified',
                'idempotency_key'  => $idempKey,
                'notes'            => $data['notes'] ?? 'Pelunasan / pembayaran termin pesanan',
                'created_by'       => $userId
            ]);

            // Update order
            Database::update('orders', [
                'paid_amount'      => $newPaid,
                'remaining_amount' => $newRemaining,
                'payment_status'   => $newPayStatus
            ], 'id = :id', ['id' => $orderId]);

            // Update customer receivable
            Database::query("UPDATE customers SET current_receivable = GREATEST(0, current_receivable - :amt) WHERE id = :cid", [
                'amt' => $amount,
                'cid' => $order['customer_id']
            ]);

            // Accounting journal: Dr. Kas/Bank, Cr. Piutang Usaha
            $cashAccId = ($order['branch_id'] === 2) ? 2 : (($order['branch_id'] === 3) ? 3 : 1);
            $payAccId = ($method === 'cash') ? $cashAccId : 4; // BCA

            $entries = [
                ['account_id' => $payAccId, 'debit' => $amount, 'credit' => 0.00, 'memo' => "Penerimaan pembayaran {$paymentNumber}"],
                ['account_id' => 6, 'debit' => 0.00, 'credit' => $amount, 'memo' => "Pengurangan piutang order {$order['order_number']}"]
            ];

            AccountingService::postJournal(
                $order['branch_id'],
                date('Y-m-d'),
                'payment',
                $paymentId,
                "Pembayaran order {$order['order_number']} ({$paymentNumber})",
                $entries,
                $userId,
                $order['company_id']
            );

            Database::commit();

            // WhatsApp Notification
            WhatsAppService::sendPaymentReceived($paymentId);

            return [
                'payment_id'       => $paymentId,
                'payment_number'   => $paymentNumber,
                'paid_amount'      => $newPaid,
                'remaining_amount' => $newRemaining,
                'payment_status'   => $newPayStatus
            ];
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
}

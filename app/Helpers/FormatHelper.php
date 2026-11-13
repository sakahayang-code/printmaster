<?php
namespace App\Helpers;

class FormatHelper {
    public static function rupiah($amount, bool $withSymbol = true): string {
        $val = (float) $amount;
        $formatted = number_format($val, 0, ',', '.');
        return $withSymbol ? 'Rp ' . $formatted : $formatted;
    }

    public static function tanggal(?string $datetime, bool $withTime = true): string {
        if (!$datetime) return '-';
        $time = strtotime($datetime);
        $bulan = [
            1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ];
        $d = date('d', $time);
        $m = $bulan[(int)date('m', $time)];
        $y = date('Y', $time);
        
        if ($withTime) {
            $h = date('H:i', $time);
            return "{$d} {$m} {$y}, {$h} WIB";
        }
        return "{$d} {$m} {$y}";
    }

    public static function datetime(?string $datetime): string {
        return self::tanggal($datetime, true);
    }

    public static function date(?string $date): string {
        return self::tanggal($date, false);
    }

    public static function luasM2(float $widthCm, float $heightCm): float {
        return round(($widthCm * $heightCm) / 10000, 4);
    }

    public static function badgeStatusOrder(string $status): string {
        $badges = [
            'pending_confirmation'      => ['bg-secondary', 'Menunggu Konfirmasi', 'bi-hourglass-split'],
            'awaiting_payment'          => ['bg-warning text-dark', 'Menunggu Pembayaran', 'bi-wallet2'],
            'awaiting_design'           => ['bg-info text-dark', 'Menunggu File Desain', 'bi-file-earmark-arrow-up'],
            'design_in_progress'        => ['bg-info', 'Desain Sedang Dikerjakan', 'bi-brush'],
            'awaiting_design_approval'  => ['bg-primary', 'Menunggu ACC Desain', 'bi-check2-circle'],
            'queued'                    => ['bg-secondary', 'Antrean Produksi', 'bi-clock-history'],
            'in_production'             => ['bg-primary', 'Sedang Diproduksi', 'bi-gear-wide-connected'],
            'finishing'                 => ['bg-info', 'Tahap Finishing', 'bi-scissors'],
            'quality_control'           => ['bg-warning text-dark', 'Inspeksi QC', 'bi-shield-check'],
            'ready_for_pickup'          => ['bg-success', 'Siap Diambil', 'bi-bag-check'],
            'in_delivery'               => ['bg-primary', 'Dalam Pengiriman', 'bi-truck'],
            'completed'                 => ['bg-success', 'Selesai & Diterima', 'bi-check-all'],
            'cancelled'                 => ['bg-danger', 'Dibatalkan', 'bi-x-circle'],
        ];

        $b = $badges[$status] ?? ['bg-secondary', ucfirst($status), 'bi-circle'];
        return "<span class=\"badge {$b[0]} d-inline-flex align-items-center gap-1\"><i class=\"bi {$b[2]}\"></i> {$b[1]}</span>";
    }

    public static function badgeStatusPayment(string $status): string {
        $badges = [
            'unpaid'   => ['bg-danger', 'Belum Bayar', 'bi-x-octagon'],
            'partial'  => ['bg-warning text-dark', 'DP / Sebagian', 'bi-pie-chart'],
            'paid'     => ['bg-success', 'Lunas', 'bi-check-circle-fill'],
            'refunded' => ['bg-secondary', 'Refund', 'bi-arrow-counterclockwise'],
        ];
        $b = $badges[$status] ?? ['bg-secondary', ucfirst($status), 'bi-circle'];
        return "<span class=\"badge {$b[0]} d-inline-flex align-items-center gap-1\"><i class=\"bi {$b[2]}\"></i> {$b[1]}</span>";
    }

    public static function badgeStatusJob(string $status): string {
        $badges = [
            'queued'      => ['bg-secondary', 'Antre Mesin', 'bi-clock'],
            'scheduled'   => ['bg-info text-dark', 'Terjadwal', 'bi-calendar-check'],
            'in_progress' => ['bg-primary', 'Berjalan', 'bi-play-fill'],
            'completed'   => ['bg-success', 'Selesai Cetak', 'bi-check-circle'],
            'failed'      => ['bg-danger', 'Gagal / Reject', 'bi-exclamation-triangle'],
            'cancelled'   => ['bg-dark', 'Batal', 'bi-x'],
        ];
        $b = $badges[$status] ?? ['bg-secondary', ucfirst($status), 'bi-circle'];
        return "<span class=\"badge {$b[0]} d-inline-flex align-items-center gap-1\"><i class=\"bi {$b[2]}\"></i> {$b[1]}</span>";
    }

    public static function e(?string $string): string {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

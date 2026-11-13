<?php
namespace App\Services;

use App\Core\Database;

class PricingService {
    /**
     * Calculate price and cost breakdown for a printing item.
     * Supports:
     * - area_sqm (Banner, baliho, backlite, spanduk)
     * - sheet_a3 (Digital print A3+, brosur, poster, stiker)
     * - pcs (Kartu nama, x-banner, merchandise)
     * - custom / rim / meter
     */
    public static function calculate(array $params): array {
        $productId  = (int) ($params['product_id'] ?? 0);
        $product    = Database::fetchOne("SELECT * FROM products WHERE id = :id", ['id' => $productId]);

        if (!$product) {
            return ['error' => 'Produk tidak ditemukan'];
        }

        $widthCm    = (float) ($params['width_cm'] ?? 0);
        $heightCm   = (float) ($params['height_cm'] ?? 0);
        $qty        = max(1, (float) ($params['quantity'] ?? 1));
        $finishingIds = $params['finishing_ids'] ?? [];

        $pricingMode = $product['pricing_mode'];
        $basePrice   = (float) $product['base_price'];
        $setupCost   = (float) $product['setup_cost'];
        $overheadCost= (float) $product['overhead_cost'];
        $wastePct    = (float) $product['waste_percent'];
        $marginPct   = (float) $product['default_margin_percent'];
        $minPrice    = (float) $product['min_price'];

        $areaSqm = 0.0;
        $totalAreaSqm = 0.0;
        $rawMaterialCost = 0.0;
        $subtotalBase = 0.0;

        if ($pricingMode === 'area_sqm') {
            $areaSqm = round(($widthCm * $heightCm) / 10000, 4);
            // Minimum billing area e.g. 1 m2 per pcs if area is smaller
            $billableArea = max(1.0, $areaSqm);
            $totalAreaSqm = $areaSqm * $qty;
            $subtotalBase = $basePrice * $billableArea * $qty;
            $rawMaterialCost = ($basePrice * 0.40) * $billableArea * $qty; // Approx 40% material cost
        } elseif ($pricingMode === 'sheet_a3') {
            $subtotalBase = $basePrice * $qty;
            $rawMaterialCost = ($basePrice * 0.45) * $qty;
        } else {
            // pcs, rim, meter, custom
            $subtotalBase = $basePrice * $qty;
            $rawMaterialCost = ($basePrice * 0.50) * $qty;
        }

        // Finishing costs
        $finishingTotal = 0.0;
        $finishingCostTotal = 0.0;
        $finishingItems = [];

        if (!empty($finishingIds)) {
            $inClause = implode(',', array_map('intval', (array) $finishingIds));
            $finishings = Database::fetchAll("SELECT * FROM product_finishing WHERE id IN ({$inClause}) AND is_active = 1");
            
            foreach ($finishings as $fin) {
                $calcType = $fin['calculation_type'];
                $sell = (float) $fin['selling_price'];
                $cost = (float) $fin['cost_price'];
                $minFee = (float) $fin['min_fee'];

                $itemSell = 0.0;
                $itemCost = 0.0;

                if ($calcType === 'per_sqm') {
                    $itemSell = max($minFee, $sell * max(1.0, $areaSqm) * $qty);
                    $itemCost = $cost * max(1.0, $areaSqm) * $qty;
                } elseif ($calcType === 'per_pcs' || $calcType === 'per_side') {
                    $itemSell = max($minFee, $sell * $qty);
                    $itemCost = $cost * $qty;
                } else { // fixed
                    $itemSell = max($minFee, $sell);
                    $itemCost = $cost;
                }

                $finishingTotal += $itemSell;
                $finishingCostTotal += $itemCost;

                $finishingItems[] = [
                    'id'    => $fin['id'],
                    'name'  => $fin['name'],
                    'cost'  => $itemCost,
                    'price' => $itemSell
                ];
            }
        }

        // Waste allowance
        $wasteCost = ($rawMaterialCost * ($wastePct / 100));
        
        // Total Estimated Cost of Production (HPP)
        $totalCostEstimate = $rawMaterialCost + $wasteCost + $setupCost + $overheadCost + $finishingCostTotal;

        // Selling Price Calculation
        $calculatedSelling = $subtotalBase + $finishingTotal + $setupCost;
        
        // Ensure not below minimum price per unit
        $minTotal = $minPrice * $qty;
        $finalSubtotal = max($calculatedSelling, $minTotal);

        return [
            'product_id'          => $productId,
            'product_name'        => $product['name'],
            'pricing_mode'        => $pricingMode,
            'width_cm'            => $widthCm,
            'height_cm'           => $heightCm,
            'area_sqm'            => $areaSqm,
            'quantity'            => $qty,
            'base_rate'           => $basePrice,
            'subtotal_base'       => $subtotalBase,
            'finishing_items'     => $finishingItems,
            'finishing_total'     => $finishingTotal,
            'setup_cost'          => $setupCost,
            'overhead_cost'       => $overheadCost,
            'waste_pct'           => $wastePct,
            'waste_cost'          => $wasteCost,
            'cost_estimate_total' => round($totalCostEstimate, 2),
            'selling_subtotal'    => round($finalSubtotal, 2),
            'unit_price'          => round($finalSubtotal / $qty, 2),
            'estimated_gross_margin' => round($finalSubtotal - $totalCostEstimate, 2)
        ];
    }
}

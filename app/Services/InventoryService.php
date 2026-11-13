<?php
namespace App\Services;

use App\Core\Database;
use Exception;

class InventoryService {
    /**
     * Records an immutable stock movement and updates material stock cache.
     * Types: 'purchase_in', 'production_out', 'transfer_in', 'transfer_out', 'adjustment', 'return_in', 'scrap_out'
     */
    public static function recordMovement(
        int $branchId,
        int $warehouseId,
        int $materialId,
        string $movementType,
        float $quantity,
        float $unitCost,
        ?string $refType,
        ?int $refId,
        ?string $notes,
        int $userId,
        int $companyId = 1
    ): int {
        Database::beginTransaction();
        try {
            $material = Database::fetchOne("SELECT * FROM materials WHERE id = :id FOR UPDATE", ['id' => $materialId]);
            if (!$material) {
                throw new Exception("Bahan baku ID {$materialId} tidak ditemukan.");
            }

            $currentStock = (float) $material['current_stock'];
            $isPositive = in_array($movementType, ['purchase_in', 'transfer_in', 'return_in']);
            
            if ($isPositive) {
                $newStock = $currentStock + $quantity;
            } elseif ($movementType === 'adjustment') {
                $newStock = $currentStock + $quantity; // quantity can be negative or positive
            } else {
                // Outgoing
                if ($currentStock < $quantity) {
                    throw new Exception("Stok tidak mencukupi untuk bahan '{$material['name']}'. Stok tersedia: {$currentStock}, diminta: {$quantity}.");
                }
                $newStock = $currentStock - $quantity;
            }

            // Insert movement record
            $movementId = Database::insert('stock_movements', [
                'company_id'     => $companyId,
                'branch_id'      => $branchId,
                'warehouse_id'   => $warehouseId,
                'material_id'    => $materialId,
                'movement_type'  => $movementType,
                'quantity'       => $quantity,
                'unit_cost'      => $unitCost,
                'balance_after'  => $newStock,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'notes'          => $notes,
                'created_by'     => $userId
            ]);

            // Update materials stock
            Database::update('materials', [
                'current_stock' => $newStock,
                'avg_cost'      => $unitCost > 0 ? $unitCost : (float) $material['avg_cost']
            ], 'id = :id', ['id' => $materialId]);

            Database::commit();
            return $movementId;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
}

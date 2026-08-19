<?php

namespace App\Services;

class JobWorkCalculationService
{
    /**
     * Convert any supported weight unit to base grams.
     *
     * @param float $weight
     * @param string $unit (Gram, KG, Milligram, Ton)
     * @return float
     */
    public function convertToGrams(float $weight, string $unit = 'Gram'): float
    {
        $weight = max(0, $weight);
        $normalizedUnit = strtoupper(trim($unit));

        return match ($normalizedUnit) {
            'KG', 'KILOGRAM', 'KILOGRAMS' => $weight * 1000,
            'TON', 'TONS', 'METRIC TON'   => $weight * 1000000,
            'MILLIGRAM', 'MG'             => $weight / 1000,
            default                       => $weight, // Grams
        };
    }

    /**
     * Convert grams back to a target unit.
     */
    public function convertFromGrams(float $grams, string $targetUnit = 'KG'): float
    {
        $grams = max(0, $grams);
        $normalizedUnit = strtoupper(trim($targetUnit));

        return match ($normalizedUnit) {
            'KG', 'KILOGRAM', 'KILOGRAMS' => $grams / 1000,
            'TON', 'TONS', 'METRIC TON'   => $grams / 1000000,
            'MILLIGRAM', 'MG'             => $grams * 1000,
            default                       => $grams, // Grams
        };
    }

    /**
     * Calculate Gross Pieces from received material weight and product piece weight.
     * Formula: Gross Pieces = Received Weight in Grams ÷ Product Weight in Grams
     *
     * @param float $receivedWeightGrams
     * @param float $productWeightGrams
     * @param string $roundingMethod (floor, round, ceil, decimal)
     * @return float
     */
    public function calculateGrossQuantity(
        float $receivedWeightGrams,
        float $productWeightGrams,
        string $roundingMethod = 'floor'
    ): float {
        if ($receivedWeightGrams <= 0 || $productWeightGrams <= 0) {
            return 0.0;
        }

        $rawPieces = $receivedWeightGrams / $productWeightGrams;

        return $this->applyRounding($rawPieces, $roundingMethod);
    }

    /**
     * Calculate Wastage quantity from gross pieces.
     *
     * @param float $grossQuantity
     * @param string $wastageType ('percentage', 'fixed', 'none')
     * @param float $wastagePercentage
     * @param float $fixedWastage
     * @param string $roundingMethod
     * @return float
     */
    public function calculateWastage(
        float $grossQuantity,
        string $wastageType = 'percentage',
        float $wastagePercentage = 0.0,
        float $fixedWastage = 0.0,
        string $roundingMethod = 'floor'
    ): float {
        if ($grossQuantity <= 0) {
            return 0.0;
        }

        $rawWastage = match (strtolower(trim($wastageType))) {
            'percentage' => $grossQuantity * (max(0, $wastagePercentage) / 100),
            'fixed'      => min($grossQuantity, max(0, $fixedWastage)),
            default      => 0.0,
        };

        return $this->applyRounding($rawWastage, $roundingMethod);
    }

    /**
     * Calculate Net Quantity.
     * Formula: Net Quantity = Gross Quantity - Wastage Quantity
     *
     * @param float $grossQuantity
     * @param float $wastageQuantity
     * @return float
     */
    public function calculateNetQuantity(float $grossQuantity, float $wastageQuantity): float
    {
        return max(0.0, $grossQuantity - $wastageQuantity);
    }

    /**
     * Calculate financial amount based on rate type.
     *
     * @param string $rateType ('per_piece', 'per_kg', 'fixed')
     * @param float $rate
     * @param float $netQuantity
     * @param float $receivedWeight
     * @param string $receivedWeightUnit
     * @return float
     */
    public function calculateItemAmount(
        string $rateType,
        float $rate,
        float $netQuantity,
        float $receivedWeight,
        string $receivedWeightUnit = 'KG'
    ): float {
        $rate = max(0, $rate);

        $amount = match (strtolower(trim($rateType))) {
            'per_kg' => $this->convertFromGrams($this->convertToGrams($receivedWeight, $receivedWeightUnit), 'KG') * $rate,
            'fixed'  => $rate,
            default  => $netQuantity * $rate, // 'per_piece'
        };

        return round($amount, 2);
    }

    /**
     * Calculate all derived values for a single Job Work line item.
     *
     * @param array $data
     * @param string $roundingMethod
     * @return array
     */
    public function calculateItem(array $data, string $roundingMethod = 'floor'): array
    {
        $receivedWeight      = (float) ($data['received_weight'] ?? 0);
        $receivedWeightUnit  = (string) ($data['received_weight_unit'] ?? 'KG');
        $receivedWeightGrams = $this->convertToGrams($receivedWeight, $receivedWeightUnit);

        $productWeight      = (float) ($data['product_weight'] ?? 0);
        $productWeightUnit  = (string) ($data['product_weight_unit'] ?? 'Gram');
        $productWeightGrams = $this->convertToGrams($productWeight, $productWeightUnit);

        $grossQuantity = $this->calculateGrossQuantity($receivedWeightGrams, $productWeightGrams, $roundingMethod);

        $wastageType       = (string) ($data['wastage_type'] ?? 'percentage');
        $wastagePercentage = (float) ($data['wastage_percentage'] ?? 0);
        $fixedWastage      = (float) ($data['fixed_wastage'] ?? 0);

        $wastageQuantity = $this->calculateWastage(
            $grossQuantity,
            $wastageType,
            $wastagePercentage,
            $fixedWastage,
            $roundingMethod
        );

        $netQuantity = $this->calculateNetQuantity($grossQuantity, $wastageQuantity);

        $rateType = (string) ($data['rate_type'] ?? 'per_piece');
        $rate     = (float) ($data['rate'] ?? 0);
        $amount   = $this->calculateItemAmount($rateType, $rate, $netQuantity, $receivedWeight, $receivedWeightUnit);

        $deliveredQuantity = (float) ($data['delivered_quantity'] ?? 0);
        $balanceQuantity   = max(0.0, $netQuantity - $deliveredQuantity);

        return [
            'received_weight'       => $receivedWeight,
            'received_weight_unit'  => $receivedWeightUnit,
            'received_weight_grams' => $receivedWeightGrams,
            'product_weight'        => $productWeight,
            'product_weight_unit'   => $productWeightUnit,
            'product_weight_grams'  => $productWeightGrams,
            'gross_quantity'        => $grossQuantity,
            'wastage_type'          => $wastageType,
            'wastage_percentage'    => $wastagePercentage,
            'wastage_quantity'      => $wastageQuantity,
            'net_quantity'          => $netQuantity,
            'delivered_quantity'    => $deliveredQuantity,
            'balance_quantity'      => $balanceQuantity,
            'rate_type'             => $rateType,
            'rate'                  => $rate,
            'amount'                => $amount,
        ];
    }

    /**
     * Calculate summary totals for an entire Job Work Order.
     *
     * @param array $calculatedItems
     * @param float $additionalCharges
     * @param float $discount
     * @param float $tax
     * @param float $paidAmount
     * @return array
     */
    public function calculateOrderTotals(
        array $calculatedItems,
        float $additionalCharges = 0.0,
        float $discount = 0.0,
        float $tax = 0.0,
        float $paidAmount = 0.0
    ): array {
        $totalReceivedGrams = 0.0;
        $totalGrossPieces   = 0.0;
        $totalWastagePieces = 0.0;
        $totalNetPieces     = 0.0;
        $totalDelivered     = 0.0;
        $subtotal           = 0.0;

        foreach ($calculatedItems as $item) {
            $totalReceivedGrams += (float) ($item['received_weight_grams'] ?? 0);
            $totalGrossPieces   += (float) ($item['gross_quantity'] ?? 0);
            $totalWastagePieces += (float) ($item['wastage_quantity'] ?? 0);
            $totalNetPieces     += (float) ($item['net_quantity'] ?? 0);
            $totalDelivered     += (float) ($item['delivered_quantity'] ?? 0);
            $subtotal           += (float) ($item['amount'] ?? 0);
        }

        $totalReceivedWeightKg = round($totalReceivedGrams / 1000, 4);
        $totalBalancePieces    = max(0.0, $totalNetPieces - $totalDelivered);

        $grandTotal    = max(0.0, $subtotal + $additionalCharges - $discount + $tax);
        $balanceAmount = max(0.0, $grandTotal - $paidAmount);

        return [
            'total_received_weight_kg' => $totalReceivedWeightKg,
            'total_gross_pieces'       => $totalGrossPieces,
            'total_wastage_pieces'     => $totalWastagePieces,
            'total_net_pieces'         => $totalNetPieces,
            'total_delivered_pieces'   => $totalDelivered,
            'total_balance_pieces'     => $totalBalancePieces,
            'subtotal'                 => round($subtotal, 2),
            'additional_charges'       => round($additionalCharges, 2),
            'discount'                 => round($discount, 2),
            'tax'                      => round($tax, 2),
            'grand_total'              => round($grandTotal, 2),
            'paid_amount'              => round($paidAmount, 2),
            'balance_amount'           => round($balanceAmount, 2),
        ];
    }

    /**
     * Apply the specified rounding method.
     */
    public function applyRounding(float $value, string $method = 'floor'): float
    {
        return match (strtolower(trim($method))) {
            'round'   => (float) round($value),
            'ceil', 'ceiling' => (float) ceil($value),
            'decimal' => (float) round($value, 4),
            default   => (float) floor($value), // Floor is standard
        };
    }
}

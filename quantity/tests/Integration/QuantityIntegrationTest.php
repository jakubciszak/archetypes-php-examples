<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Quantity\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Quantity\Money\Money;
use SoftwareArchetypes\Quantity\Quantity;
use SoftwareArchetypes\Quantity\Unit;

/**
 * Integration tests demonstrating real-world usage scenarios
 * for the Quantity archetype pattern.
 */
final class QuantityIntegrationTest extends TestCase
{
    public function testInventoryManagementScenario(): void
    {
        // Initial stock
        $initialStock = Quantity::of(1000, Unit::pieces());

        // Order received
        $orderReceived = Quantity::of(500, Unit::pieces());
        $newStock = $initialStock->add($orderReceived);

        // Shipment sent
        $shipmentSent = Quantity::of(200, Unit::pieces());
        $finalStock = $newStock->subtract($shipmentSent);

        self::assertEquals('1300 pcs', (string) $finalStock);
        self::assertFalse($finalStock->isZero());
    }

    public function testWarehouseStorageScenario(): void
    {
        // Calculate total volume of stored items
        $palletVolume = Quantity::of('1.2', Unit::cubicMeters());
        $numberOfPallets = 50;

        $totalVolume = $palletVolume;
        for ($i = 1; $i < $numberOfPallets; $i++) {
            $totalVolume = $totalVolume->add($palletVolume);
        }

        // Warehouse capacity
        $warehouseCapacity = Quantity::of(100, Unit::cubicMeters());

        self::assertTrue($totalVolume->isLessThan($warehouseCapacity));
        self::assertEquals('60 m³', (string) $totalVolume);
    }

    public function testWeightCalculationScenario(): void
    {
        // Product weights
        $productA = Quantity::of('2.5', Unit::kilograms());
        $productB = Quantity::of('3.75', Unit::kilograms());
        $productC = Quantity::of('1.25', Unit::kilograms());

        $totalWeight = $productA->add($productB)->add($productC);

        // Check against shipping limit
        $shippingLimit = Quantity::of(10, Unit::kilograms());

        self::assertTrue($totalWeight->isLessThan($shippingLimit));
        self::assertEquals('7.5 kg', (string) $totalWeight);
    }

    public function testLiquidMeasurementScenario(): void
    {
        // Tank capacity and contents
        $tankCapacity = Quantity::of(5000, Unit::liters());
        $currentLevel = Quantity::of(3500, Unit::liters());

        // Delivery scheduled
        $deliveryAmount = Quantity::of(2000, Unit::liters());
        $projectedLevel = $currentLevel->add($deliveryAmount);

        // Check if delivery will overflow
        self::assertTrue($projectedLevel->isGreaterThan($tankCapacity));

        // Calculate remaining capacity
        $remainingCapacity = $tankCapacity->subtract($currentLevel);
        self::assertEquals('1500 l', (string) $remainingCapacity);
    }

    public function testAreaCalculationScenario(): void
    {
        // Office spaces
        $office1 = Quantity::of('25.5', Unit::squareMeters());
        $office2 = Quantity::of('30.0', Unit::squareMeters());
        $office3 = Quantity::of('22.75', Unit::squareMeters());

        $totalArea = $office1->add($office2)->add($office3);

        self::assertEquals('78.25 m²', (string) $totalArea);
    }

    public function testMoneyCalculationScenario(): void
    {
        // Shopping cart
        $itemPrice1 = Money::pln('49.99');
        $itemPrice2 = Money::pln('29.99');
        $itemPrice3 = Money::pln('15.50');

        $subtotal = $itemPrice1->add($itemPrice2)->add($itemPrice3);

        // Apply discount
        $discount = Money::pln('10.00');
        $total = $subtotal->subtract($discount);

        // Check against budget
        $budget = Money::pln(100);

        self::assertTrue($total->isLessThan($budget));
        self::assertEquals('PLN 85.48', (string) $total);
    }

    public function testMoneyWithQuantityScenario(): void
    {
        // Calculate total cost for bulk order
        $unitPrice = Money::pln('25.50');
        $quantity = 10;

        $totalCost = $unitPrice->multiply($quantity);

        self::assertEquals('PLN 255', (string) $totalCost);

        // Apply bulk discount (10%)
        $discountedPrice = $totalCost->multiply('0.9');

        self::assertEquals('PLN 229.5', (string) $discountedPrice);
    }

    public function testMixedUnitsValidationScenario(): void
    {
        // Ensure operations fail with different units
        $weight = Quantity::of(100, Unit::kilograms());
        $volume = Quantity::of(100, Unit::liters());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot add quantities with different units');

        $weight->add($volume);
    }

    public function testTimeTrackingScenario(): void
    {
        // Work hours tracking
        $mondayHours = Quantity::of(8, Unit::hours());
        $tuesdayHours = Quantity::of(7.5, Unit::hours());
        $wednesdayHours = Quantity::of(8, Unit::hours());
        $thursdayHours = Quantity::of(6.5, Unit::hours());
        $fridayHours = Quantity::of(8, Unit::hours());

        $totalWeekHours = $mondayHours
            ->add($tuesdayHours)
            ->add($wednesdayHours)
            ->add($thursdayHours)
            ->add($fridayHours);

        $standardWeek = Quantity::of(40, Unit::hours());

        self::assertTrue($totalWeekHours->isLessThan($standardWeek));
        self::assertEquals('38 h', (string) $totalWeekHours);
    }

    public function testPrecisionInFinancialCalculations(): void
    {
        // Tax calculation
        $price = Money::pln('99.99');
        $taxRate = '0.23'; // 23% VAT

        $taxAmount = $price->multiply($taxRate);
        $totalPrice = $price->add($taxAmount);

        // Verify precision is maintained (not rounded automatically)
        self::assertEquals('PLN 122.9877', (string) $totalPrice);
        self::assertTrue($totalPrice->value()->isEqualTo(\Brick\Math\BigDecimal::of('122.9877')));

        // For display purposes, you might want to round
        $roundedTotal = Money::pln($totalPrice->value()->toScale(2, \Brick\Math\RoundingMode::HALF_UP));
        self::assertEquals('PLN 122.99', (string) $roundedTotal);
    }

    public function testComparisonOperationsScenario(): void
    {
        // Stock level monitoring
        $currentStock = Quantity::of(150, Unit::pieces());
        $minimumStock = Quantity::of(200, Unit::pieces());
        $criticalStock = Quantity::of(50, Unit::pieces());

        // Need reorder?
        self::assertTrue($currentStock->isLessThan($minimumStock));

        // Critical level?
        self::assertFalse($currentStock->isLessThan($criticalStock));

        // Still have stock?
        self::assertFalse($currentStock->isZero());
    }

    public function testCustomUnitScenario(): void
    {
        // Industry-specific units
        $pallets = Unit::of('plt', 'pallets');
        $containers = Unit::of('ctr', 'containers');

        $warehouseStock = Quantity::of(45, $pallets);
        $incomingShipment = Quantity::of(15, $pallets);

        $projectedStock = $warehouseStock->add($incomingShipment);

        self::assertEquals('60 plt', (string) $projectedStock);
    }
}

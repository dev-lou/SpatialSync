<?php

namespace Tests\Unit\Support;

use App\Support\CostEstimate;
use Tests\TestCase;

class CostEstimateTest extends TestCase
{
    public function test_it_measures_walls_by_area_and_counts_units_individually(): void
    {
        // Defaults from config/spatialsync.php: wall 120/m², door 350 each.
        $estimate = CostEstimate::forParts([
            ['type' => 'wall', 'width' => 4, 'height' => 3, 'depth' => 0.2],
            ['type' => 'door', 'width' => 1, 'height' => 2.4, 'depth' => 0.2],
        ]);

        // 4 x 3 = 12 m² of wall at 120 = 1440, plus one door at 350.
        $this->assertSame(1790.0, $estimate['total']);
        $this->assertSame('$1,790', $estimate['formatted']);
        $this->assertSame('Walls', $estimate['lines'][0]['label']);
        $this->assertSame(12.0, $estimate['lines'][0]['quantity']);
        $this->assertSame('m²', $estimate['lines'][0]['unit']);
        $this->assertSame(350.0, $estimate['lines'][1]['amount']);
        $this->assertSame('units', $estimate['lines'][1]['unit']);
    }

    public function test_floors_are_measured_by_width_and_depth(): void
    {
        $estimate = CostEstimate::forParts([
            ['type' => 'floor', 'width' => 5, 'height' => 0.05, 'depth' => 4],
        ]);

        // 5 x 4 = 20 m² at 90 = 1800
        $this->assertSame(1800.0, $estimate['total']);
    }

    public function test_an_empty_design_costs_nothing(): void
    {
        $estimate = CostEstimate::forParts([]);

        $this->assertSame(0.0, $estimate['total']);
        $this->assertSame('$0', $estimate['formatted']);
        $this->assertSame([], $estimate['lines']);
    }

    public function test_unknown_part_types_use_the_generic_rate(): void
    {
        $estimate = CostEstimate::forParts([
            ['type' => 'hologram', 'width' => 9, 'height' => 9, 'depth' => 9],
        ]);

        $this->assertSame(100.0, $estimate['total']);
        $this->assertSame('Other parts', $estimate['lines'][0]['label']);
    }

    public function test_the_estimate_is_presented_as_an_estimate(): void
    {
        $estimate = CostEstimate::forParts([]);

        $this->assertSame('Planning estimate', $estimate['label']);
        $this->assertStringContainsString('not a quote', $estimate['note']);
    }
}

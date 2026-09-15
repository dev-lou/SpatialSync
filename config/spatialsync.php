<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Planning cost estimate
    |--------------------------------------------------------------------------
    | A running "planning estimate" shown while a design is being built. The
    | rates below are illustrative defaults, NOT market data — swap them for
    | your own supplier or regional figures before anyone treats the number as
    | a price. The editor labels the figure as an estimate for exactly that
    | reason.
    |
    | 'basis' => 'area' measures width x height for walls and width x depth for
    | flat parts; 'unit' counts the part once. Parts drawn as custom polygons
    | fall back to their bounding box.
    |
    | The same table is mirrored in public/js/cost-estimate.js so the figure can
    | update as parts are placed without a round trip. If you change a rate here,
    | change it there too — tests/Unit/Support/CostEstimateTest.php pins the
    | expected numbers so drift shows up as a failing test.
    */
    'estimates' => [
        'currency' => 'USD',
        'symbol' => '$',
        'label' => 'Planning estimate',
        'note' => 'Indicative only — not a quote.',

        'rates' => [
            'wall' => ['basis' => 'area', 'rate' => 120, 'label' => 'Walls'],
            'floor' => ['basis' => 'area', 'rate' => 90, 'label' => 'Floors'],
            'roof' => ['basis' => 'area', 'rate' => 140, 'label' => 'Roof'],
            'door' => ['basis' => 'unit', 'rate' => 350, 'label' => 'Doors'],
            'window' => ['basis' => 'unit', 'rate' => 280, 'label' => 'Windows'],
            'stairs' => ['basis' => 'unit', 'rate' => 900, 'label' => 'Stairs'],
            'furniture' => ['basis' => 'unit', 'rate' => 150, 'label' => 'Furniture'],
            'generic' => ['basis' => 'unit', 'rate' => 100, 'label' => 'Other parts'],
        ],
    ],

];

<?php
/**
 * Shared price list, keyed by the same size labels used in the
 * order form and stored in orders.size.
 *
 * Keep this in sync with $product['sizes'] in index.php — this file
 * exists so index.php, admin/index.php, and pay.php all compute the
 * same amount instead of three separate copies drifting apart.
 */

$productSizePrices = [
    '1 Pieces'  => 30.00,
    '2 Pieces'  => 60.00,
    '4 Pieces'  => 120.00,
];

/**
 * Look up the price for a size label. Returns null if the label
 * isn't recognized (e.g. old data, or the price list changed).
 */
function get_size_price(string $size): ?float
{
    global $productSizePrices;
    return $productSizePrices[$size] ?? null;
}
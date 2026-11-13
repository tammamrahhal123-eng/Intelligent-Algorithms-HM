<?php
function calculateSelectionWeights(
    array $population,
    array $fitnessResults
): array {

    $weights = [];

    foreach ($population as $index => $chromosome) {

        $fitness = (float) $fitnessResults[$index]['fitness'];
        // Avoid division by zero.
        $weights[$index] = 1 / ($fitness + 0.000001);
    }

    return $weights;
}



function rouletteWheelSelection(
    array $population,
    array $weights
): array {

    $totalWeight = array_sum($weights);


    // If something unexpected happens, select a random chromosome.
    if ($totalWeight <= 0) {
        $randomIndex = array_rand($population);
        return $population[$randomIndex];
    }

    
    // Generate random value between 0 and totalWeight.
    $randomValue =
        (mt_rand() / mt_getrandmax()) * $totalWeight;

    $currentWeight = 0.0;

    foreach ($weights as $index => $weight) {

        $currentWeight += $weight;

        if ($randomValue <= $currentWeight) {

            return $population[$index];
        }
    }

    
    // Floating-point safety fallback.
    $lastIndex = array_key_last($population);

    return $population[$lastIndex];
}
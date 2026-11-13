<?php

function singlePointCrossover(
    array $parent1,
    array $parent2
): array {

    $length = count($parent1);

    if ($length < 2) {
        return [
            'performed' => false,
            'point' => null,
            'child' => $parent1
        ];
    }

    $point = random_int(1, $length - 1);

    $firstPart = array_slice(
        $parent1,
        0,
        $point
    );

    $secondPart = array_slice(
        $parent2,
        $point
    );

    $child = array_merge(
        $firstPart,
        $secondPart
    );

    return [
        'performed' => true,
        'point' => $point,
        'child' => $child
    ];
}
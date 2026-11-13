<?php

function randomEmployeeId(array $employeeIds): int
{
    $index = array_rand($employeeIds);

    return (int) $employeeIds[$index];
}


function updateDiscretePosition(
    array $position,
    array $personalBest,
    array $globalBest,
    array $employeeIds,
    array &$velocity,
    float $cognitiveCoefficient,
    float $socialCoefficient
): array {

    $newPosition =
        $position;


    foreach (
        $position as $index => $currentEmployee
    ) {

        if (!isset($velocity[$index])) {

            $velocity[$index] = 0.0;
        }


        $random1 =
            mt_rand() / mt_getrandmax();

        $random2 =
            mt_rand() / mt_getrandmax();


        $personalDifference =
            (
                isset($personalBest[$index]) &&
                $personalBest[$index]
                !=
                $currentEmployee
            )
            ? 1
            : 0;


        $globalDifference =
            (
                isset($globalBest[$index]) &&
                $globalBest[$index]
                !=
                $currentEmployee
            )
            ? 1
            : 0;


        
        // Discrete velocity update.
        $velocity[$index] =
            (
                $velocity[$index]
            )
            +
            (
                $cognitiveCoefficient *
                $random1 *
                $personalDifference
            )
            +
            (
                $socialCoefficient *
                $random2 *
                $globalDifference
            );


        // Limit velocity.
        $velocity[$index] =
            max(
                -4.0,
                min(
                    4.0,
                    $velocity[$index]
                )
            );


        // Convert velocity to probability.
        $probability =
            1 /
            (
                1 +
                exp(
                    -$velocity[$index]
                )
            );


        $random =
            mt_rand() / mt_getrandmax();


        if (
            $random
            <
            $probability
        ) {

            // Prefer personal best or global best.

            if (
                isset($personalBest[$index]) &&
                isset($globalBest[$index])
            ) {

                $targetRandom =
                    mt_rand() / mt_getrandmax();


                if ($targetRandom < 0.5) {

                    $newPosition[$index] =
                        (int) $personalBest[$index];

                } else {

                    $newPosition[$index] =
                        (int) $globalBest[$index];
                }

            } elseif (
                isset($globalBest[$index])
            ) {

                $newPosition[$index] =
                    (int) $globalBest[$index];

            } elseif (
                isset($personalBest[$index])
            ) {

                $newPosition[$index] =
                    (int) $personalBest[$index];
            }

        } else {

            // Exploration.
            $explorationProbability =
                0.10;


            $explorationRandom =
                mt_rand() / mt_getrandmax();


            if (
                $explorationRandom
                <
                $explorationProbability
            ) {

                $newPosition[$index] =
                    randomEmployeeId(
                        $employeeIds
                    );
            }
        }
    }


    return $newPosition;
}
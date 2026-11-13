<?php

function randomResetMutation(
    array $chromosome,
    array $employeeIds,
    float $mutationRate
): array {

    if (
        empty($chromosome)
        ||
        empty($employeeIds)
    ) {
        return [
            'chromosome' => $chromosome,
            'performed' => false,
            'mutations' => []
        ];
    }

    $mutations = [];

    foreach (
        $chromosome
        as $index => $employeeId
    ) {

        $randomProbability =
            mt_rand() / mt_getrandmax();

        if (
            $randomProbability
            <
            $mutationRate
        ) {

            $randomEmployeeIndex =
                array_rand($employeeIds);

            $newEmployeeId =
                $employeeIds[$randomEmployeeIndex];

            $oldEmployeeId =
                $chromosome[$index];

            $chromosome[$index] =
                $newEmployeeId;

            $mutations[] = [
                'position' => $index,
                'old_value' => $oldEmployeeId,
                'new_value' => $newEmployeeId
            ];
        }
    }

    return [
        'chromosome' => $chromosome,
        'performed' => !empty($mutations),
        'mutations' => $mutations
    ];
}
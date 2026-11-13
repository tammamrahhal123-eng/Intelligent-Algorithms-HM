<?php

class PopulationFactory
{
    /*
     * Create the common initial population.
     */
    public static function createInitialPopulation(
        array $employees,
        array $tasks,
        int $populationSize
    ): array {

        if ($populationSize < 1) {

            throw new InvalidArgumentException(
                'Population size must be at least 1.'
            );
        }


        if (empty($employees)) {

            throw new RuntimeException(
                'Cannot create population without employees.'
            );
        }


        if (empty($tasks)) {

            throw new RuntimeException(
                'Cannot create population without tasks.'
            );
        }


        $employeeIds =
            array_keys($employees);


        $population = [];


        for (
            $i = 0;$i < $populationSize; $i++) {

            $chromosome = [];


            foreach ($tasks as $task) {

                $randomIndex =
                    array_rand($employeeIds);


                $employeeId =
                    $employeeIds[$randomIndex];


                $chromosome[] =
                    (int) $employeeId;
            }


            $population[] =
                $chromosome;
        }


        return $population;
    }
}
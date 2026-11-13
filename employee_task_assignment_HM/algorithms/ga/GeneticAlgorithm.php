<?php

require_once __DIR__ . '/fitness.php';
require_once __DIR__ . '/selection.php';
require_once __DIR__ . '/crossover.php';
require_once __DIR__ . '/mutation.php';


class GeneticAlgorithm
{

    private array $employees = [];

    private array $tasks = [];

    private array $employeeSkills = [];

    private array $assignmentCosts = [];


    private int $generations;

    private int $eliteCount;

    private float $crossoverRate;

    private float $mutationRate;

    private float $skillPenalty;

    private float $capacityPenalty;

    private array $initialPopulation = [];


    public function __construct(
        array $employees,
        array $tasks,
        array $employeeSkills,
        array $assignmentCosts,
        
        int $generations = 100,
        int $eliteCount = 1,
        float $crossoverRate = 0.80,
        float $mutationRate = 0.01,
        float $skillPenalty = 10000,
        float $capacityPenalty = 1000
    ) {

        $this->employees =
            $employees;

        $this->tasks =
            $tasks;

        $this->employeeSkills =
            $employeeSkills;

        $this->assignmentCosts =
            $assignmentCosts;

        $this->generations =
            $generations;

        $this->eliteCount =
            $eliteCount;

        $this->crossoverRate =
            $crossoverRate;

        $this->mutationRate =
            $mutationRate;

        $this->skillPenalty =
            $skillPenalty;

        $this->capacityPenalty =
            $capacityPenalty;
    }


    
     // Receive the common initial population.
     
    public function initializeFromPopulation(
        array $initialPopulation
    ): void {

        if (empty($initialPopulation)) {

            throw new RuntimeException(
                'Initial population cannot be empty.'
            );
        }


        $expectedChromosomeLength =
            count($this->tasks);


        foreach (
            $initialPopulation as $index => $chromosome
        ) {

            if (
                count($chromosome)
                !==
                $expectedChromosomeLength
            ) {

                throw new RuntimeException(
                    'Invalid chromosome length at population index '
                    . $index
                    . '. Expected '
                    . $expectedChromosomeLength
                    . ' genes.'
                );
            }
        }


        $this->initialPopulation =
            $initialPopulation;
    }


    
     // Evaluate complete population.
     
    private function evaluatePopulation(
        array $population
    ): array {

        $fitnessResults = [];


        foreach (
            $population as $chromosome
        ) {

            $fitnessResults[] =
                calculateFitness(
                    $chromosome,
                    $this->employees,
                    $this->tasks,
                    $this->employeeSkills,
                    $this->assignmentCosts,
                    $this->skillPenalty,
                    $this->capacityPenalty
                );
        }


        return $fitnessResults;
    }


    
     // Find the best individual in a population.
     
    private function getBestIndividual(
        array $population,
        array $fitnessResults
    ): array {

        if (empty($population)) {

            throw new RuntimeException(
                'Cannot find best individual in an empty population.'
            );
        }


        $bestIndex =
            0;


        $bestFitness =
            $fitnessResults[0]['fitness'];


        foreach (
            $fitnessResults as $index => $result
        ) {

            if (
                $result['fitness']
                <
                $bestFitness
            ) {

                $bestFitness =
                    $result['fitness'];

                $bestIndex =
                    $index;
            }
        }


        return [

            'chromosome' =>
                $population[$bestIndex],

            'fitness' =>
                $fitnessResults[$bestIndex]
        ];
    }


    
     // Sort population by fitness.

    private function sortPopulation(
        array $population,
        array $fitnessResults
    ): array {

        $combined = [];


        foreach (
            $population as $index => $chromosome
        ) {

            $combined[] = [

                'chromosome' =>
                    $chromosome,

                'fitness' =>
                    $fitnessResults[$index]
            ];
        }


        usort(
            $combined,
            function ($a, $b) {

                return
                    $a['fitness']['fitness']
                    <=>
                    $b['fitness']['fitness'];
            }
        );


        return $combined;
    }


    
     // Run the Genetic Algorithm.
     
    public function run(): array
    {

        if (
            empty($this->initialPopulation)
        ) {

            throw new RuntimeException(
                'GA cannot run before initializing the population.'
            );
        }


        if (
            empty($this->employees)
        ) {

            throw new RuntimeException(
                'No employees found.'
            );
        }


        if (
            empty($this->tasks)
        ) {

            throw new RuntimeException(
                'No tasks found.'
            );
        }


        $population =
            $this->initialPopulation;



        $globalBest =
            null;


        $history = [];


        $detailedHistory = [];



         // GA loop
        for (
            $generation = 1;
            $generation <= $this->generations;
            $generation++
        ) {

            $fitnessResults =
                $this->evaluatePopulation(
                    $population
                );

            $currentBest =
                $this->getBestIndividual(
                    $population,
                    $fitnessResults
                );


            if (
                $globalBest === null
                ||
                $currentBest['fitness']['fitness']
                <
                $globalBest['fitness']['fitness']
            ) {

                $globalBest =
                    $currentBest;
            }



            // Save basic history
            $history[] = [

                'generation' =>
                    $generation,

                'best_fitness' =>
                    $currentBest[
                        'fitness'
                    ]['fitness'],

                'best_cost' =>
                    $currentBest[
                        'fitness'
                    ]['total_cost'],

                'penalty' =>
                    $currentBest[
                        'fitness'
                    ]['penalty'],

                'skill_violations' =>
                    $currentBest[
                        'fitness'
                    ]['skill_violations'],

                'capacity_violations' =>
                    $currentBest[
                        'fitness'
                    ]['capacity_violations']
            ];



            // Detailed generation log
            $generationLog = [

                'generation' =>
                    $generation,

                'population_size' =>
                    count($population),

                'best' => [

                    'chromosome' =>
                        $currentBest[
                            'chromosome'
                        ],

                    'fitness' =>
                        $currentBest[
                            'fitness'
                        ]['fitness'],

                    'cost' =>
                        $currentBest[
                            'fitness'
                        ]['total_cost'],

                    'penalty' =>
                        $currentBest[
                            'fitness'
                        ]['penalty'],

                    'skill_violations' =>
                        $currentBest[
                            'fitness'
                        ]['skill_violations'],

                    'capacity_violations' =>
                        $currentBest[
                            'fitness'
                        ]['capacity_violations'],

                    'workload' =>
                        $currentBest[
                            'fitness'
                        ]['workload']
                ],

                'operations' => []
            ];



            // Sort population
            $sorted =
                $this->sortPopulation(
                    $population,
                    $fitnessResults
                );


            $newPopulation = [];


            $actualEliteCount =
                min(
                    $this->eliteCount,
                    count($population)
                );


            for (
                $i = 0;
                $i < $actualEliteCount;
                $i++
            ) {

                $newPopulation[] =
                    $sorted[$i]['chromosome'];
            }



             // Calculate Roulette Wheel weights
            $weights =
                calculateSelectionWeights(
                    $population,
                    $fitnessResults
                );


            // Generate children
            while (
                count($newPopulation)
                <
                count($population)
            ) {

                $parent1 =
                    rouletteWheelSelection(
                        $population,
                        $weights
                    );


                $parent2 =
                    rouletteWheelSelection(
                        $population,
                        $weights
                    );



             // Crossover
                $crossoverPerformed =
                    false;


                $crossoverPoint =
                    null;


                $child =
                    $parent1;


                $random =
                    mt_rand()
                    /
                    mt_getrandmax();


                if (
                    $random
                    <
                    $this->crossoverRate
                ) {

                    $crossoverResult =
                        singlePointCrossover(
                            $parent1,
                            $parent2
                        );


                    $child =
                        $crossoverResult[
                            'child'
                        ];


                    $crossoverPerformed =
                        $crossoverResult[
                            'performed'
                        ];


                    $crossoverPoint =
                        $crossoverResult[
                            'point'
                        ];
                }



                // Store chromosome before mutation.
                $childBeforeMutation =
                    $child;

                // Mutation
                $employeeIds =
                    array_keys(
                        $this->employees
                    );


                $mutationResult =
                    randomResetMutation(
                        $child,
                        $employeeIds,
                        $this->mutationRate
                    );


                $child =
                    $mutationResult[
                        'chromosome'
                    ];



                // Save operation details
                $generationLog[
                    'operations'
                ][] = [

                    'crossover' => [

                        'performed' =>
                            $crossoverPerformed,

                        'point' =>
                            $crossoverPoint,

                        'child' =>
                            $childBeforeMutation
                    ],

                    'mutation' => [

                        'performed' =>
                            $mutationResult[
                                'performed'
                            ],

                        'mutations' =>
                            $mutationResult[
                                'mutations'
                            ]
                    ]
                ];


                $newPopulation[] =
                    $child;
            }



            $detailedHistory[] =
                $generationLog;


            // The old population is replaced by the newly created population.
            $population =
                $newPopulation;
        }


        return [

            'algorithm' =>
                'GA',

            'best' => [

                'chromosome' =>
                    $globalBest[
                        'chromosome'
                    ],

                'fitness' =>
                    $globalBest[
                        'fitness'
                    ]['fitness'],

                'cost' =>
                    $globalBest[
                        'fitness'
                    ]['total_cost'],

                'penalty' =>
                    $globalBest[
                        'fitness'
                    ]['penalty'],

                'skill_violations' =>
                    $globalBest[
                        'fitness'
                    ]['skill_violations'],

                'capacity_violations' =>
                    $globalBest[
                        'fitness'
                    ]['capacity_violations'],

                'workload' =>
                    $globalBest[
                        'fitness'
                    ]['workload']
            ],

            'history' =>
                $history,

            'detailed_history' =>
                $detailedHistory,

            'parameters' => [

                'population_size' =>
                    count(
                        $this->initialPopulation
                    ),

                'generations' =>
                    $this->generations,

                'elite_count' =>
                    $this->eliteCount,

                'crossover_rate' =>
                    $this->crossoverRate,

                'mutation_rate' =>
                    $this->mutationRate,

                'skill_penalty' =>
                    $this->skillPenalty,

                'capacity_penalty' =>
                    $this->capacityPenalty
            ]
        ];
    }
}
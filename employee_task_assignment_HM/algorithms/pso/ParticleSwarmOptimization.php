<?php

require_once
    __DIR__ .
    '/../ga/fitness.php';

require_once
    __DIR__ .
    '/position.php';


class ParticleSwarmOptimization
{
    private array $employees;

    private array $tasks;

    private array $employeeSkills;

    private array $assignmentCosts;


    private int $iterations;


    private float $skillPenalty;

    private float $capacityPenalty;


    private float $cognitiveCoefficient;

    private float $socialCoefficient;


    private array $particles = [];

    private ?array $globalBest = null;


    private array $history = [];

    private array $detailedHistory = [];


    public function __construct(
        array $employees,
        array $tasks,
        array $employeeSkills,
        array $assignmentCosts,
        int $iterations = 100,
        float $skillPenalty = 10,
        float $capacityPenalty = 10,
        float $cognitiveCoefficient = 1.5,
        float $socialCoefficient = 1.5
    ) {

        $this->employees =
            $employees;

        $this->tasks =
            $tasks;

        $this->employeeSkills =
            $employeeSkills;

        $this->assignmentCosts =
            $assignmentCosts;

        $this->iterations =
            $iterations;

        $this->skillPenalty =
            $skillPenalty;

        $this->capacityPenalty =
            $capacityPenalty;

        $this->cognitiveCoefficient =
            $cognitiveCoefficient;

        $this->socialCoefficient =
            $socialCoefficient;
    }



    // Initialize particles from the shared population.
    public function initializeFromPopulation(
        array $initialPopulation
    ): void {

        $this->particles = [];

        $this->history = [];

        $this->detailedHistory = [];


        foreach (
            $initialPopulation as $index => $position
        ) {

            $fitnessResult =
                calculateFitness(
                    $position,
                    $this->employees,
                    $this->tasks,
                    $this->employeeSkills,
                    $this->assignmentCosts,
                    $this->skillPenalty,
                    $this->capacityPenalty
                );


            $this->particles[] = [

                'id' =>
                    $index + 1,

                'position' =>
                    $position,

                'velocity' =>
                    array_fill(
                        0,
                        count($position),
                        0.0
                    ),

                'fitness' =>
                    $fitnessResult,

                'personal_best' =>
                    $position,

                'personal_best_fitness' =>
                    $fitnessResult
            ];
        }


        $this->updateGlobalBest();
    }


    // Update global best.
    private function updateGlobalBest(): void
    {
        if (empty($this->particles)) {

            $this->globalBest =
                null;

            return;
        }


        $bestParticle =
            null;


        foreach (
            $this->particles as $particle
        ) {

            if (
                $bestParticle === null
            ) {

                $bestParticle =
                    $particle;

                continue;
            }


            if (
                $particle[
                    'personal_best_fitness'
                ]['fitness']
                <
                $bestParticle[
                    'personal_best_fitness'
                ]['fitness']
            ) {

                $bestParticle =
                    $particle;
            }
        }


        $bestFitness =
            $bestParticle[
                'personal_best_fitness'
            ];


        $this->globalBest = [

            'particle_id' =>
                $bestParticle['id'],

            'position' =>
                $bestParticle['personal_best'],

            'fitness' =>
                $bestFitness['fitness'],

            'cost' =>
                $bestFitness['total_cost'],

            'penalty' =>
                $bestFitness['penalty'],

            'skill_violations' =>
                $bestFitness['skill_violations'],

            'capacity_violations' =>
                $bestFitness['capacity_violations'],

            'workload' =>
                $bestFitness['workload']
        ];
    }



    // Find the best particle according to the current fitness values in the current iteration.

    private function getIterationBest(): ?array
    {
        if (empty($this->particles)) {
            return null;
        }


        $bestParticle =
            null;


        foreach (
            $this->particles as $particle
        ) {

            if (
                $bestParticle === null
            ) {

                $bestParticle =
                    $particle;

                continue;
            }


            if (
                $particle['fitness']['fitness']
                <
                $bestParticle['fitness']['fitness']
            ) {

                $bestParticle =
                    $particle;
            }
        }


        if ($bestParticle === null) {
            return null;
        }


        $fitness =
            $bestParticle['fitness'];


        return [

            'particle_id' =>
                $bestParticle['id'],

            'position' =>
                $bestParticle['position'],

            'fitness' =>
                $fitness['fitness'],

            'cost' =>
                $fitness['total_cost'],

            'penalty' =>
                $fitness['penalty'],

            'skill_violations' =>
                $fitness['skill_violations'],

            'capacity_violations' =>
                $fitness['capacity_violations'],

            'workload' =>
                $fitness['workload']
        ];
    }



    // Create a complete snapshot of all particles.
    private function getParticlesSnapshot(): array
    {
        $snapshot = [];


        foreach (
            $this->particles as $particle
        ) {

            $snapshot[] = [

                'particle_id' =>
                    $particle['id'],

                'position' =>
                    $particle['position'],

                'velocity' =>
                    $particle['velocity'],

                'fitness' =>
                    $particle['fitness']['fitness'],

                'cost' =>
                    $particle['fitness']['total_cost'],

                'penalty' =>
                    $particle['fitness']['penalty'],

                'skill_violations' =>
                    $particle['fitness']['skill_violations'],

                'capacity_violations' =>
                    $particle['fitness']['capacity_violations'],

                'personal_best' =>
                    $particle['personal_best'],

                'personal_best_fitness' =>
                    $particle[
                        'personal_best_fitness'
                    ]['fitness'],

                'personal_best_cost' =>
                    $particle[
                        'personal_best_fitness'
                    ]['total_cost'],

                'personal_best_penalty' =>
                    $particle[
                        'personal_best_fitness'
                    ]['penalty']
            ];
        }


        return $snapshot;
    }



    // Run PSO.

    public function run(): array
    {
        if (empty($this->particles)) {

            throw new RuntimeException(
                'PSO cannot run before population initialization.'
            );
        }


        $employeeIds =
            array_keys($this->employees);



        // Initial global best.

        $this->updateGlobalBest();


        for (
            $iteration = 1;
            $iteration <= $this->iterations;
            $iteration++
        ) {

            $iterationOperations =
                [];


            
            // Move every particle.

            foreach (
                $this->particles as $particleIndex => $particle
            ) {

                // Store the old position before updating the particle.
                $oldPosition =
                    $particle['position'];


                // Update position and velocity.
                $newPosition =
                    updateDiscretePosition(
                        $particle['position'],
                        $particle['personal_best'],
                        $this->globalBest['position'],
                        $employeeIds,
                        $this->particles[
                            $particleIndex
                        ]['velocity'],
                        $this->cognitiveCoefficient,
                        $this->socialCoefficient
                    );
                    $this->particles[$particleIndex]['velocity'] = array_map(
                        fn($velocity) => round($velocity, 1),
                        $this->particles[$particleIndex]['velocity']
                    );


                // Calculate fitness of the new position.
                $fitnessResult =
                    calculateFitness(
                        $newPosition,
                        $this->employees,
                        $this->tasks,
                        $this->employeeSkills,
                        $this->assignmentCosts,
                        $this->skillPenalty,
                        $this->capacityPenalty
                    );



                // Update current position.
                $this->particles[
                    $particleIndex
                ]['position'] =
                    $newPosition;

                // Update current fitness.
                $this->particles[
                    $particleIndex
                ]['fitness'] =
                    $fitnessResult;


                $personalBestUpdated =
                    false;

                if (
                    $fitnessResult['fitness']
                    <
                    $this->particles[
                        $particleIndex
                    ]['personal_best_fitness']['fitness']
                ) {

                    $this->particles[
                        $particleIndex
                    ]['personal_best'] =
                        $newPosition;


                    $this->particles[
                        $particleIndex
                    ]['personal_best_fitness'] =
                        $fitnessResult;


                    $personalBestUpdated =
                        true;
                }


                // Record the operation of this particle.
                $iterationOperations[] = [

                    'particle_id' =>
                        $particle['id'],

                    'old_position' =>
                        $oldPosition,

                    'new_position' =>
                        $newPosition,

                    'velocity' =>
                        $this->particles[
                            $particleIndex
                        ]['velocity'],

                    'fitness' =>
                        $fitnessResult['fitness'],

                    'cost' =>
                        $fitnessResult['total_cost'],

                    'penalty' =>
                        $fitnessResult['penalty'],

                    'personal_best_updated' =>
                        $personalBestUpdated
                ];
            }



        // Update global best after all particles have moved and their Personal Bests have been updated.
            $this->updateGlobalBest();


            // Find the best particle in this iteration.
            $iterationBest =
                $this->getIterationBest();



            // Store simple history, this is used for charts and general iteration progress.
            $this->history[] = [

                'iteration' =>
                    $iteration,

                'fitness' =>
                    $this->globalBest['fitness'],

                'cost' =>
                    $this->globalBest['cost'],

                'penalty' =>
                    $this->globalBest['penalty']
            ];

            $this->detailedHistory[] = [

                'iteration' =>
                    $iteration,

                'particle_count' =>
                    count($this->particles),


                'iteration_best' =>
                    $iterationBest,


                'global_best' => [

                    'particle_id' =>
                        $this->globalBest['particle_id'],

                    'position' =>
                        $this->globalBest['position'],

                    'fitness' =>
                        $this->globalBest['fitness'],

                    'cost' =>
                        $this->globalBest['cost'],

                    'penalty' =>
                        $this->globalBest['penalty'],

                    'skill_violations' =>
                        $this->globalBest[
                            'skill_violations'
                        ],

                    'capacity_violations' =>
                        $this->globalBest[
                            'capacity_violations'
                        ],

                    'workload' =>
                        $this->globalBest[
                            'workload'
                        ]
                ],


                'particles' =>
                    $this->getParticlesSnapshot(),


                'operations' =>
                    $iterationOperations
            ];
        }


        return [

            'algorithm' =>
                'PSO',

            'best' =>
                $this->globalBest,

            'history' =>
                $this->history,

            'detailed_history' =>
                $this->detailedHistory,

            'particles' =>
                $this->particles,

            'parameters' => [

                'iterations' =>
                    $this->iterations,

                'skill_penalty' =>
                    $this->skillPenalty,

                'capacity_penalty' =>
                    $this->capacityPenalty,

                'cognitive_coefficient' =>
                    $this->cognitiveCoefficient,

                'social_coefficient' =>
                    $this->socialCoefficient
            ]
        ];
    }


    public function getParticles(): array
    {
        return $this->particles;
    }


    public function getGlobalBest(): ?array
    {
        return $this->globalBest;
    }


    public function getHistory(): array
    {
        return $this->history;
    }


    public function getDetailedHistory(): array
    {
        return $this->detailedHistory;
    }
}
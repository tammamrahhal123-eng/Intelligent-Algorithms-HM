<?php

require_once __DIR__ . '/../ga/fitness.php';

class AntColonyOptimization
{
    private array $employees;
    private array $tasks;
    private array $employeeSkills;
    private array $assignmentCosts;
    private int $iterations;
    private float $skillPenalty;
    private float $capacityPenalty;
    private float $alpha;
    private float $beta;
    private float $rho;
    private float $q;

    private array $ants = [];
    private array $pheromone = [];
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
        float $alpha = 1.0,
        float $beta = 1.0,
        float $rho = 0.1,
        float $q = 10000.0
    ) {
        if ($iterations < 1) {
            throw new InvalidArgumentException('ACO iterations must be at least 1.');
        }
        if ($alpha < 0 || $beta < 0) {
            throw new InvalidArgumentException('ACO alpha and beta cannot be negative.');
        }
        if ($rho < 0 || $rho > 1) {
            throw new InvalidArgumentException('ACO rho must be between 0 and 1.');
        }
        if ($q <= 0) {
            throw new InvalidArgumentException('ACO Q must be greater than zero.');
        }

        $this->employees = $employees;
        $this->tasks = $tasks;
        $this->employeeSkills = $employeeSkills;
        $this->assignmentCosts = $assignmentCosts;
        $this->iterations = $iterations;
        $this->skillPenalty = $skillPenalty;
        $this->capacityPenalty = $capacityPenalty;
        $this->alpha = $alpha;
        $this->beta = $beta;
        $this->rho = $rho;
        $this->q = $q;
    }

    public function initializeFromPopulation(array $initialPopulation): void
    {
        if (empty($initialPopulation)) {
            throw new RuntimeException('ACO cannot initialize without an initial population.');
        }

        $this->ants = [];
        $this->pheromone = [];
        $this->globalBest = null;
        $this->history = [];
        $this->detailedHistory = [];

        $employeeIds = array_keys($this->employees);

        foreach ($this->tasks as $taskIndex => $task) {
            $taskId = (int) $task['id'];
            $this->pheromone[$taskId] = [];

            foreach ($employeeIds as $employeeId) {
                $this->pheromone[$taskId][(int) $employeeId] = 1.0;
            }
        }

        foreach ($initialPopulation as $index => $position) {
            $fitness = calculateFitness(
                $position,
                $this->employees,
                $this->tasks,
                $this->employeeSkills,
                $this->assignmentCosts,
                $this->skillPenalty,
                $this->capacityPenalty
            );

            $this->ants[] = [
                'id' => $index + 1,
                'position' => array_values($position),
                'fitness' => $fitness,
            ];
        }

        $this->updateGlobalBestFromAnts();

        // Use the common initial population as the initial pheromone seed.
        $initialDeposits = [];
        foreach ($this->ants as $ant) {
            $deposit = $this->q / max((float) $ant['fitness']['fitness'], 1e-9);
            $initialDeposits[] = [
                'ant_id' => $ant['id'],
                'deposit' => $deposit,
                'position' => $ant['position'],
            ];

            foreach ($ant['position'] as $taskIndex => $employeeId) {
                if (!isset($this->tasks[$taskIndex])) {
                    continue;
                }
                $taskId = (int) $this->tasks[$taskIndex]['id'];
                $employeeId = (int) $employeeId;
                if (isset($this->pheromone[$taskId][$employeeId])) {
                    $this->pheromone[$taskId][$employeeId] += $deposit;
                }
            }
        }

        $this->detailedHistory[] = [
            'stage' => 'initialization',
            'iteration' => 0,
            'ant_count' => count($this->ants),
            'pheromone' => $this->pheromone,
            'initial_ants' => $initialDeposits,
            'global_best' => $this->formatBest($this->globalBest),
        ];
    }

    public function run(): array
    {
        if (empty($this->ants)) {
            throw new RuntimeException('ACO must be initialized before run().');
        }

        $this->history = [];

        for ($iteration = 1; $iteration <= $this->iterations; $iteration++) {
            $pheromoneBefore = $this->pheromone;
            $constructionLog = [];
            $iterationAnts = [];

            foreach ($this->ants as $antIndex => $ant) {
                $construction = $this->constructSolution($ant['id']);
                $position = $construction['position'];

                $fitness = calculateFitness(
                    $position,
                    $this->employees,
                    $this->tasks,
                    $this->employeeSkills,
                    $this->assignmentCosts,
                    $this->skillPenalty,
                    $this->capacityPenalty
                );

                $this->ants[$antIndex]['position'] = $position;
                $this->ants[$antIndex]['fitness'] = $fitness;

                $constructionLog[] = [
                    'ant_id' => $ant['id'],
                    'steps' => $construction['steps'],
                ];

                $iterationAnts[] = $this->formatAnt($this->ants[$antIndex]);
            }

            $iterationBest = $this->getIterationBest();
            $this->updateGlobalBestFromAnts();

            $deposits = [];
            foreach ($this->ants as $ant) {
                $deposit = $this->q / max((float) $ant['fitness']['fitness'], 1e-9);
                $deposits[] = [
                    'ant_id' => $ant['id'],
                    'fitness' => $ant['fitness']['fitness'],
                    'deposit' => $deposit,
                    'edges' => [],
                ];

                $depositIndex = count($deposits) - 1;
                foreach ($ant['position'] as $taskIndex => $employeeId) {
                    if (!isset($this->tasks[$taskIndex])) {
                        continue;
                    }
                    $taskId = (int) $this->tasks[$taskIndex]['id'];
                    $employeeId = (int) $employeeId;
                    if (!isset($this->pheromone[$taskId][$employeeId])) {
                        continue;
                    }

                    $deposits[$depositIndex]['edges'][] = [
                        'task_id' => $taskId,
                        'employee_id' => $employeeId,
                    ];
                }
            }

            $evaporation = [];
            foreach ($this->pheromone as $taskId => &$employeeValues) {
                foreach ($employeeValues as $employeeId => &$value) {
                    $oldValue = (float) $value;
                    $newValue = $oldValue *(1- $this->rho);
                    $value = max($newValue, 1e-12);
                    $evaporation[] = [
                        'task_id' => (int) $taskId,
                        'employee_id' => (int) $employeeId,
                        'before' => $oldValue,
                        'after_evaporation' => $value,
                    ];
                }
                unset($value);
            }
            unset($employeeValues);

            // evaporation followed by new pheromone deposits.
            foreach ($this->ants as $ant) {
                $deposit = $this->q / max((float) $ant['fitness']['fitness'], 1e-9);
                foreach ($ant['position'] as $taskIndex => $employeeId) {
                    if (!isset($this->tasks[$taskIndex])) {
                        continue;
                    }
                    $taskId = (int) $this->tasks[$taskIndex]['id'];
                    $employeeId = (int) $employeeId;
                    if (isset($this->pheromone[$taskId][$employeeId])) {
                        $this->pheromone[$taskId][$employeeId] += $deposit;
                    }
                }
            }

            $globalBest = $this->formatBest($this->globalBest);
            $iterationBestFormatted = $this->formatBest($iterationBest);

            $this->history[] = [
                'iteration' => $iteration,
                'fitness' => $globalBest['fitness'] ?? null,
                'cost' => $globalBest['cost'] ?? null,
                'penalty' => $globalBest['penalty'] ?? null,
            ];

            $this->detailedHistory[] = [
                'stage' => 'iteration',
                'iteration' => $iteration,
                'ant_count' => count($this->ants),
                'pheromone_before' => $pheromoneBefore,
                'ants' => $iterationAnts,
                'construction' => $constructionLog,
                'iteration_best' => $iterationBestFormatted,
                'global_best' => $globalBest,
                'evaporation' => $evaporation,
                'deposits' => $deposits,
                'pheromone_after' => $this->pheromone,
            ];
        }

        return [
            'algorithm' => 'ACO',
            'algorithm_name' => 'Ant Colony Optimization',
            'best' => $this->formatBest($this->globalBest),
            'history' => $this->history,
            'detailed_history' => $this->detailedHistory,
            'ants' => array_map(fn($ant) => $this->formatAnt($ant), $this->ants),
            'parameters' => [
                'iterations' => $this->iterations,
                'skill_penalty' => $this->skillPenalty,
                'capacity_penalty' => $this->capacityPenalty,
                'alpha' => $this->alpha,
                'beta' => $this->beta,
                'rho' => $this->rho,
                'q' => $this->q,
            ],
        ];
    }

    private function constructSolution(int $antId): array
    {
        $position = [];
        $steps = [];

        $employeeIds = array_map('intval', array_keys($this->employees));

        foreach ($this->tasks as $taskIndex => $task) {
            $taskId = (int) $task['id'];

            $candidates = [];
            $probabilitySum = 0.0;

            foreach ($employeeIds as $employeeId) {
                $tau = (float) ($this->pheromone[$taskId][$employeeId] ?? 1.0);
                $cost = $this->getAssignmentCost($employeeId, $taskId);
                $eta = $cost > 0 ? 100/$cost : 1.0;

                $weight = pow(max($tau, 1e-12), $this->alpha)
                    * pow(max($eta, 1e-12), $this->beta);

                $candidates[] = [
                    'employee_id' => $employeeId,
                    'pheromone' => $tau,
                    'heuristic' => $eta,
                    'weight' => $weight,
                ];
                $probabilitySum += $weight;
            }

            if ($probabilitySum <= 0.0 || !is_finite($probabilitySum)) {
                $probabilitySum = count($candidates);
                foreach ($candidates as &$candidate) {
                    $candidate['weight'] = 1.0;
                }
                unset($candidate);
            }

            foreach ($candidates as &$candidate) {
                $candidate['probability'] = $candidate['weight'] / $probabilitySum;
            }
            unset($candidate);

            // Roulette-wheel selection over ALL employees.
            $random = mt_rand() / mt_getrandmax();
            $cumulative = 0.0;
            $selectedEmployee = (int) $employeeIds[array_key_last($employeeIds)];
            $selectedProbability = 0.0;

            foreach ($candidates as $candidate) {
                $cumulative += $candidate['probability'];
                if ($random <= $cumulative) {
                    $selectedEmployee = (int) $candidate['employee_id'];
                    $selectedProbability = (float) $candidate['probability'];
                    break;
                }
            }

            $position[] = $selectedEmployee;

            usort($candidates, fn($a, $b) => $b['probability'] <=> $a['probability']);
            $topCandidates = [];
            foreach (array_slice($candidates, 0, 5) as $candidate) {
                $topCandidates[] = [
                    'employee_id' => (int) $candidate['employee_id'],
                    'probability' => (float) $candidate['probability'],
                ];
            }

            $steps[] = [
                'task_index' => $taskIndex,
                'task_id' => $taskId,
                'candidate_count' => count($employeeIds),
                'top_candidates' => $topCandidates,
                'random_value' => $random,
                'selected_employee_id' => $selectedEmployee,
                'selected_probability' => $selectedProbability,
                'execution_time' => $this->getExecutionTime($selectedEmployee, $taskId),
            ];
        }

        return [
            'position' => $position,
            'steps' => $steps,
        ];
    }

    private function updateGlobalBestFromAnts(): void
    {
        foreach ($this->ants as $ant) {
            if (
                $this->globalBest === null ||
                $ant['fitness']['fitness'] < $this->globalBest['fitness']
            ) {
                $this->globalBest = [
                    'ant_id' => $ant['id'],
                    'position' => $ant['position'],
                    'fitness' => $ant['fitness']['fitness'],
                    'cost' => $ant['fitness']['total_cost'],
                    'penalty' => $ant['fitness']['penalty'],
                    'skill_violations' => $ant['fitness']['skill_violations'],
                    'capacity_violations' => $ant['fitness']['capacity_violations'],
                    'workload' => $ant['fitness']['workload'],
                ];
            }
        }
    }

    private function getIterationBest(): ?array
    {
        $best = null;
        foreach ($this->ants as $ant) {
            if ($best === null || $ant['fitness']['fitness'] < $best['fitness']) {
                $best = [
                    'ant_id' => $ant['id'],
                    'position' => $ant['position'],
                    'fitness' => $ant['fitness']['fitness'],
                    'cost' => $ant['fitness']['total_cost'],
                    'penalty' => $ant['fitness']['penalty'],
                    'skill_violations' => $ant['fitness']['skill_violations'],
                    'capacity_violations' => $ant['fitness']['capacity_violations'],
                    'workload' => $ant['fitness']['workload'],
                ];
            }
        }
        return $best;
    }

    private function formatAnt(array $ant): array
    {
        $fitness = $ant['fitness'] ?? [];
        return [
            'ant_id' => $ant['id'],
            'position' => $ant['position'],
            'fitness' => $fitness['fitness'] ?? null,
            'cost' => $fitness['total_cost'] ?? null,
            'penalty' => $fitness['penalty'] ?? null,
            'skill_violations' => $fitness['skill_violations'] ?? null,
            'capacity_violations' => $fitness['capacity_violations'] ?? null,
            'workload' => $fitness['workload'] ?? [],
        ];
    }

    private function formatBest(?array $best): ?array
    {
        if ($best === null) {
            return null;
        }
        return $best;
    }

    private function getExecutionTime(int $employeeId, int $taskId): float
    {
        return (float) ($this->assignmentCosts[$employeeId][$taskId]['execution_time'] ?? 0.0);
    }

    private function getAssignmentCost(int $employeeId, int $taskId): float
    {
        return (float) ($this->assignmentCosts[$employeeId][$taskId]['cost'] ?? 0.0);
    }
}

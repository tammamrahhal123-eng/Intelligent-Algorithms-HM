
<?php

session_start();

// Read experiment data

$experimentData =
    $_SESSION['experiment_result']
    ?? null;

if (is_array($experimentData)) {
    $results = $experimentData['results'] ?? [];
    $configuration = $experimentData['configuration']
        ?? $experimentData['experiment']
        ?? $_SESSION['experiment_configuration']
        ?? [];
    $experimentTime = $experimentData['experiment_time']
        ?? $_SESSION['experiment_time']
        ?? '';
    $tasks = $experimentData['tasks'] ?? [];
    $employees = $experimentData['employees'] ?? [];
} else {
    $results = $_SESSION['experiment_results'] ?? [];
    $configuration = $_SESSION['experiment_configuration'] ?? [];
    $experimentTime = $_SESSION['experiment_time'] ?? '';
    $tasks = [];
    $employees = [];
}

$selectedAlgorithms =
    $_SESSION['selected_algorithms']
    ?? [];

if (!is_array($results) || empty($results)) {
    header("Location: ../algorithms/algorithm.php");
    exit;
}



$normalizedResults = [];


foreach ($results as $key => $result) {
    if (!is_array($result)) {
        continue;
    }


   
    
    $rawAlgorithm =
        strtoupper((string) ($result['algorithm_code']
            ?? $result['algorithm']
            ?? $key));

    if (strpos($rawAlgorithm, 'ACO') !== false
        || strpos($rawAlgorithm, 'ANT COLONY') !== false) {
        $algorithmCode = 'ACO';
    } elseif (strpos($rawAlgorithm, 'PSO') !== false
        || strpos($rawAlgorithm, 'PARTICLE') !== false) {
        $algorithmCode = 'PSO';
    } elseif (strpos($rawAlgorithm, 'GA') !== false
        || strpos($rawAlgorithm, 'GENETIC') !== false) {
        $algorithmCode = 'GA';
    } else {
        $algorithmCode = $rawAlgorithm;
    }



   
    $best =
        $result['best']
        ?? [];


    

    $totalCost =
        (float) (
            $best['fitness']
            ?? $result['fitness']
            ?? 0
        );


    $fitness =
        (float) (
            $best['fitness']
            ?? $result['fitness']
            ?? 0
        );


    $penalty =
        (float) (
            $best['penalty']
            ?? $result['penalty']
            ?? 0
        );


    $skillViolations =
        (int) (
            $best['skill_violations']
            ?? $result['skill_violations']
            ?? 0
        );


    $capacityViolations =
        (float) (
            $best['capacity_violations']
            ?? $result['capacity_violations']
            ?? 0
        );


    $executionTime =
        (float) (
            $result['execution_time']
            ?? 0
        );




    $chromosome =
        $best['chromosome']
        ?? $best['position']
        ?? $result['best_chromosome']
        ?? $result['best_position']
        ?? [];




    $history =
        $result['history']
        ?? [];



    // Store normalized result
    $normalizedResults[$algorithmCode] = [

        'algorithm_code' =>
            $algorithmCode,

        'algorithm' =>
            $result['algorithm_name']
            ?? match ($algorithmCode) {
                'GA' => 'Genetic Algorithm',
                'PSO' => 'Particle Swarm Optimization',
                'ACO' => 'Ant Colony Optimization',
                default => ($result['algorithm'] ?? $algorithmCode),
            },

        'total_cost' =>
            $totalCost,

        'fitness' =>
            $fitness,

        'penalty' =>
            $penalty,

        'skill_violations' =>
            $skillViolations,

        'capacity_violations' =>
            $capacityViolations,

        'execution_time' =>
            $executionTime,

        'chromosome' =>
            $chromosome,

        'history' =>
            $history,

        'detailed_history' =>
            $result['detailed_history']
            ?? [],

        'parameters' =>
            $result['parameters']
            ?? []

    ];
}


if (empty($normalizedResults)) {

    die(
        "No valid experiment results were found."
    );
}




$algorithmCount =
    count($normalizedResults);


$bestCost =
    null;


$bestFitness =
    null;


$totalExecutionTime =
    0;


foreach (
    $normalizedResults
    as $result
) {

    $cost =
        $result['total_cost'];

    $fitness =
        $result['fitness'];

    $executionTime =
        $result['execution_time'];


    if (
        $bestCost === null ||
        $cost < $bestCost
    ) {
        $bestCost = $cost;
    }


    if (
        $bestFitness === null ||
        $fitness < $bestFitness
    ) {
        $bestFitness = $fitness;
    }


    $totalExecutionTime +=
        $executionTime;
}



// Solution quality and convergence metrics
$bestFitnessForQuality = $bestFitness;

foreach ($normalizedResults as $algorithmCode => &$result) {

    $fitnessValue = (float) $result['fitness'];

    if ($bestFitnessForQuality !== null && $fitnessValue > 0) {
        $result['solution_quality'] = max(
            0,
            min(100, ((float) $bestFitnessForQuality / $fitnessValue) * 100)
        );
    } elseif ($bestFitnessForQuality !== null && $fitnessValue == 0 && (float) $bestFitnessForQuality == 0) {
        $result['solution_quality'] = 100.0;
    } else {
        $result['solution_quality'] = null;
    }

    $historyRows = $result['history'] ?? [];
    $finalBest = (float) $result['fitness'];
    $convergenceIteration = null;
    $lastRecordedIteration = 0;

    foreach ($historyRows as $row) {
        $iterationValue = (int) ($row['generation'] ?? $row['iteration'] ?? 0);
        $historyFitness = $row['best_fitness']
            ?? $row['fitness']
            ?? null;

        if ($iterationValue > $lastRecordedIteration) {
            $lastRecordedIteration = $iterationValue;
        }

        if ($historyFitness !== null && (float) $historyFitness <= $finalBest + 1e-9) {
            if ($convergenceIteration === null || $iterationValue < $convergenceIteration) {
                $convergenceIteration = $iterationValue;
            }
        }
    }

    $result['convergence_iteration'] = $convergenceIteration;
}
unset($result);



// Chart data

$algorithmLabels = [];

$costData = [];

$fitnessData = [];

$executionTimeData = [];


foreach (
    $normalizedResults
    as $result
) {

    $algorithmLabels[] =
        $result['algorithm_code'];


    $costData[] =
        $result['total_cost'];


    $fitnessData[] =
        $result['fitness'];


    $executionTimeData[] =
        round(
            $result['execution_time'],
            6
        );
}



// Convergence data
$convergenceData = [];


foreach (
    $normalizedResults
    as $algorithmCode => $result
) {

    $history =
        $result['history']
        ?? [];


    $convergenceData[
        $algorithmCode
    ] = [];


    foreach ($history as $row) {

        $generation =
            $row['generation']
            ?? $row['iteration']
            ?? 0;


        $fitness =
            $row['best_fitness']
            ?? $row['fitness']
            ?? 0;


        $cost =
            $row['best_cost']
            ?? $row['cost']
            ?? 0;


        $convergenceData[
            $algorithmCode
        ][] = [

            'generation' =>
                (int) $generation,

            'fitness' =>
                (float) $fitness,

            'cost' =>
                (float) $cost

        ];
    }
}



// Get employees and tasks
require_once "../CONFIG/database.php";

$problemSize =
    (int) (
        $configuration['problem_size']
        ?? $configuration['problemSize']
        ?? count($tasks)
    );


if (empty($tasks) && $problemSize > 0) {

    $stmt =
        $pdo->prepare("
            SELECT
                id,
                name,
                required_skill_id
            FROM tasks
            ORDER BY id
            LIMIT :problem_size
        ");

    $stmt->bindValue(
        ':problem_size',
        $problemSize,
        PDO::PARAM_INT
    );

    $stmt->execute();

    $tasks =
        $stmt->fetchAll();

} elseif (empty($tasks)) {

    $stmt =
        $pdo->query("
            SELECT
                id,
                name,
                required_skill_id
            FROM tasks
            ORDER BY id
        ");

    $tasks =
        $stmt->fetchAll();
}



if (empty($employees)) {
    $stmt =
        $pdo->query("
            SELECT
                id,
                name
            FROM employees
            ORDER BY id
        ");

    $employees = $stmt->fetchAll();
}



$employeeNames = [];


foreach ($employees as $employee) {

    $employeeNames[
        (int) $employee['id']
    ] =
        $employee['name'];
}



$algorithmAssignments = [];

$bestSolutionText = [];

foreach ($normalizedResults as $algorithmCode => $normalizedResult) {

    $solution = $normalizedResult['chromosome'] ?? [];

    if (empty($solution)) {
        continue;
    }

    $algorithmAssignments[$algorithmCode] = [];
    $bestSolutionText[$algorithmCode] = [];

    foreach ($tasks as $index => $task) {

        if (!array_key_exists($index, $solution)) {
            continue;
        }

        $employeeId = (int) $solution[$index];

        $algorithmAssignments[$algorithmCode][] = [
            'task_id' => (int) $task['id'],
            'task_name' => $task['name'],
            'employee_id' => $employeeId,
            'employee_name' => $employeeNames[$employeeId] ?? 'Unknown'
        ];

        $bestSolutionText[$algorithmCode][] = $employeeId;
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Results | EmployeeTask Optimizer
    </title>

    <link
        rel="stylesheet"
        href="../CSS/style.css"
    >

    <script
        src="https://cdn.jsdelivr.net/npm/chart.js"
    ></script>
    
    <style>

        .results-page {
            max-width: 1250px;
            margin: 40px auto;
            padding: 0 25px 60px;
        }


        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
        }


        .page-header h1 {
            margin: 0 0 8px;
            font-size: 32px;
            color: #172033;
        }


        .page-header p {
            margin: 0;
            color: #697386;
            font-size: 14px;
        }


        .new-experiment-button {
            text-decoration: none;
            background: #4f46e5;
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .details-button {
            display: inline-block;
            text-decoration: none;
            background: #eef2ff;
            color: #4338ca;
            padding: 7px 11px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .details-button:hover {
            background: #e0e7ff;
        }


        .summary-grid {
            display: grid;
            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(210px, 1fr)
                );
            gap: 18px;
            margin-bottom: 30px;
        }


        .summary-card {
            background: #ffffff;
            border: 1px solid #e5e9f2;
            border-radius: 15px;
            padding: 22px;
        }


        .summary-label {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 9px;
        }


        .summary-value {
            color: #172033;
            font-size: 26px;
            font-weight: 800;
        }


        .summary-small {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 5px;
        }


        .section-card {
            background: #ffffff;
            border: 1px solid #e5e9f2;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
        }


        .section-title {
            margin: 0 0 20px;
            color: #172033;
            font-size: 20px;
        }


        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }


        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }


        .comparison-table th {
            background: #f8f9fc;
            color: #4b5563;
            font-size: 12px;
            text-align: left;
            padding: 13px;
            border-bottom: 1px solid #e5e9f2;
        }


        .comparison-table td {
            padding: 14px 13px;
            border-bottom: 1px solid #edf0f5;
            color: #374151;
            font-size: 13px;
        }


        .algorithm-badge {
            display: inline-block;
            background: #eef2ff;
            color: #4338ca;
            padding: 6px 10px;
            border-radius: 7px;
            font-weight: 800;
            font-size: 12px;
        }

        .best-solution-cell {
            min-width: 260px;
            max-width: 420px;
            line-height: 1.7;
            color: #374151;
            font-size: 12px;
            word-break: break-word;
        }


        .chart-grid {
            display: grid;
            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(350px, 1fr)
                );
            gap: 25px;
        }


        .chart-container {
            position: relative;
            height: 320px;
        }


        .chart-full {
            position: relative;
            height: 380px;
        }


        .config-grid {
            display: grid;
            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(180px, 1fr)
                );
            gap: 15px;
        }


        .config-item {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 14px;
        }


        .config-item span {
            display: block;
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 5px;
        }


        .config-item strong {
            color: #172033;
            font-size: 15px;
        }


        .assignment-table {
            width: 100%;
            border-collapse: collapse;
        }


        .assignment-table th {
            background: #f8f9fc;
            text-align: left;
            padding: 12px;
            color: #4b5563;
            font-size: 12px;
        }


        .assignment-table td {
            padding: 13px 12px;
            border-bottom: 1px solid #edf0f5;
            font-size: 13px;
            color: #374151;
        }


        .employee-badge {
            background: #ecfdf3;
            color: #16824a;
            padding: 5px 9px;
            border-radius: 7px;
            font-weight: 700;
        }


        .empty-message {
            color: #6b7280;
            font-size: 14px;
        }


        @media (max-width: 700px) {

            .page-header {
                flex-direction: column;
            }

            .new-experiment-button {
                width: 100%;
                text-align: center;
                box-sizing: border-box;
            }

        }

</style>

</head>


<body>


    <?php include "../includes/navbar.php"; ?>


    <div class="results-page">


        <div class="page-header">

            <div>

                <div class="section-label">
                    EXPERIMENT RESULTS
                </div>

                <h1>
                    Optimization Results
                </h1>

                <p>
                    Comparison of the selected optimization
                    algorithms for the Employee–Task Assignment problem.
                </p>

            </div>


            <a
                href="../algorithms/algorithms.php"
                class="new-experiment-button"
            >
                New Experiment
            </a>

        </div>


        <!-- Summary -->

        <div class="summary-grid">


            <div class="summary-card">

                <div class="summary-label">
                    Algorithms Tested
                </div>

                <div class="summary-value">
                    <?= $algorithmCount ?>
                </div>

                <div class="summary-small">
                    Selected algorithms
                </div>

            </div>


            <div class="summary-card">

                <div class="summary-label">
                    Problem Size
                </div>

                <div class="summary-value">

                    <?= htmlspecialchars(
                        $problemSize > 0
                            ? $problemSize
                            : '-'
                    ) ?>

                </div>

                <div class="summary-small">
                    Number of tasks
                </div>

            </div>


            <div class="summary-card">

                <div class="summary-label">
                    Best Total Cost
                </div>

                <div class="summary-value">

                    $
                    <?= number_format(
                        $bestCost ?? 0,
                        2
                    ) ?>

                </div>

                <div class="summary-small">
                    Lowest final assignment cost
                </div>

            </div>


            <div class="summary-card">

                <div class="summary-label">
                    Best Fitness
                </div>

                <div class="summary-value">

                    <?= number_format(
                        $bestFitness ?? 0,
                        2
                    ) ?>

                </div>

                <div class="summary-small">
                    Lowest final fitness value
                </div>

            </div>


            <div class="summary-card">

                <div class="summary-label">
                    Experiment Time
                </div>

                <div class="summary-value">

                    <?= number_format(
                        $totalExecutionTime,
                        4
                    ) ?>

                    s

                </div>

                <div class="summary-small">
                    Total algorithm execution time
                </div>

            </div>

        </div>


<!-- Algorithm Comparison -->

<div class="result-section">

    <div class="result-section-header">

        <div>

            <div class="section-label">
                COMPARISON
            </div>

            <h2>
                Algorithm Comparison
            </h2>

        </div>

    </div>


    <div class="table-wrapper">

        <table class="results-table">

            <thead>

                <tr>

                    <th>
                        Algorithm
                    </th>

                    <th>
                        Total Cost
                    </th>


                    <th>
                        Solution Quality
                    </th>

                    <th>
                        Convergence Speed
                    </th>

                    <th>
                        Penalty
                    </th>

                    <th>
                        Skill Violations
                    </th>

                    <th>
                        Capacity Violations
                    </th>

                    <th>
                        Best Solution
                    </th>

                    <th>
                        Execution Time
                    </th>

                    <th>
                        Details
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php foreach (
                $normalizedResults
                as $result
            ): ?>

                <tr>

                    <td>

                        <span class="algorithm-badge">

                            <?= htmlspecialchars(
                                $result['algorithm_code']
                            ) ?>

                        </span>

                    </td>


                    <td>

                        $
                        <?= number_format(
                            $result['fitness'],
                            2
                        ) ?>

                    </td>

                    <td>
                        <?php if ($result['solution_quality'] !== null): ?>
                            <strong><?= number_format($result['solution_quality'], 2) ?>%</strong>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($result['convergence_iteration'] !== null): ?>
                            <strong><?= (int) $result['convergence_iteration'] ?></strong>
                        <?php else: ?>
                            <strong>Not recorded</strong>
                        <?php endif; ?>
                    </td>

                    <td>

                        <?= number_format(
                            $result['penalty'],
                            2
                        ) ?>

                    </td>


                    <td>

                        <?= $result[
                            'skill_violations'
                        ] ?>

                    </td>


                    <td>

                        <?= number_format(
                            $result[
                                'capacity_violations'
                            ],
                            2
                        ) ?>

                    </td>


                    <td>

                        <div class="best-solution-cell">
                            [
                            <?php if (!empty($bestSolutionText[$result['algorithm_code']])): ?>

                                <?= htmlspecialchars(
                                    implode(', ', $bestSolutionText[$result['algorithm_code']]),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>
                            ]
                        </div>

                    </td>


                    <td>

                        <?= number_format(
                            $result[
                                'execution_time'
                            ],
                            6
                        ) ?>

                        s

                    </td>


                    <td>

                        <?php if (
                            in_array($result['algorithm_code'], ['GA', 'PSO', 'ACO'], true)
                        ): ?>

                            <a
                                href="report.php?algorithm=<?= strtolower(
                                    htmlspecialchars($result['algorithm_code'], ENT_QUOTES, 'UTF-8')
                                ) ?>"
                                class="details-button"
                            >
                                More Details
                            </a>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    <div class="result-section-header">

        <div>

            <div class="section-label">
                Solution Quality:
            </div>

            <h2>
                Relative to best final fitness (best fitness / this fitness)
            </h2>

            <div class="section-label">
                Convergence Speed:
            </div>

            <h2>
                Iteration / generation to final best
            </h2>

        </div>

    </div>

</div>
  


        <!-- Performance Charts -->

        <div class="result-section">


            <div class="result-section-header">

                <div>

                    <div class="section-label">
                        PERFORMANCE
                    </div>

                    <h2>
                        Performance Comparison
                    </h2>

                </div>

            </div>


            <div class="chart-grid">


                <div>

                    <h3>
                        Total Cost
                    </h3>

                    <div class="chart-container">

                        <canvas
                            id="costChart"
                        ></canvas>

                    </div>

                </div>


                <div>

                    <h3>
                        Execution Time
                    </h3>

                    <div class="chart-container">

                        <canvas
                            id="timeChart"
                        ></canvas>

                    </div>

                </div>


                <div>

                    <h3>
                        Final Fitness
                    </h3>

                    <div class="chart-container">

                        <canvas
                            id="fitnessChart"
                        ></canvas>

                    </div>

                </div>

            </div>

        </div>


        <!--Convergence -->

        <div class="result-section">


            <div class="result-section-header">

                <div>

                    <div class="section-label">
                        CONVERGENCE
                    </div>

                    <h2>
                        Optimization History
                    </h2>

                </div>

            </div>


            <p class="empty-message">

                The chart shows how the best fitness value changes across generations or iterations.
                The comparison table also reports relative solution quality and the first recorded
                iteration/generation at which each algorithm reached its final best fitness.
                Iterations per second is an estimate based on the recorded history length and total runtime.

            </p>


            <div class="chart-full">

                <canvas
                    id="convergenceChart"
                ></canvas>

            </div>

        </div>


        <!-- Experiment Configuration -->

        <div class="result-section">


            <div class="result-section-header">

                <div>

                    <div class="section-label">
                        SETTINGS
                    </div>

                    <h2>
                        Experiment Configuration
                    </h2>

                </div>

            </div>


            <div class="config-grid">


                <div class="config-item">

                    <span>
                        Problem Size
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $configuration[
                                'problem_size'
                            ]
                            ?? '-'
                        ) ?>

                        Tasks

                    </strong>

                </div>


                <div class="config-item">

                    <span>
                        Population Size
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $configuration[
                                'population_size'
                            ]
                            ?? '-'
                        ) ?>

                    </strong>

                </div>


                <div class="config-item">

                    <span>
                        Generations / Iterations
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $configuration[
                                'generations'
                            ]
                            ?? '-'
                        ) ?>

                    </strong>

                </div>


                <div class="config-item">

                    <span>
                        Elite Count
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $configuration[
                                'elite_count'
                            ]
                            ?? '-'
                        ) ?>

                    </strong>

                </div>


                <div class="config-item">

                    <span>
                        Crossover Rate
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $configuration[
                                'crossover_rate'
                            ]
                            ?? '-'
                        ) ?>

                    </strong>

                </div>


                <div class="config-item">

                    <span>
                        Mutation Rate
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $configuration[
                                'mutation_rate'
                            ]
                            ?? '-'
                        ) ?>

                    </strong>

                </div>


                <div class="config-item">

                    <span>
                        Skill Penalty
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $configuration[
                                'skill_penalty'
                            ]
                            ?? '-'
                        ) ?>

                    </strong>

                </div>


                <div class="config-item">

                    <span>
                        Capacity Penalty
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $configuration[
                                'capacity_penalty'
                            ]
                            ?? '-'
                        ) ?>

                    </strong>

                </div>


                <div class="config-item">

                    <span>
                        Experiment Time
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $experimentTime
                        ) ?>

                    </strong>

                </div>

            </div>

        </div>


        <!-- Best Assignments -->

        <?php foreach ($algorithmAssignments as $algorithmCode => $assignments): ?>

            <div class="result-section">

                <div class="result-section-header">

                    <div>

                        <div class="section-label">
                            BEST ASSIGNMENT
                        </div>

                        <h2>
                            <?= htmlspecialchars(
                                $normalizedResults[$algorithmCode]['algorithm']
                                ?? $algorithmCode,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h2>

                    </div>

                </div>

                <div class="table-wrapper">

                    <table class="results-table">

                        <thead>

                            <tr>
                                <th>Task ID</th>
                                <th>Task</th>
                                <th>Employee</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($assignments as $assignment): ?>

                            <tr>

                                <td>
                                    T<?= (int) $assignment['task_id'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $assignment['task_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <span class="employee-badge">
                                        <?= htmlspecialchars(
                                            $assignment['employee_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        <?php endforeach; ?>

    <script>



    // Chart Data


    const algorithmLabels =
        <?= json_encode(
            $algorithmLabels
        ) ?>;


    const costData =
        <?= json_encode(
            $costData
        ) ?>;


    const fitnessData =
        <?= json_encode(
            $fitnessData
        ) ?>;


    const executionTimeData =
        <?= json_encode(
            $executionTimeData
        ) ?>;


    const convergenceData =
        <?= json_encode(
            $convergenceData
        ) ?>;



    // Cost Chart
    new Chart(
        document.getElementById(
            'costChart'
        ),
        {

            type: 'bar',

            data: {

                labels:
                    algorithmLabels,

                datasets: [

                    {
                        label:
                            'Total Cost',

                        data:
                            costData,

                        borderWidth:
                            1
                    }

                ]

            },

            options: {

                responsive:
                    true,

                maintainAspectRatio:
                    false,

                plugins: {

                    legend: {
                        display:
                            false
                    }

                }

            }

        }
    );


    // Execution Time Chart

    new Chart(
        document.getElementById(
            'timeChart'
        ),
        {

            type: 'bar',

            data: {

                labels:
                    algorithmLabels,

                datasets: [

                    {
                        label:
                            'Execution Time (seconds)',

                        data:
                            executionTimeData,

                        borderWidth:
                            1
                    }

                ]

            },

            options: {

                responsive:
                    true,

                maintainAspectRatio:
                    false,

                plugins: {

                    legend: {
                        display:
                            false
                    }

                }

            }

        }
    );


    // Fitness Chart
    new Chart(
        document.getElementById(
            'fitnessChart'
        ),
        {

            type: 'bar',

            data: {

                labels:
                    algorithmLabels,

                datasets: [

                    {
                        label:
                            'Fitness',

                        data:
                            fitnessData,

                        borderWidth:
                            1
                    }

                ]

            },

            options: {

                responsive:
                    true,

                maintainAspectRatio:
                    false,

                plugins: {

                    legend: {
                        display:
                            false
                    }

                }

            }

        }
    );



    // Convergence Chart

    const convergenceDatasets = [];


    Object.keys(
        convergenceData
    ).forEach(
        function (algorithmKey) {

            const rows =
                convergenceData[
                    algorithmKey
                ];


            if (
                !rows.length
            ) {
                return;
            }


            convergenceDatasets.push({

                label:
                    algorithmKey,

                data:
                    rows.map(
                        function (row) {

                            return {

                                x:
                                    row.generation,

                                y:
                                    row.fitness

                            };

                        }
                    ),

                fill:
                    false,

                tension:
                    0.2

            });

        }
    );


    new Chart(
        document.getElementById(
            'convergenceChart'
        ),
        {

            type:
                'line',

            data: {

                datasets:
                    convergenceDatasets

            },

            options: {

                responsive:
                    true,

                maintainAspectRatio:
                    false,

                parsing:
                    false,

                scales: {

                    x: {

                        type:
                            'linear',

                        title: {

                            display:
                                true,

                            text:
                                'Generation / Iteration'

                        }

                    },

                    y: {

                        title: {

                            display:
                                true,

                            text:
                                'Fitness'

                        }

                    }

                }

            }

        }
    );

    </script>


</body>

</html>
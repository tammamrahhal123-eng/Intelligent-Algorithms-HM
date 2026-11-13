
<?php

session_start();

if (
    !isset($_SESSION['experiment_result']) ||
    empty($_SESSION['experiment_result'])
) {

    header(
        'Location: ../algorithms/algorithm.php'
    );

    exit;
}


$experimentResult =
    $_SESSION['experiment_result'];



$experiment =
    $experimentResult['experiment']
    ?? [];


$results =
    $experimentResult['results']
    ?? [];


$initialPopulation =
    $experimentResult['initial_population']
    ?? [];


$employees =
    $experimentResult['employees']
    ?? [];


$tasks =
    $experimentResult['tasks']
    ?? [];


$employeeSkills =
    $experimentResult['employee_skills']
    ?? [];


$assignmentCosts =
    $experimentResult['assignment_costs']
    ?? [];



if (empty($results)) {

    die('No algorithm results found.');
}

$algorithm =
    strtoupper(
        trim(
            $_GET['algorithm'] ?? ''
        )
    );



// If only one algorithm exists and no algorithm was specified automatically use it.
if (
    empty($algorithm) &&
    count($results) === 1
) {

    $algorithm =
        strtoupper(
            array_key_first($results)
        );
}


$allowedAlgorithms = [
    'GA',
    'PSO',
    'ACO'
];


if (
    !in_array(
        $algorithm,
        $allowedAlgorithms,
        true
    )
) {

    header(
        'Location: result.php'
    );

    exit;
}



if (
    !isset($results[$algorithm])
) {

    header(
        'Location: result.php'
    );

    exit;
}



$algorithmResult =
    $results[$algorithm];



$best =
    $algorithmResult['best']
    ?? [];


$bestSolution =
    $best['chromosome']
    ?? $best['position']
    ?? null;



$bestFitness =
    $best['fitness']
    ?? null;


$bestCost =
    $best['cost']
    ?? null;


$bestPenalty =
    $best['penalty']
    ?? null;


$skillViolations =
    $best['skill_violations']
    ?? null;


$capacityViolations =
    $best['capacity_violations']
    ?? null;


$workload =
    $best['workload']
    ?? null;



$executionTime =
    $algorithmResult['execution_time']
    ?? null;



$detailedHistory =
    $algorithmResult['detailed_history']
    ?? [];



// Experiment configuration
$problemSize =
    $experiment['problem_size']
    ?? 'N/A';


$populationSize =
    $experiment['population_size']
    ?? 'N/A';


$generations =
    $experiment['generations']
    ?? 'N/A';


$eliteCount =
    $experiment['elite_count']
    ?? 'N/A';


$crossoverRate =
    $experiment['crossover_rate']
    ?? 'N/A';


$mutationRate =
    $experiment['mutation_rate']
    ?? 'N/A';


$skillPenalty =
    $experiment['skill_penalty']
    ?? 'N/A';


$capacityPenalty =
    $experiment['capacity_penalty']
    ?? 'N/A';


$taskCount =
    $experiment['task_count']
    ?? count($tasks);


function h($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


function formatNumber(
    $value,
    int $decimals = 2
): string {

    if (
        is_numeric($value)
    ) {

        return number_format(
            (float) $value,
            $decimals
        );
    }

    return h($value);
}


function formatValue($value): string
{
    if (
        is_array($value)
    ) {

        return h(
            json_encode(
                $value,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            )
        );
    }


    if (
        is_bool($value)
    ) {

        return $value
            ? 'Yes'
            : 'No';
    }


    if (
        $value === null
    ) {

        return '—';
    }


    return h($value);
}



$algorithmName = match ($algorithm) {
    'GA' => 'Genetic Algorithm',
    'PSO' => 'Particle Swarm Optimization',
    'ACO' => 'Ant Colony Optimization',
    default => $algorithm
};

$algorithmParameters =
    $algorithmResult['parameters']
    ?? [];


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
        <?= h($algorithmName) ?> Report
    </title>

    <link
        rel="stylesheet"
        href="../CSS/style.css"
    >

    <style>


        .report-page {
            padding-top: 30px;
            padding-bottom: 60px;
        }

        .report-header {
            background: white;
            border-radius: 16px;
            padding: 28px 30px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        }

        .report-header h1 {
            margin: 6px 0 8px;
            font-size: 30px;
            color: #111827;
        }

        .report-header p {
            margin: 0;
            color: #6b7280;
        }

        .report-header-actions {
            flex-shrink: 0;
        }

        .result-section {
            background: white;
            border-radius: 16px;
            padding: 26px;
            margin-bottom: 24px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        }

        .result-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 22px;
        }

        .result-section-header h2 {
            margin: 0 0 6px;
            font-size: 21px;
            color: #111827;
        }

        .result-section-header p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(180px, 1fr)
            );
            gap: 14px;
        }

        .result-stat {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            background: #f9fafb;
        }

        .result-stat-label {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 7px;
        }

        .result-stat strong {
            display: block;
            font-size: 18px;
            color: #111827;
        }

        .best-solution-box {
            margin-top: 25px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #f9fafb;
        }

        .best-solution-box h3 {
            margin: 0 0 7px;
            color: #111827;
        }

        .best-solution-box .muted {
            margin-bottom: 18px;
        }

        .best-chromosome {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .gene-item {
            min-width: 70px;
            padding: 10px 12px;
            border: 1px solid #dbe2ea;
            border-radius: 9px;
            background: white;
            text-align: center;
        }

        .gene-task {
            display: block;
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .gene-item strong {
            color: #111827;
        }

        .generation-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .generation-header {
            background: #f9fafb;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .generation-header h3 {
            margin: 0;
            font-size: 17px;
            color: #111827;
        }

        .generation-number {
            display: inline-block;
            background: #eef2ff;
            color: #4338ca;
            padding: 5px 10px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
        }

        .generation-body {
            padding: 20px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(160px, 1fr)
            );
            gap: 12px;
            margin-bottom: 20px;
        }

        .metric {
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 13px;
            background: white;
        }

        .metric-label {
            display: block;
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 5px;
        }

        .metric-value {
            display: block;
            color: #111827;
            font-size: 15px;
        }

        .sub-title {
            margin: 20px 0 12px;
            font-size: 15px;
            color: #374151;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .results-table {
            width: 100%;
            min-width: 650px;
            border-collapse: collapse;
        }

        .results-table th {
            background: #f9fafb;
            color: #374151;
            font-size: 12px;
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .results-table td {
            padding: 12px;
            font-size: 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }

        .results-table tr:last-child td {
            border-bottom: none;
        }

        .code-box {
            background: #111827;
            color: #e5e7eb;
            border-radius: 9px;
            padding: 15px;
            overflow-x: auto;
            font-family: Consolas, Monaco, monospace;
            font-size: 12px;
            line-height: 1.7;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-yes {
            background: #dcfce7;
            color: #166534;
        }

        .badge-no {
            background: #fee2e2;
            color: #991b1b;
        }

        .mutation-item {
            margin-bottom: 6px;
        }

        .chromosome-cell {
            font-family: Consolas, Monaco, monospace;
            font-size: 12px;
            word-break: break-word;
        }

        .empty-state {
            padding: 25px;
            text-align: center;
            color: #6b7280;
            background: #f9fafb;
            border-radius: 9px;
            border: 1px dashed #d1d5db;
        }

        .report-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
            margin-bottom: 40px;
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

        .strategy-box {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 18px;
            background: #f9fafb;
            line-height: 1.7;
            color: #374151;
        }

        .strategy-box strong {
            color: #111827;
        }

        .strategy-flow {
            margin-top: 14px;
            padding: 14px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            color: #374151;
        }

        .aco-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }

        /* =========================================================
        REPORT RESPONSIVE
        ========================================================= */

        @media (max-width: 700px) {

            .report-page {
                padding-top: 20px;
            }

            .report-header {
                padding: 20px;
                flex-direction: column;
                align-items: flex-start;
            }

            .report-header h1 {
                font-size: 24px;
            }

            .result-section {
                padding: 18px;
            }

            .result-section-header {
                align-items: flex-start;
            }

            .generation-body {
                padding: 15px;
            }

            .report-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .report-actions .button {
                text-align: center;
            }
}
    </style>


</head>


<body>


<?php

require_once
    __DIR__ .
    '/../includes/navbar.php';

?>


<main class="container report-page">


    <section class="report-header">

        <div>

            <span class="section-label">
                Optimization Report
            </span>

            <h1>
                <?= h($algorithmName) ?> Report
            </h1>

            <p>
                Detailed execution history and optimization results.
            </p>

        </div>


        <div class="report-header-actions">

            <a
                href="result.php"
                class="details-button"
            >
                ← Back to Results
            </a>

        </div>

    </section>


    <!-- Algorithm Information -->

    <section class="result-section">


        <div class="result-section-header">

            <div>

                <h2>
                    Algorithm Information
                </h2>

                <p>
                    Selected algorithm and experiment size.
                </p>

            </div>

        </div>


        <div class="result-stats">


            <div class="result-stat">

                <span class="result-stat-label">
                    Algorithm
                </span>

                <strong>
                    <?= h($algorithmName) ?>
                </strong>

            </div>


            <div class="result-stat">

                <span class="result-stat-label">
                    Problem Size
                </span>

                <strong>
                    <?= formatValue($problemSize) ?>
                </strong>

            </div>


            <div class="result-stat">

                <span class="result-stat-label">
                    Task Count
                </span>

                <strong>
                    <?= formatValue($taskCount) ?>
                </strong>

            </div>


            <div class="result-stat">

                <span class="result-stat-label">
                    Population Size
                </span>

                <strong>
                    <?= formatValue($populationSize) ?>
                </strong>

            </div>


        </div>

    </section>


    <!-- Experiment Configuration -->

    <section class="result-section">


        <div class="result-section-header">

            <div>

                <h2>
                    Experiment Configuration
                </h2>

                <p>
                    Parameters used during the experiment.
                </p>

            </div>

        </div>


        <div class="result-stats">


            <div class="result-stat">

                <span class="result-stat-label">
                    Population Size
                </span>

                <strong>
                    <?= formatValue($populationSize) ?>
                </strong>

            </div>


            <div class="result-stat">

                <span class="result-stat-label">
                    Generations / Iterations
                </span>

                <strong>
                    <?= formatValue($generations) ?>
                </strong>

            </div>


            <?php if ($algorithm === 'GA'): ?>

                <div class="result-stat">

                    <span class="result-stat-label">
                        Elite Count
                    </span>

                    <strong>
                        <?= formatValue($eliteCount) ?>
                    </strong>

                </div>


                <div class="result-stat">

                    <span class="result-stat-label">
                        Crossover Rate
                    </span>

                    <strong>
                        <?= formatValue($crossoverRate) ?>
                    </strong>

                </div>


                <div class="result-stat">

                    <span class="result-stat-label">
                        Mutation Rate
                    </span>

                    <strong>
                        <?= formatValue($mutationRate) ?>
                    </strong>

                </div>

            <?php endif; ?>


            <?php if ($algorithm === 'ACO'): ?>

                <?php foreach (
                    [
                        'alpha' => 'Alpha',
                        'beta' => 'Beta',
                        'rho' => 'Rho (ρ)',
                        'q' => 'Pheromone Constant (Q)'
                    ] as $parameterKey => $parameterLabel
                ): ?>

                    <div class="result-stat">

                        <span class="result-stat-label">
                            <?= h($parameterLabel) ?>
                        </span>

                        <strong>
                            <?= formatValue(
                                $algorithmParameters[$parameterKey] ?? '—'
                            ) ?>
                        </strong>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>


            <div class="result-stat">

                <span class="result-stat-label">
                    Skill Penalty
                </span>

                <strong>
                    <?= formatValue($skillPenalty) ?>
                </strong>

            </div>


            <div class="result-stat">

                <span class="result-stat-label">
                    Capacity Penalty
                </span>

                <strong>
                    <?= formatValue($capacityPenalty) ?>
                </strong>

            </div>


        </div>

    </section>


    <!--Final Result-->

    <section class="result-section">


        <div class="result-section-header">

            <div>

                <h2>
                    Final Result
                </h2>

                <p>
                    Best solution found by the selected algorithm.
                </p>

            </div>

        </div>


        <div class="result-stats">


            <?php if ($bestFitness !== null): ?>

                <div class="result-stat">

                    <span class="result-stat-label">
                        Best Fitness
                    </span>

                    <strong>
                        <?= formatNumber(
                            $bestFitness,
                            4
                        ) ?>
                    </strong>

                </div>

            <?php endif; ?>


            <?php if ($bestCost !== null): ?>

                <div class="result-stat">

                    <span class="result-stat-label">
                        Total Cost
                    </span>

                    <strong>
                        <?= formatNumber(
                            $bestCost,
                            2
                        ) ?>
                    </strong>

                </div>

            <?php endif; ?>


            <?php if ($bestPenalty !== null): ?>

                <div class="result-stat">

                    <span class="result-stat-label">
                        Penalty
                    </span>

                    <strong>
                        <?= formatNumber(
                            $bestPenalty,
                            2
                        ) ?>
                    </strong>

                </div>

            <?php endif; ?>


            <?php if ($skillViolations !== null): ?>

                <div class="result-stat">

                    <span class="result-stat-label">
                        Skill Violations
                    </span>

                    <strong>
                        <?= formatNumber(
                            $skillViolations,
                            0
                        ) ?>
                    </strong>

                </div>

            <?php endif; ?>


            <?php if ($capacityViolations !== null): ?>

                <div class="result-stat">

                    <span class="result-stat-label">
                        Capacity Violations
                    </span>

                    <strong>
                        <?= formatNumber(
                            $capacityViolations,
                            2
                        ) ?>
                    </strong>

                </div>

            <?php endif; ?>


            <?php if ($executionTime !== null): ?>

                <div class="result-stat">

                    <span class="result-stat-label">
                        Execution Time
                    </span>

                    <strong>
                        <?= formatNumber(
                            $executionTime,
                            4
                        ) ?>
                        s
                    </strong>

                </div>

            <?php endif; ?>


        </div>


        <!-- Best Solution-->

        <?php if ($bestSolution !== null): ?>

            <div class="best-solution-box">

                <h3>
                    Best Solution
                </h3>


                <p class="muted">

                    <?php if ($algorithm === 'GA'): ?>

                        Best chromosome where each gene
                        represents the employee assigned to a task.

                    <?php elseif ($algorithm === 'PSO'): ?>

                        Best particle position where each position
                        represents the employee assigned to a task.

                    <?php else: ?>

                        Best ant solution where each position
                        represents the employee assigned to a task.

                    <?php endif; ?>

                </p>


                <div class="best-chromosome">

                    <?php foreach (
                        $bestSolution as $index => $employeeId
                    ): ?>

                        <div class="gene-item">

                            <span class="gene-task">
                                T<?= $index + 1 ?>
                            </span>

                            <strong>
                                E<?= h($employeeId) ?>
                            </strong>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php endif; ?>


    </section>

    <!-- Detailed History -->

    <section class="result-section">


        <div class="result-section-header">

            <div>

                <h2>
                    <?= $algorithm === 'ACO'
                        ? 'Optimization History'
                        : 'Detailed Execution History' ?>
                </h2>

                <p>
                    <?= $algorithm === 'ACO'
                        ? 'A simple view of the optimization progress and search strategy.'
                        : 'Progress of the algorithm during execution.' ?>
                </p>

            </div>

        </div>


        <?php if (empty($detailedHistory)): ?>

            <div class="empty-state">

                No detailed history was recorded
                for this experiment.

            </div>

        <?php elseif ($algorithm === 'ACO'): ?>

            <?php
            // Simplified ACO report
            $acoProgress = [];

            foreach ($detailedHistory as $acoIndex => $acoGeneration) {

                $acoIteration =
                    $acoGeneration['iteration']
                    ?? $acoGeneration['generation']
                    ?? ($acoIndex + 1);

                $acoBest =
                    $acoGeneration['global_best']
                    ?? $acoGeneration['iteration_best']
                    ?? $acoGeneration['best']
                    ?? [];

                if (!is_array($acoBest)) {
                    $acoBest = [];
                }

                $acoProgress[] = [
                    'iteration' => $acoIteration,
                    'fitness' => $acoBest['fitness'] ?? null,
                    'cost' => $acoBest['cost'] ?? null,
                ];
            }

            $firstAcoProgress = $acoProgress[0] ?? [];
            $lastAcoProgress = !empty($acoProgress)
                ? $acoProgress[count($acoProgress) - 1]
                : [];

            $firstFitness = $firstAcoProgress['fitness'] ?? null;
            $firstCost = $firstAcoProgress['cost'] ?? null;

            $finalAcoFitness = $bestFitness ?? ($lastAcoProgress['fitness'] ?? null);
            $finalAcoCost = $bestCost ?? ($lastAcoProgress['cost'] ?? null);

            $fitnessImprovement =
                is_numeric($firstFitness) && is_numeric($finalAcoFitness)
                ? (float) $firstFitness - (float) $finalAcoFitness
                : null;

            $costImprovement =
                is_numeric($firstCost) && is_numeric($finalAcoCost)
                ? (float) $firstCost - (float) $finalAcoCost
                : null;
            ?>

            <!-- ACO Optimization Progress -->

            <h4 class="sub-title">
                Optimization Progress
            </h4>

            <p class="muted">
                The table shows how the best solution changed during the ACO iterations.
            </p>

            <?php if (!empty($acoProgress)): ?>

                <div class="table-wrapper">

                    <table class="results-table">

                        <thead>
                            <tr>
                                <th>Iteration</th>
                                <th>Best Fitness</th>
                                <th>Best Cost</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($acoProgress as $progress): ?>

                            <tr>
                                <td>
                                    <strong>#<?= h($progress['iteration']) ?></strong>
                                </td>
                                <td>
                                    <?= $progress['fitness'] !== null
                                        ? formatNumber($progress['fitness'], 2)
                                        : '—' ?>
                                </td>
                                <td>
                                    <?= $progress['cost'] !== null
                                        ? formatNumber($progress['cost'], 2)
                                        : '—' ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-state">
                    No ACO progress data was recorded.
                </div>

            <?php endif; ?>


            <h4 class="sub-title">
                Optimization Summary
            </h4>

            <div class="aco-summary-grid">

                <div class="metric">
                    <span class="metric-label">Iterations</span>
                    <strong class="metric-value">
                        <?= formatValue($generations) ?>
                    </strong>
                </div>

                <div class="metric">
                    <span class="metric-label">First Recorded Fitness</span>
                    <strong class="metric-value">
                        <?= $firstFitness !== null
                            ? formatNumber($firstFitness, 2)
                            : '—' ?>
                    </strong>
                </div>

                <div class="metric">
                    <span class="metric-label">Final Best Fitness</span>
                    <strong class="metric-value">
                        <?= $finalAcoFitness !== null
                            ? formatNumber($finalAcoFitness, 2)
                            : '—' ?>
                    </strong>
                </div>

                <div class="metric">
                    <span class="metric-label">First Recorded Cost</span>
                    <strong class="metric-value">
                        <?= $firstCost !== null
                            ? formatNumber($firstCost, 2)
                            : '—' ?>
                    </strong>
                </div>

                <div class="metric">
                    <span class="metric-label">Final Best Cost</span>
                    <strong class="metric-value">
                        <?= $finalAcoCost !== null
                            ? formatNumber($finalAcoCost, 2)
                            : '—' ?>
                    </strong>
                </div>

                <div class="metric">
                    <span class="metric-label">Fitness Improvement</span>
                    <strong class="metric-value">
                        <?= $fitnessImprovement !== null
                            ? formatNumber($fitnessImprovement, 2)
                            : '—' ?>
                    </strong>
                </div>

                <div class="metric">
                    <span class="metric-label">Cost Improvement</span>
                    <strong class="metric-value">
                        <?= $costImprovement !== null
                            ? formatNumber($costImprovement, 2)
                            : '—' ?>
                    </strong>
                </div>

            </div>

        <?php else: ?>

            <?php foreach (
                $detailedHistory as $index => $generation
            ): ?>


                <?php
                $generationNumber =
                    $generation['generation']
                    ?? $generation['iteration']
                    ?? ($index + 1);


                $generationPopulationSize =
                    $generation['population_size']
                    ?? $generation['particle_count']
                    ?? null;


                $generationBest =
                    $generation['best']
                    ?? ($algorithm === 'ACO'
                        ? (
                            $generation['global_best']
                            ?? $generation['iteration_best']
                            ?? []
                        )
                        : []);


                $generationBestChromosome =
                    $generationBest['chromosome']
                    ?? $generationBest['position']
                    ?? null;


                $generationBestFitness =
                    $generationBest['fitness']
                    ?? null;


                $generationBestCost =
                    $generationBest['cost']
                    ?? null;


                $generationBestPenalty =
                    $generationBest['penalty']
                    ?? null;


                $generationSkillViolations =
                    $generationBest['skill_violations']
                    ?? null;


                $generationCapacityViolations =
                    $generationBest['capacity_violations']
                    ?? null;


                $generationWorkload =
                    $generationBest['workload']
                    ?? null;



                $operations =
                    $generation['operations']
                    ?? [];


                // PSO details
                $iterationBest =
                    $generation['iteration_best']
                    ?? $generation['current_best']
                    ?? null;

                $globalBest =
                    $generation['global_best']
                    ?? $generation['gbest']
                    ?? null;

                $particleStates =
                    $generation['particles']
                    ?? $generation['particle_states']
                    ?? $generation['swarm']
                    ?? [];



                if (
                    $algorithm === 'PSO' &&
                    $globalBest === null &&
                    !empty($generationBest)
                ) {
                    $globalBest = $generationBest;
                }


                $iterationBestPosition =
                    is_array($iterationBest)
                    ? (
                        $iterationBest['position']
                        ?? $iterationBest['chromosome']
                        ?? null
                    )
                    : null;


                $iterationBestFitness =
                    is_array($iterationBest)
                    ? (
                        $iterationBest['fitness']
                        ?? null
                    )
                    : null;


                $globalBestPosition =
                    is_array($globalBest)
                    ? (
                        $globalBest['position']
                        ?? $globalBest['chromosome']
                        ?? null
                    )
                    : null;


                $globalBestFitness =
                    is_array($globalBest)
                    ? (
                        $globalBest['fitness']
                        ?? null
                    )
                    : null;

                ?>


                <div class="generation-card">

                    <div class="generation-header">

                        <div>

                            <h3>

                                <?php if ($algorithm === 'GA'): ?>

                                    Generation
                                    <?= h($generationNumber) ?>

                                <?php else: ?>

                                    Iteration
                                    <?= h($generationNumber) ?>

                                <?php endif; ?>

                            </h3>

                        </div>


                        <span class="generation-number">

                            #<?= h($generationNumber) ?>

                        </span>

                    </div>


                    <div class="generation-body">

                        <div class="metrics-grid">


                            <?php if (
                                $generationPopulationSize !== null &&
                                $algorithm === 'GA'
                            ): ?>

                                <div class="metric">

                                    <span class="metric-label">

                                        <?php if (
                                            $algorithm === 'GA'
                                        ): ?>

                                            Population Size

                                        <?php else: ?>

                                            Particle Count

                                        <?php endif; ?>

                                    </span>

                                    <strong class="metric-value">

                                        <?= formatValue(
                                            $generationPopulationSize
                                        ) ?>

                                    </strong>

                                </div>

                            <?php endif; ?>


                            <?php if (
                                $generationBestFitness !== null
                            ): ?>

                                <div class="metric">

                                    <span class="metric-label">
                                        Best Fitness
                                    </span>

                                    <strong class="metric-value">

                                        <?= formatNumber(
                                            $generationBestFitness,
                                            2
                                        ) ?>

                                    </strong>

                                </div>

                            <?php endif; ?>


                            <?php if (
                                $generationBestCost !== null
                            ): ?>

                                <div class="metric">

                                    <span class="metric-label">
                                        Best Cost
                                    </span>

                                    <strong class="metric-value">

                                        <?= formatNumber(
                                            $generationBestCost,
                                            2
                                        ) ?>

                                    </strong>

                                </div>

                            <?php endif; ?>


                            <?php if (
                                $generationBestPenalty !== null
                            ): ?>

                                <div class="metric">

                                    <span class="metric-label">
                                        Penalty
                                    </span>

                                    <strong class="metric-value">

                                        <?= formatNumber(
                                            $generationBestPenalty,
                                            2
                                        ) ?>

                                    </strong>

                                </div>

                            <?php endif; ?>


                            <?php if (
                                $generationSkillViolations !== null
                            ): ?>

                                <div class="metric">

                                    <span class="metric-label">
                                        Skill Violations
                                    </span>

                                    <strong class="metric-value">

                                        <?= formatNumber(
                                            $generationSkillViolations,
                                            0
                                        ) ?>

                                    </strong>

                                </div>

                            <?php endif; ?>


                            <?php if (
                                $generationCapacityViolations !== null
                            ): ?>

                                <div class="metric">

                                    <span class="metric-label">
                                        Capacity Violations
                                    </span>

                                    <strong class="metric-value">

                                        <?= formatNumber(
                                            $generationCapacityViolations,
                                            2
                                        ) ?>

                                    </strong>

                                </div>

                            <?php endif; ?>


                        </div>

                        <?php if (
                            $generationBestChromosome !== null
                        ): ?>


                            <h4 class="sub-title">

                                <?php if (
                                    $algorithm === 'GA'
                                ): ?>

                                    Best Chromosome

                                <?php else: ?>

                                    <?= $algorithm === 'ACO' ? 'Best Ant Position' : 'Best Position' ?>

                                <?php endif; ?>

                            </h4>


                            <div class="code-box">

                                <?= formatValue(
                                    $generationBestChromosome
                                ) ?>

                            </div>


                        <?php endif; ?>


                        <!-- PSO Detailed Information -->

                        <?php if ($algorithm === 'PSO'): ?>

                            <h4 class="sub-title">
                                Particle information in this iteration
                            </h4>


                            <div class="metrics-grid">

                                <?php if ($iterationBestFitness !== null): ?>

                                    <div class="metric">

                                        <span class="metric-label">
                                            In this iteration, fitness is better.
                                        </span>

                                        <strong class="metric-value">
                                            <?= formatNumber(
                                                $iterationBestFitness,
                                                2
                                            ) ?>
                                        </strong>

                                    </div>

                                <?php endif; ?>


                                <?php if ($globalBestFitness !== null): ?>

                                    <div class="metric">

                                        <span class="metric-label">
                                            best Fitness GBest
                                        </span>

                                        <strong class="metric-value">
                                            <?= formatNumber(
                                                $globalBestFitness,
                                                2
                                            ) ?>
                                        </strong>

                                    </div>

                                <?php endif; ?>


                                <div class="metric">

                                    <span class="metric-label">
                                        Number of recorded particles
                                    </span>

                                    <strong class="metric-value">
                                        <?= is_array($particleStates)
                                            ? count($particleStates)
                                            : 0 ?>
                                    </strong>

                                </div>

                            </div>


                            <?php if ($iterationBestPosition !== null): ?>

                                <h4 class="sub-title">
                                    The best solution in this iteration
                                </h4>

                                <div class="code-box">
                                    <?= formatValue(
                                        $iterationBestPosition
                                    ) ?>
                                </div>

                            <?php endif; ?>


                            <?php if ($globalBestPosition !== null): ?>

                                <h4 class="sub-title">
                                    GBest for the best solution
                                </h4>

                                <div class="code-box">
                                    <?= formatValue(
                                        $globalBestPosition
                                    ) ?>
                                </div>

                            <?php endif; ?>


                            <?php if (
                                is_array($particleStates) &&
                                !empty($particleStates)
                            ): ?>

                                <h4 class="sub-title">
                                    Details for each particle
                                </h4>


                                <div class="table-wrapper">

                                    <table class="results-table">

                                        <thead>

                                            <tr>

                                                <th>
                                                    particle
                                                </th>

                                                <th>
                                                    current position
                                                </th>

                                                <th>
                                                    velocity
                                                </th>

                                                <th>
                                                    PBest
                                                </th>

                                                <th>
                                                    current Fitness 
                                                </th>

                                                <th>
                                                    the Fitness for PBest
                                                </th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            <?php foreach (
                                                $particleStates
                                                as $particleIndex
                                                => $particle
                                            ): ?>

                                                <?php

                                                $currentPosition =
                                                    $particle['position']
                                                    ?? $particle['current_position']
                                                    ?? $particle['current_solution']
                                                    ?? null;


                                                $velocity =
                                                    $particle['velocity']
                                                    ?? null;


                                                $personalBest =
                                                    $particle['personal_best']
                                                    ?? $particle['pbest']
                                                    ?? $particle['best_position']
                                                    ?? null;


                                                $currentFitness =
                                                    $particle['fitness']
                                                    ?? $particle['current_fitness']
                                                    ?? null;


                                                $personalBestFitness =
                                                    $particle['personal_best_fitness']
                                                    ?? $particle['pbest_fitness']
                                                    ?? $particle['best_fitness']
                                                    ?? null;

                                                ?>


                                                <tr>

                                                    <td>
                                                        <strong>
                                                            #<?= $particleIndex + 1 ?>
                                                        </strong>
                                                    </td>


                                                    <td class="chromosome-cell">
                                                        <?= formatValue(
                                                            $currentPosition
                                                        ) ?>
                                                    </td>


                                                    <td class="chromosome-cell">
                                                        <?= formatValue(
                                                            $velocity
                                                        ) ?>
                                                    </td>


                                                    <td class="chromosome-cell">
                                                        <?= formatValue(
                                                            $personalBest
                                                        ) ?>
                                                    </td>


                                                    <td>

                                                        <?php if (
                                                            $currentFitness !== null
                                                        ): ?>

                                                            <?= formatNumber(
                                                                $currentFitness,
                                                                2
                                                            ) ?>

                                                        <?php else: ?>

                                                            —

                                                        <?php endif; ?>

                                                    </td>


                                                    <td>

                                                        <?php if (
                                                            $personalBestFitness !== null
                                                        ): ?>

                                                            <?= formatNumber(
                                                                $personalBestFitness,
                                                                2
                                                            ) ?>

                                                        <?php else: ?>

                                                            —

                                                        <?php endif; ?>

                                                    </td>

                                                </tr>

                                            <?php endforeach; ?>

                                        </tbody>

                                    </table>

                                </div>


                            <?php else: ?>

                                <div class="empty-state">

                                    The particle position, velocity, and pbest for this iteration were not recorded.

                                </div>

                            <?php endif; ?>


                        <?php endif; ?>


                        <!--  ACO Detailed Information -->

                        <?php if ($algorithm === 'ACO'): ?>

                            <h4 class="sub-title">
                                Ant Colony Construction & Pheromone Update
                            </h4>

                            <div class="metrics-grid">

                                <?php if (is_array($iterationBest) && isset($iterationBest['fitness'])): ?>
                                    <div class="metric">
                                        <span class="metric-label">
                                            Iteration Best Fitness
                                        </span>
                                        <strong class="metric-value">
                                            <?= formatNumber($iterationBest['fitness'], 2) ?>
                                        </strong>
                                    </div>
                                <?php endif; ?>

                                <?php if (is_array($globalBest) && isset($globalBest['fitness'])): ?>
                                    <div class="metric">
                                        <span class="metric-label">
                                            Global Best Fitness
                                        </span>
                                        <strong class="metric-value">
                                            <?= formatNumber($globalBest['fitness'], 2) ?>
                                        </strong>
                                    </div>
                                <?php endif; ?>

                                <div class="metric">
                                    <span class="metric-label">
                                        Ant Count
                                    </span>
                                    <strong class="metric-value">
                                        <?= formatValue($generation['ant_count'] ?? count($generation['ants'] ?? [])) ?>
                                    </strong>
                                </div>

                                <?php if (isset($algorithmParameters['alpha'])): ?>
                                    <div class="metric">
                                        <span class="metric-label">Alpha (α)</span>
                                        <strong class="metric-value">
                                            <?= formatNumber($algorithmParameters['alpha'], 2) ?>
                                        </strong>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($algorithmParameters['beta'])): ?>
                                    <div class="metric">
                                        <span class="metric-label">Beta (β)</span>
                                        <strong class="metric-value">
                                            <?= formatNumber($algorithmParameters['beta'], 2) ?>
                                        </strong>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($algorithmParameters['rho'])): ?>
                                    <div class="metric">
                                        <span class="metric-label">Rho (ρ)</span>
                                        <strong class="metric-value">
                                            <?= formatNumber($algorithmParameters['rho'], 2) ?>
                                        </strong>
                                    </div>
                                <?php endif; ?>

                            </div>

                            <?php
                            $acoAnts = $generation['ants'] ?? $generation['initial_ants'] ?? [];
                            $acoConstruction = $generation['construction'] ?? [];
                            $acoDeposits = $generation['deposits'] ?? [];
                            $acoEvaporation = $generation['evaporation'] ?? [];
                            $pheromoneBefore = $generation['pheromone_before'] ?? null;
                            $pheromoneAfter = $generation['pheromone_after'] ?? null;
                            ?>

                            <?php if (!empty($acoAnts)): ?>

                                <h4 class="sub-title">
                                    Ant Evaluation After Constructing the Solution
                                </h4>

                                <div class="table-wrapper">
                                    <table class="results-table">
                                        <thead>
                                            <tr>
                                                <th>Ant</th>
                                                <th>Solution</th>
                                                <th>Cost</th>
                                                <th>Penalty</th>
                                                <th>Fitness</th>
                                                <th>Skill Violations</th>
                                                <th>Capacity Violations</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($acoAnts as $ant): ?>
                                            <tr>
                                                <td><strong>#<?= h($ant['ant_id'] ?? '—') ?></strong></td>
                                                <td class="chromosome-cell">
                                                    <?= formatValue($ant['position'] ?? null) ?>
                                                </td>
                                                <td><?= formatNumber($ant['cost'] ?? null, 2) ?></td>
                                                <td><?= formatNumber($ant['penalty'] ?? null, 2) ?></td>
                                                <td><?= formatNumber($ant['fitness'] ?? null, 2) ?></td>
                                                <td><?= formatNumber($ant['skill_violations'] ?? null, 0) ?></td>
                                                <td><?= formatNumber($ant['capacity_violations'] ?? null, 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                            <?php endif; ?>

                            <?php if (!empty($acoConstruction)): ?>

                                <h4 class="sub-title">
                                    Construction Stage — Probability and Selected Employee
                                </h4>

                                <?php foreach ($acoConstruction as $antConstruction): ?>

                                    <div class="best-solution-box">
                                        <h3>
                                            Ant #<?= h($antConstruction['ant_id'] ?? '—') ?>
                                        </h3>

                                        <div class="table-wrapper">
                                            <table class="results-table">
                                                <thead>
                                                    <tr>
                                                        <th>Task</th>
                                                        <th>Candidate Employees</th>
                                                        <th>Employee Probabilities</th>
                                                        <th>Random Value</th>
                                                        <th>Selected Employee</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach (($antConstruction['steps'] ?? []) as $step): ?>
                                                    <tr>
                                                        <td>
                                                            T<?= h($step['task_id'] ?? '—') ?>
                                                        </td>
                                                        <td class="chromosome-cell">
                                                            <?= formatValue($step['candidate_employees'] ?? []) ?>
                                                        </td>
                                                        <td>
                                                            <?php foreach (($step['candidates'] ?? []) as $candidate): ?>
                                                                <div>
                                                                    Employee
                                                                    <strong>#<?= h($candidate['employee_id'] ?? '—') ?></strong>
                                                                    —
                                                                    P = <?= formatNumber($candidate['probability'] ?? null, 4) ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </td>
                                                        <td>
                                                            <?= formatNumber($step['random_value'] ?? null, 4) ?>
                                                        </td>
                                                        <td>
                                                            <strong>
                                                                #<?= h($step['selected_employee_id'] ?? '—') ?>
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                            <?php if (!empty($acoDeposits)): ?>

                                <h4 class="sub-title">
                                    Pheromone Deposit
                                </h4>

                                <div class="table-wrapper">
                                    <table class="results-table">
                                        <thead>
                                            <tr>
                                                <th>Ant</th>
                                                <th>Fitness</th>
                                                <th>Deposit Amount</th>
                                                <th>Task → Employee Edges</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($acoDeposits as $deposit): ?>
                                            <tr>
                                                <td>#<?= h($deposit['ant_id'] ?? '—') ?></td>
                                                <td><?= formatNumber($deposit['fitness'] ?? null, 2) ?></td>
                                                <td><?= formatNumber($deposit['deposit'] ?? null, 6) ?></td>
                                                <td class="chromosome-cell">
                                                    <?php foreach (($deposit['edges'] ?? []) as $edge): ?>
                                                        T<?= h($edge['task_id'] ?? '—') ?>
                                                        → E<?= h($edge['employee_id'] ?? '—') ?><br>
                                                    <?php endforeach; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                            <?php endif; ?>

                            <?php if (!empty($acoEvaporation)): ?>

                                <h4 class="sub-title">
                                    Pheromone Evaporation
                                </h4>

                                <div class="table-wrapper">
                                    <table class="results-table">
                                        <thead>
                                            <tr>
                                                <th>Task</th>
                                                <th>Employee</th>
                                                <th>Before</th>
                                                <th>After Evaporation</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($acoEvaporation as $row): ?>
                                            <tr>
                                                <td>T<?= h($row['task_id'] ?? '—') ?></td>
                                                <td>E<?= h($row['employee_id'] ?? '—') ?></td>
                                                <td><?= formatNumber($row['before'] ?? null, 6) ?></td>
                                                <td><?= formatNumber($row['after_evaporation'] ?? null, 6) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                            <?php endif; ?>

                            <?php if ($pheromoneBefore !== null): ?>

                                <h4 class="sub-title">
                                    Pheromone Matrix Before Update
                                </h4>

                                <div class="code-box">
                                    <?= formatValue($pheromoneBefore) ?>
                                </div>

                            <?php endif; ?>

                            <?php if ($pheromoneAfter !== null): ?>

                                <h4 class="sub-title">
                                    Pheromone Matrix After Update
                                </h4>

                                <div class="code-box">
                                    <?= formatValue($pheromoneAfter) ?>
                                </div>

                            <?php endif; ?>

                        <?php endif; ?>


                        <?php if (
                            $algorithm === 'GA' &&
                            !empty($operations)
                        ): ?>


                            <h4 class="sub-title">

                                Crossover & Mutation Operations

                            </h4>


                            <div class="table-wrapper">

                                <table class="results-table">


                                    <thead>

                                        <tr>

                                            <th>
                                                #
                                            </th>

                                            <th>
                                                Crossover
                                            </th>

                                            <th>
                                                Point
                                            </th>

                                            <th>
                                                Child
                                            </th>

                                            <th>
                                                Mutation
                                            </th>

                                            <th>
                                                Changes
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                        <?php foreach (
                                            $operations
                                            as $operationIndex
                                            => $operation
                                        ): ?>


                                            <?php

                                            $crossover =
                                                $operation['crossover']
                                                ?? [];


                                            $crossoverPerformed =
                                                $crossover['performed']
                                                ?? false;


                                            $crossoverPoint =
                                                $crossover['point']
                                                ?? null;


                                            $child =
                                                $crossover['child']
                                                ?? null;


                                            $mutation =
                                                $operation['mutation']
                                                ?? [];


                                            $mutationPerformed =
                                                $mutation['performed']
                                                ?? false;


                                            $mutations =
                                                $mutation['mutations']
                                                ?? [];

                                            ?>


                                            <tr>


                                                <td>

                                                    <?= $operationIndex + 1 ?>

                                                </td>


                                                <td>

                                                    <span
                                                        class="
                                                            badge
                                                            <?= $crossoverPerformed
                                                                ? 'badge-yes'
                                                                : 'badge-no'
                                                            ?>
                                                        "
                                                    >

                                                        <?= $crossoverPerformed
                                                            ? 'Yes'
                                                            : 'No'
                                                        ?>

                                                    </span>

                                                </td>


                                                <td>

                                                    <?php if (
                                                        $crossoverPoint
                                                        !== null
                                                    ): ?>

                                                        <?= h(
                                                            $crossoverPoint
                                                        ) ?>

                                                    <?php else: ?>

                                                        —

                                                    <?php endif; ?>

                                                </td>


                                                <td>

                                                    <?php if (
                                                        $child !== null
                                                    ): ?>

                                                        <?= formatValue(
                                                            $child
                                                        ) ?>

                                                    <?php else: ?>

                                                        —

                                                    <?php endif; ?>

                                                </td>


                                                <td>

                                                    <span
                                                        class="
                                                            badge
                                                            <?= $mutationPerformed
                                                                ? 'badge-yes'
                                                                : 'badge-no'
                                                            ?>
                                                        "
                                                    >

                                                        <?= $mutationPerformed
                                                            ? 'Yes'
                                                            : 'No'
                                                        ?>

                                                    </span>

                                                </td>


                                                <td>


                                                    <?php if (
                                                        !empty($mutations)
                                                    ): ?>


                                                        <?php foreach (
                                                            $mutations
                                                            as $mutationItem
                                                        ): ?>


                                                            <?php

                                                            $position =
                                                                $mutationItem[
                                                                    'position'
                                                                ]
                                                                ?? null;


                                                            $oldValue =
                                                                $mutationItem[
                                                                    'old_value'
                                                                ]
                                                                ?? null;


                                                            $newValue =
                                                                $mutationItem[
                                                                    'new_value'
                                                                ]
                                                                ?? null;

                                                            ?>


                                                            <div
                                                                class="mutation-item"
                                                            >

                                                                Position:

                                                                <strong>
                                                                    <?= h(
                                                                        $position
                                                                    ) ?>
                                                                </strong>

                                                                <br>

                                                                <?= h(
                                                                    $oldValue
                                                                ) ?>

                                                                →

                                                                <?= h(
                                                                    $newValue
                                                                ) ?>

                                                            </div>


                                                        <?php endforeach; ?>


                                                    <?php else: ?>

                                                        —

                                                    <?php endif; ?>


                                                </td>


                                            </tr>


                                        <?php endforeach; ?>


                                    </tbody>


                                </table>

                            </div>


                        <?php elseif (
                            $algorithm === 'GA'
                        ): ?>


                            <div class="empty-state">

                                No crossover or mutation operations
                                were recorded for this generation.

                            </div>


                        <?php endif; ?>


                    </div>

                </div>


            <?php endforeach; ?>

        <?php endif; ?>


    </section>


    <!-- Common Initial Population -->

    <section class="result-section">


        <div class="result-section-header">

            <div>

                <h2>
                    Common Initial Population
                </h2>

                <p>
                    The same initial population was provided to
                    the selected algorithms.
                </p>

            </div>

        </div>


        <?php if (
            !empty($initialPopulation)
        ): ?>


            <div class="table-wrapper">

                <table class="results-table">


                    <thead>

                        <tr>

                            <th>
                                Candidate
                            </th>

                            <th>
                                Solution
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach (
                            $initialPopulation
                            as $populationIndex
                            => $chromosome
                        ): ?>


                            <tr>

                                <td>

                                    <strong>
                                        #<?= $populationIndex + 1 ?>
                                    </strong>

                                </td>


                                <td>

                                    <div class="chromosome-cell">

                                        <?= formatValue(
                                            $chromosome
                                        ) ?>

                                    </div>

                                </td>

                            </tr>


                        <?php endforeach; ?>


                    </tbody>


                </table>

            </div>


        <?php else: ?>


            <div class="empty-state">

                No initial population data found.

            </div>


        <?php endif; ?>


    </section>



    <section class="report-actions">

        <a
            href="result.php"
            class="button secondary-button"
        >
            ← Back to Results
        </a>

        <a
            href="../algorithms/algorithm.php"
            class="button"
        >
            New Experiment
        </a>

    </section>


</main>


</body>

</html>
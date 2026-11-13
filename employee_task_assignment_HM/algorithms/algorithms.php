<?php

$algorithms = [

    "ga" => [
        "name" => "Genetic Algorithm",
        "short" => "GA",
        "description" =>
            "An evolutionary optimization algorithm using selection, crossover, mutation and elitism.",
        "status" => "available"
    ],

    "pso" => [
        "name" => "Particle Swarm Optimization",
        "short" => "PSO",
        "description" =>
            "A population-based optimization algorithm inspired by collective particle movement.",
        "status" => "available"
    ],

    "aco" => [
        "name" => "Ant Colony Optimization",
        "short" => "ACO",
        "description" =>
            "An optimization algorithm inspired by ant colony behavior and pheromone trails.",
        "status" => "available"
    ]

];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Algorithms | EmployeeTask Optimizer</title>

    <link rel="stylesheet" href="../CSS/style.css">

    <style>

        .algorithms-page {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 25px 60px;
        }

        .algorithms-page .page-header {
            margin-bottom: 30px;
        }

        .algorithms-page .page-header h1 {
            margin: 0 0 8px;
            font-size: 32px;
            color: #172033;
        }

        .algorithms-page .page-header p {
            margin: 0;
            color: #697386;
            font-size: 15px;
            line-height: 1.7;
        }

        .algorithm-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(260px, 1fr));
            gap: 22px;
        }

        .algorithm-card {
            position: relative;
            display: block;
            background: #ffffff;
            border: 1px solid #e5e9f2;
            border-radius: 16px;
            padding: 25px;
            transition: 0.2s ease;
            cursor: pointer;
        }

        .algorithm-card:hover {
            transform: translateY(-3px);
            box-shadow:
                0 10px 30px rgba(30, 50, 90, 0.08);
        }

        .algorithm-card.selected {
            border-color: #4f46e5;
            box-shadow:
                0 0 0 3px rgba(79, 70, 229, 0.10);
        }

        .algorithm-card.disabled {
            opacity: 0.62;
            cursor: not-allowed;
        }

        .algorithm-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .algorithm-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 17px;
        }

        .algorithm-status {
            font-size: 12px;
            font-weight: 700;
            padding: 6px 10px;
            border-radius: 20px;
        }

        .status-ready {
            background: #ecfdf3;
            color: #16824a;
        }

        .status-coming {
            background: #fff7e6;
            color: #a86600;
        }

        .algorithm-card h3 {
            margin: 0 0 10px;
            color: #172033;
            font-size: 19px;
        }

        .algorithm-card p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.7;
            min-height: 72px;
            margin-bottom: 20px;
        }

        .algorithm-select {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
        }

        .algorithm-select input {
            width: 18px;
            height: 18px;
            accent-color: #4f46e5;
        }


        /* Experiment Settings */

        .settings-section {
            margin-top: 35px;
            background: #ffffff;
            border: 1px solid #e5e9f2;
            border-radius: 16px;
            padding: 25px;
        }

        .settings-section h2 {
            margin: 0 0 7px;
            color: #172033;
            font-size: 21px;
        }

        .settings-description {
            margin: 0 0 25px;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.7;
        }

        .settings-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(210px, 1fr));
            gap: 18px;
        }

        .setting-group label {
            display: block;
            margin-bottom: 7px;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
        }

        .setting-group input,
        .setting-group select {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d9deea;
            border-radius: 9px;
            padding: 11px 12px;
            background: #ffffff;
            color: #172033;
            font-size: 14px;
            outline: none;
        }

        .setting-group input:focus,
        .setting-group select:focus {
            border-color: #4f46e5;
            box-shadow:
                0 0 0 3px rgba(79, 70, 229, 0.08);
        }

        .setting-help {
            display: block;
            margin-top: 6px;
            color: #8a93a3;
            font-size: 11px;
        }


        /* Shared Population Information */

        .population-info {
            margin-top: 25px;
            padding: 17px 18px;
            background: #f8faff;
            border: 1px solid #e4e9f5;
            border-radius: 12px;
            display: flex;
            align-items: flex-start;
            gap: 13px;
        }

        .population-info-icon {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            border-radius: 9px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
        }

        .population-info strong {
            display: block;
            margin-bottom: 4px;
            color: #172033;
            font-size: 14px;
        }

        .population-info p {
            margin: 0;
            color: #697386;
            font-size: 13px;
            line-height: 1.6;
        }


        /* Start Experiment */

        .experiment-panel {
            margin-top: 30px;
            background: #ffffff;
            border: 1px solid #e5e9f2;
            border-radius: 16px;
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .experiment-info h3 {
            margin: 0 0 7px;
            color: #172033;
        }

        .experiment-info p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }

        .start-button {
            border: none;
            background: #4f46e5;
            color: white;
            padding: 13px 25px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            white-space: nowrap;
        }

        .start-button:hover {
            background: #4338ca;
        }


        @media (max-width: 700px) {

            .algorithms-page {
                margin: 25px auto;
                padding: 0 15px 40px;
            }

            .algorithms-page .page-header h1 {
                font-size: 27px;
            }

            .experiment-panel {
                flex-direction: column;
                align-items: stretch;
            }

            .start-button {
                width: 100%;
            }

            .population-info {
                flex-direction: column;
            }

        }
    </style>

</head>

<body>

<?php include "../includes/navbar.php"; ?>


<div class="algorithms-page">

    <!-- Page Header -->

    <div class="page-header">

        <h1>
            Algorithm Experiments
        </h1>

        <p>
            Select one or more optimization algorithms
            and configure the experiment parameters.
            The selected algorithms will be executed
            on the same Employee–Task Assignment problem
            and the same initial population.
        </p>

    </div>


    <form
        method="POST"
        action="run.php"
        id="algorithmForm"
    >

        <!-- Algorithms -->

        <div class="algorithm-grid">

            <?php foreach ($algorithms as $key => $algorithm): ?>

                <label
                    class="
                        algorithm-card
                        <?= $algorithm['status'] === 'coming'
                            ? 'disabled'
                            : ''
                        ?>
                    "
                >

                    <div class="algorithm-top">

                        <div class="algorithm-icon">
                            <?= htmlspecialchars(
                                $algorithm['short']
                            ) ?>
                        </div>


                        <?php if (
                            $algorithm['status'] === 'available'
                        ): ?>

                            <span
                                class="algorithm-status status-ready"
                            >
                                Ready
                            </span>

                        <?php else: ?>

                            <span
                                class="algorithm-status status-coming"
                            >
                                Coming Soon
                            </span>

                        <?php endif; ?>

                    </div>


                    <h3>
                        <?= htmlspecialchars(
                            $algorithm['name']
                        ) ?>
                    </h3>


                    <p>
                        <?= htmlspecialchars(
                            $algorithm['description']
                        ) ?>
                    </p>


                    <div class="algorithm-select">

                        <input
                            type="checkbox"
                            name="algorithms[]"
                            value="<?= htmlspecialchars($key) ?>"
                            <?= $algorithm['status'] === 'coming'
                                ? 'disabled'
                                : ''
                            ?>
                        >

                        <span>
                            Select algorithm
                        </span>

                    </div>

                </label>

            <?php endforeach; ?>

        </div>


        <!-- Experiment Settings -->

        <div class="settings-section">

            <h2>
                Experiment Settings
            </h2>

            <p class="settings-description">
                Problem Size determines the number of tasks
                included in the experiment. Population Size
                determines the number of candidate solutions
                shared by all selected algorithms.
            </p>


            <div class="settings-grid">


                <!-- Problem Size -->

                <div class="setting-group">

                    <label for="problem_size">
                        Problem Size
                    </label>

                    <select
                        id="problem_size"
                        name="problem_size"
                        required
                    >

                        <option value="10" selected>
                            Small — 10 Tasks
                        </option>

                        <option value="15">
                            Medium — 15 Tasks
                        </option>

                        <option value="25">
                            Large — 25 Tasks
                        </option>

                    </select>

                </div>


                <!-- Population Size -->

                <div class="setting-group">

                    <label for="population_size">
                        Population Size
                    </label>

                    <input
                        type="number"
                        id="population_size"
                        name="population_size"
                        value="50"
                        min="2"
                        max="1000"
                        required
                    >

                </div>


                <!-- Generations / Iterations -->

                <div class="setting-group">

                    <label for="generations">
                        Generations / Iterations
                    </label>

                    <input
                        type="number"
                        id="generations"
                        name="generations"
                        value="50"
                        min="1"
                        max="1000"
                        required
                    >

                </div>


                <!-- Elite Count -->

                <div class="setting-group">

                    <label for="elite_count">
                        Elite Count
                    </label>

                    <input
                        type="number"
                        id="elite_count"
                        name="elite_count"
                        value="1"
                        min="0"
                        max="1000"
                    >

                    <small class="setting-help">
                        Used by Genetic Algorithm.
                    </small>

                </div>


                <!-- Crossover Rate -->

                <div class="setting-group">

                    <label for="crossover_rate">
                        Crossover Rate
                    </label>

                    <input
                        type="number"
                        id="crossover_rate"
                        name="crossover_rate"
                        value="0.80"
                        min="0"
                        max="1"
                        step="0.01"
                    >

                    <small class="setting-help">
                        Used by Genetic Algorithm.
                    </small>

                </div>


                <!-- Mutation Rate -->

                <div class="setting-group">

                    <label for="mutation_rate">
                        Mutation Rate
                    </label>

                    <input
                        type="number"
                        id="mutation_rate"
                        name="mutation_rate"
                        value="0.05"
                        min="0"
                        max="1"
                        step="0.01"
                    >

                    <small class="setting-help">
                        Used by Genetic Algorithm.
                    </small>

                </div>


                <!-- Skill Penalty -->

                <div class="setting-group">

                    <label for="skill_penalty">
                        Skill Penalty
                    </label>

                    <input
                        type="number"
                        id="skill_penalty"
                        name="skill_penalty"
                        value="10000"
                        min="0"
                        step="1000"
                    >

                </div>


                <!-- Capacity Penalty -->

                <div class="setting-group">

                    <label for="capacity_penalty">
                        Capacity Penalty
                    </label>

                    <input
                        type="number"
                        id="capacity_penalty"
                        name="capacity_penalty"
                        value="1000"
                        min="0"
                        step="100"
                    >

                </div>

            </div>


            <!-- Population Explanation -->

            <div class="population-info">

                <div class="population-info-icon">
                    P
                </div>

                <div>

                    <strong>
                        Shared Initial Population
                    </strong>

                    <p>
                        The selected algorithms will start
                        from the same initial population.
                        Each algorithm will then work on
                        its own copy of that population.
                    </p>

                </div>

            </div>

        </div>


        <!-- Start Experiment -->

        <div class="experiment-panel">

            <div class="experiment-info">

                <h3>
                    Ready to start?
                </h3>

                <p id="experimentSummary">
                    Select at least one available algorithm
                    and configure the experiment settings.
                </p>

            </div>


            <button
                type="submit"
                class="start-button"
            >
                Start Experiment
            </button>

        </div>

    </form>

</div>


<script>

const algorithmForm =
    document.getElementById('algorithmForm');


const problemSize =
    document.getElementById('problem_size');


const populationSize =
    document.getElementById('population_size');


const generations =
    document.getElementById('generations');


const experimentSummary =
    document.getElementById('experimentSummary');




 // Algorithm card selection
 

document.querySelectorAll(
    '.algorithm-card input[type="checkbox"]'
).forEach(function (checkbox) {

    checkbox.addEventListener(
        'change',
        function () {

            const card =
                this.closest('.algorithm-card');

            if (this.checked) {

                card.classList.add('selected');

            } else {

                card.classList.remove('selected');

            }

        }
    );

});




 // Update experiment summary


function updateExperimentSummary()
{
    const selectedTasks =
        parseInt(problemSize.value);

    const selectedPopulation =
        parseInt(populationSize.value);

    const selectedGenerations =
        parseInt(generations.value);


    experimentSummary.textContent =
        selectedTasks +
        ' tasks, ' +
        selectedPopulation +
        ' initial solutions, and ' +
        selectedGenerations +
        ' generations / iterations. ' +
        'The same initial population will be used ' +
        'by all selected algorithms.';
}


problemSize.addEventListener(
    'change',
    updateExperimentSummary
);


populationSize.addEventListener(
    'input',
    updateExperimentSummary
);


generations.addEventListener(
    'input',
    updateExperimentSummary
);


updateExperimentSummary();




 // Form validation


algorithmForm.addEventListener(
    'submit',
    function (event) {

        const checked =
            document.querySelectorAll(
                'input[name="algorithms[]"]:checked'
            );


        if (checked.length === 0) {

            event.preventDefault();

            alert(
                'Please select at least one algorithm.'
            );

            return;
        }


        const population =
            parseInt(
                populationSize.value
            );


        const numberOfGenerations =
            parseInt(
                generations.value
            );


        const eliteCount =
            parseInt(
                document.getElementById(
                    'elite_count'
                ).value
            );


        const crossoverRate =
            parseFloat(
                document.getElementById(
                    'crossover_rate'
                ).value
            );


        const mutationRate =
            parseFloat(
                document.getElementById(
                    'mutation_rate'
                ).value
            );


        if (population < 2) {

            event.preventDefault();

            alert(
                'Population size must be at least 2.'
            );

            return;
        }


        if (numberOfGenerations < 1) {

            event.preventDefault();

            alert(
                'Generations / Iterations must be at least 1.'
            );

            return;
        }


        if (
            eliteCount < 0 ||
            eliteCount > population
        ) {

            event.preventDefault();

            alert(
                'Elite Count must be between 0 and Population Size.'
            );

            return;
        }


        if (
            crossoverRate < 0 ||
            crossoverRate > 1
        ) {

            event.preventDefault();

            alert(
                'Crossover Rate must be between 0 and 1.'
            );

            return;
        }


        if (
            mutationRate < 0 ||
            mutationRate > 1
        ) {

            event.preventDefault();

            alert(
                'Mutation Rate must be between 0 and 1.'
            );

            return;
        }

    }
);

</script>

</body>

</html>
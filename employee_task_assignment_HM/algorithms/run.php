<?php

session_start();

require_once
    __DIR__ .
    '/../CONFIG/database.php';

require_once
    __DIR__ .
    '/population/ProblemLoader.php';

require_once
    __DIR__ .
    '/population/PopulationFactory.php';

require_once
    __DIR__ .
    '/ga/GeneticAlgorithm.php';

require_once
    __DIR__ .
    '/pso/ParticleSwarmOptimization.php';

require_once
    __DIR__ .
    '/aco/AntColonyOptimization.php';



// Only POST requests are accepted.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: algorithms.php'
    );

    exit;
}


try {


    //  Read selected algorithms
    $selectedAlgorithms =
        $_POST['algorithms'] ?? [];


    if (!is_array($selectedAlgorithms)) {

        $selectedAlgorithms =
            [];
    }


    $allowedAlgorithms = [
        'ga',
        'pso',
        'aco'
    ];


    $selectedAlgorithms =
        array_values(
            array_intersect(
                $selectedAlgorithms,
                $allowedAlgorithms
            )
        );


    if (empty($selectedAlgorithms)) {

        throw new RuntimeException(
            'Please select at least one algorithm.'
        );
    }



    //  Read experiment settings
    $problemSize =
        (int) (
            $_POST['problem_size']
            ?? 25
        );


    $populationSize =
        (int) (
            $_POST['population_size']
            ?? 20
        );


    $generations =
        (int) (
            $_POST['generations']
            ?? 100
        );


    $eliteCount =
        (int) (
            $_POST['elite_count']
            ?? 1
        );


    $crossoverRate =
        (float) (
            $_POST['crossover_rate']
            ?? 0.80
        );


    $mutationRate =
        (float) (
            $_POST['mutation_rate']
            ?? 0.05
        );


    $skillPenalty =
        (float) (
            $_POST['skill_penalty']
            ?? 100000
        );


    $capacityPenalty =
        (float) (
            $_POST['capacity_penalty']
            ?? 100000
        );



    //  Validate settings

    if (
        !in_array(
            $problemSize,
            [10, 15, 25],
            true
        )
    ) {

        throw new RuntimeException(
            'Invalid Problem Size.'
        );
    }


    if ($populationSize < 2) {

        throw new RuntimeException(
            'Population Size must be at least 2.'
        );
    }


    if ($generations < 1) {

        throw new RuntimeException(
            'Generations / Iterations must be at least 1.'
        );
    }


    if (
        $eliteCount < 0 ||
        $eliteCount > $populationSize
    ) {

        throw new RuntimeException(
            'Elite Count must be between 0 and Population Size.'
        );
    }


    if (
        $crossoverRate < 0 ||
        $crossoverRate > 1
    ) {

        throw new RuntimeException(
            'Crossover Rate must be between 0 and 1.'
        );
    }


    if (
        $mutationRate < 0 ||
        $mutationRate > 1
    ) {

        throw new RuntimeException(
            'Mutation Rate must be between 0 and 1.'
        );
    }


    if ($skillPenalty < 0) {

        throw new RuntimeException(
            'Skill Penalty cannot be negative.'
        );
    }


    if ($capacityPenalty < 0) {

        throw new RuntimeException(
            'Capacity Penalty cannot be negative.'
        );
    }


    //  Load problem data

    $loader =
        new ProblemLoader($pdo);


    $problem =
        $loader->loadProblem(
            $problemSize
        );


    $employees =
        $problem['employees'];


    $tasks =
        $problem['tasks'];


    $employeeSkills =
        $problem['employee_skills'];


    $assignmentCosts =
        $problem['assignment_costs'];



    //  Create ONE common initial population
    $initialPopulation =
        PopulationFactory::createInitialPopulation(
            $employees,
            $tasks,
            $populationSize
        );




    // Prepare experiment result
    $experimentStart =
        microtime(true);


    $results = [];



    // Store experiment configuration.
    $experiment = [

        'problem_size' =>
            $problemSize,

        'task_count' =>
            count($tasks),

        'population_size' =>
            $populationSize,

        'generations' =>
            $generations,

        'elite_count' =>
            $eliteCount,

        'crossover_rate' =>
            $crossoverRate,

        'mutation_rate' =>
            $mutationRate,

        'skill_penalty' =>
            $skillPenalty,

        'capacity_penalty' =>
            $capacityPenalty,

        'selected_algorithms' =>
            $selectedAlgorithms,

        'started_at' =>
            date('Y-m-d H:i:s')
    ];



    //  Run Genetic Algorithm

    if (
        in_array(
            'ga',
            $selectedAlgorithms,
            true
        )
    ) {

    
        $gaPopulation =
            $initialPopulation;


        $ga =
            new GeneticAlgorithm(
                $employees,
                $tasks,
                $employeeSkills,
                $assignmentCosts,
                $generations,
                $eliteCount,
                $crossoverRate,
                $mutationRate,
                $skillPenalty,
                $capacityPenalty
            );


        $ga->initializeFromPopulation(
            $gaPopulation
        );


        $gaStart =
            microtime(true);


        $gaResult =
            $ga->run();


        $gaEnd =
            microtime(true);


        $gaResult['execution_time'] =
            $gaEnd - $gaStart;


        $results['GA'] =
            $gaResult;
    }


    //  Run Particle Swarm Optimization
    if (
        in_array(
            'pso',
            $selectedAlgorithms,
            true
        )
    ) {

        $psoPopulation =
            $initialPopulation;


        $pso =
            new ParticleSwarmOptimization(
                $employees,
                $tasks,
                $employeeSkills,
                $assignmentCosts,
                $generations,
                $skillPenalty,
                $capacityPenalty
            );


        $pso->initializeFromPopulation(
            $psoPopulation
        );


        $psoStart =
            microtime(true);


        $psoResult =
            $pso->run();


        $psoEnd =
            microtime(true);


        $psoResult['execution_time'] =
            $psoEnd - $psoStart;


        $results['PSO'] =
            $psoResult;
    }


    //  Run Ant Colony Optimization


    if (
        in_array(
            'aco',
            $selectedAlgorithms,
            true
        )
    ) {
        $acoPopulation =
            $initialPopulation;

        $aco =
            new AntColonyOptimization(
                $employees,
                $tasks,
                $employeeSkills,
                $assignmentCosts,
                $generations,
                $skillPenalty,
                $capacityPenalty
            );

        $aco->initializeFromPopulation(
            $acoPopulation
        );

        $acoStart =
            microtime(true);

        $acoResult =
            $aco->run();

        $acoEnd =
            microtime(true);

        $acoResult['execution_time'] =
            $acoEnd - $acoStart;

        $results['ACO'] =
            $acoResult;
    }



    // Final experiment data
    $experimentEnd =
        microtime(true);


    $experiment['execution_time'] =
        $experimentEnd -
        $experimentStart;



// Store result in session
$_SESSION['experiment_result'] = [

    'experiment' =>
        $experiment,

    'initial_population' =>
        $initialPopulation,

    'employees' =>
        $employees,

    'tasks' =>
        $tasks,

    'employee_skills' =>
        $employeeSkills,

    'assignment_costs' =>
        $assignmentCosts,

    'results' =>
        $results
];


    // Redirect to results
    header(
        'Location: ../result/result.php'
    );

    exit;


} catch (Throwable $e) {

    echo "<h2>Optimization Error</h2>";
    echo "<p><strong>Message:</strong> "
        . htmlspecialchars($e->getMessage())
        . "</p>";

    echo "<p><strong>File:</strong> "
        . htmlspecialchars($e->getFile())
        . "</p>";

    echo "<p><strong>Line:</strong> "
        . $e->getLine()
        . "</p>";

    echo "<pre>";
    echo htmlspecialchars($e->getTraceAsString());
    echo "</pre>";

    exit;
}
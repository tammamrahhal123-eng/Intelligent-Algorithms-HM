<?php

require_once '../CONFIG/database.php';

$employeesStmt = $pdo->query("
    SELECT
        id,
        name,
        capacity

    FROM employees

    ORDER BY id
");

$employees = $employeesStmt->fetchAll();


$tasksStmt = $pdo->query("
    SELECT
        t.id,
        t.name,
        s.name AS required_skill

    FROM tasks t

    INNER JOIN skills s
        ON t.required_skill_id = s.id

    ORDER BY t.id
");

$tasks = $tasksStmt->fetchAll();


$costsStmt = $pdo->query("
    SELECT
        employee_id,
        task_id,
        execution_time,
        cost

    FROM assignment_costs
");

$assignmentCosts = [];


foreach ($costsStmt->fetchAll() as $row) {

    $assignmentCosts[
        $row['employee_id']
    ][
        $row['task_id']
    ] = [

        'execution_time' => $row['execution_time'],

        'cost' => $row['cost']

    ];
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

    <title>Assignment Costs</title>

    <link rel="stylesheet" href="../CSS/style.css">

</head>


<body>


<?php require_once "../includes/navbar.php"; ?>


<main class="container">



    <div class="page-header">

        <div>

            <span class="section-label">
                ASSIGNMENT COSTS
            </span>

            <h1>
                Assignment Costs
            </h1>

            <p>
                Execution time and cost for each employee-task assignment.
            </p>

        </div>


        <div>

            <a
                href="add.php"
                class="btn"
            >
                + Add Assignment execution time
            </a>

        </div>

    </div>


    <div class="section">


        <div class="table-wrapper cost-matrix-wrapper">


            <table class="cost-matrix">


                <thead>

                    <tr>


                        <th class="employee-column">

                            Employee

                        </th>


                        <?php foreach ($tasks as $task): ?>

                            <th>

                                <div class="task-header">

                                    <strong>
                                        <?= htmlspecialchars(
                                            $task['name']
                                        ) ?>
                                    </strong>

                                    <span>
                                        <?= htmlspecialchars(
                                            $task['required_skill']
                                        ) ?>
                                    </span>

                                </div>

                            </th>

                        <?php endforeach; ?>


                    </tr>

                </thead>


                <tbody>


                <?php if (count($employees) > 0): ?>


                    <?php foreach ($employees as $employee): ?>

                        <tr>


                            <!-- Employee -->

                            <td class="employee-cell">

                                <strong>

                                    <?= htmlspecialchars(
                                        $employee['name']
                                    ) ?>

                                </strong>


                                <span class="capacity-text">

                                    Capacity:
                                    <?= htmlspecialchars(
                                        $employee['capacity']
                                    ) ?>
                                    h

                                </span>

                            </td>



                            <!-- Tasks -->

                            <?php foreach ($tasks as $task): ?>


                                <?php

                                $employeeId = $employee['id'];

                                $taskId = $task['id'];

                                $hasAssignment =
                                    isset(
                                        $assignmentCosts[
                                            $employeeId
                                        ][
                                            $taskId
                                        ]
                                    );

                                ?>


                                <td>


                                    <?php if ($hasAssignment): ?>


                                        <div class="cost-cell">


                                            <div class="execution-time">

                                                <?= htmlspecialchars(
                                                    $assignmentCosts[
                                                        $employeeId
                                                    ][
                                                        $taskId
                                                    ][
                                                        'execution_time'
                                                    ]
                                                ) ?>

                                                <span>h</span>

                                            </div>


                                            <div class="assignment-cost">

                                                cost:

                                                <?= htmlspecialchars(
                                                    $assignmentCosts[
                                                        $employeeId
                                                    ][
                                                        $taskId
                                                    ][
                                                        'cost'
                                                    ]
                                                ) ?>

                                            </div>


                                        </div>


                                    <?php else: ?>


                                        <span class="not-available">
                                            —
                                        </span>


                                    <?php endif; ?>


                                </td>


                            <?php endforeach; ?>


                        </tr>

                    <?php endforeach; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="<?= count($tasks) + 1 ?>"
                            class="empty-state"
                        >

                            No employees found.

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>


            </table>


        </div>


    </div>



</main>


<footer class="footer">

    Employee–Task Assignment Optimization Project

</footer>


</body>

</html>
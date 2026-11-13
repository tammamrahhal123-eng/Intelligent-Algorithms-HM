<?php

require_once '../CONFIG/database.php';


$error = "";


$employeesStmt = $pdo->query("
    SELECT
        e.id,
        e.name
    FROM employees e
    ORDER BY e.id
");

$employees = $employeesStmt->fetchAll();


$tasksStmt = $pdo->query("
    SELECT
        t.id,
        t.name,
        t.required_skill_id,
        s.name AS required_skill

    FROM tasks t

    INNER JOIN skills s
        ON t.required_skill_id = s.id

    ORDER BY t.id
");

$tasks = $tasksStmt->fetchAll();


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employeeId = (int) ($_POST["employee_id"] ?? 0);

    $taskId = (int) ($_POST["task_id"] ?? 0);

    $executionTime =
        (float) ($_POST["execution_time"] ?? 0);



    // Validation
    if (
        $employeeId <= 0 ||
        $taskId <= 0 ||
        $executionTime <= 0
    ) {

        $error = "Please enter valid assignment information.";

    } else {




        $employeeStmt = $pdo->prepare("
            SELECT hourly_cost
            FROM employees
            WHERE id = ?
        ");

        $employeeStmt->execute([
            $employeeId
        ]);

        $hourlyCost = $employeeStmt->fetchColumn();


        if ($hourlyCost === false) {

            $error = "Employee not found.";

        } else {



            $skillStmt = $pdo->prepare("
                SELECT COUNT(*)

                FROM employee_skills es

                INNER JOIN tasks t
                    ON es.skill_id = t.required_skill_id

                WHERE
                    es.employee_id = ?
                    AND t.id = ?
            ");

            $skillStmt->execute([
                $employeeId,
                $taskId
            ]);

            $hasSkill = $skillStmt->fetchColumn();


            if (!$hasSkill) {

                $error =
                    "This employee does not have the required skill for this task.";

            } else {



                // Check Duplicate
                $duplicateStmt = $pdo->prepare("
                    SELECT COUNT(*)

                    FROM assignment_costs

                    WHERE
                        employee_id = ?
                        AND task_id = ?
                ");

                $duplicateStmt->execute([
                    $employeeId,
                    $taskId
                ]);

                $exists = $duplicateStmt->fetchColumn();


                if ($exists) {

                    $error =
                        "An assignment already exists for this employee and task.";

                } else {


               
                    // Calculate Cost Automatically
                    $cost =
                        $executionTime * (float) $hourlyCost;




                    $insertStmt = $pdo->prepare("
                        INSERT INTO assignment_costs
                        (
                            employee_id,
                            task_id,
                            execution_time,
                            cost
                        )

                        VALUES (?, ?, ?, ?)
                    ");

                    $insertStmt->execute([

                        $employeeId,

                        $taskId,

                        $executionTime,

                        $cost

                    ]);


                    header("Location: costs.php");

                    exit;
                }
            }
        }
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

    <title>Add Assignment Cost</title>

    <link rel="stylesheet" href="../CSS/style.css">

</head>


<body>


<?php require_once "../includes/navbar.php"; ?>


<main class="container">


    <div class="page-header">

        <div>

            <span class="section-label">
                ASSIGNMENT execution time
            </span>

            <h1>
                Add Assignment execution time
            </h1>

            <p>
                Define the execution time for an employee-task assignment.
            </p>

        </div>

    </div>


    <div class="form-card">


        <?php if ($error): ?>

            <div class="alert alert-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form method="POST">



            <div class="form-group">

                <label for="employee_id">
                    Employee
                </label>


                <select
                    id="employee_id"
                    name="employee_id"
                    required
                >

                    <option value="">
                        Select Employee
                    </option>


                    <?php foreach ($employees as $employee): ?>

                        <option
                            value="<?= $employee['id'] ?>"

                            <?= (
                                ($_POST['employee_id'] ?? '')
                                == $employee['id']
                            )
                            ? 'selected'
                            : ''
                            ?>
                        >

                            <?= htmlspecialchars(
                                $employee['name']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>



            <div class="form-group">

                <label for="task_id">
                    Task
                </label>


                <select
                    id="task_id"
                    name="task_id"
                    required
                >

                    <option value="">
                        Select Task
                    </option>


                    <?php foreach ($tasks as $task): ?>

                        <option
                            value="<?= $task['id'] ?>"

                            <?= (
                                ($_POST['task_id'] ?? '')
                                == $task['id']
                            )
                            ? 'selected'
                            : ''
                            ?>
                        >

                            <?= htmlspecialchars(
                                $task['name']
                            ) ?>

                            -
                            <?= htmlspecialchars(
                                $task['required_skill']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>



            <div class="form-group">

                <label for="execution_time">
                    Execution Time (hours)
                </label>

                <input
                    type="number"
                    id="execution_time"
                    name="execution_time"
                    min="0.01"
                    step="0.01"
                    placeholder="Example: 3"
                    value="<?= htmlspecialchars(
                        $_POST['execution_time'] ?? ''
                    ) ?>"
                    required
                >

                <small>
                    Time required by this employee to complete the task.
                </small>

            </div>


            <div class="form-actions">

                <a
                    href="costs.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="btn"
                >
                    Add Cost
                </button>

            </div>


        </form>

    </div>


</main>


<footer class="footer">

    Employee–Task Assignment Optimization Project

</footer>


</body>

</html>
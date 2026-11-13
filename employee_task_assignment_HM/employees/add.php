<?php

require_once '../CONFIG/database.php';

$message = "";
$error = "";

$skills = $pdo->query("
    SELECT id, name
    FROM skills
    ORDER BY name
")->fetchAll();


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $capacity = $_POST["capacity"] ?? "";
    $hourlyCost = (float) ($_POST["hourly_cost"] ?? 0);
    $selectedSkills = $_POST["skills"] ?? [];


    if ($name === "") {

        $error = "Employee name is required.";

    } elseif ($capacity === "" || !is_numeric($capacity)) {

        $error = "Capacity must be a valid number.";

    } elseif ($hourlyCost === "" || !is_numeric($hourlyCost)) {

        $error = "hourly Cost must be a valid number.";

    } elseif ((float)$capacity <= 0) {

        $error = "Capacity must be greater than zero.";

    } else {

        try {

            $pdo->beginTransaction();



             $stmt = $pdo->prepare("
             INSERT INTO employees
             (name, capacity, hourly_cost)
             VALUES (?, ?, ?)
         ");
         
         $stmt->execute([
             $name,
             $capacity,
             $hourlyCost
         ]);


            $employeeId = $pdo->lastInsertId();



            if (!empty($selectedSkills)) {

                $stmtSkill = $pdo->prepare("
                    INSERT INTO employee_skills
                    (employee_id, skill_id)
                    VALUES (?, ?)
                ");

                foreach ($selectedSkills as $skillId) {

                    $stmtSkill->execute([
                        $employeeId,
                        $skillId
                    ]);
                }
            }


            $pdo->commit();


            header("Location: employees.php");
            exit;

        } catch (PDOException $e) {

            $pdo->rollBack();

            $error = "Failed to add employee.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Employee</title>

    <link rel="stylesheet" href="../CSS/style.css">

</head>

<body>

<?php require_once "../includes/navbar.php"; ?>


<main class="container">

    <div class="page-header">

        <div>

            <span class="section-label">
                EMPLOYEES
            </span>

            <h1>Add Employee</h1>

            <p>
                Create a new employee and assign their skills.
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

                <label for="name">
                    Employee Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Example: Employee 5"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="capacity">
                    Capacity (hours)
                </label>

                <input
                    type="number"
                    id="capacity"
                    name="capacity"
                    min="0.01"
                    step="0.01"
                    placeholder="Example: 8"
                    value="<?= htmlspecialchars($_POST['capacity'] ?? '') ?>"
                    required
                >

                <small>
                    Maximum number of hours the employee can work.
                </small>

            </div>

            <div class="form-group">

                <label for="hourly_cost">
                    Hourly Cost ($/hour)
                </label>

                <input
                    type="number"
                    id="hourly_cost"
                    name="hourly_cost"
                    min="0"
                    step="0.01"
                    placeholder="Example: 10"
                    required
                >

                <small>
                    Cost of one working hour for this employee.
                </small>

            </div>

            <div class="form-group">

                <label>
                    Skills
                </label>

                <div class="checkbox-grid">

                    <?php foreach ($skills as $skill): ?>

                        <label class="checkbox-item">

                            <input
                                type="checkbox"
                                name="skills[]"
                                value="<?= $skill['id'] ?>"
                                <?= in_array(
                                    $skill['id'],
                                    $_POST['skills'] ?? []
                                ) ? 'checked' : '' ?>
                            >

                            <span>
                                <?= htmlspecialchars($skill['name']) ?>
                            </span>

                        </label>

                    <?php endforeach; ?>

                </div>

                <small>
                    Select all skills this employee has.
                </small>

            </div>


            <div class="form-actions">

                <a href="employees.php"
                   class="btn btn-secondary">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn"
                >
                    Add Employee
                </button>

            </div>

        </form>

    </div>

</main>


<footer class="footer">
    Employee Task Assignment Optimization Project
</footer>

</body>
</html>
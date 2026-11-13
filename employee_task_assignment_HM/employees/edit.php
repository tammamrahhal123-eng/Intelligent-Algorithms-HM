<?php

require_once '../CONFIG/database.php';


$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id) {

    header("Location: employees.php");
    exit;
}



$stmt = $pdo->prepare("
    SELECT id, name, capacity, hourly_cost
    FROM employees
    WHERE id = ?
");

$stmt->execute([$id]);

$employee = $stmt->fetch();


if (!$employee) {

    header("Location: employees.php");
    exit;
}




$skills = $pdo->query("
    SELECT id, name
    FROM skills
    ORDER BY name
")->fetchAll();



$stmt = $pdo->prepare("
    SELECT skill_id
    FROM employee_skills
    WHERE employee_id = ?
");

$stmt->execute([$id]);

$currentSkills = $stmt->fetchAll(
    PDO::FETCH_COLUMN
);


$error = "";



if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $capacity = $_POST["capacity"] ?? "";
    $hourly_cost = $_POST["hourly_cost"] ?? "";
    $selectedSkills = $_POST["skills"] ?? [];


    if ($name === "") {

        $error = "Employee name is required.";

    } elseif ($capacity === "" || !is_numeric($capacity)) {

        $error = "Capacity must be a valid number.";

    } elseif ($hourly_cost === "" || !is_numeric($hourly_cost)) {

        $error = "hourly cost must be a valid number.";

    } elseif ((float)$capacity <= 0) {

        $error = "Capacity must be greater than zero.";

    } else {

        try {

            $pdo->beginTransaction();


            

            $stmt = $pdo->prepare("
                UPDATE employees
                SET name = ?, capacity = ?, hourly_cost = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $name,
                $capacity,
                $hourly_cost,
                $id
            ]);


          

            $stmt = $pdo->prepare("
                DELETE FROM employee_skills
                WHERE employee_id = ?
            ");

            $stmt->execute([$id]);


            if (!empty($selectedSkills)) {

                $stmtSkill = $pdo->prepare("
                    INSERT INTO employee_skills
                    (employee_id, skill_id)
                    VALUES (?, ?)
                ");

                foreach ($selectedSkills as $skillId) {

                    $stmtSkill->execute([
                        $id,
                        $skillId
                    ]);
                }
            }


            $pdo->commit();


            header("Location: employees.php");
            exit;

        } catch (PDOException $e) {

            $pdo->rollBack();

            $error = "Failed to update employee.";
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

    <title>Edit Employee</title>

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

            <h1>Edit Employee</h1>

            <p>
                Update employee information and skills.
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
                    value="<?= htmlspecialchars($employee['name']) ?>"
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
                    value="<?= htmlspecialchars($employee['capacity']) ?>"
                    required
                >

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
                    value="<?= htmlspecialchars($employee['hourly_cost']) ?>"
                    required
                >

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
                                    $currentSkills
                                ) ? 'checked' : '' ?>
                            >

                            <span>
                                <?= htmlspecialchars($skill['name']) ?>
                            </span>

                        </label>

                    <?php endforeach; ?>

                </div>

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
                    Save Changes
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
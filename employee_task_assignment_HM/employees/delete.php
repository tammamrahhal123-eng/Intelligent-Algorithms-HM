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
    SELECT id, name, capacity
    FROM employees
    WHERE id = ?
");

$stmt->execute([$id]);

$employee = $stmt->fetch();


if (!$employee) {

    header("Location: employees.php");
    exit;
}



if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {

        $stmt = $pdo->prepare("
            DELETE FROM employees
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        header("Location: employees.php");
        exit;

    } catch (PDOException $e) {

        $error = "Unable to delete employee.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Delete Employee</title>

    <link rel="stylesheet" href="../CSS/style.css">

</head>

<body>

<?php require_once "../includes/navbar.php"; ?>


<main class="container">

    <div class="confirm-card">

        <div class="danger-icon">
            !
        </div>

        <h1>Delete Employee?</h1>

        <p>
            You are about to delete:
        </p>

        <strong class="confirm-name">
            <?= htmlspecialchars($employee['name']) ?>
        </strong>

        <p class="warning-text">
            This will also remove the employee's skills
            and assignment cost records because of
            the database cascade rules.
        </p>


        <?php if (!empty($error)): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-actions center">

                <a href="employees.php"
                   class="btn btn-secondary">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    Yes, Delete Employee
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
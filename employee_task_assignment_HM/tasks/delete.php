<?php

require_once '../CONFIG/database.php';


$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id) {

    header("Location: tasks.php");
    exit;
}



$stmt = $pdo->prepare("
    SELECT
        t.id,
        t.name,
        s.name AS required_skill

    FROM tasks t

    INNER JOIN skills s
        ON t.required_skill_id = s.id

    WHERE t.id = ?
");

$stmt->execute([$id]);

$task = $stmt->fetch();


if (!$task) {

    header("Location: tasks.php");
    exit;
}


$error = "";




if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {

        $stmt = $pdo->prepare("
            DELETE FROM tasks
            WHERE id = ?
        ");

        $stmt->execute([$id]);


        header("Location: tasks.php");
        exit;

    } catch (PDOException $e) {

        $error = "Unable to delete task.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Delete Task</title>

    <link rel="stylesheet" href="../CSS/style.css">

</head>

<body>

<?php require_once "../includes/navbar.php"; ?>


<main class="container">

    <div class="confirm-card">

        <div class="danger-icon">
            !
        </div>


        <h1>Delete Task?</h1>


        <p>
            You are about to delete:
        </p>


        <strong class="confirm-name">
            <?= htmlspecialchars($task['name']) ?>
        </strong>


        <p class="warning-text">
            Required skill:
            <strong>
                <?= htmlspecialchars($task['required_skill']) ?>
            </strong>
            <br><br>

            This will also remove all assignment cost
            records associated with this task.
        </p>


        <?php if ($error): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-actions center">

                <a
                    href="tasks.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    Yes, Delete Task
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
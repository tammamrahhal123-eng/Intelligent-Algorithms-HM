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
    SELECT id, name, required_skill_id
    FROM tasks
    WHERE id = ?
");

$stmt->execute([$id]);

$task = $stmt->fetch();


if (!$task) {

    header("Location: tasks.php");
    exit;
}



$skills = $pdo->query("
    SELECT id, name
    FROM skills
    ORDER BY name
")->fetchAll();


$error = "";


/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");

    $requiredSkill = filter_input(
        INPUT_POST,
        "required_skill_id",
        FILTER_VALIDATE_INT
    );


    if ($name === "") {

        $error = "Task name is required.";

    } elseif (!$requiredSkill) {

        $error = "Please select a required skill.";

    } else {

        try {

            $stmt = $pdo->prepare("
                UPDATE tasks

                SET
                    name = ?,
                    required_skill_id = ?

                WHERE id = ?
            ");

            $stmt->execute([
                $name,
                $requiredSkill,
                $id
            ]);


            header("Location: tasks.php");
            exit;

        } catch (PDOException $e) {

            $error = "Failed to update task.";
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

    <title>Edit Task</title>

    <link rel="stylesheet" href="../CSS/style.css">

</head>

<body>

<?php require_once "../includes/navbar.php"; ?>


<main class="container">

    <div class="page-header">

        <div>

            <span class="section-label">
                TASKS
            </span>

            <h1>Edit Task</h1>

            <p>
                Update task information.
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
                    Task Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars(
                        $task['name']
                    ) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="required_skill_id">
                    Required Skill
                </label>

                <select
                    id="required_skill_id"
                    name="required_skill_id"
                    required
                >

                    <?php foreach ($skills as $skill): ?>

                        <option
                            value="<?= $skill['id'] ?>"
                            <?= $task['required_skill_id']
                                == $skill['id']
                                ? 'selected'
                                : '' ?>
                        >

                            <?= htmlspecialchars($skill['name']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-actions">

                <a href="tasks.php"
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
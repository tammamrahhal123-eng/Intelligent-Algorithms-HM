<?php

require_once '../CONFIG/database.php';


$skills = $pdo->query("
    SELECT id, name
    FROM skills
    ORDER BY name
")->fetchAll();


$error = "";


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
                INSERT INTO tasks
                (name, required_skill_id)
                VALUES (?, ?)
            ");

            $stmt->execute([
                $name,
                $requiredSkill
            ]);


            header("Location: tasks.php");
            exit;

        } catch (PDOException $e) {

            $error = "Failed to add task.";
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

    <title>Add Task</title>

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

            <h1>Add Task</h1>

            <p>
                Create a new task and define its required skill.
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
                    placeholder="Example: Task 8"
                    value="<?= htmlspecialchars(
                        $_POST['name'] ?? ''
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

                    <option value="">
                        -- Select Skill --
                    </option>

                    <?php foreach ($skills as $skill): ?>

                        <option
                            value="<?= $skill['id'] ?>"
                            <?= (
                                ($_POST['required_skill_id'] ?? '')
                                == $skill['id']
                            ) ? 'selected' : '' ?>
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
                    Add Task
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
<?php
require_once '../CONFIG/database.php';

$stmt = $pdo->query("
    SELECT
        t.id,
        t.name,
        s.name AS required_skill

    FROM tasks t

    INNER JOIN skills s
        ON t.required_skill_id = s.id

    ORDER BY t.id
");

$tasks = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tasks - Employee Task Optimizer</title>

    <link rel="stylesheet" href="../CSS/style.css">
</head>

<body>

<?php require_once "../includes/navbar.php"; ?>


<main class="container">

    <div class="page-header">

        <div>

            <span class="section-label">
                MANAGEMENT
            </span>

            <h1>Tasks</h1>

            <p>
                Manage tasks and their required skills.
            </p>

        </div>


        <a href="add.php"
           class="btn">
            + Add Task
        </a>

    </div>


    <div class="section">

        <div class="table-wrapper">

            <table>

                <thead>

                <tr>
                    <th>ID</th>
                    <th>Task Name</th>
                    <th>Required Skill</th>
                    <th>Actions</th>
                </tr>

                </thead>

                <tbody>

                <?php if (count($tasks) > 0): ?>

                    <?php foreach ($tasks as $task): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($task['id']) ?>
                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars($task['name']) ?>
                                </strong>

                            </td>

                            <td>

                                <span class="skill-badge">
                                    <?= htmlspecialchars(
                                        $task['required_skill']
                                    ) ?>
                                </span>

                            </td>

                            <td>

                                <div class="action-buttons">

                                    <a
                                        href="edit.php?id=<?= $task['id'] ?>"
                                        class="btn btn-small btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="delete.php?id=<?= $task['id'] ?>"
                                        class="btn btn-small btn-delete"
                                    >
                                        Delete
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="4"
                            class="empty-state">

                            No tasks found.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>


<footer class="footer">
    Employee Task Assignment Optimization Project
</footer>

</body>
</html>
<?php
require_once '../CONFIG/database.php';

$stmt = $pdo->query("
SELECT
e.id,
e.name,
e.capacity,
e.hourly_cost,
GROUP_CONCAT(
    s.name
    ORDER BY s.name
    SEPARATOR ', '
) AS skills

FROM employees e

LEFT JOIN employee_skills es
ON e.id = es.employee_id

LEFT JOIN skills s
ON es.skill_id = s.id

GROUP BY
e.id,
e.name,
e.capacity,
e.hourly_cost

ORDER BY e.id
");

$employees = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Employees - Employee Task Optimizer</title>

    <link rel="stylesheet" href="../CSS/style.css">
</head>

<body>

<?php require_once "../includes/navbar.php"; ?>


<main class="container">

    <div class="page-header">

        <div>
            <span class="section-label">MANAGEMENT</span>

            <h1>Employees</h1>

            <p>
                Manage employees, capacities and skills.
            </p>
        </div>

        <a href="add.php" class="btn">
            + Add Employee
        </a>

    </div>


    <div class="section">

        <div class="table-wrapper">

            <table>

                <thead>

                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Skills</th>
                    <th>Capacity</th>
                    <th>Hourly Cost</th>
                    <th>Actions</th>
                </tr>

                </thead>

                <tbody>

                <?php if (count($employees) > 0): ?>

                    <?php foreach ($employees as $employee): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($employee['id']) ?>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($employee['name']) ?>
                                </strong>
                            </td>

                            <td>

                                <?php if ($employee['skills']): ?>

                                    <?php
                                    $skills = explode(
                                        ', ',
                                        $employee['skills']
                                    );
                                    ?>

                                    <?php foreach ($skills as $skill): ?>

                                        <span class="skill-badge">
                                            <?= htmlspecialchars($skill) ?>
                                        </span>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <span class="muted">
                                        No skills
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?= htmlspecialchars($employee['capacity']) ?>
                                h
                            </td>

                            <td>
                                $<?= htmlspecialchars($employee['hourly_cost']) ?>/h
                            </td>

                            <td>

                                <div class="action-buttons">

                                    <a
                                        href="edit.php?id=<?= $employee['id'] ?>"
                                        class="btn btn-small btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="delete.php?id=<?= $employee['id'] ?>"
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

                        <td colspan="5" class="empty-state">
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
    Employee Task Assignment Optimization Project
</footer>

</body>
</html>
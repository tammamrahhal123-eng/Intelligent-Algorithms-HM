<?php

$currentPage = basename($_SERVER["PHP_SELF"]);

?>

<nav class="navbar">

    <div class="navbar-brand">

        <a href="../home/index.php" class="brand">

            <div class="brand-logo">
                ET
            </div>

            <div class="brand-text">

                <span class="brand-title">
                    EmployeeTask
                </span>

                <span class="brand-subtitle">
                    Optimizer
                </span>

            </div>

        </a>

        <div class="system-status">
            <span class="status-dot"></span>
            System Ready
        </div>

    </div>


    <div class="nav-links">

        <a
            href="../home/index.php"
            class="<?= $currentPage == 'index.php' ? 'active' : '' ?>"
        >
            Dashboard
        </a>


        <a
            href="../employees/employees.php"
            class="<?= strpos($_SERVER['PHP_SELF'], 'employees.php') !== false ? 'active' : '' ?>"
        >
            Employees
        </a>


        <a
            href="../tasks/tasks.php"
            class="<?= strpos($_SERVER['PHP_SELF'], 'tasks/tasks.php') !== false ? 'active' : '' ?>"
        >
            Tasks
        </a>


        <a
            href="../costs/costs.php"
            class="<?= strpos($_SERVER['PHP_SELF'], 'costs/costs.php') !== false ? 'active' : '' ?>"
        >
            Costs
        </a>


        <a href="../algorithms/algorithms.php"
            class="<?= strpos($_SERVER['PHP_SELF'], 'algorithms/algorithms.php') !== false ? 'active' : '' ?>"
        >
            Algorithms
        </a>


        <a href="../result/result.php"
            class="<?= strpos($_SERVER['PHP_SELF'], 'result/result.php') !== false ? 'active' : '' ?>"
        >
            Results
        </a>

    </div>

</nav>
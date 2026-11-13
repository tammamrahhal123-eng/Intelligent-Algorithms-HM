<?php

class ProblemLoader
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    /**
     * Load employees.
     */
    public function loadEmployees(): array
    {
        $sql = "
            SELECT
                id,
                name,
                capacity,
                hourly_cost
            FROM employees
            ORDER BY id
        ";

        $stmt = $this->pdo->query($sql);

        $employees = [];

        while ($row = $stmt->fetch()) {

            $employees[(int) $row['id']] = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'capacity' => (float) $row['capacity'],
                'hourly_cost' => (float) $row['hourly_cost']
            ];
        }

        return $employees;
    }


    /**
     * Load tasks according to problem size.
     *
     * Problem size = number of tasks.
     */
    public function loadTasks(int $problemSize): array
    {
        $sql = "
            SELECT
                id,
                name,
                required_skill_id
            FROM tasks
            ORDER BY id
            LIMIT :problem_size
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':problem_size',
            $problemSize,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $tasks = [];

        while ($row = $stmt->fetch()) {

            $tasks[] = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'required_skill_id' =>
                    (int) $row['required_skill_id']
            ];
        }

        return $tasks;
    }


    /**
     * Load employee skills.
     */
    public function loadEmployeeSkills(): array
    {
        $sql = "
            SELECT
                employee_id,
                skill_id
            FROM employee_skills
            ORDER BY employee_id, skill_id
        ";

        $stmt = $this->pdo->query($sql);

        $employeeSkills = [];

        while ($row = $stmt->fetch()) {

            $employeeId = (int) $row['employee_id'];
            $skillId = (int) $row['skill_id'];

            if (!isset($employeeSkills[$employeeId])) {
                $employeeSkills[$employeeId] = [];
            }

            $employeeSkills[$employeeId][] = $skillId;
        }

        return $employeeSkills;
    }


    /**
     * Load assignment execution times and costs.
     */
    public function loadAssignmentCosts(): array
    {
        $sql = "
            SELECT
                employee_id,
                task_id,
                execution_time,
                cost
            FROM assignment_costs
            ORDER BY employee_id, task_id
        ";

        $stmt = $this->pdo->query($sql);

        $assignmentCosts = [];

        while ($row = $stmt->fetch()) {

            $employeeId = (int) $row['employee_id'];
            $taskId = (int) $row['task_id'];

            if (!isset($assignmentCosts[$employeeId])) {
                $assignmentCosts[$employeeId] = [];
            }

            $assignmentCosts[$employeeId][$taskId] = [
                'execution_time' =>
                    (float) $row['execution_time'],

                'cost' =>
                    (float) $row['cost']
            ];
        }

        return $assignmentCosts;
    }


    /**
     * Load complete problem data.
     */
    public function loadProblem(int $problemSize): array
    {
        if (!in_array($problemSize, [10, 15, 25], true)) {

            throw new InvalidArgumentException(
                'Problem Size must be 10, 15, or 25.'
            );
        }


        $employees =
            $this->loadEmployees();


        $tasks =
            $this->loadTasks($problemSize);


        $employeeSkills =
            $this->loadEmployeeSkills();


        $assignmentCosts =
            $this->loadAssignmentCosts();


        if (count($tasks) !== $problemSize) {

            throw new RuntimeException(
                "The database contains only "
                . count($tasks)
                . " tasks, but the experiment requires "
                . $problemSize
                . " tasks."
            );
        }


        if (empty($employees)) {

            throw new RuntimeException(
                'No employees were found in the database.'
            );
        }


        return [

            'employees' =>
                $employees,

            'tasks' =>
                $tasks,

            'employee_skills' =>
                $employeeSkills,

            'assignment_costs' =>
                $assignmentCosts
        ];
    }
}
<?php


// Calculate the fitness of a chromosome


function calculateFitness(
    array $chromosome,
    array $employees,
    array $tasks,
    array $employeeSkills,
    array $assignmentCosts,
    float $skillPenalty = 10,
    float $capacityPenalty = 10
): array {

    $totalCost = 0.0;

    $skillViolations = 0;

    $capacityViolations = 0.0;

    
    // Store total working hours for each employee.

    $workload = [];

    foreach ($employees as $employeeId => $employee) {
        $workload[$employeeId] = 0.0;
    }

    /*
     * -----------------------------------------
     * 2. Evaluate every gene
     * -----------------------------------------
     */

    foreach ($chromosome as $taskIndex => $employeeId) {

        if (!isset($tasks[$taskIndex])) {
            continue;
        }

        $task = $tasks[$taskIndex];

        $taskId = (int) $task['id'];

        $requiredSkillId = (int) $task['required_skill_id'];

        /*
         * -------------------------------------
         * Check Invalid employee: the algorithm might produce an invalid solution
         * such as assigning a task to a non-existent employee
         * in which case that solution is considered a violation.
         * -------------------------------------
         */

        if (!isset($employees[$employeeId])) {
            $skillViolations++;
            continue;
        }

        // Check employee skill
        $hasRequiredSkill = false;

        if (isset($employeeSkills[$employeeId])) {

            $hasRequiredSkill = in_array(
                $requiredSkillId,
                $employeeSkills[$employeeId]
            );
        }

        // if Employee does not have the skill required by this task, he is considered a violation.
        if (!$hasRequiredSkill) {
            $skillViolations++;
        }


        /*
        * if there is no Employee-Task record in assignment_costs.
        * therefore we cannot calculate the execution time/cost for this assignment.
        * we count it as an invalid assignment.
        */
        if (
            !isset($assignmentCosts[$employeeId]) ||
            !isset($assignmentCosts[$employeeId][$taskId])
        ) {

            
            $skillViolations++;

            continue;
        }

        $assignment = $assignmentCosts[$employeeId][$taskId];

        $executionTime = (float) $assignment['execution_time'];

        $cost = (float) $assignment['cost'];

        $totalCost += $cost;


        // Add execution time to workload
        $workload[$employeeId] += $executionTime;
    }


    // Check employee capacities
    foreach ($employees as $employeeId => $employee) {

        $capacity = (float) $employee['capacity'];

        $employeeWorkload = $workload[$employeeId];

        if ($employeeWorkload > $capacity) {
            $capacityViolations +=
                $employeeWorkload - $capacity;
        }
    }



    $penalty =
        ($skillPenalty * $skillViolations)
        +
        ($capacityPenalty * $capacityViolations);


    $fitness = $totalCost + $penalty;


    return [
        'fitness' => $fitness,

        'total_cost' => $totalCost,

        'penalty' => $penalty,

        'skill_violations' => $skillViolations,

        'capacity_violations' => $capacityViolations,

        'workload' => $workload
    ];
}
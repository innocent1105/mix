<?php

$pythonExecutable = "precision_arima_model.exe";

$data = [188, 173,122, 188,157,197];
$interval = 3;
$user_id = 1234432;
$project_name = "Arima forecasting";

$data = [$data, $interval, $user_id, $project_name];
$arg1 = json_encode($data);

$command = escapeshellcmd("$pythonExecutable $arg1");
$output = shell_exec($command);

echo "python : " . $output;












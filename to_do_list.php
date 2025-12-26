<?php
session_start();

if(!isset($_SESSION['tasks']) || !is_array($_SESSION['tasks'])){
    $_SESSION['tasks'] = [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["input"]) && !isset($_POST["action"])) {
    $newtask = htmlspecialchars($_POST["input"]);
    $priority = $_POST["priority"] ?? "medium";
    if(trim($newtask) != ""){
        $_SESSION['tasks'][] = ["text" => $newtask, "completed" => false,"priority" => $priority];
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
elseif($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "clear_all"){
    $_SESSION['tasks'] = [];
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
elseif($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "delete"){
    $index = intval($_POST["task_index"]);
    if(isset($_SESSION['tasks'][$index])){
        unset($_SESSION['tasks'][$index]);
        $_SESSION['tasks'] = array_values($_SESSION['tasks']);
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
elseif($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "completed"){
    $index = intval($_POST["task_index"]);
    if(isset($_SESSION['tasks'][$index])){
        $_SESSION['tasks'][$index]['completed'] = !$_SESSION['tasks'][$index]['completed'];
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}elseif($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "edit"){
    $index = intval($_POST["task_index"]);
    $text = htmlspecialchars($_POST["edit_input"]);
    $priority = $_POST["edit_priority"] ?? $_SESSION['tasks'][$index]['priority'];
    if(isset($_SESSION['tasks'][$index])){
        if(trim($text) != ""){
            $_SESSION['tasks'][$index]['text'] = $text; 
        }
        $_SESSION['tasks'][$index]['priority'] = $priority;
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
$filter = $_GET['filter'] ?? 'all';
    $tasks = $_SESSION['tasks']; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do list</title>
</head>
<body>
    <?php 
                $count = count($tasks);
                $i = 0;
                $j = 0;
                foreach($tasks as $index){
                    if($index["completed"]){
                        $i++;
                    }else{
                        $j++;
                    }
                }
                echo "Total : ".$count." completed : ".$i." remaining : ".$j ?>
            <br>
            <a href="?filter=all">All</a> | 
            <a href="?filter=active">Active</a> | 
            <a href="?filter=completed">Completed</a>  
    <div id="task-list">
        <h2>Your Tasks</h2>
        <?php if(empty($tasks)):?>
            <p>No tasks yet. Add your first task!</p>
        <?php else: ?>
            <ul>
                <?php foreach($tasks as $index=>$task): ?>
                    <?php
    
                    $bgColor = "white"; 
                    if($task['priority'] == 'high'){
                        $bgColor = "#ffcccc"; 
                    }elseif($task['priority'] == 'medium'){
                        $bgColor = "#ffffcc"; 
                    }elseif($task['priority'] == 'low'){
                        $bgColor = "#ccffcc"; 
                    }
                    ?>
                    <li style="background-color: <?php echo $bgColor; ?>; padding: 10px; margin: 5px;" >
                        <?php
                        if($filter == 'active' && $task['completed']) continue;
                        if($filter == 'completed' && !$task['completed']) continue;
    
                        echo $task['text'];
                        echo " ";
                        echo strtoupper($task['priority']);
                        if($task['completed']){
                            echo " ✓";
                        }else{
                            echo " ✗";
                        }
                        ?>

                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="task_index" value="<?php echo $index; ?>">
                            <input type="submit" value="Delete">
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="completed">
                            <input type="hidden" name="task_index" value="<?php echo $index; ?>">
                            <input type="submit" value="Toggle Complete">
                        </form>
                        <form method="POST" style="display:inline;">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="task_index" value="<?php echo $index; ?>">
                                <input type="text" name="edit_input" placeholder="<?php echo htmlspecialchars($task['text']); ?>">
                                <select name="edit_priority">
                                    <option value="low" <?php if($task['priority'] == 'low') echo 'selected'; ?>>Low</option>
                                    <option value="medium" <?php if($task['priority'] == 'medium') echo 'selected'; ?>>Medium</option>
                                    <option value="high" <?php if($task['priority'] == 'high') echo 'selected'; ?>>High</option>
                                </select>
                                <input type="submit" value="Update">
                            </form>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            
        <?php endif; ?>
    </div>

    <form method="POST">
        <label for="input">Task:</label>
        <input type="text" id="input" name="input" required>

        <label for="priority">Priority:</label>
        <select id="priority" name="priority">
            <option value="low">Low</option>
            <option value="medium" selected>Medium</option>
            <option value="high">High</option>
        </select>
        <input type="submit" value="Add">
    </form>

    <form method="POST">
        <input type="hidden" id="action" name="action" value="clear_all">
        <input type="submit" value="Clear All">
    </form>
</body>
</html>
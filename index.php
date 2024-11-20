<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dsn = "mysql:host=$host";
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Database Explorer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
            font-size: 0.9rem;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            height: 100vh;
            overflow-y: auto;
            position: relative;
        }
        .sidebar h3 {
            margin-bottom: 15px;
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 10px;
        }
        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            padding: 5px 10px;
            display: block;
            border-radius: 4px;
        }
        .sidebar ul li a:hover {
            background-color: #495057;
        }
        .content {
            flex: 1;
            padding: 20px;
            background-color: #f8f9fa;
            max-width: calc(100vw - 250px);
            overflow: auto;
            max-height: calc(100vh - 0px);
        }
        h1, h3 {
            margin-bottom: 20px;
            font-weight: normal;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            color: #1c1c1c;
        }
        .pagination
        {
            padding: 10px 0;
        }
        .pagination a {
            text-decoration: none;
            color: #007BFF;
            padding: 5px 10px;
            border: 1px solid #ddd;
            margin-right: 5px;
            border-radius: 4px;
        }
        .pagination a:hover {
            background-color: #007BFF;
            color: #fff;
        }
        form {
            margin-top: 20px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            border: none;
            background-color: #007BFF;
            color: #fff;
            cursor: pointer;
            border-radius: 4px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .sidebar form
        {
            position: relative;
            width: 100%;
            padding-bottom: 10px;
        }
        #database-select{
            padding: 4px 8px;
            background-color: #ECECEC;
            border: 1px solid #ddd;
            width: 100%;
            box-sizing: border-box;
            margin: 5px 0;
        }
        .table-list
        {
            max-height: calc(100vh - 150px);
            overflow: auto;
        }
        .table-list li a{
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .button-toggle{
            width: 20px;
            padding: 1px 5px;
            border: 1px solid #DDDDDD;
            background-color: #EEEEEE;
            cursor: pointer;
        }
        .collapsible > div
        {
            display: none;
        }
        .collapsible.open > div
        {
            display: block;
        }
        .collapsible .button-toggle
        {
            float: right;
        }
        .collapsible .button-toggle::before{
            content: attr(data-close);
        }
        .collapsible.open .button-toggle::before{
            content: attr(data-open);
        }
        .table-structure-inner, .table-content-inner
        {
            overflow-x: auto;
            max-width: 100%;
            margin-bottom: 10px;
        }
        .table-content-inner
        {
            height: calc(100vh - 340px);
        }
        .table-content-inner table td{
            white-space: nowrap;
        }
        textarea
        {
            margin-bottom: 5px;
            transition: background-color ease-in-out 0.2s;
        }
        textarea:focus-visible{
            outline: none;
            border:1px solid #007BFF
        }
        
    </style>
    <script>
        window.onload = function() {
            // Select all toggle buttons within collapsible elements
            const toggles = document.querySelectorAll('.collapsible .button-toggle');

            // Attach event listeners to each toggle button
            toggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    // Find the closest collapsible element and toggle the 'open' class
                    e.target.closest('.collapsible').classList.toggle('open');
                });
            });
        };

    </script>
</head>
<body>
    <div class="sidebar">
        <h3>Navigation</h3>
        <?php
        // Database connection configuration


        try {
            $pdo = new PDO($dsn, $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Variables to control navigation
            $database = isset($_GET['database']) ? $_GET['database'] : null;
            $table = isset($_GET['table']) ? $_GET['table'] : null;

            // Show available databases
            function showSidebarDatabases($pdo, $database, $table) {
                // Form for selecting database
                echo "<form method='GET' action=''>";
                echo "<label for='database-select'>Select Database:</label>";
                echo "<select name='database' id='database-select' onchange='this.form.submit()'>";
                echo "<option value=''>-- Choose Database --</option>";
                $stmt = $pdo->query("SHOW DATABASES");
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $dbName = $row[0];
                    $selected = $database === $dbName ? "selected" : "";
                    echo "<option value='$dbName' $selected>$dbName</option>";
                }
                echo "</select>";
                echo "</form>";
            }

            // Show tables of the selected database
            function showSidebarTables($pdo, $database, $table) {
                $pdo->exec("USE `$database`");
                $stmt = $pdo->query("SHOW TABLES");
                echo "<ul class=\"table-list\">";
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $tableName = $row[0];
                    echo "<li><a href='?database=$database&table=$tableName'" .
                        ($table == $tableName ? " style='font-weight: bold;'" : "") .
                        ">$tableName</a></li>";
                }
                echo "</ul>";
            }

            // Render sidebar
            showSidebarDatabases($pdo, $database, $table);
            showSidebarTables($pdo, $database, $table);
        } catch (PDOException $e) {
            if($e->getCode() == 0x3D000 || strpos($e->getMessage(), '1046') !== false)
            {
                echo "Please choose one database";
            }
            else
            {
                echo "Connection failed: " . $e->getMessage();
            }
        }
        ?>
    </div>
    <div class="content">
        <?php
        try {
            // Load table structure or data based on navigation
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 20; // Rows per page
            $query = isset($_POST['query']) ? $_POST['query'] : null;

            if ($database) {
                $pdo->exec("USE `$database`");

                if ($table) {
                    // Show table structure
                    function showTableStructure($pdo, $table) {
                        echo "<div class=\"table-structure collapsible\">";
                        echo "<button class=\"button-toggle toggle-structure\" data-open=\"-\" data-close=\"+\"></button>";
                        echo "<h3>Structure of $table</h3>";
                        echo "<div class=\"table-structure-inner\">";
                        $stmt = $pdo->query("DESCRIBE `$table`");
                        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            foreach ($row as $value) {
                                echo "<td>$value</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</table>";
                        echo "</div>";
                        echo "</div>";
                    }
                    showTableStructure($pdo, $table);

                    // Show table data with pagination
                    function showTableData($pdo, $table, $page, $limit) {
                        $offset = ($page - 1) * $limit;
                        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM `$table`");
                        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                        $totalPages = ceil($total / $limit);
                    
                        
                        echo "<div class=\"table-content collapsible open\">";
                        echo "<button class=\"button-toggle toggle-structure\" data-open=\"-\" data-close=\"+\"></button>";
                        echo "<h3>Data in $table (Page $page)</h3>";
                        echo "<div class=\"table-content-inner\">";
                        $stmt = $pdo->query("SELECT * FROM `$table` LIMIT $limit OFFSET $offset");
                        echo "<table><tr>";
                        for ($i = 0; $i < $stmt->columnCount(); $i++) {
                            $col = $stmt->getColumnMeta($i);
                            echo "<th>{$col['name']}</th>";
                        }
                        echo "</tr>";
                    
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            foreach ($row as $value) {
                                echo "<td>".htmlspecialchars($value)."</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</table>";
                        echo "</div>";
                        echo "</div>";
                    
                        // Pagination navigation with a maximum of 7 pages
                        echo "<div class='pagination'>";
                        $startPage = max(1, $page - 3);
                        $endPage = min($totalPages, $page + 3);
                    
                        if ($startPage > 1) {
                            echo "<a href='?database={$_GET['database']}&table=$table&page=1'>First</a> ";
                            if ($startPage > 2) {
                                echo "<span>...</span> ";
                            }
                        }
                    
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            if ($i == $page) {
                                echo "<a href='?database={$_GET['database']}&table=$table&page=$i' style='font-weight: bold;'>$i</a> ";
                            } else {
                                echo "<a href='?database={$_GET['database']}&table=$table&page=$i'>$i</a> ";
                            }
                        }
                    
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo "<span>...</span> ";
                            }
                            echo "<a href='?database={$_GET['database']}&table=$table&page=$totalPages'>Last</a>";
                        }
                        echo "</div>";
                    }
                    
                    showTableData($pdo, $table, $page, $limit);
                }
            }

            // Query execution form
            echo "<h3>Execute Query</h3>";
            echo "<form method='post'>
                    <textarea name='query' rows='4' cols='50' spellcheck='false'></textarea><br>
                    <input type='submit' value='Execute'>
                  </form>";
            if ($query) {
                function executeQuery($pdo, $query) {
                    try {
                        $stmt = $pdo->query($query);
                        echo "<table><tr>";
                        for ($i = 0; $i < $stmt->columnCount(); $i++) {
                            $col = $stmt->getColumnMeta($i);
                            echo "<th>{$col['name']}</th>";
                        }
                        echo "</tr>";
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            foreach ($row as $value) {
                                echo "<td>$value</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</table>";
                    } catch (PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }
                }
                executeQuery($pdo, $query);
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>
    </div>
</body>
</html>

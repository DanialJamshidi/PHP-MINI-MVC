<?php
function MakeSecureHash($password)
{

    $options = [
        'memory_cost' => 65536,
        'time_cost'   => 4,
        'threads'     => 1
    ];

    return password_hash($password, PASSWORD_ARGON2ID, $options);
}

function CheckSecureHashed($hashed_value, $un_hashed_value)
{
    return password_verify($un_hashed_value, $hashed_value);
}

function periodPath($path)
{
    return str_replace(".", "/", $path);
}

function redirect($path)
{
    header("location:" . \Config::URLROOT() . $path);
}

function dd($content)
{
    echo "<style>*{margin:0;padding:0;box-sizing:border-box;}pre{background:#191e2df2;color:white;margin:20px;padding:10px;border-radius:20px;box-shadow:0 0 25px 0.5px #bf0fff;font-size:17.5px;outline-offset:2px;outline-width:2px;outline-color:rgb(251, 0, 255);outline-style:solid;}</style>";
    if (is_array($content)) {
        echo "<pre>";
        print_r($content);
        echo "</pre>";
    } else {
        echo "<pre>";
        var_dump($content);
        echo "</pre>";
    }
}

function pdf()
{
    echo "<script>window.print();</script>";
}

function download($path, $name)
{
    if (!$path || !$name) return false;
    $file = realpath($path . "/" . basename($name));
    if (!$file || !file_exists($file) || strpos($file, realpath(\Config::PUBLICROOT)) !== 0) return false;

    header("Content-Type: " . (mime_content_type($file) ?: 'application/octet-stream'));
    header("Content-Disposition: attachment; filename=\"" . basename($name) . "\"");
    header("Content-Length: " . filesize($file));
    readfile($file);
    exit;
}

function require_view($path)
{

    if (file_exists(\Config::APPROOT . "/views/" . periodPath($path) . ".php")) {
        require_once \Config::APPROOT . "/views/" . periodPath($path) . ".php";
    } else {
        \Controller::errors(404);
    }
}

function safeEcho($value)
{
    echo htmlspecialchars($value);
}

function urlPath($path)
{
    safeEcho(\Config::URLROOT() . $path);
}

function publicPath($path)
{
    safeEcho(\Config::URLROOT() . $path);
}

function getDbConnection(): PDO
{
    $db = new \Database;
    return $db->db;
}

function getTableColumns(PDO $conn, string $tableName): array
{
    $stmt = $conn->prepare("SELECT * FROM $tableName LIMIT 0");
    $stmt->execute();
    $columnCount = $stmt->columnCount();
    $columns = [];

    for ($i = 0; $i < $columnCount; $i++) {
        $meta = $stmt->getColumnMeta($i);
        if (isset($meta['name'])) {
            $columns[] = $meta['name'];
        }
    }

    return $columns;
}

function handleExportError(Exception $e): void
{
    \Controller::errors(500);
}

function excel(string $tableName): void
{
    try {
        $conn = getDbConnection();
        $columns = getTableColumns($conn, $tableName);

        $output = implode("\t", $columns) . "\n";

        $stmt = $conn->prepare("SELECT * FROM $tableName");
        $stmt->execute();
        $rowCount = 0;

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $rowData = array_map(function ($value) {
                $value = strval($value);
                $value = str_replace(["\t", "\n", "\r"], ' ', $value);
                $value = str_replace('"', '""', $value);
                return '"' . $value . '"';
            }, $row);

            $output .= implode("\t", $rowData) . "\n";
            $rowCount++;
        }

        if ($rowCount === 0) {
            die("No data found in table '$tableName'");
        }

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"" . $tableName . "_export_" . date('Y-m-d') . ".xls\"");
        header("Cache-Control: max-age=0");

        echo $output;
    } catch (PDOException $e) {
        handleExportError($e);
    } finally {
        if (isset($conn)) {
            $conn = null;
        }
    }
}

function word(string $tableName): void
{
    try {
        $conn = getDbConnection();
        $columns = getTableColumns($conn, $tableName);

        $stmt = $conn->prepare("SELECT * FROM $tableName");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);

        if (empty($data)) {
            die("No data found in table '$tableName'");
        }

        $content = '<html xmlns:o="urn:schemas-microsoft-com:office:office"
                xmlns:w="urn:schemas-microsoft-com:office:word"
                xmlns="http://www.w3.org/TR/REC-html40">
                <head>
                <meta charset="UTF-8">
                <title>' . htmlspecialchars($tableName) . '</title>
                <style>
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: right; }
                    th { background-color: #f2f2f2; font-weight: bold; }
                </style>
                </head>
                <body>';

        $content .= '<table dir="rtl"><tr>';
        foreach ($columns as $column) {
            $content .= '<th>' . htmlspecialchars($column) . '</th>';
        }
        $content .= '</tr>';

        foreach ($data as $row) {
            $content .= '<tr>';
            foreach ($row as $value) {
                $content .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $content .= '</tr>';
        }

        $content .= '</table></body></html>';

        header("Content-Type: application/vnd.ms-word");
        header("Content-Disposition: attachment; filename=\"" . $tableName . "_export_" . date('Y-m-d') . ".doc\"");
        header("Cache-Control: max-age=0");

        echo $content;
    } catch (PDOException $e) {
        handleExportError($e);
    } finally {
        if (isset($conn)) {
            $conn = null;
        }
    }
}

function csv(string $tableName): void
{
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM $tableName");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $tableName . "_export_" . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    } catch (PDOException $e) {
        handleExportError($e);
    }
}

function tableExport(string $tableName): void
{
    try {
        $pdo = getDbConnection();

        $stmt = $pdo->prepare("SHOW CREATE TABLE $tableName");
        $stmt->execute();
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT * FROM $tableName");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $tableName . '_export_' . date('Y-m-d') . '.sql"');

        echo "-- SQL Export for table: $tableName\n";
        echo "-- Export time: " . date('Y-m-d H:i:s') . "\n\n";
        echo "DROP TABLE IF EXISTS `$tableName`;\n";
        echo $createTable['Create Table'] . ";\n\n";

        if (!empty($data)) {
            foreach ($data as $row) {
                $columns = array_map(function ($col) {
                    return "`$col`";
                }, array_keys($row));

                $values = array_map(function ($value) use ($pdo) {
                    return $value === null ? 'NULL' : $pdo->quote($value);
                }, array_values($row));

                echo "INSERT INTO `$tableName` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
        }

        exit;
    } catch (PDOException $e) {
        handleExportError($e);
    }
}

function generateToken(): string
{
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
}

function validateToken(): void
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $postToken = $_POST['csrf_token'] ?? '';

    if (empty($postToken) || !hash_equals($sessionToken, $postToken)) {
        // \Controller::errors(403);
?>
        <script>
            history.back();
        </script>
<?php
        exit;
    }

    if (isset($_SESSION["csrf_token"])) {
        unset($_SESSION["csrf_token"]);
    }
}

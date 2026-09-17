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

function encryptText(string $text): string
{
    $key = base64_decode(Config::APP_KEY);

    if (strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
        throw new RuntimeException('Invalid encryption key.');
    }

    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

    $encrypted = sodium_crypto_secretbox($text, $nonce, $key);

    return base64_encode($nonce . $encrypted);
}

function decryptText(string $encrypted): string
{
    $key = base64_decode($_ENV['APP_KEY']);

    if (strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
        throw new RuntimeException('Invalid encryption key.');
    }

    $data = base64_decode($encrypted, true);

    if ($data === false) {
        throw new RuntimeException('Invalid encrypted data.');
    }

    $nonceSize = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

    $nonce = substr($data, 0, $nonceSize);
    $ciphertext = substr($data, $nonceSize);

    $decrypted = sodium_crypto_secretbox_open(
        $ciphertext,
        $nonce,
        $key
    );

    if ($decrypted === false) {
        throw new RuntimeException('Decryption failed.');
    }

    return $decrypted;
}

function periodPath($path)
{
    return str_replace(".", "/", $path);
}

function redirect($path)
{
    header("location:" . Config::URLROOT() . $path);
}

function redirectBack(): never
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';

    $refererHost = parse_url($referer, PHP_URL_HOST);
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';

    if (
        !$refererHost ||
        !hash_equals($currentHost, $refererHost)
    ) {
        $referer = '/';
    }

    header('Location: ' . $referer);
    exit;
}


function dd(...$contents)
{
    echo ' <style> .debug-box { margin: 25px; padding: 0; background: #0d1117; color: #e6edf3; border: 1px solid #30363d; border-radius: 14px; overflow: hidden; font-family: Consolas, Monaco, monospace; box-shadow: 0 10px 40px rgba(0, 0, 0, .35), 0 0 35px rgba(168, 85, 247, .12); } .debug-header { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #161b22; border-bottom: 1px solid #30363d; } .debug-title { display: flex; align-items: center; gap: 9px; color: #fff; font-family: Arial, sans-serif; font-size: 13px; font-weight: 700; } .debug-dot { width: 9px; height: 9px; border-radius: 50%; background: #a855f7; box-shadow: 0 0 12px #a855f7; } .debug-type { padding: 4px 9px; color: #c084fc; background: rgba(168, 85, 247, .1); border: 1px solid rgba(168, 85, 247, .25); border-radius: 6px; font-family: Arial, sans-serif; font-size: 11px; } .debug-content { padding: 20px; overflow-x: auto; } .debug-content::-webkit-scrollbar { width: 7px; height: 7px; } .debug-content::-webkit-scrollbar-track { background: #0d1117; } .debug-content::-webkit-scrollbar-thumb { background: #30363d; border-radius: 10px; } .debug-value { font-size: 15px; line-height: 1.8; white-space: pre-wrap; word-break: break-word; } .debug-array, .debug-object { display: flex; flex-direction: column; gap: 7px; } .debug-item { padding: 10px 13px; background: #161b22; border: 1px solid #21262d; border-radius: 8px; transition: .2s ease; } .debug-item:hover { border-color: #8b5cf6; background: #1b1f27; } .debug-key { color: #79c0ff; } .debug-string { color: #a5d6ff; } .debug-number { color: #79c0ff; } .debug-boolean { color: #ff7b72; } .debug-null { color: #8b949e; } .debug-object { color: #d2a8ff; } .debug-meta { color: #8b949e; font-size: 12px; } .debug-toggle { cursor: pointer; list-style: none; } .debug-toggle::-webkit-details-marker { display: none; } .debug-toggle::before { content: "▶"; display: inline-block; margin-right: 7px; color: #a855f7; font-size: 10px; transition: transform .15s ease; } details[open] > .debug-toggle::before { transform: rotate(90deg); } .debug-nested { margin-top: 9px; margin-left: 15px; padding-left: 12px; border-left: 1px solid #30363d; } .debug-empty { color: #8b949e; font-style: italic; padding: 8px 0; } </style> ';
    echo '<div class="debug-box">';
    foreach ($contents as $index => $content) {
        echo '<div class="debug-header">';
        echo '<div class="debug-title">';
        echo '<span class="debug-dot"></span>';
        echo 'DEBUG #' . ($index + 1);
        echo '</div>';
        echo '<span class="debug-type">';
        echo htmlspecialchars(gettype($content));
        echo '</span>';
        echo '</div>';
        echo '<div class="debug-content">';
        ddRenderValue($content);
        echo '</div>';
    }
    echo '</div>';
    echo '<script> document.querySelectorAll(".debug-toggle").forEach(function (element) { element.addEventListener("click", function (event) { event.stopPropagation(); }); }); </script>';
    die;
}
function ddRenderValue($value, $depth = 0)
{
    if ($depth > 30) {
        echo '<div class="debug-empty">Maximum depth reached</div>';
        return;
    }
    if (is_array($value)) {
        if (empty($value)) {
            echo '<div class="debug-empty">Array [0]</div>';
            return;
        }
        echo '<div class="debug-array">';
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                echo '<div class="debug-item">';
                echo '<details>';
                echo '<summary class="debug-toggle">';
                echo '<span class="debug-key">';
                echo htmlspecialchars((string) $key);
                echo '</span>';
                echo ' <span class="debug-meta">';
                echo 'Array [' . count($item) . ']';
                echo '</span>';
                echo '</summary>';
                echo '<div class="debug-nested">';
                ddRenderValue($item, $depth + 1);
                echo '</div>';
                echo '</details>';
                echo '</div>';
            } elseif (is_object($item)) {
                echo '<div class="debug-item">';
                echo '<details>';
                echo '<summary class="debug-toggle">';
                echo '<span class="debug-key">';
                echo htmlspecialchars((string) $key);
                echo '</span>';
                echo ' <span class="debug-meta">';
                echo 'Object (' . htmlspecialchars(get_class($item)) . ')';
                echo '</span>';
                echo '</summary>';
                echo '<div class="debug-nested">';
                ddRenderObject($item, $depth + 1);
                echo '</div>';
                echo '</details>';
                echo '</div>';
            } else {
                echo '<div class="debug-item">';
                echo '<span class="debug-key">';
                echo htmlspecialchars((string) $key);
                echo '</span>';
                echo ' <span style="color:#8b949e;">=&gt;</span> ';
                ddRenderScalar($item);
                echo '</div>';
            }
        }
        echo '</div>';
        return;
    }
    if (is_object($value)) {
        ddRenderObject($value, $depth);
        return;
    }
    ddRenderScalar($value);
}
function ddRenderObject($object, $depth = 0)
{
    if ($depth > 30) {
        echo '<div class="debug-empty">Maximum depth reached</div>';
        return;
    }
    $properties = (array) $object;
    if (empty($properties)) {
        echo '<div class="debug-empty">';
        echo 'Object (' . htmlspecialchars(get_class($object)) . ') [0]';
        echo '</div>';
        return;
    }
    echo '<div class="debug-object">';
    foreach ($properties as $key => $value) {
        $key = preg_replace('/^\0.*\0/', '', $key);
        echo '<div class="debug-item">';
        if (is_array($value)) {
            echo '<details>';
            echo '<summary class="debug-toggle">';
            echo '<span class="debug-key">';
            echo htmlspecialchars((string) $key);
            echo '</span>';
            echo ' <span class="debug-meta">';
            echo 'Array [' . count($value) . ']';
            echo '</span>';
            echo '</summary>';
            echo '<div class="debug-nested">';
            ddRenderValue($value, $depth + 1);
            echo '</div>';
            echo '</details>';
        } elseif (is_object($value)) {
            echo '<details>';
            echo '<summary class="debug-toggle">';
            echo '<span class="debug-key">';
            echo htmlspecialchars((string) $key);
            echo '</span>';
            echo ' <span class="debug-meta">';
            echo 'Object (' . htmlspecialchars(get_class($value)) . ')';
            echo '</span>';
            echo '</summary>';
            echo '<div class="debug-nested">';
            ddRenderObject($value, $depth + 1);
            echo '</div>';
            echo '</details>';
        } else {
            echo '<span class="debug-key">';
            echo htmlspecialchars((string) $key);
            echo '</span>';
            echo ' <span style="color:#8b949e;">=&gt;</span> ';
            ddRenderScalar($value);
        }
        echo '</div>';
    }
    echo '</div>';
}
function ddRenderScalar($value)
{
    if (is_string($value)) {
        echo '<span class="debug-string">';
        echo '"' . htmlspecialchars($value) . '"';
        echo '</span>';
    } elseif (is_int($value) || is_float($value)) {
        echo '<span class="debug-number">';
        echo htmlspecialchars((string) $value);
        echo '</span>';
    } elseif (is_bool($value)) {
        echo '<span class="debug-boolean">';
        echo $value ? 'true' : 'false';
        echo '</span>';
    } elseif (is_null($value)) {
        echo '<span class="debug-null">';
        echo 'null';
        echo '</span>';
    } elseif (is_resource($value)) {
        echo '<span class="debug-object">';
        echo 'Resource (' . htmlspecialchars(get_resource_type($value)) . ')';
        echo '</span>';
    } else {
        echo '<span>';
        echo htmlspecialchars((string) $value);
        echo '</span>';
    }
}


function pdf()
{
    echo "<script>window.print();</script>";
}

function download($path)
{
    if (!$path) {
        return false;
    }

    $file = realpath(substr($path, 1, (strlen($path) - 1)));

    if (!$file || !is_file($file)) {
        return false;
    }

    // Only allow files inside PUBLICROOT
    $publicRoot = realpath(\Config::PUBLICROOT);

    if (
        !$publicRoot ||
        !str_starts_with($file, $publicRoot . DIRECTORY_SEPARATOR)
    ) {
        return false;
    }

    // Block sensitive files
    $blockedExtensions = [
        'php',
        'php3',
        'php4',
        'php5',
        'phtml',
        'phar',
        'env',
        'ini',
        'htaccess',
        'htpasswd',
        'config',
        'log',
        'sql',
    ];

    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (in_array($extension, $blockedExtensions, true)) {
        return false;
    }

    // Allowed file types
    $allowedExtensions = [
        // Images
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'svg',
        'bmp',
        'ico',
        'tif',
        'tiff',
        'avif',

        // Videos
        'mp4',
        'webm',
        'mkv',
        'avi',
        'mov',
        'wmv',
        'flv',
        'm4v',
        'mpeg',
        'mpg',
        '3gp',

        // Audio
        'mp3',
        'wav',
        'ogg',
        'oga',
        'm4a',
        'aac',
        'flac',
        'wma',

        // Documents
        'txt',
        'csv',
        'pdf',
        'rtf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'odt',
        'ods',
        'odp',

        // Archives
        'zip',
        'rar',
        '7z',
        'tar',
        'gz',
        'bz2',
        'xz',

        // Web / Data
        'xml',
        'json',
        'yaml',
        'yml',

        // Subtitles
        'srt',
        'vtt',

        // Fonts
        'ttf',
        'otf',
        'woff',
        'woff2',
    ];

    if (!in_array($extension, $allowedExtensions, true)) {
        return false;
    }

    $mime = mime_content_type($file) ?: 'application/octet-stream';

    header('Content-Type: ' . $mime);
    header(
        'Content-Disposition: attachment; filename="' .
            basename($file) .
            '"'
    );
    header('Content-Length: ' . filesize($file));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    readfile($file);
    exit;
}

function require_view($path)
{

    if (file_exists(\Config::PRIVATEROOT . "/views/" . periodPath($path) . ".php")) {
        require_once Config::PRIVATEROOT . "/views/" . periodPath($path) . ".php";
    } else {
        Errors::error(404);
    }
}

function safeEcho($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function urlPath($path)
{
    return safeEcho(\Config::URLROOT() . '/' . ltrim($path, '/'));
}

function publicPath($path)
{
    $path = substr($path, 1, (strlen($path) - 1));
    return safeEcho(\Config::PUBLICROOT . $path);
}
function privatePath($path)
{
    return Config::PRIVATEROOT . $path;
}

function getDbConnection(): PDO
{
    return DB::connect();
}

function dbIdentifier(string $name): string
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
        Errors::error(500);
        exit;
    }

    return "`{$name}`";
}

function getTableColumns(PDO $conn, string $tableName): array
{
    $table = dbIdentifier($tableName);

    $stmt = $conn->query("SHOW COLUMNS FROM {$table}");

    $columns = [];

    while ($column = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (isset($column['Field'])) {
            $columns[] = $column['Field'];
        }
    }

    return $columns;
}

function handleExportError(\Throwable $e): void
{
    Errors::error(500);
    exit;
}

function exportFileName(string $tableName, string $extension): string
{
    $safeTableName = preg_replace(
        '/[^a-zA-Z0-9_-]/',
        '_',
        $tableName
    );

    return $safeTableName
        . '_export_'
        . date('Y-m-d')
        . '.'
        . $extension;
}

function excel(string $tableName): void
{
    try {
        $conn = getDbConnection();

        $table = dbIdentifier($tableName);
        $columns = getTableColumns($conn, $tableName);

        if (empty($columns)) {
            Errors::error(404);
            exit;
        }

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header(
            'Content-Disposition: attachment; filename="'
                . exportFileName($tableName, 'xls')
                . '"'
        );
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $output = fopen('php://output', 'w');

        if ($output === false) {
            throw new RuntimeException('Unable to open output stream.');
        }

        /*
         * Header
         */
        $header = array_map(
            static function (string $column): string {
                $column = str_replace(
                    ["\t", "\n", "\r"],
                    ' ',
                    $column
                );

                return '"' . str_replace('"', '""', $column) . '"';
            },
            $columns
        );

        fwrite(
            $output,
            implode("\t", $header) . "\n"
        );

        /*
         * Data
         */
        $stmt = $conn->query("SELECT * FROM {$table}");

        $rowCount = 0;

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {

            $rowData = array_map(
                static function ($value): string {

                    if ($value === null) {
                        $value = '';
                    }

                    $value = (string) $value;

                    $value = str_replace(
                        ["\t", "\n", "\r"],
                        ' ',
                        $value
                    );

                    $value = str_replace(
                        '"',
                        '""',
                        $value
                    );

                    return '"' . $value . '"';
                },
                $row
            );

            fwrite(
                $output,
                implode("\t", $rowData) . "\n"
            );

            $rowCount++;
        }

        fclose($output);

        if ($rowCount === 0) {
            return;
        }

        exit;
    } catch (\Throwable $e) {
        handleExportError($e);
    }
}

function word(string $tableName): void
{
    try {
        $conn = getDbConnection();

        $table = dbIdentifier($tableName);
        $columns = getTableColumns($conn, $tableName);

        if (empty($columns)) {
            Errors::error(404);
            exit;
        }

        $stmt = $conn->query(
            "SELECT * FROM {$table}"
        );

        $data = $stmt->fetchAll(PDO::FETCH_NUM);

        if (empty($data)) {
            Errors::error(404);
            exit;
        }

        $safeTableName = htmlspecialchars(
            $tableName,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $content = '<!DOCTYPE html>
<html
    xmlns:o="urn:schemas-microsoft-com:office:office"
    xmlns:w="urn:schemas-microsoft-com:office:word"
    xmlns="http://www.w3.org/TR/REC-html40"
    dir="rtl"
>
<head>
    <meta charset="UTF-8">
    <title>' . $safeTableName . '</title>

    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            direction: rtl;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: right;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>

<body>

<table dir="rtl">

<tr>';

        foreach ($columns as $column) {

            $safeColumn = htmlspecialchars(
                $column,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            $content .= '<th>'
                . $safeColumn
                . '</th>';
        }

        $content .= '</tr>';

        foreach ($data as $row) {

            $content .= '<tr>';

            foreach ($row as $value) {

                $safeValue = htmlspecialchars(
                    (string) ($value ?? ''),
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                );

                $content .= '<td>'
                    . $safeValue
                    . '</td>';
            }

            $content .= '</tr>';
        }

        $content .= '
</table>

</body>
</html>';

        header('Content-Type: application/vnd.ms-word; charset=utf-8');
        header(
            'Content-Disposition: attachment; filename="'
                . exportFileName($tableName, 'doc')
                . '"'
        );
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        echo $content;

        exit;
    } catch (\Throwable $e) {
        handleExportError($e);
    }
}

function csv(string $tableName): void
{
    try {
        $pdo = getDbConnection();

        $table = dbIdentifier($tableName);

        $stmt = $pdo->query(
            "SELECT * FROM {$table}"
        );

        $output = fopen('php://output', 'w');

        if ($output === false) {
            throw new RuntimeException(
                'Unable to open output stream.'
            );
        }

        header('Content-Type: text/csv; charset=utf-8');
        header(
            'Content-Disposition: attachment; filename="'
                . exportFileName($tableName, 'csv')
                . '"'
        );
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        /*
         * UTF-8 BOM
         * باعث می‌شود Excel فارسی را درست تشخیص دهد.
         */
        fwrite(
            $output,
            "\xEF\xBB\xBF"
        );

        $columns = getTableColumns(
            $pdo,
            $tableName
        );

        if (empty($columns)) {
            fclose($output);
            Errors::error(404);
            exit;
        }

        /*
         * Header
         */
        fputcsv(
            $output,
            $columns
        );

        /*
         * Data
         */
        $rowCount = 0;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            fputcsv(
                $output,
                array_values($row)
            );

            $rowCount++;
        }

        fclose($output);

        exit;
    } catch (\Throwable $e) {
        handleExportError($e);
    }
}

function tableExport(string $tableName): void
{
    try {
        $pdo = getDbConnection();

        $table = dbIdentifier($tableName);

        /*
         * Get CREATE TABLE
         */
        $stmt = $pdo->query(
            "SHOW CREATE TABLE {$table}"
        );

        $createTable = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        if (
            !$createTable ||
            !isset($createTable['Create Table'])
        ) {
            Errors::error(404);
            exit;
        }

        /*
         * Get columns
         */
        $columns = getTableColumns(
            $pdo,
            $tableName
        );

        if (empty($columns)) {
            Errors::error(404);
            exit;
        }

        /*
         * Export headers
         */
        header(
            'Content-Type: application/sql; charset=utf-8'
        );

        header(
            'Content-Disposition: attachment; filename="'
                . exportFileName($tableName, 'sql')
                . '"'
        );

        header('Cache-Control: max-age=0');
        header('Pragma: public');

        echo "-- SQL Export for table: {$tableName}\n";
        echo "-- Export time: "
            . date('Y-m-d H:i:s')
            . "\n\n";

        echo "DROP TABLE IF EXISTS {$table};\n";

        echo $createTable['Create Table']
            . ";\n\n";

        /*
         * Get data
         */
        $stmt = $pdo->query(
            "SELECT * FROM {$table}"
        );

        /*
         * Prepare escaped column names
         */
        $escapedColumns = array_map(
            static function (string $column): string {
                return dbIdentifier($column);
            },
            $columns
        );

        $columnList = implode(
            ', ',
            $escapedColumns
        );

        /*
         * Export rows
         */
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $values = array_map(
                static function ($value) use ($pdo): string {

                    if ($value === null) {
                        return 'NULL';
                    }

                    return $pdo->quote(
                        (string) $value
                    );
                },
                array_values($row)
            );

            echo "INSERT INTO {$table} ({$columnList}) VALUES ("
                . implode(', ', $values)
                . ");\n";
        }

        exit;
    } catch (\Throwable $e) {
        handleExportError($e);
    }
}

function databaseExport($path = '')
{
    try {
        $pdo = getDbConnection();
        $database = Config::DB_NAME;

        $fileName = "database.sql";

        /*
        |--------------------------------------------------------------------------
        | Build Export
        |--------------------------------------------------------------------------
        */

        $output = '';

        $output .= "-- ========================================\n";
        $output .= "-- Database Backup\n";
        $output .= "-- Database: {$database}\n";
        $output .= "-- Export time: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- ========================================\n\n";

        $output .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $output .= "SET NAMES utf8mb4;\n\n";

        /*
        |--------------------------------------------------------------------------
        | Get Tables
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        /*
        |--------------------------------------------------------------------------
        | Get Foreign Keys
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT
                kcu.TABLE_NAME,
                kcu.CONSTRAINT_NAME,
                kcu.COLUMN_NAME,
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME,
                kcu.ORDINAL_POSITION,
                rc.UPDATE_RULE,
                rc.DELETE_RULE
            FROM information_schema.KEY_COLUMN_USAGE kcu
            LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
                AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                AND rc.TABLE_NAME = kcu.TABLE_NAME
            WHERE kcu.CONSTRAINT_SCHEMA = ?
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
            ORDER BY
                kcu.TABLE_NAME,
                kcu.CONSTRAINT_NAME,
                kcu.ORDINAL_POSITION
        ");

        $stmt->execute([$database]);

        $foreignKeys = [];

        while ($fk = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $key =
                $fk['TABLE_NAME']
                . '|'
                . $fk['CONSTRAINT_NAME'];

            if (!isset($foreignKeys[$key])) {
                $foreignKeys[$key] = [
                    'table' => $fk['TABLE_NAME'],
                    'constraint' => $fk['CONSTRAINT_NAME'],
                    'columns' => [],
                    'reference_table' => $fk['REFERENCED_TABLE_NAME'],
                    'reference_columns' => [],
                    'on_update' => $fk['UPDATE_RULE'],
                    'on_delete' => $fk['DELETE_RULE'],
                ];
            }

            $foreignKeys[$key]['columns'][] =
                $fk['COLUMN_NAME'];

            $foreignKeys[$key]['reference_columns'][] =
                $fk['REFERENCED_COLUMN_NAME'];
        }

        /*
        |--------------------------------------------------------------------------
        | Export Tables
        |--------------------------------------------------------------------------
        */

        foreach ($tables as $tableName) {

            $table = dbIdentifier($tableName);

            $output .= "-- ========================================\n";
            $output .= "-- Table: {$tableName}\n";
            $output .= "-- ========================================\n\n";

            /*
            |--------------------------------------------------------------------------
            | CREATE TABLE
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->query(
                "SHOW CREATE TABLE {$table}"
            );

            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                !$createTable ||
                !isset($createTable['Create Table'])
            ) {
                continue;
            }

            $createSQL = $createTable['Create Table'];

            /*
            |--------------------------------------------------------------------------
            | Remove Foreign Keys
            |--------------------------------------------------------------------------
            */

            $lines = preg_split(
                '/\R/',
                $createSQL
            );

            $cleanLines = [];

            foreach ($lines as $line) {

                if (
                    preg_match(
                        '/^\s*CONSTRAINT\s+`[^`]+`\s+FOREIGN KEY\b/i',
                        $line
                    )
                ) {
                    continue;
                }

                $cleanLines[] = $line;
            }

            $createSQL = implode(
                "\n",
                $cleanLines
            );

            $createSQL = preg_replace(
                '/,\s*\)\s*ENGINE=/i',
                "\n) ENGINE=",
                $createSQL
            );

            $output .=
                "DROP TABLE IF EXISTS {$table};\n";

            $output .=
                $createSQL . ";\n\n";

            /*
            |--------------------------------------------------------------------------
            | Export Data
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->query(
                "SELECT * FROM {$table}"
            );

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $columns = [];
                $values = [];

                foreach ($row as $column => $value) {

                    $columns[] =
                        dbIdentifier($column);

                    $values[] =
                        $value === null
                        ? 'NULL'
                        : $pdo->quote(
                            (string) $value
                        );
                }

                $output .=
                    "INSERT INTO {$table} ("
                    . implode(', ', $columns)
                    . ") VALUES ("
                    . implode(', ', $values)
                    . ");\n";
            }

            $output .= "\n";
        }

        /*
        |--------------------------------------------------------------------------
        | Foreign Keys
        |--------------------------------------------------------------------------
        */

        if (!empty($foreignKeys)) {

            $output .=
                "-- ========================================\n";

            $output .=
                "-- Foreign Keys\n";

            $output .=
                "-- ========================================\n\n";

            foreach ($foreignKeys as $foreignKey) {

                $table = dbIdentifier(
                    $foreignKey['table']
                );

                $constraint = dbIdentifier(
                    $foreignKey['constraint']
                );

                $referenceTable = dbIdentifier(
                    $foreignKey['reference_table']
                );

                $columns = array_map(
                    'dbIdentifier',
                    $foreignKey['columns']
                );

                $referenceColumns = array_map(
                    'dbIdentifier',
                    $foreignKey['reference_columns']
                );

                $output .=
                    "ALTER TABLE {$table}\n";

                $output .=
                    "ADD CONSTRAINT {$constraint}\n";

                $output .=
                    "FOREIGN KEY ("
                    . implode(', ', $columns)
                    . ")\n";

                $output .=
                    "REFERENCES {$referenceTable} ("
                    . implode(', ', $referenceColumns)
                    . ")";

                if (
                    !empty($foreignKey['on_delete']) &&
                    strtoupper($foreignKey['on_delete']) !== 'RESTRICT'
                ) {
                    $output .=
                        " ON DELETE "
                        . strtoupper(
                            $foreignKey['on_delete']
                        );
                }

                if (
                    !empty($foreignKey['on_update']) &&
                    strtoupper($foreignKey['on_update']) !== 'RESTRICT'
                ) {
                    $output .=
                        " ON UPDATE "
                        . strtoupper(
                            $foreignKey['on_update']
                        );
                }

                $output .= ";\n\n";
            }
        }

        $output .=
            "SET FOREIGN_KEY_CHECKS = 1;\n";

        /*
        |--------------------------------------------------------------------------
        | Save Or Download
        |--------------------------------------------------------------------------
        */

        if ($path !== '') {

            if (!is_dir($path)) {

                if (file_exists($path)) {
                    Errors::error(400);
                    exit;
                }

                if (!mkdir($path, 0755, true) && !is_dir($path)) {
                    Errors::error(500);
                    exit;
                }
            }

            if (!is_writable($path)) {
                Errors::error(403);
                exit;
            }

            $filePath =
                rtrim($path, '/\\')
                . DIRECTORY_SEPARATOR
                . $fileName;

            if (file_put_contents($filePath, $output) === false) {
                Errors::error(500);
                exit;
            }

            return $filePath;
        }

        /*
        |--------------------------------------------------------------------------
        | Download
        |--------------------------------------------------------------------------
        */

        header(
            'Content-Type: application/sql; charset=utf-8'
        );

        header(
            'Content-Disposition: attachment; filename="'
                . $fileName
                . '"'
        );

        header(
            'Content-Length: '
                . strlen($output)
        );

        header('Cache-Control: max-age=0');
        header('Pragma: public');

        echo $output;

        exit;
    } catch (\Throwable $e) {

        handleExportError($e);
    }
}

function databaseImport(string $filePath): bool
{
    try {
        /*
        |--------------------------------------------------------------------------
        | Check File
        |--------------------------------------------------------------------------
        */

        if (!is_file($filePath)) {
            Errors::error(404);
            exit;
        }

        if (!is_readable($filePath)) {
            Errors::error(403);
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Read SQL File
        |--------------------------------------------------------------------------
        */

        $sql = file_get_contents($filePath);

        if ($sql === false || trim($sql) === '') {
            Errors::error(400);
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Database Connection
        |--------------------------------------------------------------------------
        */

        $pdo = getDbConnection();

        /*
        |--------------------------------------------------------------------------
        | Start Transaction
        |--------------------------------------------------------------------------
        */

        $pdo->beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | Disable Foreign Keys
        |--------------------------------------------------------------------------
        */

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        /*
        |--------------------------------------------------------------------------
        | Execute SQL
        |--------------------------------------------------------------------------
        */

        $pdo->exec($sql);

        /*
        |--------------------------------------------------------------------------
        | Enable Foreign Keys
        |--------------------------------------------------------------------------
        */

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        /*
        |--------------------------------------------------------------------------
        | Commit
        |--------------------------------------------------------------------------
        */

        $pdo->commit();

        return true;
    } catch (\Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Rollback
        |--------------------------------------------------------------------------
        */

        if (
            isset($pdo) &&
            $pdo instanceof PDO &&
            $pdo->inTransaction()
        ) {
            $pdo->rollBack();
        }

        /*
        |--------------------------------------------------------------------------
        | Make Sure Foreign Keys Are Enabled
        |--------------------------------------------------------------------------
        */

        if (isset($pdo) && $pdo instanceof PDO) {
            try {
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            } catch (\Throwable) {
                // Ignore cleanup errors
            }
        }

        handleExportError($e);
    }

    return false;
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
        redirectBack();
        exit;
    }

    if (isset($_SESSION["csrf_token"])) {
        unset($_SESSION["csrf_token"]);
    }
}

function csrf(): void
{
    echo '<div style="opacity: 0;" hidden>';
    echo '<div style="opacity: 0;" hidden>';
    echo '<div style="opacity: 0;" hidden>';
    echo '<input type="hidden" style="opacity: 0;" hidden name="csrf_token" value="' .
        htmlspecialchars(generateToken(), ENT_QUOTES, 'UTF-8') .
        '">';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

function device($ua)
{
    if (preg_match('/Android/i', $ua)) {
        return 'Android (Mobile/Tablet)';
    } elseif (preg_match('/iPhone|iPad|iPod/i', $ua)) {
        return 'Apple iOS Device';
    } elseif (preg_match('/Windows Phone/i', $ua)) {
        return 'Windows Phone';
    } elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) {
        return 'Mac Desktop';
    } elseif (preg_match('/Windows NT/i', $ua)) {
        return 'Windows Desktop';
    } elseif (preg_match('/Linux/i', $ua)) {
        return 'Linux Desktop';
    } else {
        return 'Unknown Device';
    }
}

function detect_browser($ua)
{
    if (preg_match('/Edg/i', $ua)) {
        return 'Microsoft Edge';
    } elseif (preg_match('/Opera|OPR/i', $ua)) {
        return 'Opera';
    } elseif (preg_match('/Chrome/i', $ua)) {
        return 'Google Chrome';
    } elseif (preg_match('/Safari/i', $ua) && !preg_match('/Chrome/i', $ua)) {
        return 'Safari';
    } elseif (preg_match('/Firefox|FxiOS/i', $ua)) {
        return 'Mozilla Firefox';
    } elseif (preg_match('/Trident|MSIE/i', $ua)) {
        return 'Internet Explorer';
    } elseif (preg_match('/Brave/i', $ua)) {
        return 'Brave';
    } else {
        return 'Unknown Browser';
    }
}

function batteryCharge()
{
?>
    <script>
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/; secure; samesite=Lax";
        }
        navigator.getBattery().then(battery => {
            setCookie("battery", Math.round(battery.level * 100), 1);
        });
    </script>
<?php
}

function getBatteryCharge()
{
    if (isset($_COOKIE['battery'])) {
        $batteryLevel = $_COOKIE['battery'];
        return $batteryLevel . "%";
    } else {
        return "0%";
    }
}

function getInternetSpeed()
{
    $testUrl = "https://avatars.githubusercontent.com/u/312878616?s=60&v=4";

    $startTime = microtime(true);

    $fileContent = @file_get_contents($testUrl);

    $endTime = microtime(true);

    if ($fileContent === false) {
        return "❌ خطا: نمی‌توان به اینترنت متصل شد یا فایل دریافت نشد.";
    }

    $fileSize = strlen($fileContent);

    $duration = $endTime - $startTime;

    $speedBytesPerSec = $fileSize / $duration;

    $speedMbps = ($speedBytesPerSec * 8) / (1024 * 1024);

    if ($speedMbps < 1) {
        $quality = "Very Low ";
    } elseif ($speedMbps < 4) {
        $quality = "Low ";
    } elseif ($speedMbps < 10) {
        $quality = "Medium ";
    } elseif ($speedMbps < 30) {
        $quality = "Good ";
    } else {
        $quality = "Very Good ";
    }

    return [
        'speed_mbps' => round($speedMbps, 2),
        'quality' => $quality,
        'duration_seconds' => round($duration, 3),
        'file_size_kb' => round($fileSize / 1024, 2)
    ];
}

function dateTime()
{
    $t = time();
    return "date: " . date('l، j F Y (d/m/Y)') . " - time: " . date('H:i:s') . " - " .
        "houre: " . date('g') . " " . date('A') . " - second of start year: " . date('z') .
        " - second of start weak: " . date('N') . " - micro second: " . date('u') .
        " - region: " . date('e') . " - offset: " . date('P');
}

function getRealUserIP()
{
    $trustedProxies = ['127.0.0.1', '::1'];

    if (in_array($_SERVER['REMOTE_ADDR'] ?? '', $trustedProxies)) {
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR'
        ];

        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);

                if (
                    filter_var(
                        $ip,
                        FILTER_VALIDATE_IP,
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                    )
                ) {
                    return $ip;
                }
            }
        }
    }

    $remoteAddr = $_SERVER["REMOTE_ADDR"] ?? "Unknown";

    if (function_exists('WEB') && WEB() === "off") {
        return random_int(1, 223) . '.' .
            random_int(0, 255) . '.' .
            random_int(0, 255) . '.' .
            random_int(1, 254);
    }

    return $remoteAddr;
}

function dbTime($time)
{
    $datetime = new DateTime($time);
    $datetime->modify('+3 hours 30 minutes');
    return $datetime->format('Y-m-d H:i:s');
}
function phpTime($time)
{
    $datetime = new DateTime($time);
    $datetime->modify('-3 hours 30 minutes');
    return $datetime->format('Y-m-d H:i:s');
}

function truncateSafe($text, $length = 50)
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . "...";
}

function session(string $name, mixed $value): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION[$name] = $value;
}

function getSession(string $name, mixed $default = null): mixed
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    return $_SESSION[$name] ?? $default;
}

function unsetSession(string $name): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    unset($_SESSION[$name]);
}

function destroySession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}

function cookie(string $name, string $value, int $minutes = 60): bool
{
    return setcookie($name, $value, [
        'expires'  => time() + ($minutes * 60),
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function unsetCookie(string $name): bool
{
    return setcookie($name, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function flash(string $name, mixed $value, $path = ""): void
{
    session($name, $value);
    if (!empty($path)) {
        redirect($path);
    }
}

function getFlash(string $name, mixed $default = null): mixed
{
    $value = getSession($name, $default);

    unsetSession($name);

    return $value;
}

function old(string $key, mixed $default = ''): string
{
    $old = getSession('_old', []);

    if (
        empty($old) ||
        !isset($old['expires_at']) ||
        time() >= $old['expires_at']
    ) {
        unsetSession('_old');
        return $default;
    }

    return htmlspecialchars(
        $old['data'][$key] ?? $default,
        ENT_QUOTES,
        'UTF-8'
    );
}

function oldInput(): void
{
    $old = getSession('_old');

    if ($old !== null && isset($old['expires_at'])) {
        if (time() >= $old['expires_at']) {
            unsetSession('_old');
        }
    }

    session('_old', [
        'data' => $_POST,
        'expires_at' => time() + 600
    ]);
}

function getValue(string $value)
{
    return trim($_POST[$value] ?? "");
}

function validateEmpty(array $data, mixed $callback)
{
    foreach ($data as $item) {
        if ($item === "") {
            $callback($item);
        }
    }
}
function validateEmail(string $email, mixed $callback): void
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $callback($email);
    }
}

function validateMinLength(
    string $value,
    int $length,
    mixed $callback
): void {
    if (mb_strlen($value) < $length) {
        $callback($value);
    }
}

function validateMaxLength(
    string $value,
    int $length,
    mixed $callback
): void {
    if (mb_strlen($value) > $length) {
        $callback($value);
    }
}

<?php

declare(strict_types=1);
session_start();
date_default_timezone_set("Asia/Tehran");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer-when-downgrade");
function WEB()
{
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    $devDomains = ['localhost', '127.0.0.1', 'dev.', 'test.', 'staging.'];

    foreach ($devDomains as $devDomain) {
        if (strpos($host, $devDomain) !== false) {
            return 'off';
        }
    }

    return 'on';
}

if (WEB() === "off") {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    
define('ERROR_HANDLER_CONFIG', [
    'show_environment' => true,
    'show_request' => true,
    'show_backtrace' => true,
    'show_code_snippet' => true,
    'snippet_lines' => 7,
    'show_memory' => true,
    'show_timestamp' => true,
    'show_version' => true,
    'show_extensions' => false,
    'dark_mode' => true,
    'allow_ajax' => true,
    'log_errors' => true,
    'log_file' => __DIR__ . '/log/error_log.log',
    'email_errors' => false,
    'email_to' => 'admin@example.com',
    'email_from' => 'errors@example.com',
    'email_subject' => 'Critical Error Occurred'
]);

set_exception_handler(function (Throwable $e) {
    if (isAjaxRequest() && ERROR_HANDLER_CONFIG['allow_ajax']) {
        sendJsonError($e);
    } else {
        renderFatalError($e);
    }
    
    logError($e);
    
    if (ERROR_HANDLER_CONFIG['email_errors'] && isCriticalError($e)) {
        sendErrorEmail($e);
    }
    exit(1);
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $error = new ErrorException($errstr, 0, $errno, $errfile, $errline);

    // Only halt execution for fatal-ish errors. Recoverable warnings should not kill the page.
    $isFatal = in_array($errno, [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
        E_RECOVERABLE_ERROR
    ], true);

    if ($isFatal) {
        if (isAjaxRequest() && ERROR_HANDLER_CONFIG['allow_ajax']) {
            sendJsonError($error);
        } else {
            renderError($errno, $errstr, $errfile, $errline);
        }
        logError($error);
        if (ERROR_HANDLER_CONFIG['email_errors'] && isCriticalError($error)) {
            sendErrorEmail($error);
        }
        exit(1);
    }

    // Non-fatal: log & render inline but keep going
    logError($error);
    if (isAjaxRequest() && ERROR_HANDLER_CONFIG['allow_ajax']) {
        // For AJAX, we do not interrupt the response unless fatal
        return true;
    }
    renderError($errno, $errstr, $errfile, $errline);
    return true;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if (!$error) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($error['type'], $fatalTypes, true)) {
        return;
    }

    $exception = new ErrorException(
        $error['message'],
        0,
        $error['type'],
        $error['file'],
        $error['line']
    );

    if (isAjaxRequest() && ERROR_HANDLER_CONFIG['allow_ajax']) {
        sendJsonError($exception);
    } else {
        renderFatalError($exception);
    }
    logError($exception);
    if (ERROR_HANDLER_CONFIG['email_errors'] && isCriticalError($exception)) {
        sendErrorEmail($exception);
    }
});

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function isAjaxRequest(): bool
{
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        return false;
    }
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function isCriticalError(Throwable $e): bool
{
    if ($e instanceof Error) {
        return true;
    }
    if ($e instanceof ErrorException) {
        return in_array($e->getSeverity(), [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
            E_RECOVERABLE_ERROR
        ], true);
    }
    return false;
}

function sendJsonError(Throwable $e): void
{
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'error' => true,
        'type' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => ERROR_HANDLER_CONFIG['show_backtrace'] ? $e->getTrace() : null,
        'code' => $e->getCode(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function cleanErrorLog(): void
{
    $logFile = ERROR_HANDLER_CONFIG['log_file'];

    if (!is_file($logFile)) {
        return;
    }

    $lastModified = filemtime($logFile);

    if ($lastModified !== false && $lastModified <= time() - (7 * 24 * 60 * 60)) {
        @unlink($logFile);
    }
}
cleanErrorLog();
function logError(Throwable $e): void
{
    if (!ERROR_HANDLER_CONFIG['log_errors']) {
        return;
    }

    $logFile = ERROR_HANDLER_CONFIG['log_file'];
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }

    $logEntry = sprintf(
        "[%s] %s: %s in %s on line %d\nStack trace:\n%s\n\n",
        date('Y-m-d H:i:s'),
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );

    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

function sendErrorEmail(Throwable $e): void
{
    $subject = ERROR_HANDLER_CONFIG['email_subject'];
    $message = "A critical error occurred on your website:\n\n";
    $message .= "Error: " . get_class($e) . "\n";
    $message .= "Message: " . $e->getMessage() . "\n";
    $message .= "File: " . $e->getFile() . "\n";
    $message .= "Line: " . $e->getLine() . "\n";
    $message .= "Time: " . date('Y-m-d H:i:s') . "\n\n";
    $message .= "Stack Trace:\n" . $e->getTraceAsString() . "\n\n";
    $message .= "Request URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
    $message .= "IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n";

    $from = ERROR_HANDLER_CONFIG['email_from'];
    $headers = "From: " . $from . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    @mail(ERROR_HANDLER_CONFIG['email_to'], $subject, $message, $headers);
}

function getCodeSnippet(string $file, int $line, int $linesAround = 5): ?string
{
    if (!ERROR_HANDLER_CONFIG['show_code_snippet'] || !is_file($file) || !is_readable($file)) {
        return null;
    }

    $fileLines = @file($file);
    if ($fileLines === false) {
        return null;
    }

    $totalLines = count($fileLines);
    if ($totalLines === 0) {
        return null;
    }

    $startLine = max(0, $line - $linesAround - 1);
    $endLine = min($totalLines, $line + $linesAround);

    $snippet = '';
    for ($i = $startLine; $i < $endLine; $i++) {
        $currentLine = $i + 1;
        $lineContent = htmlspecialchars($fileLines[$i] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $highlight = ($currentLine === $line)
            ? ' style="background: rgba(255, 100, 100, 0.2); display:flex;"'
            : ' style="display:flex;"';
        $snippet .= sprintf(
            '<div class="code-line"%s><span class="line-number">%d</span><span class="line-text">%s</span></div>',
            $highlight,
            $currentLine,
            rtrim($lineContent, "\r\n")
        );
    }
    return $snippet;
}

function getRequestDetails(): array
{
    $post = $_POST;
    $get = $_GET;

    // Mask sensitive fields
$sensitiveKeys = [
    'password',
    'passwd',
    'pwd',
    'token',
    'secret',
    'api_key',
    'apikey',
    'auth'
];

$maskFn = function (&$arr) use (&$maskFn, $sensitiveKeys): void {
    foreach ($arr as $k => &$v) {
        if (is_array($v)) {
            $maskFn($v);
        } elseif (in_array(strtolower((string) $k), $sensitiveKeys, true)) {
            $v = '***';
        }
    }

    unset($v);
};
    $maskFn($post);
    $maskFn($get);

    return [
        'Method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
        'URI' => $_SERVER['REQUEST_URI'] ?? 'Command Line',
        'Protocol' => $_SERVER['SERVER_PROTOCOL'] ?? '',
        'IP' => $_SERVER['REMOTE_ADDR'] ?? '',
        'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'Referer' => $_SERVER['HTTP_REFERER'] ?? '',
        'Query String' => $_SERVER['QUERY_STRING'] ?? '',
        'POST Data' => !empty($post) ? $post : null,
        'GET Data' => !empty($get) ? $get : null
    ];
}

function getEnvironmentDetails(): array
{
    $details = [
        'PHP Version' => PHP_VERSION,
        'OS' => PHP_OS,
        'Server' => $_SERVER['SERVER_SOFTWARE'] ?? '',
        'Host' => $_SERVER['HTTP_HOST'] ?? '',
        'Memory Usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        'Peak Memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
        'Include Path' => get_include_path(),
        'Display Errors' => ini_get('display_errors'),
        'Error Reporting' => error_reporting(),
        'Timezone' => date_default_timezone_get(),
        'Current Time' => date('Y-m-d H:i:s')
    ];

    if (ERROR_HANDLER_CONFIG['show_extensions']) {
        $details['Loaded Extensions'] = implode(', ', get_loaded_extensions());
    }

    return $details;
}

/*
|--------------------------------------------------------------------------
| HTML Renderers
|--------------------------------------------------------------------------
*/

function renderPageHead(string $title): void
{
    $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>$escapedTitle</title>
<style>
:root {--notice: #00e5ff;--warning: #FFA500;--fatal: #FF3D3D;--exception: #BA68C8;--bg-dark: #0D0F15;--bg-light: #161925;--card-bg: rgba(25, 30, 45, 0.95);--text-primary: #F0F4FF;--text-secondary: #B0B8D0;--border-radius: 16px;--glow-opacity: 0.15;}* {margin: 0;padding: 0;box-sizing: border-box;}body {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;background: linear-gradient(135deg, var(--bg-dark), var(--bg-light));color: var(--text-primary);min-height: 100vh;padding: 2rem 1rem;line-height: 1.6;}.error-container {display: flex;flex-direction: column;align-items: center;gap: 2rem;max-width: 1200px;margin: 0 auto;perspective: 1000px;}.error-card {width: 100%;background: var(--card-bg);border-radius: var(--border-radius);overflow: hidden;box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);animation: cardEntrance 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;transition: all 0.3s ease;position: relative;backdrop-filter: blur(8px);border: 1px solid rgba(255, 255, 255, 0.08);}.error-card:hover {transform: translateY(-5px) scale(1.01);box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);}.error-header {padding: 1.5rem 2rem;display: flex;align-items: center;gap: 1rem;position: relative;overflow: hidden;}.error-header::after {content: '';position: absolute;bottom: 0;left: 2rem;right: 2rem;height: 1px;background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);}.error-icon {font-size: 1.8rem;width: 3.5rem;height: 3.5rem;border-radius: 50%;display: flex;align-items: center;justify-content: center;flex-shrink: 0;background: rgba(0, 0, 0, 0.3);box-shadow: 0 0 15px currentColor;color: var(--color);}.error-title {font-size: 1.5rem;font-weight: 600;display: flex;flex-direction: column;gap: 0.3rem;}.error-type {font-size: 0.9rem;font-weight: 500;opacity: 0.8;letter-spacing: 1px;}.error-body {padding: 0 2rem 2rem;}.error-message {font-size: 1.1rem;line-height: 1.8;margin: 1.5rem 0;padding: 1.5rem;background: rgba(0, 0, 0, 0.2);border-radius: 8px;border-left: 4px solid var(--color);font-family: monospace;position: relative;overflow: hidden;word-break: break-word;}.error-details {display: grid;grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));gap: 1rem;margin-top: 1.5rem;}.detail-box {background: rgba(0, 0, 0, 0.2);padding: 1rem 1.2rem;border-radius: 8px;display: flex;flex-direction: column;transition: all 0.3s ease;border: 1px solid rgba(255, 255, 255, 0.05);position: relative;overflow: hidden;}.detail-box:hover {background: rgba(0, 0, 0, 0.3);transform: translateY(-3px);box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);}.detail-box::before {content: '';position: absolute;top: 0;left: 0;width: 3px;height: 100%;background: currentColor;opacity: 0.3;}.detail-label {font-size: 0.85rem;color: var(--text-secondary);margin-bottom: 0.5rem;display: flex;align-items: center;gap: 0.5rem;}.detail-value {font-size: 0.95rem;font-weight: 500;word-break: break-word;font-family: monospace;}.error-stack {margin-top: 2rem;}.stack-title {display: flex;align-items: center;gap: 0.8rem;font-size: 1rem;margin-bottom: 1rem;color: var(--text-secondary);}.stack-content {background: rgba(0, 0, 0, 0.2);padding: 1.5rem;border-radius: 8px;font-family: monospace;font-size: 0.85rem;line-height: 1.7;white-space: pre-wrap;overflow-x: auto;border: 1px solid rgba(255, 255, 255, 0.05);}.code-snippet {margin-top: 1.5rem;}.code-snippet-title {display: flex;align-items: center;gap: 0.8rem;font-size: 1rem;margin-bottom: 1rem;color: var(--text-secondary);}.code-content {background: rgba(0, 0, 0, 0.2);padding: 1rem;border-radius: 8px;font-family: monospace;font-size: 0.85rem;line-height: 1.5;overflow-x: auto;border: 1px solid rgba(255, 255, 255, 0.05);}.code-line {display: flex;margin: 0.1rem 0;}.line-number {color: var(--text-secondary);min-width: 3rem;text-align: right;padding-right: 1rem;user-select: none;flex-shrink: 0;}.line-text {white-space: pre;}.toggle-section {margin-top: 2rem;}.toggle-header {display: flex;align-items: center;justify-content: space-between;padding: 0.8rem 1rem;background: rgba(0, 0, 0, 0.2);border-radius: 8px;cursor: pointer;transition: all 0.3s ease;}.toggle-header:hover {background: rgba(0, 0, 0, 0.3);}.toggle-title {display: flex;align-items: center;gap: 0.8rem;font-size: 1rem;color: var(--text-secondary);}.toggle-icon {transition: transform 0.3s ease;}.toggle-content {max-height: 0;overflow: hidden;transition: max-height 0.4s ease;background: rgba(0, 0, 0, 0.1);border-radius: 0 0 8px 8px;}.toggle-section.active .toggle-icon {transform: rotate(180deg);}.toggle-section.active .toggle-content {max-height: 4000px;padding: 1rem;}.notice {--color: var(--notice);}.warning {--color: var(--warning);}.fatal {--color: var(--fatal);}.exception {--color: var(--exception);}.error-card.notice {box-shadow: 0 0 30px rgba(0, 229, 255, var(--glow-opacity)), 0 12px 40px rgba(0, 0, 0, 0.4);}.error-card.warning {box-shadow: 0 0 30px rgba(255, 165, 0, var(--glow-opacity)), 0 12px 40px rgba(0, 0, 0, 0.4);}.error-card.fatal {box-shadow: 0 0 30px rgba(255, 61, 61, var(--glow-opacity)), 0 12px 40px rgba(0, 0, 0, 0.4);}.error-card.exception {box-shadow: 0 0 30px rgba(186, 104, 200, var(--glow-opacity)), 0 12px 40px rgba(0, 0, 0, 0.4);}pre {white-space: pre-wrap;word-break: break-word;font-family: monospace;}@keyframes cardEntrance {from {opacity: 0;transform: translateY(30px) rotateX(-10deg);}to {opacity: 1;transform: translateY(0) rotateX(0);}}@media (max-width: 768px) {.error-details {grid-template-columns: 1fr;}.error-header {padding: 1.2rem 1.5rem;}.error-body {padding: 0 1.5rem 1.5rem;}.error-message {padding: 1rem;font-size: 1rem;}}
</style>
</head>
<body>
<div class="error-container">
HTML;
}

function renderPageFooter(): void
{
    echo <<<'HTML'
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.error-card').forEach(function(card){card.addEventListener('dblclick',function(){var errorText=getErrorText(card);if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(errorText).then(function(){var icon=card.querySelector('.error-icon');if(!icon)return;var original=icon.innerHTML;icon.innerHTML='&#10003;';setTimeout(function(){icon.innerHTML=original},2000)}).catch(function(){})}})});document.querySelectorAll('.toggle-header').forEach(function(h){h.addEventListener('click',function(){var section=h.parentElement;if(section)section.classList.toggle('active');})})});function getErrorText(card){var text='';function safe(sel){var el=card.querySelector(sel);return el?el.textContent.trim():''}
var title=safe('.error-title span:first-child');var type=safe('.error-type');text+=title+' - '+type+'\n\n';text+='Message: '+safe('.error-message')+'\n\n';card.querySelectorAll('.error-body > .error-details .detail-box').forEach(function(detail){var label=safe.call({querySelector:function(s){return detail.querySelector(s)}},'.detail-label');var value=safe.call({querySelector:function(s){return detail.querySelector(s)}},'.detail-value');text+=label+': '+value+'\n'});var stack=card.querySelector('.stack-content');if(stack)text+='\nStack Trace:\n'+stack.textContent+'\n';card.querySelectorAll('.toggle-section').forEach(function(section){var t=section.querySelector('.toggle-title');if(!t)return;text+='\n'+t.textContent.trim()+':\n';section.querySelectorAll('.detail-box').forEach(function(detail){var l=detail.querySelector('.detail-label');var v=detail.querySelector('.detail-value');if(l&&v)text+='  '+l.textContent.trim()+': '+v.textContent.trim()+'\n'})});return text}
</script>
</body>
</html>
HTML;
}

function renderDetailBoxes(array $details): void
{
    foreach ($details as $label => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $safeLabel = htmlspecialchars((string)$label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if (is_array($value) || is_object($value)) {
            $formatted = '<pre>' . htmlspecialchars(
                json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            ) . '</pre>';
        } else {
            $formatted = htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        echo <<<HTML
            <div class="detail-box">
                <span class="detail-label">$safeLabel</span>
                <span class="detail-value">$formatted</span>
            </div>
HTML;
    }
}

function renderToggleSection(string $title, array $details): void
{
    if (empty($details)) {
        return;
    }
    $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo <<<HTML
            <div class="toggle-section">
                <div class="toggle-header">
                    <div class="toggle-title">$safeTitle</div>
                    <div class="toggle-icon">&#9660;</div>
                </div>
                <div class="toggle-content">
                    <div class="error-details">
HTML;
    renderDetailBoxes($details);
    echo <<<HTML
                    </div>
                </div>
            </div>
HTML;
}

function renderError(int $errno, string $errstr, string $errfile, int $errline): void
{
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    $types = [
        E_NOTICE => 'Notice',
        E_WARNING => 'Warning',
        E_DEPRECATED => 'Deprecation',
        E_USER_NOTICE => 'User Notice',
        E_USER_WARNING => 'User Warning',
        E_USER_DEPRECATED => 'User Deprecation',
        E_USER_ERROR => 'User Error',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_STRICT => 'Strict Standards',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_PARSE => 'Parse Error',
        E_ERROR => 'Fatal Error'
    ];

    $errorType = $types[$errno] ?? 'Error';

    $class = match (true) {
        in_array($errno, [E_NOTICE, E_USER_NOTICE, E_STRICT, E_DEPRECATED, E_USER_DEPRECATED], true) => 'notice',
        in_array($errno, [E_WARNING, E_USER_WARNING, E_COMPILE_WARNING, E_CORE_WARNING], true) => 'warning',
        default => 'fatal'
    };

    $icon = match ($class) {
        'notice' => 'i',
        'warning' => '!',
        default => 'X'
    };

    $safeMessage = htmlspecialchars($errstr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeFile = htmlspecialchars($errfile, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeType = htmlspecialchars($errorType, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    renderPageHead("PHP $errorType - $safeMessage");

    $codeSnippet = getCodeSnippet($errfile, $errline, ERROR_HANDLER_CONFIG['snippet_lines']);
    $requestDetails = ERROR_HANDLER_CONFIG['show_request'] ? getRequestDetails() : [];
    $environmentDetails = ERROR_HANDLER_CONFIG['show_environment'] ? getEnvironmentDetails() : [];

    echo <<<HTML
    <div class="error-card $class">
        <div class="error-header">
            <div class="error-icon">$icon</div>
            <div class="error-title">
                <span>PHP $safeType</span>
                <span class="error-type">Code: $errno</span>
            </div>
        </div>
        <div class="error-body">
            <div class="error-message">$safeMessage</div>
            <div class="error-details">
                <div class="detail-box">
                    <span class="detail-label">File</span>
                    <span class="detail-value">$safeFile</span>
                </div>
                <div class="detail-box">
                    <span class="detail-label">Line</span>
                    <span class="detail-value">$errline</span>
                </div>
                <div class="detail-box">
                    <span class="detail-label">Time</span>
                    <span class="detail-value">{$environmentDetails['Current Time']}</span>
                </div>
            </div>
HTML;

    if ($codeSnippet) {
        echo <<<HTML
            <div class="code-snippet">
                <div class="code-snippet-title">Code Snippet (Line $errline)</div>
                <div class="code-content">$codeSnippet</div>
            </div>
HTML;
    }

    renderToggleSection('Request Details', $requestDetails);
    renderToggleSection('Environment Details', $environmentDetails);

    echo <<<HTML
        </div>
    </div>
HTML;

    renderPageFooter();
}

function renderFatalError(Throwable $e): void
{
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    $class = 'exception';
    $icon = 'E';
    $type = get_class($e);
    $safeType = htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeFile = htmlspecialchars($e->getFile(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeTrace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // Choose a better label for ErrorException thrown from PHP internal errors
    if ($e instanceof ErrorException) {
        $label = 'PHP Fatal Error';
    } elseif ($e instanceof Error) {
        $label = 'PHP Error';
    } else {
        $label = 'Uncaught Exception';
    }
    $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    renderPageHead("$safeType - $safeMessage");

    $codeSnippet = getCodeSnippet($e->getFile(), $e->getLine(), ERROR_HANDLER_CONFIG['snippet_lines']);
    $requestDetails = ERROR_HANDLER_CONFIG['show_request'] ? getRequestDetails() : [];
    $environmentDetails = ERROR_HANDLER_CONFIG['show_environment'] ? getEnvironmentDetails() : [];

    echo <<<HTML
    <div class="error-card $class">
        <div class="error-header">
            <div class="error-icon">$icon</div>
            <div class="error-title">
                <span>$safeType</span>
                <span class="error-type">$safeLabel</span>
            </div>
        </div>
        <div class="error-body">
            <div class="error-message">$safeMessage</div>
            <div class="error-details">
                <div class="detail-box">
                    <span class="detail-label">File</span>
                    <span class="detail-value">$safeFile</span>
                </div>
                <div class="detail-box">
                    <span class="detail-label">Line</span>
                    <span class="detail-value">{$e->getLine()}</span>
                </div>
                <div class="detail-box">
                    <span class="detail-label">Code</span>
                    <span class="detail-value">{$e->getCode()}</span>
                </div>
                <div class="detail-box">
                    <span class="detail-label">Time</span>
                    <span class="detail-value">{$environmentDetails['Current Time']}</span>
                </div>
            </div>
HTML;

    if ($codeSnippet) {
        echo <<<HTML
            <div class="code-snippet">
                <div class="code-snippet-title">Code Snippet (Line {$e->getLine()})</div>
                <div class="code-content">$codeSnippet</div>
            </div>
HTML;
    }

    if (ERROR_HANDLER_CONFIG['show_backtrace']) {
        echo <<<HTML
            <div class="error-stack">
                <div class="stack-title">Stack Trace</div>
                <div class="stack-content">$safeTrace</div>
            </div>
HTML;
    }

    renderToggleSection('Request Details', $requestDetails);
    renderToggleSection('Environment Details', $environmentDetails);

    echo <<<HTML
        </div>
    </div>
HTML;

    renderPageFooter();
}
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 0);
    error_reporting(E_ALL);
}

function auto__loader($class)
{
    require_once "../private/core/Helpers.php";
    $private = "../private/";

    $paths = [
        $private . "core/" . $class . ".php",
        $private . "models/" . $class . ".php",
    ];

    foreach ($paths as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
    
    
}

spl_autoload_register('auto__loader');
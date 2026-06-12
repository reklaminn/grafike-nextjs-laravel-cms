<?php

namespace App\Support;

/**
 * Captures PHP FATAL errors (especially OOM — "Allowed memory size exhausted")
 * that Laravel's own exception handler cannot log, because by the time the
 * fatal fires there is no memory left to format a stack trace.
 *
 * Strategy:
 *   - Reserve a 1 MB buffer up front.
 *   - On shutdown, free that buffer (→ headroom), then if the last error was a
 *     fatal-class error, append ONE line to storage/logs/fatal.log via the
 *     low-level error_log() (no Laravel logging stack, minimal allocation).
 *
 * The logged line answers the only questions that matter for an OOM:
 *   - WHICH url/request blew up,
 *   - HOW much memory it peaked at,
 *   - the fatal message + file:line.
 *
 * Read it with: `bash scripts/diag.sh fatal`
 */
class FatalLogger
{
    /** Reserved memory buffer, released inside the shutdown handler. */
    private static ?string $reserved = null;

    /** Register only once even if called from index.php AND a provider. */
    private static bool $registered = false;

    public static function register(string $logFile): void
    {
        // İlk kayıt kazanır. index.php (framework'ten ÖNCE) çağırırsa bizim
        // shutdown handler'ımız Laravel'inkinden önce çalışır ve OOM'u yakalar;
        // AppServiceProvider'daki ikinci çağrı no-op olur.
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        // Reserve headroom so the handler can still run after an OOM.
        self::$reserved = str_repeat('x', 1024 * 1024); // 1 MB

        register_shutdown_function(static function () use ($logFile): void {
            // Free the reserve first — gives the handler room to work.
            self::$reserved = null;
            // OOM sonrası bile yazabilmek için limiti geçici olarak yükselt.
            @ini_set('memory_limit', '512M');

            $e = error_get_last();
            if ($e === null) {
                return;
            }

            // Only fatal-class errors (skip warnings/notices which are routine).
            $fatalTypes = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING
                | E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR;
            if (($e['type'] & $fatalTypes) === 0) {
                return;
            }

            $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
            $host   = $_SERVER['HTTP_HOST'] ?? '';
            $uri    = $_SERVER['REQUEST_URI'] ?? ($GLOBALS['argv'][1] ?? '-');
            $peakMb = round(memory_get_peak_usage(true) / 1048576, 1);

            $line = sprintf(
                "[%s] FATAL %s %s%s | peak=%sMB/limit=%s | %s in %s:%d\n",
                date('Y-m-d H:i:s'),
                $method,
                $host,
                $uri,
                $peakMb,
                ini_get('memory_limit'),
                $e['message'],
                $e['file'],
                $e['line']
            );

            // Low-level append (3 = append to file). Never throws.
            @error_log($line, 3, $logFile);
        });
    }
}

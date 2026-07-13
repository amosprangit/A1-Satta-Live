<?php

/**
 * Cron Job: Update Game Results
 *
 * Purpose:
 * - Move today_result to yesterday_result
 * - Reset today_result to WAIT
 * - Reset is_latest flags
 * - Set Disawar as the latest/default game
 *
 * Intended execution:
 * - PHP CLI through Hostinger Cron Jobs
 * - Once every day at midnight
 */

date_default_timezone_set('Asia/Kolkata');

// ======================================================
// CONFIGURATION
// ======================================================

$log_file = __DIR__ . '/cron_log.txt';
$lock_file = __DIR__ . '/cron-update-results.lock';


// ======================================================
// LOGGING FUNCTION
// ======================================================

function log_message($message)
{
    global $log_file;

    $timestamp = date('Y-m-d H:i:s');

    $formattedMessage = "[$timestamp] $message" . PHP_EOL;

    // Write to log file
    $result = file_put_contents(
        $log_file,
        $formattedMessage,
        FILE_APPEND | LOCK_EX
    );

    // Also print to standard output for Hostinger "View output"
    echo $formattedMessage;

    // If logging fails, write to PHP error log
    if ($result === false) {
        error_log(
            "CRON ERROR: Could not write to log file: " . $log_file
        );
    }
}


// ======================================================
// FIRST LOG — BEFORE CONFIG.PHP
// ======================================================

log_message("");
log_message("==================================================");
log_message("===== CRON SCRIPT ENTERED =====");
log_message("Execution time: " . date('Y-m-d H:i:s'));
log_message("PHP version: " . PHP_VERSION);
log_message("PHP SAPI: " . PHP_SAPI);
log_message("Script directory: " . __DIR__);
log_message("Current working directory: " . getcwd());


// ======================================================
// PREVENT SIMULTANEOUS EXECUTIONS
// ======================================================

$lockHandle = fopen($lock_file, 'c');

if ($lockHandle === false) {

    log_message(
        "ERROR: Could not create or open lock file: " . $lock_file
    );

    exit(1);
}

if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {

    log_message(
        "WARNING: Another instance of this cron job is already running."
    );

    fclose($lockHandle);

    exit(0);
}

log_message("Execution lock acquired successfully.");


// ======================================================
// MAIN EXECUTION
// ======================================================

try {

    // ==================================================
    // LOAD DATABASE CONFIGURATION
    // ==================================================

    $configFile = __DIR__ . '/config.php';

    log_message("Attempting to load config file: " . $configFile);

    if (!file_exists($configFile)) {

        throw new RuntimeException(
            "config.php was not found at: " . $configFile
        );
    }

    require_once $configFile;

    log_message("config.php loaded successfully.");


    // ==================================================
    // VERIFY PDO CONNECTION
    // ==================================================

    if (!isset($pdo)) {

        throw new RuntimeException(
            "PDO variable \$pdo is not defined after loading config.php."
        );
    }

    if (!($pdo instanceof PDO)) {

        throw new RuntimeException(
            "\$pdo exists but is not a valid PDO instance."
        );
    }

    log_message("PDO database connection verified successfully.");


    // ==================================================
    // START CRON
    // ==================================================

    log_message("===== CRON JOB STARTED =====");


    // ==================================================
    // START DATABASE TRANSACTION
    // ==================================================

    $pdo->beginTransaction();

    log_message("Database transaction started.");


    // ==================================================
    // FETCH ACTIVE GAMES
    // ==================================================

    $stmt = $pdo->query("
        SELECT
            id,
            game_name,
            today_result
        FROM game_results
        WHERE status = 1
        ORDER BY id ASC
    ");

    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_games = count($games);
    $updated_count = 0;
    $skipped_count = 0;

    log_message(
        "Found $total_games active game(s) to process."
    );


    // ==================================================
    // PREPARE UPDATE QUERY ONCE
    // ==================================================

    $update_stmt = $pdo->prepare("
        UPDATE game_results
        SET
            yesterday_result = ?,
            today_result = 'WAIT',
            is_latest = 0
        WHERE id = ?
    ");


    // ==================================================
    // PROCESS EACH GAME
    // ==================================================

    foreach ($games as $game) {

        $today_result = $game['today_result'];
        $game_name = $game['game_name'];
        $game_id = $game['id'];

        log_message(
            "Processing game: $game_name " .
            "(ID: $game_id) - " .
            "Today result: '$today_result'"
        );


        // Normalize result for reliable comparison
        $normalized_result = is_string($today_result)
            ? trim($today_result)
            : $today_result;


        // Check whether result should be moved
        $hasValidResult = (
            $normalized_result !== null &&
            $normalized_result !== '' &&
            strtoupper((string) $normalized_result) !== 'WAIT' &&
            (string) $normalized_result !== '-1' &&
            (string) $normalized_result !== '--'
        );


        if ($hasValidResult) {

            $update_stmt->execute([
                $today_result,
                $game_id
            ]);


            if ($update_stmt->rowCount() > 0) {

                $updated_count++;

                log_message(
                    "SUCCESS: Updated game '$game_name'. " .
                    "Moved '$today_result' to yesterday_result " .
                    "and set today_result to WAIT."
                );

            } else {

                log_message(
                    "WARNING: No row was changed for '$game_name' " .
                    "(ID: $game_id)."
                );
            }

        } else {

            $skipped_count++;

            log_message(
                "SKIPPED: '$game_name' because today_result " .
                "is WAIT, empty, -1, or --."
            );
        }
    }


    // ==================================================
    // RESET ALL is_latest FLAGS
    // ==================================================

    $resetStmt = $pdo->prepare("
        UPDATE game_results
        SET is_latest = 0
        WHERE status = 1
    ");

    $resetStmt->execute();

    log_message(
        "Reset is_latest = 0 for active games. " .
        "Affected rows: " . $resetStmt->rowCount()
    );


    // ==================================================
    // SET DISAWAR AS LATEST
    // ==================================================

    $latestStmt = $pdo->prepare("
        UPDATE game_results
        SET is_latest = 1
        WHERE LOWER(TRIM(game_name)) = 'disawar'
        AND status = 1
    ");

    $latestStmt->execute();


    if ($latestStmt->rowCount() > 0) {

        log_message(
            "SUCCESS: Set is_latest = 1 for Disawar."
        );

    } else {

        log_message(
            "WARNING: Active Disawar game not found. " .
            "Attempting to use the first active game."
        );


        // Find first active game
        $firstStmt = $pdo->query("
            SELECT id, game_name
            FROM game_results
            WHERE status = 1
            ORDER BY id ASC
            LIMIT 1
        ");

        $first = $firstStmt->fetch(PDO::FETCH_ASSOC);


        if ($first) {

            $fallbackStmt = $pdo->prepare("
                UPDATE game_results
                SET is_latest = 1
                WHERE id = ?
            ");

            $fallbackStmt->execute([
                $first['id']
            ]);

            log_message(
                "SUCCESS: Set is_latest = 1 for fallback game: " .
                $first['game_name'] .
                " (ID: " . $first['id'] . ")."
            );

        } else {

            log_message(
                "WARNING: No active games were found. " .
                "Could not set any game as latest."
            );
        }
    }


    // ==================================================
    // COMMIT TRANSACTION
    // ==================================================

    $pdo->commit();

    log_message("Database transaction committed successfully.");


    // ==================================================
    // SUCCESS SUMMARY
    // ==================================================

    log_message("===== CRON JOB COMPLETED SUCCESSFULLY =====");

    log_message(
        "SUMMARY: Total = $total_games, " .
        "Updated = $updated_count, " .
        "Skipped = $skipped_count"
    );

    log_message("==================================================");
    log_message("");


} catch (Throwable $e) {

    // ==================================================
    // ROLLBACK IF TRANSACTION IS ACTIVE
    // ==================================================

    if (
        isset($pdo) &&
        $pdo instanceof PDO &&
        $pdo->inTransaction()
    ) {

        try {

            $pdo->rollBack();

            log_message(
                "Database transaction rolled back due to error."
            );

        } catch (Throwable $rollbackError) {

            log_message(
                "ROLLBACK ERROR: " .
                $rollbackError->getMessage()
            );
        }
    }


    // ==================================================
    // DETAILED ERROR LOG
    // ==================================================

    log_message("===== CRON JOB FAILED =====");

    log_message(
        "ERROR TYPE: " . get_class($e)
    );

    log_message(
        "ERROR MESSAGE: " . $e->getMessage()
    );

    log_message(
        "ERROR FILE: " . $e->getFile()
    );

    log_message(
        "ERROR LINE: " . $e->getLine()
    );

    log_message("==================================================");
    log_message("");


    // Release execution lock
    if (isset($lockHandle) && is_resource($lockHandle)) {

        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }

    exit(1);
}


// ======================================================
// RELEASE EXECUTION LOCK
// ======================================================

if (isset($lockHandle) && is_resource($lockHandle)) {

    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

exit(0);
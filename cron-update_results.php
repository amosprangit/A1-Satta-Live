<?
date_default_timezone_set('Asia/Kolkata');
require_once 'config.php';

$log_file = __DIR__ . '/cron_log.txt';

function log_message($message)
{
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    log_message("===== CRON JOB STARTED =====");

    // Get all games - ONLY existing rows with status = 1 (active)
    $stmt = $pdo->query("SELECT * FROM game_results WHERE status = 1");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updated_count = 0;
    $skipped_count = 0;

    foreach ($games as $game) {
        $today_result = $game['today_result'];
        $game_name = $game['game_name'];
        $game_id = $game['id'];

        log_message("Processing game: $game_name (ID: $game_id) - Today result: '$today_result'");

        // Check if today's result is not WAIT or empty
        if ($today_result !== 'WAIT' && !empty($today_result) && $today_result !== '-1' && $today_result !== '--') {
            // UPDATE existing row - NEVER INSERT
            $update_stmt = $pdo->prepare("UPDATE game_results SET 
                yesterday_result = ?, 
                today_result = 'WAIT',
                is_latest = 0
                WHERE id = ?");
            $update_stmt->execute([$today_result, $game_id]);

            if ($update_stmt->rowCount() > 0) {
                $updated_count++;
                log_message("✅ Updated game: $game_name - Moved '$today_result' to yesterday_result, set today to WAIT");
            } else {
                log_message("⚠️ No rows updated for game: $game_name (ID: $game_id)");
            }
        } else {
            $skipped_count++;
            log_message("⏭️ Skipped game: $game_name - Today result is already WAIT or empty");
        }
    }

    // Set is_latest = 1 for games that have today_result != WAIT
    $stmt = $pdo->query("UPDATE game_results SET is_latest = 1 WHERE today_result != 'WAIT' AND today_result != '-1' AND today_result != '' AND today_result IS NOT NULL AND status = 1");
    $latest_count = $stmt->rowCount();
    log_message("Set is_latest = 1 for $latest_count games with results");

    log_message("===== CRON JOB COMPLETED =====");
    log_message("Updated: $updated_count games, Skipped: $skipped_count games, Latest set: $latest_count games");
    log_message("");

} catch (PDOException $e) {
    log_message("❌ ERROR: " . $e->getMessage());
} catch (Exception $e) {
    log_message("❌ ERROR: " . $e->getMessage());
}
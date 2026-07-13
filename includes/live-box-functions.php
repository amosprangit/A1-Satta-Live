<?php
// includes/live-box-functions.php

function getLatestGame($pdo, $defaultDisawer)
{
    try {
        // FIRST: Check game_results for is_latest = 1
        $stmt = $pdo->query("SELECT SQL_NO_CACHE * FROM game_results WHERE status = 1 AND is_latest = 1 ORDER BY id DESC LIMIT 1");
        $latest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($latest) {
            return $latest;
        }

        // SECOND: If no latest game in game_results, get most recently updated custom table game
        $stmt = $pdo->query("
            SELECT ctg.*, ct.table_name as custom_table_name 
            FROM custom_table_games ctg
            INNER JOIN custom_tables ct ON ctg.table_id = ct.id
            WHERE ct.status = 1
            ORDER BY ctg.id DESC 
            LIMIT 1
        ");
        $customGame = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customGame) {
            $customGame['is_custom'] = true;
            $customGame['source'] = 'custom_table_games';
            return $customGame;
        }

        // FINAL: Fallback to Disawar
        return $defaultDisawer;

    } catch (PDOException $e) {
        error_log("Error in getLatestGame: " . $e->getMessage());
        return $defaultDisawer;
    }
}

function renderLiveBox($game)
{
    $gameName = !empty($game['display_name']) ? $game['display_name'] : strtoupper($game['game_name']);
    $result = $game['today_result'] ?? 'WAIT';
    ?>
    <div class="live-box">
        <div id="clock" class="clock"></div>
        <h2>हा भाई यही आती हे सबसे पहले खबर रूको और देखो</h2>
        <h1>
            <?php echo htmlspecialchars(strtoupper($gameName)); ?>
        </h1>
        <div class="result-number">
            <?php
            if ($result == 'WAIT' || $result == '-1' || empty($result)) {
                echo '<span style="color: #d32f2f; font-size: 24px;">⏳ WAIT</span>';
            } else {
                echo htmlspecialchars($result);
            }
            ?>
        </div>
    </div>
    <?php
}
?>
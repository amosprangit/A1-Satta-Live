<?php
// includes/disawar-functions.php

function getDisawarResult($pdo)
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = 'disawar' AND status = 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

function renderDisawarBox($disawer)
{
    $result = $disawer['today_result'] ?? '86';
    $yesterday = $disawer['yesterday_result'] ?? '05';
    $displayName = $disawer['display_name'] ?? 'DISAWAR';
    $time = $disawer['result_time'] ?? '5:15 AM';
    ?>
    <div class="highlight">
        <?php echo htmlspecialchars(strtolower($displayName)); ?>
    </div>
    <div class="disawer-timing">
        <?php echo htmlspecialchars($time); ?>
    </div>
    <div class="disawer-arrow">
        <?php echo htmlspecialchars($yesterday); ?> ➡️
        <?php
        if ($result == 'WAIT' || $result == '-1' || empty($result)) {
            echo 'WAIT';
        } else {
            echo htmlspecialchars($result);
        }
        ?>
    </div>
    <?php
}
?>
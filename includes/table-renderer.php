<?php
// includes/table-renderer.php
function renderGameTable($games, $allResults, $tableHeader = 'सट्टा का नाम')
{
    if (empty($games)) {
        return '';
    }
    ?>
    <div class="table-wrapper">
        <table class="result-table">
            <thead>
                <tr>
                    <th><?php echo $tableHeader; ?></th>
                    <th>कल आया था</th>
                    <th>आज का रिज़ल्ट</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game):
                    $key = strtolower($game);
                    $data = isset($allResults[$key]) ? $allResults[$key] : [
                        'yesterday_result' => '--',
                        'today_result' => 'WAIT',
                        'result_time' => '--',
                        'display_name' => $game
                    ];
                    $displayName = !empty($data['display_name']) ? $data['display_name'] : strtoupper($game);
                    $isWait = ($data['today_result'] == 'WAIT' || $data['today_result'] == '-1' || empty($data['today_result']));
                    $gameSlug = strtolower(str_replace(' ', '-', $game));
                    ?>
                    <tr>
                        <td class="game-name">
                            <a href="game.php?game=<?php echo urlencode($gameSlug); ?>">
                                <?php echo strtoupper(htmlspecialchars($displayName)); ?>
                            </a>
                            <span class="game-time"><?php echo htmlspecialchars($data['result_time'] ?? '--'); ?></span>
                        </td>
                        <td class="yesterday-result"><?php echo htmlspecialchars($data['yesterday_result'] ?? '--'); ?></td>
                        <td class="today-result">
                            <?php if ($isWait): ?>
                                <span class="wait-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28"
                                        fill="#d32f2f">
                                        <path
                                            d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M12,20c-4.41,0-8-3.59-8-8s3.59-8,8-8s8,3.59,8,8 S16.41,20,12,20z" />
                                        <path
                                            d="M12,6c-0.55,0-1,0.45-1,1v5c0,0.55,0.45,1,1,1h4c0.55,0,1-0.45,1-1s-0.45-1-1-1h-3V7C13,6.45,12.55,6,12,6z" />
                                    </svg>
                                    WAIT
                                </span>
                            <?php else: ?>
                                <span class="result-value"><?php echo htmlspecialchars($data['today_result']); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($games)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 20px; color: #999;">No games available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function renderAllTables($table1Games, $table2Games, $allResults, $tableHeader = 'सट्टा का नाम')
{
    $output = '';

    // Render Table 1
    if (!empty($table1Games)) {
        ob_start();
        renderGameTable($table1Games, $allResults, $tableHeader);
        $output .= ob_get_clean();
    }

    // Render Table 2
    if (!empty($table2Games)) {
        ob_start();
        renderGameTable($table2Games, $allResults, $tableHeader);
        $output .= ob_get_clean();
    }

    return $output;
}
?>
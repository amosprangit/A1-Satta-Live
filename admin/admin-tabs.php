<?php
// /admin/includes/admin-tabs.php

// ============================================
// RENDER GAMES TAB - FIXED (Removed duplicate)
// ============================================
function renderGamesTab($pdo, $all_games, $table1_games, $table2_games, $all_games_list = null)
{
    // If $all_games_list is not provided, fetch it
    if ($all_games_list === null) {
        $all_games_list = getAllGameNamesForAdmin($pdo);
    }

    // Debug: Check if we have games
    if (empty($all_games_list)) {
        echo '<!-- Debug: No games found in getAllGameNamesForAdmin() -->';
    } else {
        echo '<!-- Debug: Found ' . count($all_games_list) . ' games in dropdown -->';
    }

    // Merge all games into one list for the table
    $all_game_names = array_merge($table1_games, $table2_games);
    ?>
    <!-- Edit Game Result -->
    <div class="admin-section" style="border-left-color: #28a745;">
        <h2>✏️ Edit Game Result (Any Game)</h2>
        <form method="POST" class="admin-form" id="editGameForm">
            <input type="hidden" name="update_result" value="1">
            <div class="form-group">
                <label>Select Game:</label>
                <select name="game_name" id="selectGameName" required onchange="loadGameData(this.value)">
                    <option value="">-- Select Game --</option>
                    <?php
                    // Loop through all games from both tables
                    foreach ($all_games_list as $game):
                        $is_custom = ($game['source'] !== 'Main Table');
                        ?>
                        <option value="<?php echo htmlspecialchars($game['game_name']); ?>"
                            data-source="<?php echo htmlspecialchars($game['source']); ?>">
                            <?php echo htmlspecialchars(strtoupper($game['display_name'])); ?>
                            <?php if ($is_custom): ?>
                                (📋 <?php echo htmlspecialchars($game['source']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>

                    <?php if (empty($all_games_list)): ?>
                        <option value="" disabled>No games found</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Display Name:</label>
                <input type="text" name="display_name" id="editDisplayName" required placeholder="e.g. DELHI BAZAR">
            </div>
            <div class="form-group">
                <label>Result Time:</label>
                <input type="text" name="result_time" id="editResultTime" placeholder="e.g. 5:15 PM">
            </div>
            <div class="form-group">
                <label>Yesterday Result:</label>
                <input type="text" name="yesterday_result" id="editYesterdayResult" placeholder="--">
                <small style="color: #888; font-size: 11px;">Leave empty to auto-move current today to yesterday</small>
            </div>
            <div class="form-group">
                <label>Today Result:</label>
                <input type="text" name="today_result" id="editTodayResult" placeholder="Enter new result number">
                <small style="color: #28a745; font-size: 12px; display: block; margin-top: 5px;">
                    💡 This will update the selected game's result. Old today result moves to yesterday.
                </small>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">💾 Update Result</button>
                <button type="button" class="btn-secondary" onclick="clearForm()">🔄 Clear</button>
            </div>
        </form>
        <div id="gameInfoDisplay"
            style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; display: none;">
            <p style="margin: 0; font-size: 14px; color: #333;">
                <strong>Current Status:</strong>
                <span id="currentStatus">Select a game to view details</span>
            </p>
        </div>
    </div>

    <!-- Add New Game -->
    <div class="admin-section">
        <h2>➕ Add New Game</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="add_game" value="1">
            <div class="form-group">
                <label>Game Name (slug):</label>
                <input type="text" name="new_game_name" placeholder="e.g. new-game" required>
            </div>
            <div class="form-group">
                <label>Display Name:</label>
                <input type="text" name="new_display_name" placeholder="सट्टा का नाम">
            </div>
            <div class="form-group">
                <label>Yesterday:</label>
                <input type="text" name="new_yesterday_result" placeholder="--">
            </div>
            <div class="form-group">
                <label>Today:</label>
                <input type="text" name="new_today_result" placeholder="WAIT">
            </div>
            <div class="form-group">
                <label>Time:</label>
                <input type="text" name="new_result_time" placeholder="5:15 PM">
            </div>
            <div class="form-group">
                <label>Table Type (Frontend Display):</label>
                <select name="new_table_type">
                    <option value="table1">Main Table</option>
                    <option value="table2">Secondary Table</option>
                </select>
                <small style="color: #888; font-size: 11px; display: block;">This determines which table the game appears in
                    on the frontend</small>
            </div>
            <button type="submit" class="btn-success">➕ Add Game</button>
        </form>
    </div>

    <!-- Manage All Games - Single Table -->
    <div class="admin-section">
        <h2>📊 Manage All Games</h2>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Game Name</th>
                        <th>Display Name</th>
                        <th>Yesterday</th>
                        <th>Today</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Sort games to maintain order: first table1, then table2
                    $sorted_games = array_merge($table1_games, $table2_games);
                    if (empty($sorted_games)) {
                        echo '<tr><td colspan="7" style="padding: 30px; text-align: center; color: #999;">No games found. Add your first game above!</td></tr>';
                    } else {
                        foreach ($sorted_games as $game):
                            $data = isset($all_games[$game]) ? $all_games[$game] : [
                                'yesterday_result' => '--',
                                'today_result' => 'WAIT',
                                'result_time' => '--',
                                'display_name' => $game,
                                'status' => 1,
                                'table_type' => 'table1'
                            ];
                            $status_text = ($data['status'] == 1) ? '✅ Active' : '❌ Inactive';
                            $status_color = ($data['status'] == 1) ? '#28a745' : '#dc3545';
                            ?>
                            <tr>
                                <td class="game-name-cell"><?php echo ucfirst($game); ?></td>
                                <td><?php echo $data['display_name'] ?: ucfirst($game); ?></td>
                                <td><?php echo $data['yesterday_result']; ?></td>
                                <td><?php echo $data['today_result']; ?></td>
                                <td><?php echo $data['result_time']; ?></td>
                                <td><span style="color: <?php echo $status_color; ?>;"><?php echo $status_text; ?></span></td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="btn-edit"
                                            onclick="openEditModal('<?php echo $game; ?>', '<?php echo $data['yesterday_result']; ?>', '<?php echo $data['today_result']; ?>', '<?php echo $data['result_time']; ?>', '<?php echo $data['display_name']; ?>')">✏️
                                            Edit</button>
                                        <a href="?toggle_game=<?php echo urlencode($game); ?>" class="btn-toggle">🔄 Toggle</a>
                                        <a href="?delete_game=<?php echo urlencode($game); ?>" class="btn-delete"
                                            onclick="return confirm('Delete game \'<?php echo ucfirst($game); ?>\'?')">🗑️
                                            Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach;
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// ============================================
// RENDER CHART TAB - FIXED (Added getAllGameNamesForAdmin)
// ============================================
function renderChartTab($pdo, $edit_game, $edit_date, $edit_result, $chart_data_display)
{
    // FIXED: Use getAllGameNamesForAdmin instead of getGameNamesList
    $all_games_list = getAllGameNamesForAdmin($pdo);
    ?>
    <?php if ($edit_game && $edit_date): ?>
        <div class="admin-section" style="border-left-color: #17a2b8; background: #f0f8ff;">
            <h2>✏️ Edit Chart Data</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="update_chart" value="1">
                <input type="hidden" name="chart_game" value="<?php echo $edit_game; ?>">
                <input type="hidden" name="chart_date" value="<?php echo $edit_date; ?>">
                <div class="form-group"><label>Game:</label><input type="text" value="<?php echo ucfirst($edit_game); ?>"
                        disabled></div>
                <div class="form-group"><label>Date:</label><input type="text" value="<?php echo $edit_date; ?>" disabled></div>
                <div class="form-group"><label>Result Number:</label><input type="text" name="chart_result"
                        value="<?php echo $edit_result; ?>" required></div>
                <button type="submit" class="btn-info">💾 Update Chart</button>
                <a href="admin-dashboard.php?tab=chart" class="btn-danger">❌ Cancel</a>
            </form>
        </div>
    <?php endif; ?>

    <div class="admin-section">
        <h2>➕ Add New Chart Data</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="update_chart" value="1">
            <div class="form-group"><label>Game Name:</label>
                <select name="chart_game" required>
                    <option value="">-- Select Game --</option>
                    <?php foreach ($all_games_list as $game): ?>
                        <option value="<?php echo htmlspecialchars($game['game_name']); ?>">
                            <?php echo htmlspecialchars(ucfirst($game['display_name'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Date (YYYY-MM-DD):</label><input type="date" name="chart_date" required></div>
            <div class="form-group"><label>Result Number:</label><input type="text" name="chart_result"
                    placeholder="Enter result number" required></div>
            <button type="submit" class="btn-success">💾 Save Chart Data</button>
        </form>
    </div>

    <div class="admin-section" style="border-left-color: #28a745;">
        <h2>📅 Generate Full Month Chart Data</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="generate_month_chart" value="1">
            <div class="form-group"><label>Game Name:</label>
                <select name="gen_chart_game" required>
                    <option value="">-- Select Game --</option>
                    <?php foreach ($all_games_list as $game): ?>
                        <option value="<?php echo htmlspecialchars($game['game_name']); ?>">
                            <?php echo htmlspecialchars(ucfirst($game['display_name'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Month:</label>
                <select name="gen_chart_month" required>
                    <option value="01">January</option>
                    <option value="02">February</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06" selected>June</option>
                    <option value="07">July</option>
                    <option value="08">August</option>
                    <option value="09">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
            </div>
            <div class="form-group"><label>Year:</label>
                <select name="gen_chart_year" required>
                    <option value="2026" selected>2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                </select>
            </div>
            <button type="submit" class="btn-info">📊 Generate Full Month</button>
        </form>
    </div>

    <div class="admin-section">
        <h2>📋 All Chart Data</h2>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Game Name</th>
                        <th>Date</th>
                        <th>Result</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($chart_data_display)): ?>
                        <tr>
                            <td colspan="5" style="padding:30px; text-align:center; color:#999;">No chart data found.</td>
                        </tr>
                    <?php else:
                        foreach ($chart_data_display as $row): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><strong><?php echo ucfirst($row['game_name']); ?></strong></td>
                                <td><?php echo $row['chart_date']; ?></td>
                                <td style="font-weight:bold; color:#c49a00; font-size:18px;"><?php echo $row['result'] ?: '--'; ?>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="admin-dashboard.php?tab=chart&edit_game=<?php echo urlencode($row['game_name']); ?>&edit_date=<?php echo urlencode($row['chart_date']); ?>&edit_result=<?php echo urlencode($row['result']); ?>"
                                            class="btn-edit">✏️ Edit</a>
                                        <a href="?delete_chart=<?php echo urlencode($row['game_name']); ?>&chart_date=<?php echo urlencode($row['chart_date']); ?>"
                                            class="btn-delete" onclick="return confirm('Delete this entry?')">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// ============================================
// RENDER TIMINGS TAB
// ============================================
function renderTimingsTab($pdo, $game_timings)
{
    ?>
    <div class="admin-section">
        <h2>⏰ Manage Game Timings</h2>
        <form method="POST" class="admin-form"
            style="margin-bottom:20px; padding:20px; background:#f9f9f9; border-radius:15px;">
            <input type="hidden" name="add_timing" value="1">
            <h3 style="width:100%; margin-bottom:15px; font-size:16px;">Add New Timing</h3>
            <div class="form-group"><label>Game Name:</label><input type="text" name="timing_game_name"
                    placeholder="e.g. disawer" required></div>
            <div class="form-group"><label>Time:</label><input type="text" name="timing_time" placeholder="e.g. 5:15 AM"
                    required></div>
            <!-- <div class="form-group"><label>Emoji:</label><input type="text" name="timing_emoji" placeholder="😇" value="😇">
            </div> -->
            <button type="submit" class="btn-success">➕ Add Timing</button>
        </form>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Game Name</th>
                        <th>Timing</th>
                        <!-- <th>Emoji</th> -->
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($game_timings)): ?>
                        <tr>
                            <td colspan="6" style="padding:30px; color:#999;">No timings added yet.</td>
                        </tr>
                    <?php else:
                        foreach ($game_timings as $timing): ?>
                            <tr>
                                <td><?php echo $timing['id']; ?></td>
                                <td><strong><?php echo ucfirst($timing['game_name']); ?></strong></td>
                                <td><?php echo $timing['timing']; ?></td>
                                <!-- <td style="font-size:24px;"><?php echo $timing; ?></td> -->
                                <td><span
                                        style="color: <?php echo $timing['is_active'] ? '#28a745' : '#dc3545'; ?>;"><?php echo $timing['is_active'] ? '✅ Active' : '❌ Inactive'; ?></span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="btn-edit"
                                            onclick="openTimingEditModal(<?php echo $timing['id']; ?>, '<?php echo $timing['game_name']; ?>', '<?php echo $timing['timing']; ?>', '<?php echo $timing; ?>', <?php echo $timing['is_active']; ?>)">✏️
                                            Edit</button>
                                        <a href="?toggle_timing=<?php echo $timing['id']; ?>"
                                            class="btn-toggle <?php echo $timing['is_active'] ? 'active' : ''; ?>">🔄 Toggle</a>
                                        <a href="?delete_timing=<?php echo $timing['id']; ?>" class="btn-delete"
                                            onclick="return confirm('Delete this timing?')">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// ============================================
// RENDER RATES TAB
// ============================================
function renderRatesTab($pdo, $game_rates)
{
    ?>
    <div class="admin-section">
        <h2>💰 Manage Game Rates</h2>
        <form method="POST" class="admin-form"
            style="margin-bottom:20px; padding:20px; background:#f9f9f9; border-radius:15px;">
            <input type="hidden" name="add_rate" value="1">
            <h3 style="width:100%; margin-bottom:15px; font-size:16px;">Add New Rate</h3>
            <div class="form-group"><label>Rate Type:</label><input type="text" name="rate_type" placeholder="e.g. जोड़ी रेट"
                    required></div>
            <div class="form-group"><label>Rate Value:</label><input type="text" name="rate_value"
                    placeholder="e.g. 10 ke..960" required></div>
            <div class="form-group"><label>Display Order:</label><input type="number" name="rate_display_order"
                    placeholder="0" value="0"></div>
            <button type="submit" class="btn-success">➕ Add Rate</button>
        </form>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rate Type</th>
                        <th>Rate Value</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($game_rates)): ?>
                        <tr>
                            <td colspan="6" style="padding:30px; color:#999;">No rates added yet.</td>
                        </tr>
                    <?php else:
                        foreach ($game_rates as $rate): ?>
                            <tr>
                                <td><?php echo $rate['id']; ?></td>
                                <td><strong><?php echo $rate['rate_type']; ?></strong></td>
                                <td><?php echo $rate['rate_value']; ?></td>
                                <td><?php echo $rate['display_order']; ?></td>
                                <td><span
                                        style="color: <?php echo $rate['is_active'] ? '#28a745' : '#dc3545'; ?>;"><?php echo $rate['is_active'] ? '✅ Active' : '❌ Inactive'; ?></span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="btn-edit"
                                            onclick="openRateEditModal(<?php echo $rate['id']; ?>, '<?php echo $rate['rate_type']; ?>', '<?php echo $rate['rate_value']; ?>', <?php echo $rate['is_active']; ?>)">✏️
                                            Edit</button>
                                        <a href="?toggle_rate=<?php echo $rate['id']; ?>"
                                            class="btn-toggle <?php echo $rate['is_active'] ? 'active' : ''; ?>">🔄 Toggle</a>
                                        <a href="?delete_rate=<?php echo $rate['id']; ?>" class="btn-delete"
                                            onclick="return confirm('Delete this rate?')">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// ============================================
// RENDER TABLES TAB (CUSTOM TABLES)
// ============================================
function renderTablesTab($pdo, $custom_tables)
{
    ?>
    <!-- Create New Table -->
    <div class="admin-section" style="border-left-color: #28a745;">
        <h2>📋 Create New Table</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="add_custom_table" value="1">
            <div class="form-group">
                <label>Table Name:</label>
                <input type="text" name="table_name" placeholder="e.g. Special Results" required>
            </div>
            <div class="form-group">
                <label>Description (Optional):</label>
                <input type="text" name="table_description" placeholder="e.g. Monthly Special Results">
            </div>
            <button type="submit" class="btn-success">➕ Create Table</button>
        </form>
    </div>

    <!-- Manage Existing Tables -->
    <?php if (!empty($custom_tables)): ?>
        <?php foreach ($custom_tables as $table): ?>
            <div class="admin-section" style="border-left-color: #17a2b8; background: #f8f9fa;">
                <h2>
                    📋 <?php echo htmlspecialchars($table['table_name']); ?>
                    <?php if (!empty($table['table_description'])): ?>
                        <span style="font-size: 14px; color: #666; font-weight: normal;">-
                            <?php echo htmlspecialchars($table['table_description']); ?></span>
                    <?php endif; ?>
                    <span style="font-size: 12px; color: #999; font-weight: normal;">(Created:
                        <?php echo date('d M Y', strtotime($table['created_at'])); ?>)</span>
                </h2>

                <!-- Add Game to Table -->
                <form method="POST" class="admin-form"
                    style="margin-bottom: 20px; padding: 15px; background: #fff; border-radius: 10px; border: 1px solid #e0e0e0;">
                    <input type="hidden" name="add_table_game" value="1">
                    <input type="hidden" name="table_id" value="<?php echo $table['id']; ?>">
                    <h3 style="width:100%; margin-bottom:10px; font-size:14px; color:#333;">➕ Add Game to This Table</h3>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
                        <div style="flex: 1; min-width: 120px;">
                            <label style="font-size:12px;">Game Name:</label>
                            <input type="text" name="game_name" placeholder="e.g. special-game" required>
                        </div>
                        <div style="flex: 1; min-width: 120px;">
                            <label style="font-size:12px;">Display Name:</label>
                            <input type="text" name="display_name" placeholder="Special Game">
                        </div>
                        <div style="flex: 0 0 80px;">
                            <label style="font-size:12px;">Yesterday:</label>
                            <input type="text" name="yesterday_result" placeholder="--">
                        </div>
                        <div style="flex: 0 0 80px;">
                            <label style="font-size:12px;">Today:</label>
                            <input type="text" name="today_result" placeholder="WAIT">
                        </div>
                        <div style="flex: 0 0 100px;">
                            <label style="font-size:12px;">Time:</label>
                            <input type="text" name="result_time" placeholder="5:15 PM">
                        </div>
                        <button type="submit" class="btn-success" style="padding: 8px 20px;">➕ Add Game</button>
                    </div>
                </form>

                <!-- Table Games List -->
                <?php
                $games = getCustomTableGamesList($pdo, $table['id']);
                ?>

                <?php if (!empty($games)): ?>
                    <div style="overflow-x: auto;">
                        <table class="admin-table" style="min-width: 700px;">
                            <thead>
                                <tr>
                                    <th>Game Name</th>
                                    <th>Display Name</th>
                                    <th>Yesterday</th>
                                    <th>Today</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($games as $game): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($game['game_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($game['display_name']); ?></td>
                                        <td><?php echo htmlspecialchars($game['yesterday_result']); ?></td>
                                        <td
                                            style="font-weight:bold; color: <?php echo ($game['today_result'] == 'WAIT' || $game['today_result'] == '-1' || empty($game['today_result'])) ? '#d32f2f' : '#28a745'; ?>;">
                                            <?php echo htmlspecialchars($game['today_result']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($game['result_time']); ?></td>
                                        <td>
                                            <div class="actions-cell">
                                                <button class="btn-edit" onclick="openTableGameEditModal(
                                                    <?php echo $game['id']; ?>,
                                                    <?php echo $table['id']; ?>,
                                                    '<?php echo htmlspecialchars($game['game_name']); ?>',
                                                    '<?php echo htmlspecialchars($game['display_name']); ?>',
                                                    '<?php echo htmlspecialchars($game['yesterday_result']); ?>',
                                                    '<?php echo htmlspecialchars($game['today_result']); ?>',
                                                    '<?php echo htmlspecialchars($game['result_time']); ?>'
                                                )">✏️ Edit</button>
                                                <a href="?delete_table_game=<?php echo $game['id']; ?>&table_id=<?php echo $table['id']; ?>"
                                                    class="btn-delete" onclick="return confirm('Delete this game?')">🗑️ Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 20px;">No games added to this table yet.</p>
                <?php endif; ?>

                <!-- Table Actions -->
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <button class="btn-primary"
                        onclick="openEditTableModal(<?php echo $table['id']; ?>, '<?php echo htmlspecialchars($table['table_name']); ?>', '<?php echo htmlspecialchars($table['table_description']); ?>')">
                        ✏️ Edit Table
                    </button>
                    <a href="?delete_custom_table=<?php echo $table['id']; ?>" class="btn-delete"
                        onclick="return confirm('Delete this table and all its games?')">🗑️ Delete Table</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="admin-section">
            <p style="text-align: center; color: #999; padding: 40px;">No custom tables created yet. Create your first table
                above!</p>
        </div>
    <?php endif; ?>
<?php
}

// ============================================
// RENDER SETTINGS TAB
// ============================================
function renderSettingsTab($pdo)
{
    ?>
    <!-- Featured Game -->
    <div class="admin-section">
        <h2>⭐ Featured Game for Live Box</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="update_featured_game" value="1">
            <div class="form-group"><label>Select Game to Show in Live Box:</label>
                <select name="featured_game" required>
                    <option value="disawar" <?php echo (getWebsiteContentValue($pdo, 'featured_game') == 'disawar') ? 'selected' : ''; ?>>DISAWAR</option>
                    <?php
                    $stmt = $pdo->query("SELECT game_name, display_name FROM game_results WHERE status = 1 ORDER BY game_name");
                    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($games as $game):
                        $display_name = !empty($game['display_name']) ? $game['display_name'] : strtoupper($game['game_name']);
                        $selected = (getWebsiteContentValue($pdo, 'featured_game') == $game['game_name']) ? 'selected' : '';
                        ?>
                        <option value="<?php echo htmlspecialchars($game['game_name']); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <small style="color:#555; display:block; margin:10px 0;">💡 The selected game will appear in the Live Box on the
                homepage.</small>
            <button type="submit" class="btn-primary">💾 Update Featured Game</button>
        </form>
    </div>

    <!-- Khaiwal Info -->
    <div class="admin-section">
        <h2>🔰 Edit Name Info</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="update_khaiwal" value="1">
            <div class="form-group"><label>Line 1:</label><input type="text" name="khaiwal_line1"
                    value="<?php echo htmlspecialchars(getWebsiteContentValue($pdo, 'khaiwal_line1', '🔰 *Online khaiwal* 🔰')); ?>">
            </div>
            <div class="form-group"><label>Line 2:</label><input type="text" name="khaiwal_line2"
                    value="<?php echo htmlspecialchars(getWebsiteContentValue($pdo, 'khaiwal_line2', '*( Raj Bhai Khaiwal )*')); ?>">
            </div>
            <button type="submit" class="btn-primary">💾 Update Khaiwal Info</button>
        </form>
    </div>

    <!-- WhatsApp Settings -->
    <div class="admin-section">
        <h2>📱 Edit WhatsApp Settings</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="update_whatsapp" value="1">
            <div class="form-group"><label>WhatsApp Number:</label><input type="text" name="whatsapp_number"
                    value="<?php echo htmlspecialchars(getWebsiteContentValue($pdo, 'whatsapp_number', '919812287328')); ?>">
            </div>
            <div class="form-group"><label>Button Text:</label><input type="text" name="whatsapp_text"
                    value="<?php echo htmlspecialchars(getWebsiteContentValue($pdo, 'whatsapp_text', 'WhatsApp')); ?>">
            </div>
            <div class="form-group"><label>Subtext:</label><input type="text" name="whatsapp_subtext"
                    value="<?php echo htmlspecialchars(getWebsiteContentValue($pdo, 'whatsapp_subtext', 'Click to Chat')); ?>">
            </div>
            <button type="submit" class="btn-success">💾 Update WhatsApp</button>
        </form>
    </div>

    <!-- Top WhatsApp Card -->
    <div class="admin-section">
        <h2>📞 Edit Top WhatsApp Card</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="update_top_whatsapp" value="1">
            <div class="form-group"><label>WhatsApp Number:</label><input type="text" name="top_whatsapp_number"
                    value="<?php echo htmlspecialchars(getWebsiteContentValue($pdo, 'top_whatsapp_number', '919812287328')); ?>">
            </div>
            <div class="form-group"><label>Card Text:</label><textarea name="top_whatsapp_text"
                    rows="2"><?php echo htmlspecialchars(getWebsiteContentValue($pdo, 'top_whatsapp_text', '"NOW WHATSAPP PLAYERS CAN ALSO JOIN OUR WHATSAPP CHANNEL TO GET RESULTS QUICKLY AND RECEIVE SUPERFAST RESULTS."')); ?></textarea>
            </div>
            <div class="form-group"><label>Button Text:</label><input type="text" name="top_whatsapp_btn"
                    value="<?php echo htmlspecialchars(getWebsiteContentValue($pdo, 'top_whatsapp_btn', 'Click to chat')); ?>">
            </div>
            <button type="submit" class="btn-primary">💾 Update WhatsApp Card</button>
        </form>
    </div>

    <!-- Telegram Card -->
    <div class="admin-section">
        <h2>📢 Edit Telegram Card</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="update_telegram" value="1">
            <div class="form-group"><label>Telegram Link:</label><input type="text" name="telegram_link"
                    value="<?php echo htmlspecialchars(getWebsiteContentValue($pdo, 'telegram_link', 'https://t.me/a7Resultupdates')); ?>">
            </div>
            <div class="form-group"><label>Card Text:</label><textarea name="telegram_text"
                    rows="2"><?php echo htmlspecialchars(getWebsiteContentValue($pdo, 'telegram_text', '"NOW TELEGRAM PLAYERS CAN ALSO JOIN OUR TELEGRAM CHANNEL TO GET RESULTS QUICKLY AND RECEIVE SUPERFAST RESULTS."')); ?></textarea>
            </div>
            <div class="form-group"><label>Button Text:</label><input type="text" name="telegram_btn"
                    value="<?php echo htmlspecialchars(getWebsiteContentValue($pdo, 'telegram_btn', 'Click to Connect')); ?>">
            </div>
            <button type="submit" class="btn-info">💾 Update Telegram Card</button>
        </form>
    </div>
    <?php
}
?>
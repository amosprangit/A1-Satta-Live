<?php
// includes/game-functions.php

function fetchAllGames($pdo)
{
    try {
        $stmt = $pdo->query("SELECT * FROM game_results WHERE status = 1 ORDER BY FIELD(game_name, 'pushkar', 'sadar bazar', 'gwalior', 'delhi bazar', 'shri ganesh', 'faridabad', 'gaziabad', 'gali', 'disawar')");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

function getDefaultGameNames()
{
    return ['pushkar', 'sadar bazar', 'gwalior', 'delhi bazar', 'shri ganesh', 'faridabad', 'gaziabad', 'gali', 'disawar'];
}

function getDefaultGamesData($pdo)
{
    $defaultGames = getDefaultGameNames();
    $defaultResults = [];

    try {
        // Fetch all default games from database
        $placeholders = implode(',', array_fill(0, count($defaultGames), '?'));
        $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) IN ($placeholders) AND status = 1");
        $stmt->execute(array_map('strtolower', $defaultGames));
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create associative array with lowercase keys for easy lookup
        foreach ($results as $game) {
            $key = strtolower($game['game_name']);
            $defaultResults[$key] = $game;
        }

        // Fill missing games with default data
        foreach ($defaultGames as $game) {
            $key = strtolower($game);
            if (!isset($defaultResults[$key])) {
                $defaultResults[$key] = [
                    'game_name' => $game,
                    'display_name' => strtoupper($game),
                    'yesterday_result' => '--',
                    'today_result' => 'WAIT',
                    'result_time' => '--',
                    'status' => 1,
                    'table_type' => 'table1'
                ];
            }
        }

        // Sort results in the same order as defaultGames
        $sortedResults = [];
        foreach ($defaultGames as $game) {
            $key = strtolower($game);
            if (isset($defaultResults[$key])) {
                $sortedResults[] = $defaultResults[$key];
            }
        }

        return $sortedResults;

    } catch (PDOException $e) {
        error_log("Database error in getDefaultGamesData: " . $e->getMessage());
        // Return default array with WAIT values
        $defaultData = [];
        foreach ($defaultGames as $game) {
            $defaultData[] = [
                'game_name' => $game,
                'display_name' => strtoupper($game),
                'yesterday_result' => '--',
                'today_result' => 'WAIT',
                'result_time' => '--',
                'status' => 1,
                'table_type' => 'table1'
            ];
        }
        return $defaultData;
    }
}

function getCustomTables($pdo)
{
    try {
        $stmt = $pdo->query("SELECT * FROM custom_tables ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getCustomTables: " . $e->getMessage());
        return [];
    }
}

function getCustomTableGames($pdo, $tableId)
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM custom_table_games WHERE table_id = ? ORDER BY id");
        $stmt->execute([$tableId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getCustomTableGames: " . $e->getMessage());
        return [];
    }
}

function getAllGameNames($pdo)
{
    try {
        $stmt = $pdo->query("SELECT DISTINCT game_name FROM game_results WHERE status = 1 ORDER BY game_name");
        $games = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Merge with default games if empty
        if (empty($games)) {
            $games = getDefaultGameNames();
        }

        return $games;
    } catch (PDOException $e) {
        error_log("Database error in getAllGameNames: " . $e->getMessage());
        return getDefaultGameNames();
    }
}

function getGameByName($pdo, $game_name)
{
    error_log("===== getGameByName Debug =====");
    error_log("Searching for: '" . $game_name . "'");

    // First check game_results
    try {
        $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = LOWER(?) AND status = 1");
        $stmt->execute([$game_name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $result['source'] = 'game_results';
            error_log("FOUND in game_results: " . $game_name);
            return $result;
        }
    } catch (PDOException $e) {
        error_log("Error in getGameByName (game_results): " . $e->getMessage());
    }

    // Then check custom_table_games
    try {
        $stmt = $pdo->prepare("
            SELECT ctg.*, ct.table_name as custom_table_name, ct.id as custom_table_id 
            FROM custom_table_games ctg
            INNER JOIN custom_tables ct ON ctg.table_id = ct.id
            WHERE LOWER(ctg.game_name) = LOWER(?) AND ct.status = 1
            LIMIT 1
        ");
        $stmt->execute([$game_name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $result['source'] = 'custom_table_games';
            error_log("FOUND in custom_table_games: " . $game_name);
            error_log("Custom table: " . $result['custom_table_name']);
            return $result;
        } else {
            error_log("NOT FOUND in custom_table_games: " . $game_name);

            // Debug: Show all games in custom_table_games
            $stmt2 = $pdo->query("SELECT game_name FROM custom_table_games");
            $allGames = $stmt2->fetchAll(PDO::FETCH_COLUMN);
            error_log("All custom games: " . implode(', ', $allGames));
        }
    } catch (PDOException $e) {
        error_log("Error in getGameByName (custom_table_games): " . $e->getMessage());
    }

    // Try by display_name as fallback
    try {
        $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(display_name) = LOWER(?) AND status = 1");
        $stmt->execute([$game_name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $result['source'] = 'game_results (display_name)';
            error_log("FOUND by display_name: " . $game_name);
            return $result;
        }
    } catch (PDOException $e) {
        error_log("Error in getGameByName (display_name): " . $e->getMessage());
    }

    // Try custom table by display_name
    try {
        $stmt = $pdo->prepare("
            SELECT ctg.*, ct.table_name as custom_table_name, ct.id as custom_table_id 
            FROM custom_table_games ctg
            INNER JOIN custom_tables ct ON ctg.table_id = ct.id
            WHERE LOWER(ctg.display_name) = LOWER(?) AND ct.status = 1
            LIMIT 1
        ");
        $stmt->execute([$game_name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $result['source'] = 'custom_table_games (display_name)';
            error_log("FOUND in custom_table_games by display_name: " . $game_name);
            return $result;
        }
    } catch (PDOException $e) {
        error_log("Error in getGameByName (custom_table_games display_name): " . $e->getMessage());
    }

    error_log("GAME NOT FOUND: " . $game_name);
    return null;
}
?>
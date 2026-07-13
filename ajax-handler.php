<?php
// /var/www/html/ajax-handler.php

// Enable error reporting for debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // ============================================
        // TIMING OPERATIONS
        // ============================================
        case 'add_timing':
            $game = $_POST['game_name'] ?? $_POST['game'] ?? '';
            $time = $_POST['timing'] ?? $_POST['time'] ?? '';
            $emoji = $_POST['emoji'] ?? '😇';

            if (empty($game) || empty($time)) {
                throw new Exception('Game name and time are required');
            }

            $stmt = $pdo->prepare("INSERT INTO game_timings (game_name, timing, emoji, is_active) VALUES (?, ?, ?, 1)");
            $result = $stmt->execute([$game, $time, $emoji]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Timing added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add timing']);
            }
            break;

        case 'update_timing':
            $id = $_POST['id'] ?? 0;
            $game = $_POST['game_name'] ?? $_POST['game'] ?? '';
            $time = $_POST['timing'] ?? $_POST['time'] ?? '';
            $emoji = $_POST['emoji'] ?? '😇';

            if (!$id || empty($game) || empty($time)) {
                throw new Exception('All fields are required');
            }

            $stmt = $pdo->prepare("UPDATE game_timings SET game_name = ?, timing = ?, emoji = ? WHERE id = ?");
            $result = $stmt->execute([$game, $time, $emoji, $id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Timing updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update timing']);
            }
            break;

        case 'delete_timing':
            $id = $_POST['id'] ?? 0;

            if (!$id) {
                throw new Exception('ID is required');
            }

            $stmt = $pdo->prepare("DELETE FROM game_timings WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Timing deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete timing']);
            }
            break;

        // ============================================
        // RATE OPERATIONS
        // ============================================
        case 'add_rate':
            $type = $_POST['rate_type'] ?? $_POST['type'] ?? '';
            $value = $_POST['rate_value'] ?? $_POST['value'] ?? '';

            if (empty($type) || empty($value)) {
                throw new Exception('Rate type and value are required');
            }

            $stmt = $pdo->prepare("INSERT INTO game_rates (rate_type, rate_value, is_active) VALUES (?, ?, 1)");
            $result = $stmt->execute([$type, $value]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Rate added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add rate']);
            }
            break;

        case 'update_rate':
            $id = $_POST['id'] ?? 0;
            $type = $_POST['rate_type'] ?? $_POST['type'] ?? '';
            $value = $_POST['rate_value'] ?? $_POST['value'] ?? '';

            if (!$id || empty($type) || empty($value)) {
                throw new Exception('All fields are required');
            }

            $stmt = $pdo->prepare("UPDATE game_rates SET rate_type = ?, rate_value = ? WHERE id = ?");
            $result = $stmt->execute([$type, $value, $id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Rate updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update rate']);
            }
            break;

        case 'delete_rate':
            $id = $_POST['id'] ?? 0;

            if (!$id) {
                throw new Exception('ID is required');
            }

            $stmt = $pdo->prepare("DELETE FROM game_rates WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Rate deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete rate']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }
} catch (Exception $e) {
    error_log("AJAX Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
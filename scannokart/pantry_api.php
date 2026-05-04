<?php
/**
 * pantry_api.php
 * REST-style endpoint for pantry items.
 *
 * GET    pantry_api.php          → returns all items for the logged-in user
 * POST   pantry_api.php          → adds one item  (JSON body: {name, qty, type})
 * DELETE pantry_api.php?id=N     → removes item N (only if it belongs to the user)
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

// ── Auth guard ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

require 'db.php'; // provides $conn

$userId = (int) $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// ── GET: list all pantry items for this user ──────────────────────────────────
if ($method === 'GET') {
    $stmt = $conn->prepare(
        "SELECT id, name, qty, type FROM pantry_items WHERE user_id = ? ORDER BY created_at ASC"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
    echo json_encode($items);
    exit();
}

// ── POST: add a new item ──────────────────────────────────────────────────────
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    // Support bulk insert (array of items) OR a single item object
    $toInsert = [];
    if (isset($body[0]) && is_array($body[0])) {
        $toInsert = $body; // array of items
    } else {
        $toInsert = [$body]; // single item wrapped in array
    }

    $stmt = $conn->prepare(
        "INSERT INTO pantry_items (user_id, name, qty, type) VALUES (?, ?, ?, ?)"
    );

    $inserted = [];
    foreach ($toInsert as $item) {
        $name = trim($item['name'] ?? '');
        $qty  = floatval($item['qty'] ?? 1);
        $type = trim($item['type'] ?? '');

        if ($name === '') {
            continue; // skip blank names
        }

        $stmt->bind_param('idss', $userId, $qty, $name, $type);
        // Note: bind_param types — i=int, d=double, s=string
        // Reorder to match column order: user_id(i), name(s), qty(d), type(s)
        $stmt->bind_param('isds', $userId, $name, $qty, $type);
        $stmt->execute();
        $inserted[] = [
            'id'   => (int) $conn->insert_id,
            'name' => $name,
            'qty'  => $qty,
            'type' => $type,
        ];
    }
    $stmt->close();

    http_response_code(201);
    // Return array or single object to match what the caller expects
    echo json_encode(count($inserted) === 1 ? $inserted[0] : $inserted);
    exit();
}

// ── DELETE: remove one item (must belong to this user) ───────────────────────
if ($method === 'DELETE') {
    $itemId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($itemId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing or invalid id']);
        exit();
    }

    $stmt = $conn->prepare(
        "DELETE FROM pantry_items WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param('ii', $itemId, $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found or not yours']);
    } else {
        echo json_encode(['deleted' => $itemId]);
    }
    exit();
}

// ── Fallback ──────────────────────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

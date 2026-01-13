<?php
// api/cart.php
// API endpoint for cart operations (Vercel serverless)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/database_supabase.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get cart items
        $cartItems = getCartItems();
        echo json_encode(['success' => true, 'data' => $cartItems]);
        break;
        
    case 'POST':
        // Add to cart
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = (int)($input['product_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);
        
        try {
            addToCart($productId, $quantity);
            echo json_encode(['success' => true, 'message' => 'Product added to cart']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        // Update cart
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = (int)($input['product_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);
        
        try {
            updateCartItem($productId, $quantity);
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // Clear cart
        clearCart();
        echo json_encode(['success' => true, 'message' => 'Cart cleared']);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid method']);
        break;
}
?>

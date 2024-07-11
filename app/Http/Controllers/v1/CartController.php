<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cartItems = $this->cartService->getCartItems();
        $totalAmount = $this->cartService->calculateTotalAmount($cartItems);

        return response()->json(['cart_items' => $cartItems, 'total_amount' => $totalAmount]);
    }

    public function store(CartStoreRequest $request)
    {
        $result = $this->cartService->addToCart($request->product_id, $request->quantity);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json($result['cartItem'], 201);
    }

    public function update(CartUpdateRequest $request, Cart $cart)
    {
        $result = $this->cartService->updateCartItemQuantity($cart, $request->quantity);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json($result['cartItem']);
    }

    public function destroy(Cart $cart)
    {
        $result = $this->cartService->removeFromCart($cart);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 403);
        }

        return response()->json(null, 204);
    }

    public function mergeCart(Request $request)
    {
        $localCart = $request->input('cart', []);
        $result = $this->cartService->mergeLocalCart($localCart);

        if (!$result['success']) {
            return response()->json(['message' => $result['message'], 'errors' => $result['errors']], 400);
        }

        return response()->json(['message' => $result['message']]);
    }

    public function clearCart()
    {
        $result = $this->cartService->clearCart();

        if (!$result['success']) {
            return response()->json(['message' => 'Failed to clear cart'], 500);
        }

        return response()->json(['message' => $result['message']]);
    }
}

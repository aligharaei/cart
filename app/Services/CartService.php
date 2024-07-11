<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getCartItems(): Collection|array
    {
        $userId = Auth::id();
        return Cart::where('user_id', $userId)->with('product')->get();
    }

    public function addToCart($productId, $quantity): array
    {
        try {
            $product = $this->findProductById($productId);
            $userId = Auth::id();

            $cartItem = Cart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($cartItem) {
                throw new Exception('Product already in cart');
            }

            $this->checkProductQuantity($product, $quantity);

            $cartItem = new Cart();
            $cartItem->user_id = $userId;
            $cartItem->product_id = $productId;
            $cartItem->quantity = $quantity;
            $cartItem->save();

            return ['success' => true, 'cartItem' => $cartItem];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateCartItemQuantity(Cart $cart, $newQuantity): array
    {
        try {
            $product = $this->findProductById($cart->product_id);
            $this->checkProductQuantity($product, $newQuantity);

            $cart->quantity = $newQuantity;
            $cart->save();

            return ['success' => true, 'cartItem' => $cart];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function removeFromCart(Cart $cart): array
    {
        try {
            if ($cart->user_id !== Auth::id()) {
                throw new Exception('Unauthorized');
            }

            $cart->delete();

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function mergeLocalCart(array $localCart): array
    {
        $userId = Auth::id();
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($localCart as $item) {
                $product = $this->findProductById($item['product_id']);

                $this->checkProductQuantity($product, $item['quantity']);

                $cartItem = Cart::where('user_id', $userId)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (!$cartItem) {
                    $cartItem = new Cart();
                    $cartItem->user_id = $userId;
                    $cartItem->product_id = $item['product_id'];
                }
                $cartItem->quantity = $item['quantity'];

                $cartItem->save();
            }

            DB::commit();

            if (!empty($errors)) {
                return ['success' => false, 'message' => 'Error merging cart', 'errors' => $errors];
            }

            return ['success' => true, 'message' => 'Cart merged successfully'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage(), 'errors' => $errors];
        }
    }

    public function clearCart(): array
    {
        try {
            Cart::where('user_id', Auth::id())->delete();

            return ['success' => true, 'message' => 'Cart cleared successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function calculateTotalAmount($cartItems): int
    {
        $totalAmount = 0;
        foreach ($cartItems as $cartItem) {
            $totalAmount += $cartItem->quantity * $cartItem->product->price;
        }
        return $totalAmount;
    }

    /**
     * @throws Exception
     */
    private function findProductById($productId): Product
    {
        $product = Product::find($productId);
        if (!$product) {
            throw new Exception('Product not found');
        }
        return $product;
    }

    /**
     * @throws Exception
     */
    private function checkProductQuantity(Product $product, $quantity): void
    {
        if ($product->quantity < $quantity) {
            throw new Exception('Not enough quantity available');
        }
    }
}

<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::where('user_id', Auth::id())->with('product')->get();
        return response()->json($cartItems);
    }

    public function store(CartStoreRequest $request)
    {
        $product = Product::find($request->product_id);

        if ($product->quantity < $request->quantity) {
            return response()->json(['message' => 'Not enough quantity available'], 400);
        }

        $cartItem = Cart::firstOrNew(
            ['user_id' => Auth::id(), 'product_id' => $request['product_id']]
        );
        $cartItem->quantity += $request['quantity'];
        $cartItem->save();

        return response()->json($cartItem, 201);
    }

    public function update(CartUpdateRequest $request, Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product = Product::find($cart->product_id);

        if ($product->quantity < $request->quantity) {
            return response()->json(['message' => 'Not enough quantity available'], 400);
        }

        $cart->quantity = $request->quantity;
        $cart->save();

        return response()->json($cart);
    }

    public function destroy(Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cart->delete();

        return response()->json(null, 204);
    }

    public function mergeCart(Request $request)
    {
        $localCart = $request->input('cart', []);

        foreach ($localCart as $item) {
            $cartItem = Cart::firstOrNew(
                ['user_id' => Auth::id(), 'product_id' => $item['product_id']]
            );
            $cartItem->quantity += $item['quantity'];
            $cartItem->save();
        }

        return response()->json(['message' => 'Cart merged successfully']);
    }

    public function clearCart()
    {
        Cart::where('user_id', Auth::id())->delete();

        return response()->json(['message' => 'Cart cleared successfully']);
    }
}

<?php

namespace App\Services;

use App\Events\ProductCreatedEvent;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Log;

class ProductService
{
    /**
     * @throws Exception
     */
    public function createProduct(array $data)
    {
        try {
            $product = Product::create($data);
            event(new ProductCreatedEvent($product));
            return $product;

        } catch (Exception $exception) {
            Log::error('Cannot store product: ' . $exception->getMessage());
            throw new Exception('Product creation failed', 500);
        }
    }

    /**
     * @throws Exception
     */
    public function updateProduct(Product $product, array $data): Product
    {
        try {
            $product->update($data);
            return $product;
        } catch (Exception $exception) {
            Log::error('Cannot update product: ' . $exception->getMessage());
            throw new Exception('Product update failed', 500);
        }
    }

    /**
     * @throws Exception
     */
    public function deleteProduct(Product $product): void
    {
        try {
            $product->delete();
        } catch (Exception $exception) {
            Log::error('Cannot delete product: ' . $exception->getMessage());
            throw new Exception('Product deletion failed', 500);
        }
    }
}

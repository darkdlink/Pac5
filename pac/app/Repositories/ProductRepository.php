<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return Product::class;
    }

    /**
     * Get products with category
     *
     * @return Collection
     */
    public function getWithCategory(): Collection
    {
        return $this->with(['category'])->get();
    }

    /**
     * Get products with inventory information
     *
     * @return Collection
     */
    public function getWithInventory(): Collection
    {
        return $this->with(['inventory'])->get();
    }

    /**
     * Get products with reviews
     *
     * @return Collection
     */
    public function getWithReviews(): Collection
    {
        return $this->with(['reviews'])->get();
    }

    /**
     * Get products in stock
     *
     * @param int $minStock
     * @return Collection
     */
    public function getInStock(int $minStock = 1): Collection
    {
        return $this->model->whereHas('inventory', function($query) use ($minStock) {
            $query->where('quantity', '>=', $minStock);
        })->get();
    }

    /**
     * Get products with low stock
     *
     * @param int $threshold
     * @return Collection
     */
    public function getLowStock(int $threshold = 5): Collection
    {
        return $this->model->whereHas('inventory', function($query) use ($threshold) {
            $query->where('quantity', '>', 0)
                  ->where('quantity', '<=', $threshold);
        })->get();
    }

    /**
     * Get out of stock products
     *
     * @return Collection
     */
    public function getOutOfStock(): Collection
    {
        return $this->model->whereHas('inventory', function($query) {
            $query->where('quantity', 0);
        })->get();
    }

    /**
     * Filter products by category
     *
     * @param int $categoryId
     * @return Collection
     */
    public function filterByCategory(int $categoryId): Collection
    {
        return $this->model->where('category_id', $categoryId)->get();
    }

    /**
     * Filter products by price range
     *
     * @param float $minPrice
     * @param float $maxPrice
     * @return Collection
     */
    public function filterByPrice(float $minPrice, float $maxPrice): Collection
    {
        return $this->model->whereBetween('price', [$minPrice, $maxPrice])->get();
    }

    /**
     * Filter products by rating
     *
     * @param int $minRating
     * @return Collection
     */
    public function filterByRating(int $minRating): Collection
    {
        return $this->model->whereHas('reviews', function($query) use ($minRating) {
            $query->select(DB::raw('AVG(rating) as average_rating'))
                  ->groupBy('product_id')
                  ->havingRaw('AVG(rating) >= ?', [$minRating]);
        })->get();
    }

    /**
     * Search products by name or description
     *
     * @param string $keyword
     * @return Collection
     */
    public function search(string $keyword): Collection
    {
        return $this->model->where('name', 'LIKE', "%{$keyword}%")
                          ->orWhere('description', 'LIKE', "%{$keyword}%")
                          ->get();
    }

    /**
     * Get most sold products
     *
     * @param int $limit
     * @return Collection
     */
    public function getMostSold(int $limit = 10): Collection
    {
        return $this->model->select('products.*', DB::raw('SUM(order_items.quantity) as total_sold'))
                          ->join('order_items', 'products.id', '=', 'order_items.product_id')
                          ->join('orders', 'order_items.order_id', '=', 'orders.id')
                          ->where('orders.status', 'completed')
                          ->groupBy('products.id')
                          ->orderBy('total_sold', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get featured products
     *
     * @param int $limit
     * @return Collection
     */
    public function getFeatured(int $limit = 8): Collection
    {
        return $this->model->where('featured', true)
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get newest products
     *
     * @param int $limit
     * @return Collection
     */
    public function getNewest(int $limit = 8): Collection
    {
        return $this->model->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get related products
     *
     * @param int $productId
     * @param int $limit
     * @return Collection
     */
    public function getRelated(int $productId, int $limit = 4): Collection
    {
        $product = $this->find($productId);

        return $this->model->where('category_id', $product->category_id)
                          ->where('id', '!=', $productId)
                          ->limit($limit)
                          ->get();
    }
}

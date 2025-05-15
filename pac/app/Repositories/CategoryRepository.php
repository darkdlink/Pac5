<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return Category::class;
    }

    /**
     * Get active categories
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->model->where('active', true)->get();
    }

    /**
     * Get categories with products count
     *
     * @return Collection
     */
    public function getWithProductsCount(): Collection
    {
        return $this->model->withCount('products')
                          ->where('active', true)
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get categories with services count
     *
     * @return Collection
     */
    public function getWithServicesCount(): Collection
    {
        return $this->model->withCount('services')
                          ->where('active', true)
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get categories with both products and services count
     *
     * @return Collection
     */
    public function getWithItemsCount(): Collection
    {
        return $this->model->withCount(['products', 'services'])
                          ->where('active', true)
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get product categories only
     *
     * @return Collection
     */
    public function getProductCategories(): Collection
    {
        return $this->model->where('active', true)
                          ->where('type', 'product')
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get service categories only
     *
     * @return Collection
     */
    public function getServiceCategories(): Collection
    {
        return $this->model->where('active', true)
                          ->where('type', 'service')
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get popular categories (with most products or services)
     *
     * @param int $limit
     * @return Collection
     */
    public function getPopular(int $limit = 5): Collection
    {
        return $this->model->withCount(['products', 'services'])
                          ->where('active', true)
                          ->orderByRaw('products_count + services_count DESC')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get categories by parent
     *
     * @param int|null $parentId
     * @return Collection
     */
    public function getByParent(?int $parentId = null): Collection
    {
        $query = $this->model->where('active', true);

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get category with parent and children
     *
     * @param int $id
     * @return Category
     */
    public function findWithHierarchy(int $id)
    {
        return $this->model->with(['parent', 'children'])
                          ->findOrFail($id);
    }

    /**
     * Get root categories with children
     *
     * @return Collection
     */
    public function getRootsWithChildren(): Collection
    {
        return $this->model->with(['children'])
                          ->whereNull('parent_id')
                          ->where('active', true)
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get top selling categories
     *
     * @param int $limit
     * @return array
     */
    public function getTopSelling(int $limit = 5): array
    {
        $result = DB::table('categories')
                    ->select(
                        'categories.id',
                        'categories.name',
                        DB::raw('COUNT(order_items.id) as items_sold')
                    )
                    ->leftJoin('products', 'categories.id', '=', 'products.category_id')
                    ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                    ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('categories.active', true)
                    ->where('orders.status', 'completed')
                    ->groupBy('categories.id', 'categories.name')
                    ->orderBy('items_sold', 'desc')
                    ->limit($limit)
                    ->get();

        return $result->toArray();
    }

    /**
     * Create category including slug generation
     *
     * @param array $data
     * @return Category
     */
    public function createWithSlug(array $data): Category
    {
        // Generate slug if not provided
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = \Str::slug($data['name']);
        }

        return $this->create($data);
    }

    /**
     * Update category including slug update
     *
     * @param int $id
     * @param array $data
     * @return Category
     */
    public function updateWithSlug(int $id, array $data): Category
    {
        // Update slug if name changed and slug not explicitly provided
        if (isset($data['name']) && (!isset($data['slug']) || empty($data['slug']))) {
            $data['slug'] = \Str::slug($data['name']);
        }

        return $this->update($id, $data);
    }
}

<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return Service::class;
    }

    /**
     * Get all active services
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->model->where('active', true)->get();
    }

    /**
     * Get featured services
     *
     * @param int $limit
     * @return Collection
     */
    public function getFeatured(int $limit = 5): Collection
    {
        return $this->model->where('featured', true)
                          ->where('active', true)
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get services by category
     *
     * @param int $categoryId
     * @return Collection
     */
    public function getByCategory(int $categoryId): Collection
    {
        return $this->model->where('category_id', $categoryId)
                          ->where('active', true)
                          ->get();
    }

    /**
     * Get services by price range
     *
     * @param float $minPrice
     * @param float $maxPrice
     * @return Collection
     */
    public function getByPriceRange(float $minPrice, float $maxPrice): Collection
    {
        return $this->model->whereBetween('price', [$minPrice, $maxPrice])
                          ->where('active', true)
                          ->get();
    }

    /**
     * Search services by name or description
     *
     * @param string $keyword
     * @return Collection
     */
    public function search(string $keyword): Collection
    {
        return $this->model->where(function($query) use ($keyword) {
                            $query->where('name', 'LIKE', "%{$keyword}%")
                                 ->orWhere('description', 'LIKE', "%{$keyword}%");
                          })
                          ->where('active', true)
                          ->get();
    }

    /**
     * Get most popular services (based on orders)
     *
     * @param int $limit
     * @return Collection
     */
    public function getMostPopular(int $limit = 5): Collection
    {
        return $this->model->select('services.*', DB::raw('COUNT(order_items.id) as order_count'))
                          ->leftJoin('order_items', 'services.id', '=', 'order_items.service_id')
                          ->where('services.active', true)
                          ->groupBy('services.id')
                          ->orderBy('order_count', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get newest services
     *
     * @param int $limit
     * @return Collection
     */
    public function getNewest(int $limit = 5): Collection
    {
        return $this->model->where('active', true)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get services with details
     *
     * @return Collection
     */
    public function getWithDetails(): Collection
    {
        return $this->with(['category', 'reviews'])
                   ->where('active', true)
                   ->get();
    }

    /**
     * Get service with details
     *
     * @param int $id
     * @return Service
     */
    public function findWithDetails(int $id)
    {
        return $this->model->with(['category', 'reviews'])
                          ->where('id', $id)
                          ->where('active', true)
                          ->firstOrFail();
    }

    /**
     * Get related services
     *
     * @param int $serviceId
     * @param int $limit
     * @return Collection
     */
    public function getRelated(int $serviceId, int $limit = 3): Collection
    {
        $service = $this->find($serviceId);

        return $this->model->where('category_id', $service->category_id)
                          ->where('id', '!=', $serviceId)
                          ->where('active', true)
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get service by duration
     *
     * @param int $minDuration
     * @param int $maxDuration
     * @return Collection
     */
    public function getByDuration(int $minDuration, int $maxDuration): Collection
    {
        return $this->model->whereBetween('duration', [$minDuration, $maxDuration])
                          ->where('active', true)
                          ->get();
    }
}

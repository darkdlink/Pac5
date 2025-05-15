<?php

namespace App\Repositories;

use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ReviewRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return Review::class;
    }

    /**
     * Get reviews with user, product, and service details
     *
     * @return Collection
     */
    public function getWithDetails(): Collection
    {
        return $this->model->with(['user', 'product', 'service'])->get();
    }

    /**
     * Get review with details
     *
     * @param int $id
     * @return Review
     */
    public function findWithDetails(int $id)
    {
        return $this->model->with(['user', 'product', 'service'])->findOrFail($id);
    }

    /**
     * Get approved reviews
     *
     * @return Collection
     */
    public function getApproved(): Collection
    {
        return $this->model->with(['user', 'product', 'service'])
                          ->where('approved', true)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get pending reviews
     *
     * @return Collection
     */
    public function getPending(): Collection
    {
        return $this->model->with(['user', 'product', 'service'])
                          ->where('approved', false)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get reviews by user
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUser(int $userId): Collection
    {
        return $this->model->with(['product', 'service'])
                          ->where('user_id', $userId)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get reviews by product
     *
     * @param int $productId
     * @return Collection
     */
    public function getByProduct(int $productId): Collection
    {
        return $this->model->with('user')
                          ->where('product_id', $productId)
                          ->where('approved', true)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get reviews by service
     *
     * @param int $serviceId
     * @return Collection
     */
    public function getByService(int $serviceId): Collection
    {
        return $this->model->with('user')
                          ->where('service_id', $serviceId)
                          ->where('approved', true)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get average rating for product
     *
     * @param int $productId
     * @return float
     */
    public function getAverageProductRating(int $productId): float
    {
        $average = $this->model->where('product_id', $productId)
                              ->where('approved', true)
                              ->avg('rating');

        return $average ?? 0;
    }

    /**
     * Get average rating for service
     *
     * @param int $serviceId
     * @return float
     */
    public function getAverageServiceRating(int $serviceId): float
    {
        $average = $this->model->where('service_id', $serviceId)
                              ->where('approved', true)
                              ->avg('rating');

        return $average ?? 0;
    }

    /**
     * Get rating distribution for product
     *
     * @param int $productId
     * @return array
     */
    public function getProductRatingDistribution(int $productId): array
    {
        $result = $this->model->select(
                                'rating',
                                DB::raw('COUNT(*) as count')
                            )
                            ->where('product_id', $productId)
                            ->where('approved', true)
                            ->groupBy('rating')
                            ->get();

        // Initialize distribution array with all ratings from 1 to 5
        $distribution = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];

        // Fill with actual counts
        foreach ($result as $item) {
            $distribution[$item->rating] = $item->count;
        }

        return $distribution;
    }

    /**
     * Get rating distribution for service
     *
     * @param int $serviceId
     * @return array
     */
    public function getServiceRatingDistribution(int $serviceId): array
    {
        $result = $this->model->select(
                                'rating',
                                DB::raw('COUNT(*) as count')
                            )
                            ->where('service_id', $serviceId)
                            ->where('approved', true)
                            ->groupBy('rating')
                            ->get();

        // Initialize distribution array with all ratings from 1 to 5
        $distribution = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];

        // Fill with actual counts
        foreach ($result as $item) {
            $distribution[$item->rating] = $item->count;
        }

        return $distribution;
    }

    /**
     * Get latest reviews across all products and services
     *
     * @param int $limit
     * @return Collection
     */
    public function getLatest(int $limit = 10): Collection
    {
        return $this->model->with(['user', 'product', 'service'])
                          ->where('approved', true)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get top rated reviews
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopRated(int $limit = 10): Collection
    {
        return $this->model->with(['user', 'product', 'service'])
                          ->where('approved', true)
                          ->where('rating', 5)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Approve a review
     *
     * @param int $id
     * @return Review
     */
    public function approve(int $id)
    {
        $review = $this->find($id);
        $review->approved = true;
        $review->save();

        return $review;
    }

    /**
     * Reject a review
     *
     * @param int $id
     * @return Review
     */
    public function reject(int $id)
    {
        $review = $this->find($id);
        $review->approved = false;
        $review->save();

        return $review;
    }

    /**
     * Check if a user has already reviewed a product
     *
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public function hasUserReviewedProduct(int $userId, int $productId): bool
    {
        return $this->model->where('user_id', $userId)
                          ->where('product_id', $productId)
                          ->exists();
    }

    /**
     * Check if a user has already reviewed a service
     *
     * @param int $userId
     * @param int $serviceId
     * @return bool
     */
    public function hasUserReviewedService(int $userId, int $serviceId): bool
    {
        return $this->model->where('user_id', $userId)
                          ->where('service_id', $serviceId)
                          ->exists();
    }

    /**
     * Get review statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $totalReviews = $this->model->count();
        $approvedReviews = $this->model->where('approved', true)->count();
        $pendingReviews = $this->model->where('approved', false)->count();
        $averageRating = $this->model->where('approved', true)->avg('rating') ?? 0;

        return [
            'total_reviews' => $totalReviews,
            'approved_reviews' => $approvedReviews,
            'pending_reviews' => $pendingReviews,
            'average_rating' => $averageRating
        ];
    }
}

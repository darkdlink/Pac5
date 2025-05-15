<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return User::class;
    }

    /**
     * Get customers only (no admins)
     *
     * @return Collection
     */
    public function getCustomers(): Collection
    {
        return $this->model->where('role', 'customer')->get();
    }

    /**
     * Get admins only
     *
     * @return Collection
     */
    public function getAdmins(): Collection
    {
        return $this->model->where('role', 'admin')->get();
    }

    /**
     * Get user with orders
     *
     * @param int $id
     * @return User
     */
    public function findWithOrders(int $id)
    {
        return $this->model->with(['orders.items', 'orders.payment'])
                          ->findOrFail($id);
    }

    /**
     * Get user with profile
     *
     * @param int $id
     * @return User
     */
    public function findWithProfile(int $id)
    {
        return $this->model->with('profile')
                          ->findOrFail($id);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Get newest users
     *
     * @param int $limit
     * @return Collection
     */
    public function getNewest(int $limit = 10): Collection
    {
        return $this->model->where('role', 'customer')
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get active users (with recent orders)
     *
     * @param int $daysAgo
     * @return Collection
     */
    public function getActive(int $daysAgo = 30): Collection
    {
        $date = Carbon::now()->subDays($daysAgo);

        return $this->model->where('role', 'customer')
                          ->whereHas('orders', function($query) use ($date) {
                              $query->where('created_at', '>=', $date);
                          })
                          ->get();
    }

    /**
     * Get inactive users (no orders in specified period)
     *
     * @param int $daysAgo
     * @return Collection
     */
    public function getInactive(int $daysAgo = 180): Collection
    {
        $date = Carbon::now()->subDays($daysAgo);

        return $this->model->where('role', 'customer')
                          ->whereDoesntHave('orders', function($query) use ($date) {
                              $query->where('created_at', '>=', $date);
                          })
                          ->get();
    }

    /**
     * Get users registered per month
     *
     * @param int $year
     * @return array
     */
    public function getRegistrationsPerMonth(int $year): array
    {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();

        $result = $this->model->select(
                                DB::raw('MONTH(created_at) as month'),
                                DB::raw('COUNT(*) as count')
                            )
                            ->where('role', 'customer')
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->groupBy(DB::raw('MONTH(created_at)'))
                            ->get();

        return $result->toArray();
    }

    /**
     * Get users with most reviews
     *
     * @param int $limit
     * @return array
     */
    public function getMostReviews(int $limit = 10): array
    {
        $result = $this->model->select(
                                'users.id',
                                'users.name',
                                'users.email',
                                DB::raw('COUNT(reviews.id) as review_count')
                            )
                            ->join('reviews', 'users.id', '=', 'reviews.user_id')
                            ->groupBy('users.id', 'users.name', 'users.email')
                            ->orderBy('review_count', 'desc')
                            ->limit($limit)
                            ->get();

        return $result->toArray();
    }

    /**
     * Get users with most orders
     *
     * @param int $limit
     * @return array
     */
    public function getMostOrders(int $limit = 10): array
    {
        $result = $this->model->select(
                                'users.id',
                                'users.name',
                                'users.email',
                                DB::raw('COUNT(orders.id) as order_count')
                            )
                            ->join('orders', 'users.id', '=', 'orders.user_id')
                            ->where('orders.status', 'completed')
                            ->groupBy('users.id', 'users.name', 'users.email')
                            ->orderBy('order_count', 'desc')
                            ->limit($limit)
                            ->get();

        return $result->toArray();
    }

    /**
     * Get users with highest spending
     *
     * @param int $limit
     * @return array
     */
    public function getHighestSpending(int $limit = 10): array
    {
        $result = $this->model->select(
                                'users.id',
                                'users.name',
                                'users.email',
                                DB::raw('SUM(orders.total) as total_spent')
                            )
                            ->join('orders', 'users.id', '=', 'orders.user_id')
                            ->where('orders.status', 'completed')
                            ->groupBy('users.id', 'users.name', 'users.email')
                            ->orderBy('total_spent', 'desc')
                            ->limit($limit)
                            ->get();

        return $result->toArray();
    }

    /**
     * Get users by last order date
     *
     * @param string $order
     * @param int $limit
     * @return Collection
     */
    public function getByLastOrderDate(string $order = 'desc', int $limit = 10): Collection
    {
        return $this->model->select(
                            'users.*',
                            DB::raw('MAX(orders.created_at) as last_order_date')
                        )
                        ->join('orders', 'users.id', '=', 'orders.user_id')
                        ->where('users.role', 'customer')
                        ->groupBy('users.id')
                        ->orderBy('last_order_date', $order)
                        ->limit($limit)
                        ->get();
    }

    /**
     * Get users count by region/city
     *
     * @return array
     */
    public function getCountByRegion(): array
    {
        $result = $this->model->select(
                                'city',
                                DB::raw('COUNT(*) as user_count')
                            )
                            ->whereNotNull('city')
                            ->where('role', 'customer')
                            ->groupBy('city')
                            ->orderBy('user_count', 'desc')
                            ->get();

        return $result->toArray();
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        // Ensure password is hashed
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        return $this->create($data);
    }

    /**
     * Update user with password handling
     *
     * @param int $id
     * @param array $data
     * @return User
     */
    public function updateUser(int $id, array $data): User
    {
        // Only hash password if it's provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            // Remove password from data if empty
            unset($data['password']);
        }

        return $this->update($id, $data);
    }
}

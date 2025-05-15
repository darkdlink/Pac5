<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Get all records
     *
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $columns = ['*']);

    /**
     * Get paginated records
     *
     * @param int $perPage
     * @param array $columns
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']);

    /**
     * Find record by id
     *
     * @param int $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find(int $id, array $columns = ['*']);

    /**
     * Find record by specific field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findBy(string $field, $value, array $columns = ['*']);

    /**
     * Find multiple records by field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAllBy(string $field, $value, array $columns = ['*']);

    /**
     * Create new record
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data);

    /**
     * Update record
     *
     * @param int $id
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(int $id, array $data);

    /**
     * Delete record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id);
}

<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model(): string;

    /**
     * Make Model instance
     *
     * @return Model
     */
    public function makeModel(): Model
    {
        $model = app($this->model());
        return $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /**
     * @inheritDoc
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * @inheritDoc
     */
    public function find(int $id, array $columns = ['*'])
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * @inheritDoc
     */
    public function findBy(string $field, $value, array $columns = ['*'])
    {
        return $this->model->where($field, $value)->first($columns);
    }

    /**
     * @inheritDoc
     */
    public function findAllBy(string $field, $value, array $columns = ['*']): Collection
    {
        return $this->model->where($field, $value)->get($columns);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data)
    {
        $record = $this->find($id);
        $record->update($data);
        return $record;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        return $this->find($id)->delete();
    }

    /**
     * Get with relations
     *
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function with(array $relations)
    {
        return $this->model->with($relations);
    }

    /**
     * Order by field
     *
     * @param string $field
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function orderBy(string $field, string $direction = 'asc')
    {
        return $this->model->orderBy($field, $direction);
    }

    /**
     * Where condition
     *
     * @param string $field
     * @param mixed $value
     * @param string $operator
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function where(string $field, $value, string $operator = '=')
    {
        return $this->model->where($field, $operator, $value);
    }
}

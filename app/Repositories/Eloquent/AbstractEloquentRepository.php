<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\BaseRepository;
use App\Repositories\Models\User;

abstract class AbstractEloquentRepository implements BaseRepository
{
    /**
     * 模型的完整命名空间
     *
     * @var string
     */
    protected $modelName;

    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * 返回 Model 实例
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    public function findOne($id)
    {
        return $this->findOneBy(['user_id' => $id]);
    }

    public function findOneBy(array $criteria)
    {
        return $this->model->where($criteria)->first();
    }

    public function findBy(array $searchCriteria = [])
    {
        $limit = !empty($searchCriteria['per_page']) ? (int)$searchCriteria['per_page'] : 15;

        $queryBuilder = $this->model->where(function ($query) use ($searchCriteria) {
                $this->applySearchCriteriaInQueryBuilder($query, $searchCriteria);
            }
        );

        return $queryBuilder->paginate($limit);
    }

    protected function applySearchCriteriaInQueryBuilder($queryBuilder, array $searchCriteria = [])
    {
        foreach ($searchCriteria as $key => $value) {

            // 如果有分页相关的查询则略过
            if (in_array($key, ['page', 'per_page'])) {
                continue;
            }

            // 如果有逗号 “,” 可转成数组用于 IN 查询
            $allValues = explode(',', $value);

            if (count($allValues) > 1) {
                $queryBuilder->whereIn($key, $allValues);
            } else {
                $operator = '=';
                $queryBuilder->where($key, $operator, $value);
            }
        }

        return $queryBuilder;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data)
    {
        $fillAbleProperties = $this->model->getFillable();

        foreach ($data as $key => $value) {

            // 只更新白名单的字段
            if (in_array($key, $fillAbleProperties)) {
                $model->$key = $value;
            }
        }

        // 更新模型
        $model->save();

        // 过去更新后的模型
        $model = $this->findOne($model->id);

        return $model;
    }

    public function findIn($key, array $values)
    {
        return $this->model->whereIn($key, $values)->get();
    }

    public function delete(Model $model)
    {
        return $model->delete();
    }

    /**
     * 获取登录的用户
     *
     * @return User
     */
    protected function getLoggedInUser()
    {
        $user = \Auth::user();

        if ($user instanceof User) {
            return $user;
        } else {
            return new User();
        }
    }
}

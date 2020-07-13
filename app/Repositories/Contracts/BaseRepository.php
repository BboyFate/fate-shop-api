<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepository
{
    /**
     * 根据 ID 查询一条资源
     *
     * @param $id
     * @return Model|null
     */
    public function findOne($id);

    /**
     * 条件搜索一条资源
     *
     * @param array $criteria
     * @return Model|null
     */
    public function findOneBy(array $criteria);

    /**
     * 根据条件搜索资源集合
     *
     * @param array $searchCriteria
     * @return Collection
     */
    public function findBy(array $searchCriteria = []);

    /**
     * 某字段的 IN 查询
     *
     * @param string $key
     * @param array $values
     * @return Collection
     */
    public function findIn($key, array $values);

    /**
     * 保存资源
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data);

    /**
     * 更新一个资源
     *
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function update(Model $model, array $data);

    /**
     * 删除一个资源
     *
     * @param Model $model
     * @return mixed
     */
    public function delete(Model $model);
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

class Model extends BaseModel
{
    /**
     * whereHas 的 where in 实现
     *
     * @param Builder $builder
     * @param $relationName
     * @param callable $callable
     * @return Builder
     * @throws \Exception
     */
    public function scopeWhereHasIn(Builder $builder, $relationName, callable $callable)
    {
        $relationNames = explode('.', $relationName);
        $nextRelation = implode('.', array_slice($relationNames, 1));

        $method = $relationNames[0];
        /** @var Relations\BelongsTo|Relations\HasOne $relation */
        $relation = Relation::noConstraints(function () use ($method) {
            return $this->$method();
        });

        /** @var Builder $in */
        if ($nextRelation) {
            $in = $relation->getQuery()->whereHasIn($nextRelation, $callable);
        } else {
            $in = $relation->getQuery()->where($callable);
        }

        if ($relation instanceof BelongsTo) {
            return $builder->whereIn($relation->getForeignKeyName(), $in->select($relation->getOwnerKeyName()));
        } else if ($relation instanceof HasOne) {
            return $builder->whereIn($this->getKeyName(), $in->select($relation->getForeignKeyName()));
        } else if ($relation instanceof HasMany){
            return $builder->whereIn($this->getKeyName(), $in->select($relation->getForeignKeyName()));
        }

        throw new \Exception(__METHOD__ . " 不支持 " . get_class($relation));
    }
}

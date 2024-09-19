<?php

namespace Vildanbina\ModelJson\Traits;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LogicException;

/**
 * Trait HasModel
 *
 * @package Vildanbina\ModelJson\Traits
 */
trait HasModel
{
    /**
     * @var string
     */
    protected string $model;

    /**
     * @var ?string
     */
    protected ?string $scope = null;

    /**
     * @param  string  $model
     *
     * @return $this
     */
    public function setModel(string $model): static
    {
        $this->model = $model;

        if (!class_exists($this->model)) {
            throw new ModelNotFoundException('Model ' . $this->model . ' does not exists.');
        }

        if (!new $this->model instanceof Model) {
            throw new LogicException('Class ' . $this->model . ' is not ' . Model::class . ' instance.');
        }

        return $this;
    }

    /**
     * @param  ?string  $scope
     *
     * @return $this
     */
    public function setScope(?string $scope): static
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalModelsCount(): int
    {
        return $this->modelQuery()->count();
    }

    /**
     * @return Builder
     */
    protected function modelQuery(): Builder
    {
        return $this->model::query()
            ->when(filled($relations = $this->getRelationships()), function (Builder $builder) use ($relations) {
                $builder->with($relations);
            })
            ->when(filled($scope = $this->scope), function (Builder $builder) use ($scope) {
                $builder->$scope();
            });
    }
}

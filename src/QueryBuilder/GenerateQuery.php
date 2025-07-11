<?php

declare(strict_types = 1);

namespace QuantumTecnology\ControllerQraphQLExtension\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\ControllerQraphQLExtension\Presenters\GenericPresenter;

final class GenerateQuery
{
    public function __construct(
        protected Model $model,
        protected ?object $classCallable = null,
    ) {
    }

    public function execute(
        string $fields = '',
        array $pagination = [],
        string $action = '',
    ): Builder {
        $query = $this->model->query();

        $genericPresenter = app(GenericPresenter::class);

        if (filled($allIncludes = $genericPresenter->getIncludes(
            $this->model,
            $fields,
            $pagination,
        ))) {
            $query->with($allIncludes);
        }

        if (filled($allCount = $genericPresenter->getWithCount($this->model, $allIncludes))) {
            $query->withCount($allCount);
        }

        return $query;
    }
}

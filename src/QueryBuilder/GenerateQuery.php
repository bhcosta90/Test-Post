<?php

declare(strict_types = 1);

namespace QuantumTecnology\ControllerQraphQLExtension\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\ControllerQraphQLExtension\Presenters\GenericPresenter;

final class GenerateQuery
{
    public function __construct(
        protected ?Model $model = null,
        protected ?object $classCallable = null,
        protected ?string $action = null,
    ) {
    }

    public function execute(
        string $fields = '',
        array $pagination = [],
        array $filters = [],
    ): Builder {
        $query = $this->model->query();

        $genericPresenter = app(GenericPresenter::class);

        $this->addWhereWithFilters($query, $filters[$this->model::class] ?? []);

        if (filled($allIncludes = $genericPresenter->getIncludes(
            $this->model,
            $fields,
            $pagination,
            $filters,
            $this->classCallable,
            $this->action,
        ))) {
            $query->with($allIncludes);
        }

        if (filled($allCount = $genericPresenter->getWithCount($this->model, $allIncludes))) {
            $query->withCount($allCount);
        }

        return $query;
    }

    public function addWhereWithFilters($query, array $filters = []): void
    {
        foreach ($filters as $field => $values) {
            foreach ($values as $operator => $data) {
                match ($operator) {
                    'in'    => $query->whereIn($field, $data),
                    default => $query->where($field, $operator, $data)
                };
            }
        }
    }
}

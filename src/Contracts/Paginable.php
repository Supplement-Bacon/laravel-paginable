<?php

namespace SupplementBacon\LaravelPaginable\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Paginable
{
    public function getFilterQuery(Builder $query, string $filterParam, mixed $filterValues): Builder;

    public function getSearchQuery(Builder $query, string $searchColumn, mixed $searchValue): Builder;

    public function getSortQuery(Builder $query, ?string $sort, ?string $direction): Builder;

    public function getDefaultSortQuery(Builder $query): Builder;
}

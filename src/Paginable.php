<?php

namespace SupplementBacon\LaravelPaginable;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use SupplementBacon\LaravelPaginable\Requests\IndexPaginatedRequest;

trait Paginable
{

    public function getFilterQuery(Builder $query, string $filterParam, mixed $filterValues): Builder
    {
        return $query;
    }

    public function getSearchQuery(Builder $query, string $searchColumn, mixed $searchValue): Builder
    {
        return $query;
    }
    public function getSortQuery(Builder $query, ?string $sort, ?string $direction): Builder
    {
        return $query->orderBy($this->getQualifiedKeyName(), 'desc');
    }
    public function getDefaultSortQuery(Builder $query): Builder
    {
        return $query->orderBy($this->getQualifiedKeyName(), 'desc');
    }

    /**
     * Scope a query by applying ids/filters/search/sort from IndexPaginatedRequest
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  \SupplementBacon\LaravelPaginable\Requests\IndexPaginatedRequest $request
     * @param  array<string> $withCount
     * 
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopePaginatorQuery(Builder $query, IndexPaginatedRequest $request, array $withCount = []): Builder
    {
        return $query
            // Filter by IDS
            ->when($request->has(IndexPaginatedRequest::IDS), function (Builder $q2) use ($request) {
                $q2->whereIn(
                    $this->getKeyName() ?? 'id',
                    $request->{IndexPaginatedRequest::IDS}
                );

                // Filter by params
            })->when($request->has(IndexPaginatedRequest::FILTER), function (Builder $q2) use ($request) {
                foreach ($request->{IndexPaginatedRequest::FILTER} as $filterParam => $filterValues) {
                    if (!$filterValues) {
                        continue;
                    }
                    $this->getFilterQuery(
                        $q2,
                        $filterParam,
                        $filterValues
                    );
                }

                // Search
            })->when($request->has(IndexPaginatedRequest::SEARCH) && $request->{IndexPaginatedRequest::SEARCH}, function (Builder $q2) use ($request) {
                foreach ($request->{IndexPaginatedRequest::SEARCH} as $searchColumn => $searchValue) {
                    $this->getSearchQuery(
                        $q2,
                        $searchColumn,
                        $searchValue
                    );
                }
            })

            // Sort
            ->when($request->has(IndexPaginatedRequest::SORT) && $request->{IndexPaginatedRequest::SORT} && $request->has(IndexPaginatedRequest::SORT_DIRECTION), function (Builder $q2) use ($request) {
                $this->getSortQuery(
                    $q2,
                    $request->{IndexPaginatedRequest::SORT},
                    $this->getProperSortDirection($request->{IndexPaginatedRequest::SORT_DIRECTION})
                );
            })->when(!$request->has(IndexPaginatedRequest::SORT), function (Builder $q2) {
                $this->getDefaultSortQuery($q2);
            })

            // With count
            ->when(count($withCount) > 0, function (Builder $q2) use ($withCount) {
                $q2->withCount($withCount);
            });
    }

    /**
     * Scope a query by applying ids/filters/search/sort from IndexPaginatedRequest
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  \SupplementBacon\LaravelPaginable\Requests\IndexPaginatedRequest $request
     * @param  array<string> $withCount
     * 
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function scopePaginator(Builder $query, IndexPaginatedRequest $request, array $withCount = []): LengthAwarePaginator
    {
        return $query
            // Apply the request filters, searchs, sorts, etc.
            ->paginatorQuery($request, $withCount)

            // Finally paginate the result
            ->paginate(
                page: $request->pagination['current'],
                perPage: $request->pagination['pageSize'],
            );
    }

    private function getProperSortDirection(string $order): string
    {
        return $order === 'ascend'
            ? 'asc'
            : 'desc';
    }
}

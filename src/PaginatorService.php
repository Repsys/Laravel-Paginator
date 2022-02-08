<?php

namespace Leonidark\Paginator;

use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Пагинатор для пагинирования для пагинации :)
 */
class PaginatorService
{
    const DEFAULT_PAGE   = 1;
    const DEFAULT_LIMIT  = 10;
    const DEFAULT_OFFSET = 0;

    protected int $page = 0;
    protected int $limit = 0;
    protected int $offset = 0;
    protected int $allCount = 0;
    protected int $maxPage = 0;

    /**
     * @throws ValidationException
     */
    public function __construct()
    {
        $this->setParams();
    }

    /**
     * При установке параметров $limit или $page, соответствующие параметры из запроса восприниматься не будут
     * @param int|null $limit
     * @param int|null $page
     * @param int|null $offset
     * @throws ValidationException
     */
    public function setParams(int $limit = null, int $page = null, int $offset = null)
    {
        $limit = $limit ?? Request::input('limit', self::DEFAULT_LIMIT);
        $page = $page ?? Request::input('page', self::DEFAULT_PAGE);
        $offset = $offset ?? Request::input('offset', self::DEFAULT_OFFSET);

        Validator::validate([
            'limit'  => $limit,
            'page'   => $page,
            'offset' => $offset,
        ], [
            'limit'  => 'integer|min:1|max:100',
            'page'   => 'integer|min:1',
            'offset' => 'integer',
        ]);

        $this->limit = $limit;
        $this->page = $page;
        $this->offset = $offset;
    }

    /**
     * Пагинирует данные
     *
     * @param EloquentBuilder|QueryBuilder|SupportCollection $data Строитель запроса (можно получить через getQuery()) или коллекция
     *
     * @return EloquentBuilder|QueryBuilder|SupportCollection
     */
    public function paginate(EloquentBuilder|QueryBuilder|SupportCollection $data): EloquentBuilder|QueryBuilder|SupportCollection
    {
        try {
            $this->allCount = $data->count();
            if ($this->allCount == 1 &&
                ($data instanceof EloquentBuilder || $data instanceof QueryBuilder)
            ) {
                throw new Exception();
            }
        } catch (Exception $e) {
            $this->allCount = $data->get()->count();
        }

        $this->maxPage = max(0, ceil(($this->allCount) / $this->limit) + ceil(-$this->offset / $this->limit));

        if ($data instanceof SupportCollection) {
            $offset = max(0, ($this->page - 1) * $this->limit + $this->offset);
            $paginatedData = $data->slice($offset, $this->limit);
            if (!$this->collectionIsAssoc($data)) {
                $paginatedData = $paginatedData->values();
            }
            return $paginatedData;
        }

        return $data->offset(($this->page - 1) * $this->limit + $this->offset)->limit($this->limit);
    }

    /**
     * Является ли коллекция ассоциативной?
     * Если ключи коллекции не равны индексам, то она считается ассоциативной
     * @param Collection $collection
     * @return bool
     */
    protected function collectionIsAssoc(Collection $collection): bool
    {
        if ($collection->isEmpty()) return false;
        return $collection->keys()->diff(Collection::range(0, $collection->count() - 1))->isNotEmpty();
    }

    public function assignParams($list, $extra = [])
    {
        return array_merge(['list' => $list], $extra, $this->getParams());
    }

    /**
     * Возвращает параметры пагинации (page, limit)
     *
     * @return array
     */
    public function getParams(): array
    {
        return [
            'limit'   => $this->limit,
            'page'    => $this->page,
            'offset'  => $this->offset,
            'maxPage' => $this->maxPage,
            'count'   => $this->allCount,
        ];
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getAllCount(): int
    {
        return $this->allCount;
    }

    public function getMaxPage(): int
    {
        return $this->maxPage;
    }
}

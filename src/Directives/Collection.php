<?php

namespace Edalzell\Blade\Directives;

use Edalzell\Blade\Concerns\IsDirective;
use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Eloquent\Entries\EntryQueryBuilder;
use Statamic\Support\Arr;

class Collection
{
    use IsDirective;

    public $directive = 'collection';
    public $key = 'entry';
    public $type = 'loop';
    public $method = 'handle';

    private EntryQueryBuilder $collectionQuery;
    private array $params;

    public function handle(string $handle, array $params = [])
    {
        $this->params = $params;
        $this->collectionQuery = CollectionAPI::find($handle)->queryEntries();

        $this->filter();
        $this->limit();
        $this->orderBy();

        return $this->getAugmentedValue($this->collectionQuery->get());
    }

    private function filter()
    {
        if ($where = Arr::get($this->params, 'where')) {
            foreach (explode(',', $where) as $condition) {
                list($field, $value) = explode(':', $condition);

                $this->collectionQuery->where(trim($field), trim($value));
            }
        }
    }

    private function limit()
    {
        if ($limit = Arr::get($this->params, 'limit')) {
            $this->collectionQuery->limit($limit);
        }
    }

    private function orderBy()
    {
        if ($orderBy = Arr::get($this->params, 'orderBy')) {
            $sort = explode(':', $orderBy);
            $field = $sort[0];
            $direction = $sort[1] ?? 'asc';

            $this->collectionQuery->orderBy($field, $direction);
        }
    }
}

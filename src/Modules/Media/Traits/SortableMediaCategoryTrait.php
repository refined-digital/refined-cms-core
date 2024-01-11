<?php

namespace RefinedDigital\CMS\Modules\Media\Traits;

trait SortableMediaCategoryTrait
{
    use \Spatie\EloquentSortable\SortableTrait;

    /**
     * Boot the is page trait for a model.
     *
     * @return void
     */
    public static function bootSortableMediaCategoryTrait()
    {
    }

    /**
     * Determine the order value for the new record.
     */
    public function getHighestOrderNumber(): int
    {
        return (int) $this->buildSortQuery()
            ->whereParentId($this->parent_id)
            ->max($this->determineOrderColumnName());
    }

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2, ...
     *
     * A starting order number can be optionally supplied (defaults to 1).
     *
     * @param array|\ArrayAccess $ids
     * @param int $startOrder
     */
    public static function setNewOrder($ids, int $startOrder = 1, string $primaryKeyColumn = null, callable $modifyQuery = null): void
    {
        $parentId = request()->get('parent');

        if (! is_array($ids) && ! $ids instanceof ArrayAccess) {
            throw new InvalidArgumentException('You must pass an array or ArrayAccess object to setNewOrder');
        }

        $model = new static;

        $orderColumnName = $model->determineOrderColumnName();
        $primaryKeyColumn = $model->getKeyName();

        if ($parentId < 0) {
            // if its a negative, it means the parent is a holder
            // update by parent holder id and parent
            foreach ($ids as $id) {
                static::withoutGlobalScope(SoftDeletingScope::class)
                    ->where($primaryKeyColumn, $id)
                    ->whereParentId(0)
                    ->update([$orderColumnName => $startOrder++]);
            }
        } else {
            // update just by parent
            foreach ($ids as $id) {
                static::withoutGlobalScope(SoftDeletingScope::class)
                    ->where($primaryKeyColumn, $id)
                    ->whereParentId($parentId)
                    ->update([$orderColumnName => $startOrder++]);
            }

        }
    }
}

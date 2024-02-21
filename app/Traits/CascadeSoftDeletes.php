<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait CascadeSoftDeletes
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
trait CascadeSoftDeletes
{
    /**
     * Boot the trait.
     *
     * Listen for the deleting event of a soft deleting model, and run
     * the delete operation for any configured relationship methods.
     *
     * @throws \LogicException
     */
    protected static function bootCascadeSoftDeletes()
    {
        static::deleting(function ($model) {
            $model->runCascadingDeletes();
        });
    }

    /**
     * Run the cascading soft delete for this model.
     *
     * @return void
     */
    protected function runCascadingDeletes()
    {
        foreach ($this->getActiveCascadingDeletes() as $relationship) {
            $this->cascadeSoftDeletes($relationship);
        }
    }

    /**
     * Cascade delete the given relationship on the given mode.
     *
     * @param  string  $relationship
     */
    protected function cascadeSoftDeletes($relationship)
    {
        $delete = $this->forceDeleting ? 'forceDelete' : 'delete';

        foreach ($this->{$relationship}()->get() as $model) {
            $model->pivot ? $model->pivot->{$delete}() : $model->{$delete}();
        }
    }

    /**
     * Determine if the current model implements soft deletes.
     *
     * @return bool
     */
    protected function implementsSoftDeletes()
    {
        return method_exists($this, 'runSoftDelete');
    }

    /**
     * Fetch the defined cascading soft deletes for this model.
     *
     * @return array
     */
    protected function getCascadingDeletes()
    {
        return isset($this->cascadeDeletes) ? (array) $this->cascadeDeletes : [];
    }

    /**
     * For the cascading deletes defined on the model, return only those that are not null.
     *
     * @return array
     */
    protected function getActiveCascadingDeletes()
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return ! is_null($this->{$relationship});
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait CascadeRestore
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
trait CascadeRestore
{
    /**
     * Boot the trait.
     *
     * Listen for the restoring event of a soft deleting model, and run
     * the restore operation for any configured relationship methods.
     *
     * @throws \LogicException
     */
    protected static function bootCascadeRestore()
    {
        static::restoring(function ($model) {
            $model->runCascadingRestore();
        });
    }

    /**
     * Run the cascading restore for this model.
     *
     * @return void
     */
    protected function runCascadingRestore()
    {
        foreach ($this->getActiveCascadingRestore() as $relationship) {
            $this->cascadeRestore($relationship);
        }
    }

    /**
     * Cascade restore the given relationship on the given mode.
     *
     * @param  string  $relationship
     */
    protected function cascadeRestore($relationship)
    {
        if ($this->forceDeleting) {
            return;
        }

        $models = $this->{$relationship}()->withTrashed()->get();

        foreach ($models as $model) {
            $model->pivot ? $model->pivot->restore() : $model->restore();
        }
    }

    /**
     * Determine if the current model implements soft deletes.
     *
     * @return bool
     */
    protected function implementsRestore()
    {
        return method_exists($this, 'restore');
    }

    /**
     * Fetch the defined cascading soft deletes for this model.
     *
     * @return array
     */
    protected function getCascadingRestore()
    {
        return isset($this->cascadeDeletes) ? (array) $this->cascadeDeletes : [];
    }

    /**
     * For the cascading deletes defined on the model, return only those that are not null.
     *
     * @return array
     */
    protected function getActiveCascadingRestore()
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return ! is_null($this->{$relationship});
        });
    }
}

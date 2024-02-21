<?php

namespace App\DataTransferObjects;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

abstract class JsonbMapper implements \App\DataTransferObjects\Interfaces\JsonbMapper
{
    protected $obj;

    protected $type;

    public function __construct($id = false)
    {
        if ($id) {
            if (is_object($id)) {
                $this->obj = $id;
            } else {
                $this->obj = $this->type::find($id);
            }

            if (! $this->obj) {
                $model = $this->type::withTrashed()->where('id', $id)->first();

                if (! $model) {
                    throw new \Exception('OBJ not found');
                }

                $this->obj = $model;
            }
        } else {
            $this->obj = new $this->type();
        }
    }

    public function mapToEloquent($args, $user, $ignored = [])
    {
        $this->compose_hard($user, $args, $ignored);
        $this->compose_soft($args);

        $this->obj->data = $this->appendInducted($this->obj->data);

        return $this->obj;
    }

    public function mapFromEloquent($obj = null): array
    {
        $activeObj = $obj ? $obj : $this->obj;

        return [
            'id' => $activeObj->id,
            'finished' => $activeObj->finished,
            'updated_at' => $activeObj->updated_at,
            'lastStepStatus' => $activeObj->lastStepStatus,
            'user_id' => $activeObj->user_id,
            'status' => $activeObj->status,
        ] + $activeObj->data;
    }

    public function storeStatus($status)
    {
        $this->obj->lastStepStatus = $status;

        return $this->obj;
    }

    public static function mapQuery($query, $perPage, $currentPage): array
    {
        Log::notice('mapQuery method should not be used');
        $perPage = $perPage ? $perPage : 1;
        $currentPage = $currentPage ? $currentPage : 1;
        $total = $query->count();
        $items = $query->skip(($currentPage - 1) * $perPage)->take($perPage)->get()->map(function ($item) {
            $class = get_called_class();

            return (new $class())->mapFromEloquent($item);
        });
        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage
        );

        return [
            'data' => $items,
            'paginatorInfo' => [
                'count' => $paginator->count(),
                'currentPage' => $paginator->currentPage(),
                'firstItem' => $paginator->firstItem(),
                'hasMorePages' => $paginator->hasMorePages(),
                'lastItem' => $paginator->lastItem(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function stringUnit()
    {
        return '';
    }

    public function arrayUnit()
    {
        return [];
    }

    public function enumUnit()
    {
        return 0;
    }

    public function numberUnit()
    {
        return 0;
    }

    public function booleanUnit()
    {
        return false;
    }

    protected function compose_hard($user, $args, $ignored = [])
    {
        if (! $this->obj->user_id) {
            $this->obj->user_id = $user->id;
        }
        if (! in_array('finished', $ignored)) {
            if (! $this->obj->finished) {
                $this->obj->finished = $args['finished'] ?? false;
            }
        }

        if (isset($args['lastStepStatus'])) {
            $this->obj->lastStepStatus = $args['lastStepStatus'];
        }
    }
}

<?php

namespace App\Repositories;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    /**
     * Модель, к которой относит репозиторий.
     *
     * @var Model|Application|mixed
     */
    protected mixed $model;

    /**
     * Отдает класс модели.
     *
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->model = app($this->getModelClass());
    }

    /**
     * Чтобы 100% избежать сохранения состояния.
     *
     * @return Builder|Model
     */
    protected function startCondition(): Builder|Model
    {
        return clone $this->model;
    }
}

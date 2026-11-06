<?php

namespace App\Http\Controllers;

use App\Enums\FileTypes;
use App\Exceptions\FilterException;
use App\Interfaces\HasFilesInterface;
use App\Interfaces\IsConstantInterface;
use App\Models\File;
use App\Responses\FileResponse;
use App\Responses\SimpleResponse;
use App\Services\FileService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Throwable;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private const PER_PAGE = 'per_page'; // Determine page size

    private const DONT_PAGINATE = 'dont_paginate'; // Avoid Pagination

    protected const PAGE_SIZE = 15;

    private array $errors = [];

    private array $debug = [];

    protected string $modelName;

    protected bool $female = false;

    protected bool $onlyAction = false;

    private SimpleResponse $simpleResponse;

    private FileResponse $fileResponse;

    private FileService $fileService;

    public function __construct()
    {
        $this->simpleResponse = resolve(SimpleResponse::class);
        $this->fileResponse = resolve(FileResponse::class);
        $this->fileService = resolve(FileService::class);
    }

    /**
     * Builds up and returns a json response that can includes specific data.
     */
    public function dataResponse(string $message, ?array $data = null, int $code = Response::HTTP_OK): JsonResponse
    {
        return $this->simpleResponse->jsonData($message, $data, $code);
    }

    /**
     * Basic show response with a single Resource.
     */
    public function showResponse(JsonResource $resource, ?string $verb = 'showed', ?int $code = Response::HTTP_OK): JsonResponse
    {
        if ($resource->resource instanceof Model) {
            $this->loadRelations($resource->resource);
            $this->loadCounts($resource->resource);
        }

        return $this->simpleResponse->resource($this->buildMessage($verb), $resource, $code);
    }

    /**
     * Basic index response with a collection of Resources.
     */
    public function indexResponse(JsonResource $resource, ?string $verb = 'index', ?int $code = Response::HTTP_OK): JsonResponse
    {
        //        $items = collect($resource->toArray(request()));
        //        $first = $items->first() ?? [];
        //
        //        $resource->additional([
        //            'totals' => collect(array_keys($first))
        //                ->filter(function($attribute) use ($first) {
        //                    return is_numeric($first[$attribute]) && !preg_match('/id/', $attribute);
        //                })
        //                ->flatMap(function ($attribute) use ($items) {
        //                    return [$attribute => $items->sum($attribute)];
        //                })->toArray()
        //        ]);

        /** @var JsonResource|null $itemsResource */
        $itemsResource = $resource->resource->first();
        if ($itemsResource && $itemsResource->resource instanceof IsConstantInterface) {
            $this->loadRelations($itemsResource);
        }

        return $this->simpleResponse->resource($this->buildMessage($verb, true), $resource, $code);
    }

    /**
     * Basic store response with a single Resource.
     */
    public function storeResponse(JsonResource $resource, ?string $verb = 'created', ?int $code = Response::HTTP_CREATED): JsonResponse
    {
        return $this->simpleResponse->resource($this->buildMessage($verb), $resource, $code);
    }

    /**
     * Basic update response with a single Resource.
     */
    public function updateResponse(JsonResource $resource, ?string $verb = 'updated', ?int $code = Response::HTTP_ACCEPTED): JsonResponse
    {
        return $this->simpleResponse->resource($this->buildMessage($verb), $resource, $code);
    }

    /**
     * Basic destroy response. A Resource can be specified.
     */
    public function destroyResponse(?JsonResource $resource = null, ?string $verb = 'deleted', ?int $code = Response::HTTP_ACCEPTED): JsonResponse
    {
        $message = $this->buildMessage($verb);

        return $resource
            ? $this->simpleResponse->resource($message, $resource, $code)
            : $this->simpleResponse->jsonData($message, null, Response::HTTP_ACCEPTED);
    }

    public function emptyResponse(?string $message = null): JsonResponse
    {
        return $this->dataResponse($message ?? trans('help.no_content'), null, Response::HTTP_NO_CONTENT);
    }

    public function exceptionResponse(string|Throwable $exception, ?int $code = Response::HTTP_FORBIDDEN, ?array $data = []): JsonResponse
    {
        return $this->simpleResponse->exception($exception, $code, $data);
    }

    /**
     * Force browser to download given file
     *
     * @throws BindingResolutionException|FileNotFoundException
     */
    public function fileResponse(File $file): Response
    {
        return $this->fileResponse->file($file, request('stream') ?? false);
    }

    /**
     * Force browser to download file zip
     *
     * @throws BindingResolutionException
     */
    public function zipResponse(array $fileIds): Response
    {
        return $this->fileResponse->zip($fileIds);
    }

    /**
     * Call delete() method on a given model. If all goes ok, it will return true.
     *
     * @throws AuthorizationException
     */
    public function destroyModel(?Model $model): ?Model
    {
        if (! $model) {
            return null;
        }

        try {
            $model->delete();

            return $model;
        } catch (Exception $e) {
            throw new AuthorizationException(trans('exceptions.could_not_delete'));
        }
    }

    /**
     * Executes 'load()' method on model to get data from relationships.
     * It will look for a 'with' input in request body. $with can be a string or an array of strings.
     *
     * @param  Model|Collection|JsonResource  $model
     */
    protected function loadRelations($model): Model
    {
        $relations = collect(request('with') ?? [])
            ->map(fn ($relation) => Str::camel($relation))
            ->toArray();

        return $model->load($relations);
    }

    /**
     * @param  Model|Collection|JsonResource  $model
     */
    protected function loadCounts($model): Model
    {
        $relations = collect(request('counts') ?? [])
            ->map(fn ($relation) => Str::camel($relation))
            ->filter(fn ($relation) => method_exists($model, $relation))
            ->toArray();

        return $model->loadCount($relations);
    }

    /**
     * It will try to save a single file detected in request under 'file' input
     */
    public function saveFileTo(HasFilesInterface $model, string $input = 'file', ?FileTypes $fileType = null): ?File
    {
        return $this->fileService->saveFile($model, $this->getFile($input), $fileType);
    }

    /**
     * It will try to save all files detected in request under 'files' of 'file' input
     */
    public function saveFilesTo(HasFilesInterface $model, string $input = 'files', ?string $fileType = null): Collection
    {
        $files = $this->getFiles($input);

        if ($files->isEmpty()) {
            $files = collect([$this->getFile($input)])
                ->filter();
        }

        if ($files->isEmpty()) {
            $files = collect([$this->getFile()]);
        }

        return $this->fileService->saveFiles($model, $files, $fileType ? FileTypes::from($fileType) : null);
    }

    /**
     * It will look for files in request. All Files must come in a 'files' input
     * it will return a Collection of UploadedFile
     */
    public function getFiles(?string $input = 'files'): Collection
    {
        $files = request()->allFiles()[$input] ?? [];

        return collect($files);
    }

    /**
     * It will look for a single file in request. The File must come in a 'file' input
     * If not found, it will check for files in 'files' input and will get the first one
     */
    public function getFile(?string $input = 'file'): ?UploadedFile
    {
        if ($file = request()->file($input)) {
            return $file;
        }

        return $this->getFiles()->first();
    }

    /**
     * Little helper for controller's messages. It builds up and translate a message
     */
    public function buildMessage(string $action, bool $plural = false): string
    {
        if (Lang::has("actions.$action")) {
            $action = trans_choice("actions.$action", $this->female ? 2 : 1);
        }

        if ($this->onlyAction) {
            return $action;
        }

        $modelName = Str::lower($this->getModelName());

        $model = trans_choice("models.$modelName", $plural ? 2 : 1);

        return "$model $action";
    }

    /**
     * Helper for retrieving Model name. It calculates the model name by the Controller name
     * Controller must keep naming convention (plural) of models controlled.
     * For changing the ModelName manually, modify $modelName property.
     */
    private function getModelName(): string
    {
        // Get the ModelName from Controller's name. Example: DeliveryNoteController -> delivery_note
        return $this->modelName ?? Str::snake(str_replace('Controller', '', class_basename($this)));
    }

    /**
     * Executes the query builder to get data from database.
     * It will look for a 'with' input in request body. If exits, it will eager load relations
     * If a 'per_page' input is detected, it will be paginated.
     *
     * @throws FilterException
     */
    public function getOrPaginate(Builder $builder): LengthAwarePaginator|Collection
    {
        $result = collect();

        try {
            if (request(self::DONT_PAGINATE)) {
                $result = $builder->get();
            } else {
                $result = $builder->paginate(abs(request(self::PER_PAGE) ?? self::PAGE_SIZE));
            }
        } catch (Throwable $throwable) {
            if ($throwable instanceof RelationNotFoundException) {
                preg_match('/\[(.+?)]/', $throwable->getMessage(), $match);
                $this->errors['with'] = trans('exceptions.no_relation', ['data' => $match[1] ?? '']);
            } else {
                $this->errors['error'] = trans('exceptions.server_error');
                $this->debug[] = $throwable->getMessage();
            }
        }

        if (count($this->errors)) {
            $exception = new FilterException(trans('exceptions.filter'));
            $exception->setErrors($this->errors);
            $exception->setDebugData($this->debug);
            throw $exception;
        }

        return $result;
    }
}

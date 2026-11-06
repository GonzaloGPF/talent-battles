<?php

namespace App\Http\Controllers;

use App\Events\DownloadCreated;
use App\Exceptions\FilterException;
use App\Http\Resources\DownloadResource;
use App\Interfaces\HasDownloadsInterface;
use App\Models\Download;
use App\Rules\ModelInterfaceRule;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class DownloadController
 *
 * @group Downloads
 */
class DownloadController extends Controller
{
    protected bool $female = true;

    /**
     * DownloadController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:sanctum')->except('index');
    }

    /**
     * Display a listing of the Download.
     *
     * @throws FilterException
     */
    public function index(): JsonResponse
    {
        $downloads = $this->getOrPaginate(Download::filter(request()->all()));

        return $this->indexResponse(DownloadResource::collection($downloads));
    }

    /**
     * Store a newly created Download in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'model_type' => ['required', new ModelInterfaceRule(HasDownloadsInterface::class)],
            'model_id' => ['required'],
            'file_id' => ['required', Rule::exists('files', 'id')],
        ]);

        /** @var HasCreatedBy|HasDownloadsInterface|Model $model */
        $model = morphedModel($request->get('model_type'), $request->get('model_id'));

        /** @var Download $download */
        $download = $model
            ->downloads()
            ->where('created_by', auth()->id())
            ->first();

        if ($model->created_by !== auth()->id() && ! $download) {
            /** @var Download $download */
            $download = (new Download)
                ->downloable()
                ->associate($model);

            $created = $download->save();
            DownloadCreated::dispatch($download);
        }

        if (! $download) {
            return $this->emptyResponse();
        }

        return $this->storeResponse((new DownloadResource($download))->additional(['created' => $created ?? false]));
    }
}

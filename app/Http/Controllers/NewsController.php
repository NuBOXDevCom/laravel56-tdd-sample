<?php

namespace App\Http\Controllers;

use App\News;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Class NewsController
 * @package App\Http\Controllers
 */
class NewsController extends Controller
{
    /**
     * @return JsonResponse|null
     */
    public function store(): ?JsonResponse
    {
        $this->validator(request()->all())->validate();
        $news = $this->createOrUpdate(request()->all());
        return request()->wantsJson() ? response()->json($news, 201) : null;
    }

    /**
     * @param array $data
     * @return ValidatorContract
     */
    private function validator(array $data): ValidatorContract
    {
        return Validator::make($data, [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image' => 'mimes:jpeg,png,gif|nullable'
        ]);
    }

    /**
     * @param array $data
     * @param News|null $news
     * @return News
     */
    private function createOrUpdate(array $data, ?News $news = null): News
    {
        if (isset($data['image'])) {
            $data['image_path'] = $data['image']->store('news');
            if (optional($news)->image_path && Storage::disk('local')->exists($news->image_path)) {
                Storage::disk('local')->delete($news->image_path);
            }
        }
        if ($news) {
            $news->update($data);
        } else {
            $news = News::create($data);
        }
        return $news;
    }

    /**
     * @param News $news
     * @return JsonResponse|null
     */
    public function update(News $news): ?JsonResponse
    {
        $this->validator(request()->all())->validate();
        $news = $this->createOrUpdate(request()->all(), $news);
        return request()->wantsJson()
            ? response()->json($news, 200)
            : null;
    }
}

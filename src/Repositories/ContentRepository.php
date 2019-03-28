<?php

namespace Laradium\Laradium\Content\Repositories;

use Illuminate\Support\Facades\Route;
use Laradium\Laradium\Content\Models\Page;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ContentRepository
{
    /**
     * @param $key
     * @return object
     * @throws \Exception
     */
    public function getPage($key)
    {
        $pages = cache()->rememberForever('laradium::content-pages-' . app()->getLocale(), function () {
            return Page::get();
        });

        $page = $pages->where('key', $key)->first();
        if (!$page) {
            return (object)[
                'title' => '',
                'url'   => url('/'),
                'slug'  => ''
            ];
        }

        return (object)[
            'title' => $page->title,
            'url'   => url($page->slug),
            'slug'  => $page->slug
        ];
    }

    /**
     * @param $pages
     */
    public function put($pages)
    {
        foreach ($pages as $page) {
            $p = \Laradium\Laradium\Content\Models\Page::create(array_except($page,
                ['translations', 'data', 'channel', 'content']));

            foreach (translate()->languages() as $lang) {
                $page['translations']['locale'] = $lang->iso_code;
                $p->translations()->firstOrCreate($page['translations']);
            }

            if (isset($page['channel'])) {
                $channel = new $page['channel'];
                $model = $channel->model;
                $data = $page['content'];

                $model = new $model;
                $model->fill(array_except($data, ['relations', 'translations', 'file']));
                $model->save();

                $p->update([
                    'content_type' => $channel->model,
                    'content_id'   => $model->id,
                ]);

                if (isset($data['translations'])) {
                    $this->putTranslations($data['translations'], $model);
                }

                if (isset($data['file'])) {
                    $this->putFiles($model, $data['file']);
                }

                if (isset($data['relations'])) {
                    $this->putRelations($data['relations'], $model);
                }
            }

            if (isset($page['data'])) {
                $data = $page['data'];
                $i = 1;

                foreach ($data as $item) {
                    $model = new $item['widget'];
                    $model->fill(array_except($item['data'], ['relations', 'translations', 'file']));
                    $model->save();
                    $p->blocks()->create([
                        'sequence_no' => $i,
                        'block_type'  => $item['widget'],
                        'block_id'    => $model->id
                    ]);
                    $i++;

                    if (isset($item['data']['translations'])) {
                        $this->putTranslations($item['data']['translations'], $model);
                    }

                    if (isset($item['data']['file'])) {
                        $this->putFiles($model, $item['data']['file']);
                    }

                    if (isset($item['data']['relations'])) {
                        $this->putRelations($item['data']['relations'], $model);
                    }

                }
            }
        }
    }

    /**
     * return void
     */
    public function pageRoute(): void
    {
        $middleWares = config('laradium-content.resolver.middlewares', ['web']);
        $customUri = config('laradium-content.resolver.custom_uri');
        $prependLocale = config('laradium-content.resolver.prepend_locale', false);
        $uses = config('laradium-content.resolver.uses', '\Laradium\Laradium\Content\Http\Controllers\Admin\PageController@resolve');

        $uri = ($prependLocale ? '/{locale?}' : '') . '/{slug?}';
        if (function_exists($customUri)) {
            $uri = $customUri();
        }

        Route::get($uri, [
            'uses' => $uses
        ])->middleware($middleWares)->where('slug', '(.*)')->name('page.resolve');
    }

    /**
     * @param $translations
     * @param $model
     */
    protected function putTranslations($translations, $model)
    {
        foreach (translate()->languages() as $lang) {
            $translations['locale'] = $lang->iso_code;
            $tran = $model->translations()->firstOrCreate(
                [
                    'locale' => $lang->iso_code
                ],
                array_except($translations, 'file')
            );

            if (isset($translations['file'])) {
                $this->putFiles($tran, $translations['file']);
            }
        }
    }

    /**
     * @param $relations
     * @param $model
     */
    protected function putRelations($relations, $model)
    {
        foreach ($relations as $relation => $items) {
            foreach ($items as $item) {
                $relationModel = $model->{$relation}()->create(array_except($item,
                    ['translations', 'file', 'relations']));

                if (isset($item['file'])) {
                    $this->putFiles($relationModel, $item['file']);
                }

                if (isset($item['translations'])) {
                    $this->putTranslations($item['translations'], $relationModel);
                }

                if (isset($item['relations'])) {
                    $this->putRelations($item['relations'], $relationModel);
                }
            }
        }
    }

    /**
     * @param $model
     * @param $file
     */
    private function putFiles($model, $file)
    {
        foreach ($file as $key => $path) {
            $image = new \Symfony\Component\HttpFoundation\File\File($path);
            $img = new UploadedFile($image, $image->getBasename(), $image->getMimeType(), null, null, true);

            $model->{$key} = $img;

            $model->save();
        }

    }
}
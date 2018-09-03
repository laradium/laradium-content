<?php

namespace Laradium\Laradium\Content\Repositories;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ContentRepository
{

    /**
     * @param $pages
     */
    public function put($pages)
    {
        foreach ($pages as $page) {
            $p = \Laradium\Laradium\Content\Models\Page::create(array_except($page, ['translations', 'data']));
            foreach (translate()->languages() as $lang) {
                $page['translations']['locale'] = $lang['iso_code'];
                $p->translations()->firstOrCreate($page['translations']);
            }
            $data = $page['data'];
            $i = 1;
            foreach ($data as $className => $items) {
                $model = new $className;
                $model->where('id', '>=', 1)->delete();
                $model->fill(array_except($items, ['relations', 'translations', 'file']));
                $model->save();
                $p->blocks()->create([
                    'sequence_no' => $i,
                    'block_type'  => $className,
                    'block_id'    => $model->id
                ]);
                $i++;

                if (isset($items['translations'])) {
                    $this->putTranslations($items['translations'], $model);
                }

                if (isset($items['file'])) {
                    $this->putFiles($model, $items['file']);
                }

                if (isset($items['relations'])) {
                    $this->putRelations($items['relations'], $model);
                }

            }
        }
    }

    /**
     * @param $translations
     * @param $model
     */
    protected function putTranslations($translations, $model)
    {
        foreach (translate()->languages() as $lang) {
            $translations['locale'] = $lang['iso_code'];
            $model->translations()->firstOrCreate(
                [
                    'locale' => $lang['iso_code']
                ],
                $translations
            );
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
                $relationModel = $model->{$relation}()->create(array_except($item, ['translations', 'file', 'relations']));

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
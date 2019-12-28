<?php

namespace Laradium\Laradium\Content\Base\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Laradium\Laradium\Base\AbstractResource;
use Laradium\Laradium\Base\ColumnSet;
use Laradium\Laradium\Base\FieldSet;
use Laradium\Laradium\Base\Resource;
use Laradium\Laradium\Base\Table;
use Laradium\Laradium\Content\Models\Page;
use Laradium\Laradium\Content\Registries\ChannelRegistry;
use ReflectionException;

class PageResource extends AbstractResource
{

    /**
     * @var string
     */
    protected $resource = Page::class;

    /**
     * @var array
     */
    protected $actions = [
        'edit',
        'delete'
    ];

    /**
     * @var ChannelRegistry
     */
    private $channelRegistry;

    /**
     * @var bool
     */
    protected $withoutCard = true;

    /**
     * PageResource constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->channelRegistry = app(ChannelRegistry::class);
    }

    /**
     * @return View
     */
    public function index()
    {
        $this->builder->components(function (FieldSet $set) {
            $set->col(12)->fields(function (FieldSet $set) {
                $set->breadcrumbs($this->getBreadcrumbs('index'));
            });

            $set->block(12)->fields(function (FieldSet $set) {
                $set->customContent(function () {
                    return view('laradium-content::admin.pages.index-top', [
                        'resource' => $this,
                    ], [
                        'channels' => $this->channelRegistry->all()
                    ])->render();
                });

                $set->table($this->table()
                    ->url($this->getAction('data-table'))
                    ->toggleUrl($this->getAction('toggle'))
                    ->make($this->addActionColumn())
                );
            });
        });

        return view($this->layout->getView('index'), [
            'resource' => $this,
            'builder'  => $this->builder,
            'layout'   => $this->layout
        ]);
    }

    /**
     * @return Resource
     */
    public function resource()
    {
        $model = $this->getModel();

        $channelInstance = $this->getChannelInstance($model);
        $pages = $this->getPages();

        $this->event('afterDelete', function ($page) {
            if ($content = $page->content) {
                $content->delete();
            }
        });

        return laradium()->resource(function (FieldSet $set) use ($channelInstance, $pages, $model) {
            $set->col(9)->fields(function (FieldSet $set) use ($channelInstance, $model) {
                $set->tabs()
                    ->add('Main data', function (FieldSet $set) use ($channelInstance, $model) {
                        $set->block(12)->fields(function (FieldSet $set) use ($channelInstance, $model) {
                            $set->text('title')->rules('required|max:255')->translatable()->col(6);
                            $set->text('slug')
                                ->rules('max:255')
                                ->translatable()
                                ->col(6)
                                ->label($this->getSlugLabel($model));

                            $channelInstance->fields($set);
                        });
                    })
                    ->add('SEO Meta', function (FieldSet $set) {
                        $set->block(12)->fields(function (FieldSet $set) {
                            $set->text('meta_keywords')->translatable()->col(6);
                            $set->text('meta_title')->translatable()->col(6);
                            $set->textarea('meta_description')->translatable();
                            $set->file('meta_image')->rules('max:' . config('laradium.file_size', 2024));
                            $set->boolean('meta_noindex')->label('Noindex and nofollow for robots')->translatable();
                        });
                    })
                    ->add('Options', function (FieldSet $set) {
                        $set->block(12)->fields(function (FieldSet $set) {
                            $set->text('css_class')->info('You can add multiple css classes separating them by space');
                        });
                    });
            });


            $set->col(3)->fields(function (FieldSet $set) use ($pages, $model) {
                $set->block(12)->fields(function (FieldSet $set) use ($pages, $model) {
                    $set->languageSelect();

                    $set->select('layout')->options(config('laradium-content.layouts', ['layouts.main' => 'Main']));
                    $set->select2('parent_id')->options($pages)->label('Parent');
                    $set->boolean('is_active')->col(6);
                    $set->boolean('is_homepage')->col(6);

                    $set->saveButtons()->fields(function (FieldSet $set) use ($model) {
                        if ($model->exists) {
                            $set->link('Preview', 'javascript:;')->attr([
                                'id'         => 'preview-page',
                                'class'      => 'btn btn-primary mb-1 mr-1',
                                'target'     => '_blank',
                                'data-links' => json_encode($this->getPageLinks($model))
                            ]);

                            $set->customContent('<button class="btn btn-primary mb-1" id="duplicate-page" data-url="' . route('admin.pages.duplicate',
                                    $model) . '">Duplicate</button>')->attributes([
                                'style' => 'display: inline-block;'
                            ]);
                        }
                    })->withoutLanguageSelect();
                })->attributes([
                    'id' => 'page-sidebar'
                ]);
            });

        })->js([
            versionedAsset('laradium/assets/js/page.js')
        ]);
    }

    /**
     * @return Table
     */
    public function table()
    {
        return laradium()->table(function (ColumnSet $column) {
            $column->add('title')->translatable();

            if (Route::has('page.resolve')) {
                $column->add('slug')->modify(function ($item) {
                    return view('laradium-content::admin.pages._partials.slug', [
                        'item'   => $item,
                        'column' => 'slug'
                    ])->render();
                })->notSortable()->notSearchable()->raw();
            } else {
                $column->add('slug')->translatable();
            }

            $column->add('seo_optimized')->modify(function ($item) {
                return $this->checkSeoStatus($item);
            })->notSortable()->notSearchable()->raw();

            $column->add('is_active', 'Is Visible?')->switchable()->raw();
            $column->add('content_type', 'Type')->modify(function ($item) {
                if ($item->content_type) {
                    return array_last(explode('\\', $item->content_type));
                }

                return 'Main';
            });
        });
    }

    /**
     * @param Request $request
     * @param Page $page
     * @return JsonResponse
     * @throws ReflectionException
     */
    public function duplicate(Request $request, Page $page): JsonResponse
    {
        $data = $request->all();
        foreach ($data['translations'] as $locale => $translations) {
            if ($translations['title']) {
                $data['translations'][$locale]['title'] = $translations['title'] . ' copy';
            }

            if ($translations['slug']) {
                $data['translations'][$locale]['slug'] = $translations['slug'] . '-copy';
            }

            $data['is_active'] = false;
        }


        $this->recursiveUnsetIds($data);
        $this->model(new Page());

        $model = $this->saveData($data, $this->getModel());

        return response()->json([
            'success' => true,
            'data'    => [
                'redirect_to' => route('admin.pages.edit', $model)
            ]
        ]);
    }

    /**
     * @param $array
     * @return bool
     */
    private function recursiveUnsetIds(&$array): bool
    {
        foreach ($array as $index => &$value) {
            if (in_array($index, ['id']) && !is_numeric($index)) {
                unset($array[$index]);
            }

            if (is_array($value)) {
                if (array_get($value, 'remove', null)) {
                    unset($array[$index]);
                }

                $this->recursiveUnsetIds($value, ['id']);
            }
        }

        return true;
    }

    /**
     * @return array
     */
    private function getPages(): array
    {
        $pages = [null => '-- Select --'];
        $pageList = Page::where('id', '!=', $this->getModel()->id)->get()->mapWithKeys(function ($page) {
            return [(string)$page->id => $page->title];
        })->toArray();
        $pages += $pageList;

        return $pages;
    }

    /**
     * @param $model
     * @return mixed
     */
    private function getChannelInstance($model)
    {
        $channelName = request()->get('channel', 'main');
        $channelModel = null;
        if ($model->exists && $model->content_type) {
            $channelModel = $model->content_type;
        }
        $channel = $this->getChannel($channelModel, $channelName);

        return new $channel['class'];
    }

    /**
     * @param $channelModel
     * @param $channelName
     * @return array
     */
    private function getChannel($channelModel, $channelName): array
    {
        $channelRegistry = $this->channelRegistry->all();
        $channel = $channelRegistry->where('model', $channelModel)->first();

        if ($channelModel && $channel) {
            return $channel;
        }

        return $channelRegistry->where('name', $channelName)->first();
    }

    /**
     * Get overall SEO status based on filled/empty SEO values.
     *
     * @param Page $item
     * @return string
     */
    private function checkSeoStatus(Page $item): string
    {
        $nonTranslatableSeoFields = ['meta_image_file_name'];
        $translatableSeoFields = ['meta_keywords', 'meta_title', 'meta_description'];

        $translationsCount = $item->translations->count();
        $totalSeoFields = $translationsCount * count($translatableSeoFields) + count($nonTranslatableSeoFields);
        $percentPerField = 100 / $totalSeoFields;
        $score = 0;

        foreach ($nonTranslatableSeoFields as $nonTranslatableSeoField) {
            if ($item->{$nonTranslatableSeoField}) {
                $score += $percentPerField;
            }
        }

        foreach ($item->translations as $translation) {
            foreach ($translatableSeoFields as $translatableSeoField) {
                if ($translation->{$translatableSeoField}) {
                    $score += $percentPerField;
                }
            }
        }

        if ($score > 100) {
            $score = 100;
        }

        if ($score >= 95) {
            $labelClass = 'badge-success';
            $labelText = 'Very good';
        } else {
            if ($score >= 70) {
                $labelClass = 'badge-info';
                $labelText = 'Good';
            } else {
                if ($score >= 50) {
                    $labelClass = 'badge-warning';
                    $labelText = 'Average';
                } else {
                    if ($score >= 40) {
                        $labelClass = 'badge-danger';
                        $labelText = 'Bad';
                    } else {
                        $labelClass = 'badge-danger';
                        $labelText = 'Very bad';
                    }
                }
            }
        }

        return '<label class="badge ' . $labelClass . '">' . $labelText . ' (' . (int)$score . '%)</label>';
    }

    /**
     * @param Page $model
     * @return array
     */
    private function getPageLinks(Page $model): array
    {
        $prependLocale = config('laradium-content.resolver.prepend_locale', false);

        $links = [];
        foreach (translate()->languages() as $language) {
            $translation = $model->translations->where('locale', $language->iso_code)->first();

            if (!$translation) {
                continue;
            }

            $preSlug = '';
            if ($model->parent && get_class($model) === Page::class) {
                $preSlug = $model->getParentSlugsByLocale($model->parent, $language->iso_code) . '/';
            }

            if ($translation->slug) {
                $links[] = [
                    'iso_code' => $language->iso_code,
                    'url'      => config('laradium-content.page_preview_url',
                            url('/')) . '/' . ($prependLocale ? $language->iso_code . '/' . $preSlug . $translation->slug : $preSlug . $translation->slug) . '?preview=true'
                ];
            }
        }

        return $links;
    }

    /**
     * @param $model
     * @return string
     */
    private function getSlugLabel($model): string
    {
        $slug = 'Slug';
        if (!$model->exists) {
            return $slug;
        }

        if ($model->parent) {
            return $slug . ' (<small><b>pre slug</b> ' . $model->parent_slugs . '</small>)';
        }

        return $slug;
    }
}

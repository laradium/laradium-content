<?php

namespace Laradium\Laradium\Content\Base\Resources\Api;

use Laradium\Laradium\Base\AbstractApiResource;
use Laradium\Laradium\Content\Models\Page;
use Laradium\Laradium\Content\Registries\WidgetRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageApiResource extends AbstractApiResource
{

    /**
     * @var string
     */
    protected $resource = Page::class;

    /**
     * PageApiResource constructor.
     */
    public function __construct()
    {
        $this->setApi();

        parent::__construct();
    }

    /**
     * @param null $locale
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($locale = null)
    {
        try {
            $pages = $this->getModel()::active()->get()->map(function ($item) use ($locale) {
                return $this->response($item, $locale);
            });

            return response()->json([
                'success' => true,
                'data'    => $pages
            ]);
        } catch (\Exception $e) {
            logger()->error($e);

            abort(500);
        }
    }

    /**
     * @param null $locale
     * @param null $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($locale = null, $slug = null)
    {
        try {
            $prependLocale = config('laradium-content.api.prepend_locale', false);

            if (!$prependLocale && !$slug) {
                $slug = $locale;
            } else if ($locale) {
                app()->setLocale($locale);
            }

            $page = null;

            if ($slug === 'homepage') {
                $page = $this->getModel()::whereIsHomepage(true)
                    ->active()
                    ->first();

                abort_if(!$page, 404);
            }

            if (!$page) {
                $locale = app()->getLocale();
                $page = $this->getModel()::active()
                    ->whereHas('translations', function ($q) use ($slug, $locale) {
                        $q->whereSlug($slug)->whereLocale($locale);
                    })->first();
            }

            $parentSlugs = $this->getParentSlugs($slug);
            $hasPageAndParent = $page && $page->parent;

            if ($hasPageAndParent && $page->parent_slugs !== $parentSlugs) {
                $page = null;
            }

            if (!$page) {
                $page = $this->getModel()::active()
                    ->whereHas('translations', function ($q) use ($slug, $locale) {
                        $q->whereSlug(array_last(explode('/', $slug)))
                            ->whereLocale($locale);
                    })->first();

                abort_if(!$page, 404);

                $hasSameParent = $page->parent_slugs === $parentSlugs;

                abort_if(!$page->parent || !$hasSameParent, 404);
            }

            $data = $this->responseWithContent($page, $locale);

            return response()->json([
                'success' => true,
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            if (!$e instanceof NotFoundHttpException) {
                logger()->error($e);
            }

            abort($e instanceof NotFoundHttpException ? 404 : 500);
        }
    }

    /**
     * @param Page $page
     * @param null $locale
     * @return array
     */
    private function response(Page $page, $locale = null): array
    {
        $translation = $page->translateOrNew($locale);

        return [
            'page' => [
                'key'         => $page->key,
                'title'       => $translation->title,
                'slug'        => $translation->slug,
                'is_homepage' => $page->is_homepage,
            ],
        ];
    }

    /**
     * @param Page $page
     * @param null $locale
     * @return array
     */
    private function responseWithContent(Page $page, $locale = null): array
    {
        $translation = $page->translateOrNew($locale);

        return [
            'page'    => [
                'key'         => $page->key,
                'title'       => $translation->title,
                'slug'        => $translation->slug,
                'is_homepage' => $page->is_homepage,
            ],
            'channel' => $this->channel($page, $locale),
            'widgets' => $this->widgets($page, $locale),
        ];
    }

    /**
     * @param Page $page
     * @param $locale
     * @return mixed
     */
    private function channel(Page $page, $locale)
    {
        if (!$page->content_type) {
            return null;
        }

        $channel = array_last(explode('\\', $page->content_type));
        $channel = config('laradium-content.channel_path') . '\\' . $channel . 'Channel';
        $channel = new $channel;

        if (!method_exists($channel, 'response')) {
            return null;
        }

        return $channel->response($page->content, $locale);
    }

    /**
     * @param Page $page
     * @param $locale
     * @return array
     */
    private function widgets(Page $page, $locale): array
    {
        $widgetRegistry = app(WidgetRegistry::class);
        $widgets = $widgetRegistry->all()->mapWithKeys(function ($model, $widget) {
            return [$model => $widget];
        })->toArray();
        $widgetList = collect([]);

        foreach ($page->blocks->load('block')->sortBy('sequence_no') as $block) {
            if ($widget = $this->getWidget($widgets[$block->block_type], $block->block, $locale)) {
                if ($count = $widgetList->where('name', $widget['name'])->count()) {
                    $widget['id'] = $count;
                }

                $widgetList->push($widget);
            }
        }

        return $widgetList->toArray();
    }

    /**
     * @param $widget
     * @param $data
     * @param $locale
     * @return array|bool
     */
    private function getWidget($widget, $data, $locale)
    {
        if (!$widget) {
            return false;
        }

        $widget = new $widget;
        if (!method_exists($widget, 'response')) {
            return false;
        }

        $widgetName = array_last(explode('\\', get_class($widget)));
        $widgetName = strtolower(preg_replace('/\B([A-Z])/', '-$1', $widgetName));

        return [
            'name' => $widgetName,
            'data' => $widget->response($data, $locale)
        ];
    }

    /**
     * @param string $slug
     * @return string
     */
    private function getParentSlugs($slug): ?string
    {
        $slugParts = explode('/', $slug);
        if (count($slugParts) <= 1) {
            return $slug;
        }

        unset($slugParts[count($slugParts) - 1]);

        return implode($slugParts, '/');
    }

    /**
     * Set api routes
     */
    private function setApi(): void
    {
        $apiEnabled = config('laradium-content.api.enabled', false);
        if ($apiEnabled) {
            $prependLocale = config('laradium-content.api.prepend_locale', false);
            $uri = $prependLocale ? '{locale?}/' : '';

            $this->customRoutes = [
                'index' => [
                    'method' => 'GET',
                    'params' => $uri
                ],
                'show'  => [
                    'method' => 'GET',
                    'params' => $uri . '{slug?}',
                    'where'  => [
                        'slug' => '(.*)'
                    ]
                ],
            ];
        }
    }
}
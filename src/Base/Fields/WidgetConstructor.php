<?php

namespace Laradium\Laradium\Content\Base\Fields;

use Illuminate\Database\Eloquent\Model;
use Laradium\Laradium\Base\Field;
use Laradium\Laradium\Base\Fields\Boolean;
use Laradium\Laradium\Base\Fields\HasMany;
use Laradium\Laradium\Base\Fields\Hidden;
use Laradium\Laradium\Base\Fields\MorphTo;
use Laradium\Laradium\Base\Fields\Text;
use Laradium\Laradium\Base\FieldSet;
use Laradium\Laradium\Content\Models\ContentBlock;
use Laradium\Laradium\Content\Registries\WidgetRegistry;
use Laradium\Laradium\Traits\Sortable;

class WidgetConstructor extends Field
{

    use Sortable;

    /**
     * @var
     */
    private $fields;

    /**
     * @var
     */
    private $fieldName;

    /**
     * @var
     */
    private $actions = ['create', 'delete'];

    /**
     * @var array
     */
    private $templateData = [];

    /**
     * @var bool
     */
    private $isCollapsed = true;

    /**
     * @var string
     */
    private $entryLabel = 'name';

    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    private $widgetRegistry;

    /**
     * HasMany constructor.
     * @param $parameters
     * @param Model $model
     */
    public function __construct($parameters, Model $model)
    {
        parent::__construct($parameters, $model);

        $this->fieldName('blocks');
        $this->widgetRegistry = app(WidgetRegistry::class);
    }

    /**
     * @param array $attributes
     * @return $this|Field
     */
    public function build($attributes = [])
    {
        parent::build($attributes);
        $this->sortable();

        $this->templateData = $this->getTemplateData();
        $this->validationRules($this->templateData['validation_rules']);

        return $this;
    }

    /**
     * @return array
     */
    public function formattedResponse()
    {
        $data = parent::formattedResponse();
        $data['value'] = HasMany::class;

        $data['blocks'] = $this->getEntries();
        $data['template_data'] = $this->templateData;
        $data['config']['is_sortable'] = $this->isSortable();
        $data['config']['actions'] = $this->getActions();

        return $data;
    }

    /**
     * @return array
     */
    private function getTemplateData()
    {
        $validationRules = [];
        $blocks = [];

        $this->addReplacementAttribute();
        $lastReplacementAttribute = [array_last($this->getReplacementAttributes())];
        foreach ($this->widgetRegistry->all() as $widgetClass => $modelClass) {
            $fields = [];
            $model = new $modelClass;
            $widget = new $widgetClass;
            $item = new ContentBlock;

            if ($this->isSortable()) {
                $fields[] = (new Hidden('sequence_no', $item))
                    ->replacementAttributes($this->getReplacementAttributes())
                    ->build(array_merge($this->getAttributes(), $lastReplacementAttribute))
                    ->value($item->count())
                    ->formattedResponse(); // Add hidden sortable field
            }

            $morphTo = (new MorphTo([get_class($model), 'block'], $item))
                ->attributes(array_merge($this->getAttributes(), $lastReplacementAttribute))
                ->fields(function (FieldSet $set) use ($widget) {
                    $widget->fields($set);
                })
                ->replacementAttributes($this->getReplacementAttributes())
                ->build();

            if ($morphTo->getValidationRules()) {
                $validationRules = array_merge($validationRules, $morphTo->getValidationRules());
            }

            $fields[] = (new Boolean(['is_active'], $item))
                ->replacementAttributes($this->getReplacementAttributes())
                ->col(4)
                ->value(1)
                ->build(array_merge($this->getAttributes(), $lastReplacementAttribute))
                ->formattedResponse();

            $fields[] = (new Text(['class'], $item))
                ->replacementAttributes($this->getReplacementAttributes())
                ->col(4)
                ->build(array_merge($this->getAttributes(), $lastReplacementAttribute))
                ->formattedResponse();

            $fields[] = (new Text(['style'], $item))
                ->replacementAttributes($this->getReplacementAttributes())
                ->col(4)
                ->build(array_merge($this->getAttributes(), $lastReplacementAttribute))
                ->formattedResponse();

            $fields[] = $morphTo->formattedResponse();

            $blocks[] = [
                'label'           => $this->normalizeLabel(class_basename($widget)),
                'fields'          => $fields,
                'replacement_ids' => $this->getReplacementAttributes(),
                'config'          => [
                    'is_deleted'   => false,
                    'is_collapsed' => $this->isCollapsed(),
                ],
            ];
        }

        return [
            'label'            => 'Entry',
            'blocks'           => $blocks,
            'validation_rules' => $validationRules
        ];
    }

    /**
     * @return array
     */
    private function getEntries()
    {
        $entries = [];
        foreach ($this->getModel()->blocks->sortBy($this->getSortableColumn()) as $item) {
            $fields = [];
            $model = $item->block;
            $widgetClass = $this->widgetRegistry->getByModel($item->block_type);

            if (!$widgetClass) {
                continue;
            }

            $widget = new $widgetClass;

            $fields[] = (new Hidden('id', $item))
                ->build(array_merge($this->getAttributes(), [$item->id]))
                ->formattedResponse(); // Add hidden ID field

            if ($this->isSortable()) {
                $fields[] = $this->sortableField($item); // Add hidden sortable field
            }

            $fields[] = (new MorphTo([get_class($model), 'block'], $item))
                ->attributes(array_merge($this->getAttributes(), [$item->id]))
                ->fields(function (FieldSet $set) use ($widget) {
                    $widget->fields($set);
                })
                ->build()
                ->formattedResponse();

            $fields[] = (new Boolean(['is_active'], $item))
                ->col(4)
                ->build(array_merge($this->getAttributes(), [$item->id]))
                ->formattedResponse();

            $fields[] = (new Text(['class'], $item))
                ->col(4)
                ->build(array_merge($this->getAttributes(), [$item->id]))
                ->formattedResponse();

            $fields[] = (new Text(['style'], $item))
                ->col(4)
                ->build(array_merge($this->getAttributes(), [$item->id]))
                ->formattedResponse();

            $entries[] = [
                'label'  => $this->normalizeLabel(class_basename($widget)),
                'fields' => $fields,
                'config' => [
                    'is_deleted'   => false,
                    'is_collapsed' => $this->isCollapsed(),
                ],
                'id'     => $item->id,
            ];
        }

        return $entries;
    }

    /**
     * @param $closure
     * @return $this
     */
    public function fields($closure)
    {
        $fieldSet = $this->fieldSet;
        $fieldSet->model($this->getModel());
        $closure($fieldSet);

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function actions($value)
    {
        $this->actions = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param $value
     * @return $this
     */
    public function collapse($value)
    {
        $this->isCollapsed = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCollapsed()
    {
        return $this->isCollapsed;
    }

    /**
     * @param $value
     * @return $this
     */
    public function entryLabel($value)
    {
        $this->entryLabel = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntryLabel()
    {
        return $this->entryLabel;
    }

    /**
     * @param $label
     * @return string|string[]|null
     */
    private function normalizeLabel($label)
    {
        $label = str_replace('Widget', '', $label);

        return preg_replace('/(?<!\ )[A-Z]/', ' $0', $label);
    }

}
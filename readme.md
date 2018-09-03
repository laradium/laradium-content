# Content module for Aven cms

# Installation

## For local use

1. Add this to your project repositories list in `composer.json` file

```
"repositories": [
    {
      "type": "path",
      "url": "../packages/aven-package"
    },
    {
      "type": "path",
      "url": "../packages/aven-content"
    }
  ]
```

Directory structure should look like this

```
-Project
-packages
--aven-package
--aven-content
```

## For global use

```
"repositories": [
        {
            "type": "git",
            "url": "https://git.netcore.lv/daniels.grietins/aven"
        },
        {
            "type": "git",
            "url": "https://git.netcore.lv/daniels.grietins/aven-content"
        }
    ]
```

2. ```composer require netcore/aven-content dev-master```
3. ```php artisan vendor:publish --tag=aven-content```
4. Configure `config/aven.php` file with your preferences
5. Add widgetConstructor field to you `aven.php` field list
```
'widgetConstructor' => \Netcore\Aven\Content\Aven\Fields\WidgetConstructor::class,
```

# Usage
By default there comes Main channel with widget constructor, which allows you to create sortable widget blocks for your needs.

# Creating new channels
1. Run this command (_It automatically creates model for you You can pass `--t` argument to also create translations model_)

```
php artisan aven:channel Blog
```


It will create new Channel under `App\Aven\Channels`

It should look like this

```
<?php

namespace App\Aven\Channels;

use App\Models\Channels\Blog;
use Netcore\Aven\Aven\FieldSet;
use Netcore\Aven\Content\Models\Page;

Class BlogChannel
{

    /**
     * @param FieldSet $set
     */
    public function fields(FieldSet $set)
    {
        $set->morphsTo(Blog::class, Page::class)->fields(function (FieldSet $set) {
            $set->text('author');
            $set->wysiwyg('content');
        })->morphName('content');

        $set->widgetConstructor();
    }
}
```

You need to create model for Blog channel where you can specify all your needed columns and using `morphsTo` field type you can add them to you channel

2. You need to add created channels to `ocnfig/aven-content.php` file in order to be able actually see them.

# Creating widgets

1. Run this command `php artisan aven:widget` (_It automatically creates model for you You can pass `--t` argument to also create translations model_)


It creates a widget under `App\Aven\Widgets`
```
<?php

namespace App\Aven\Widgets;

use App\Models\Widgets\Hiw;
use Netcore\Aven\Aven\FieldSet;
use Netcore\Aven\Content\Aven\AbstractWidget;

class HiwWidget extends AbstractWidget
{

    /**
     * @var string
     */
    protected $model = Hiw::class;

    /**
     * @var string
     */
    protected $view = 'widgets.HiwWidget';

    /**
     * @param FieldSet $set
     * @return mixed|void
     */
    public function fields(FieldSet $set)
    {
        $set->text('title')->translatable();
        $set->text('description')->translatable();
        $set->hasMany('items')->fields(function (FieldSet $set) {
            $set->text('title')->translatable();
            $set->text('description')->translatable();
        })->sortable('sequence_no');
    }
}
```

You can specify your fields under `fields` method.
Do not forget to add created widget in `config/aven-content.php` file under widget list
##Content module for Aven cms

###Installation

1. Add this to your project repositories list in `composer.json` file
```$xslt
"repositories": [
    {
      "type": "path",
      "url": "../packages/aven-content"
    }
  ]
```

2. ```composer require netcore/aven-content dev-master```
3. ```php artisan vendor:publish --tag=aven-content```
4. Configure `config/aven.php` file with your preferences
5. Add widgetConstructor field to you ave.php field list
```$xslt
'widgetConstructor' => \Netcore\Aven\Content\Aven\Fields\WidgetConstructor::class,
```

###Usage
By default there comes Main channel with widget constructor, which allows you to create sortable widget blocks for your needs.

## Creating new channels
1. Run this command
```$xslt
php artisan aven:channel Blog
```

It will create new Channel under `App\Aven\Channels`

It should look like this

```$xslt
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

### Creating widgets

To create new widget create new widget file under `App\Aven\Widgets` with contents

```$xslt
<?php

namespace App\Aven\Widgets;

use Netcore\Aven\Aven\FieldSet;

class HeroWidget
{

    protected $model = \App\Models\Widgets\HeroWidget::class;

    public function fields(FieldSet $set)
    {
        $set->text('key');
        $set->text('title')->translatable();
        $set->text('description')->translatable();
    }

    public function model()
    {
        return $this->model;
    }
}
```

You can specify your fields under `fields` method.
Do not forget to add created widget in `config/aven-content.php` file under widget list
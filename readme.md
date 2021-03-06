# Content module for Laradium cms

# Installation

## For local use

1. Add this to your project repositories list in `composer.json` file

```
"repositories": [
    {
      "type": "path",
      "url": "../packages/laradium"
    },
    {
      "type": "path",
      "url": "../packages/laradium-content"
    }
  ]
```

Directory structure should look like this

```
-Project
-packages
--laradium
--laradium-content
```

## For global use

```
"repositories": [
        {
            "type": "git",
            "url": "https://github.com/laradium/laradium.git"
        },
        {
            "type": "git",
            "url": "https://github.com/laradium/laradium-content.git"
        }
    ]
```

2. ```composer require laradium/laradium-content dev-master```
3. ```php artisan vendor:publish --tag=laradium-content```
4. Configure `config/laradium.php` file with your preferences
5. Add widgetConstructor field to you `laradium.php` field list
```
'widgetConstructor' => \Laradium\Laradium\Content\Base\Fields\WidgetConstructor::class,
```

# Usage
By default there comes Main channel with widget constructor, which allows you to create sortable widget blocks for your needs.

# Creating new channels
1. Run this command (_It automatically creates model for you You can pass `--t` argument to also create translations model_)

```
php artisan laradium:channel Blog
```


It will create new Channel under `App\Laradium\Channels`

It should look like this

```
<?php

namespace App\Laradium\Channels;

use App\Models\Channels\Blog;
use Laradium\Laradium\Base\FieldSet;
use Laradium\Laradium\Content\Models\Page;

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

# Creating widgets

1. Run this command `php artisan laradium:widget` (_It automatically creates model for you You can pass `--t` argument to also create translations model_)


It creates a widget under `App\Laradium\Widgets`
```
<?php

namespace App\Laradium\Widgets;

use App\Models\Widgets\Hiw;
use Laradium\Laradium\Base\FieldSet;
use Laradium\Laradium\Content\Base\AbstractWidget;

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

# Page resolver 

To actually return created pages, you need to add the page resolver route at the end of your routes file, or to your `RouteServiceProvider.php` file.

To do this, you can simply use the helper method `content()->pageRoute()`, or you can create your own route, but as the resolver route is configurable, there shouldn't be a need for that.

The config file, provides you with some options, to change the behaviour of the page resolver, in case you need some customization.

- `middlewares` - Change what middlewares the resolver route will use
- `custom_uri` - Provides you with an option, to define a function name, that will return a custom uri
- `prepend_locale` - Adds an iso code to the uri. So the url `https://example.com/my-cool-page` will become `https://example.com/en/my-cool-page`
- `uses` - Defines, what controller and method will be used to actually resolve the route. 
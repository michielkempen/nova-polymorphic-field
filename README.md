# Nova Polymorphic Field

[![Latest Version on Packagist](https://img.shields.io/packagist/v/michielkempen/nova-polymorphic-field.svg)](https://packagist.org/packages/michielkempen/nova-polymorphic-field)
[![Total Downloads](https://img.shields.io/packagist/dt/michielkempen/nova-polymorphic-field.svg)](https://packagist.org/packages/michielkempen/nova-polymorphic-field)
[![License](https://img.shields.io/packagist/l/michielkempen/nova-polymorphic-field.svg)](https://github.com/michielkempen/nova-polymorphic-field/blob/master/LICENSE.md)

### Description

...

![Scheme](https://raw.githubusercontent.com/michielkempen/nova-polymorphic-field/master/docs/scheme.png)

### Demo

![Scheme](https://raw.githubusercontent.com/michielkempen/nova-polymorphic-field/master/docs/demo.gif)

### Installation

The package can be installed through Composer.

```bash
composer require michielkempen/nova-polymorphic-field
```

### Usage

1. Add a `morphs` field to the migration of the base model.
2. Add the `MichielKempen\NovaPolymorphicField\HasPolymorphicFields` trait to your Nova Resource.
3. Add the `MichielKempen\NovaPolymorphicField\PolymorphicField` to your Nova Resource `fields` method.

You can specify the different polymorphic types by calling the `type($name, $modelClass)` on the `PolymorphicField`.
- The `$name` parameter is a readable name you assign to your polymorphic type that will be displayed in the select field.
- The `$modelClass` parameter is the class of the polymorphic model. Make sure you pass the model class and not the resource class!

### Example

Migrations:

```php
Schema::create('news_posts', function (Blueprint $table) {
    $table->increments('id');
    $table->string('title');
    $table->morphs('type'); // !!
    $table->timestamps();
});

Schema::create('videos', function (Blueprint $table) {
    $table->increments('id');
    $table->string('url');
});

Schema::create('articles', function (Blueprint $table) {
    $table->increments('id');
    $table->string('image');
    $table->text('text');
});
```

Resource: 

```php
class NewsPost extends Resource
{
    use HasPolymorphicFields;

    public function fields(Request $request)
    {
        return [
            
            Text::make('Title'),

            PolymorphicField::make('Type')
                ->type('Form', Video::class, [

                    Text::make('Url'),

                ])
                ->type('Article', Article::class, [

                    Image::make('Image'),

                    Textarea::make('Text'),

                ]),

        ];
    }
}
```

### License

The MIT License (MIT). Please see [License File](https://github.com/michielkempen/nova-polymorphic-field/blob/master/LICENSE.md) for more information.

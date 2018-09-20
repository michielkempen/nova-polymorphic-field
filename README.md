# Nova Polymorphic Field

[![Latest Version on Packagist](https://img.shields.io/packagist/v/michielkempen/nova-polymorphic-field.svg)](https://packagist.org/packages/michielkempen/nova-polymorphic-field)
[![Total Downloads](https://img.shields.io/packagist/dt/michielkempen/nova-polymorphic-field.svg)](https://packagist.org/packages/michielkempen/nova-polymorphic-field)
[![License](https://img.shields.io/packagist/l/michielkempen/nova-polymorphic-field.svg)](https://github.com/michielkempen/nova-polymorphic-field/blob/master/LICENSE.md)

### Description

A Laravel Nova field that allows you to create a **collection of polymorphic resources**.

Depending on the polymorphic type you select:
1. Different fields will be populated on the form/detail page of your resource.
2. Records will be automatically created/updated in the corresponding tables.

![Scheme](https://raw.githubusercontent.com/michielkempen/nova-polymorphic-field/master/docs/scheme.png)

### Demo

![Scheme](https://raw.githubusercontent.com/michielkempen/nova-polymorphic-field/master/docs/demo.gif)

### Installation

The package can be installed through Composer.

```bash
composer require michielkempen/nova-polymorphic-field
```

### Usage

1. Add a `morphs` field to the migration of your base model.
2. Add the `MichielKempen\NovaPolymorphicField\HasPolymorphicFields` trait to your Nova Resource.
3. Add the `MichielKempen\NovaPolymorphicField\PolymorphicField` to your Nova Resource `fields` method.
4. Specify the different polymorphic types by calling the `type($name, $modelClass)` method on the `PolymorphicField`.
    - The `$name` parameter is a readable name you assign to your polymorphic type.
    - The `$modelClass` parameter is the class of the polymorphic model.

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
                ->type('Video', \App\Video::class, [

                    Text::make('Url'),

                ])
                ->type('Article', \App\Article::class, [

                    Image::make('Image'),

                    Textarea::make('Text'),

                ]),

        ];
    }
}
```

### morphMap

By default, the fully qualified class name of the related model will be stored as type field in the base model. However, you may wish to decouple your database from your application's internal structure. In that case, you may define a relationship "morph map" to instruct Eloquent to use a custom name for each model instead of the class name:

```php
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::morphMap([
    'article' => \App\Article::class,
    'video' => \App\Video::class,
]);
```

You may register the morphMap in the boot function of your AppServiceProvider.

### License

The MIT License (MIT). Please see [License File](https://github.com/michielkempen/nova-polymorphic-field/blob/master/LICENSE.md) for more information.

# yii2-tagify

Yii2 widget wrapping [@yaireo/tagify](https://github.com/yairEO/tagify) v4 — lightweight, powerful tags/select/mix input.

## Installation

```bash
composer require okkebal/yii2-tagify
```

Or for local development, add to `composer.json`:

```json
{
    "repositories": [
        { "type": "path", "url": "/var/www/yii2-yaireo--tagify" }
    ],
    "require": {
        "okkebal/yii2-tagify": "*"
    }
}
```

## Basic widget

```php
echo \okkebal\tagify\Tagify::widget([
    'name'        => 'tags',
    'value'       => 'php,yii2',
    'placeholder' => 'Add a tag…',
    'maxTags'     => 10,
]);
```

## With ActiveForm

```php
$form = ActiveForm::begin(['fieldClass' => \okkebal\tagify\ActiveField::class]);

// Free-form tags, CSV output
echo $form->field($model, 'tags')->tagify();

// Tags with sensible defaults (no inline editing, dropdown hidden until typing)
echo $form->field($model, 'tags')->tagifyTags(['maxTags' => 20, 'maxLength' => 25]);

// Single-value select from a fixed list
echo $form->field($model, 'genre')->tagifySelect(['rock', 'pop', 'jazz']);

// Multi-select from a fixed list
echo $form->field($model, 'skills')->tagifyMultiSelect(['php', 'js', 'css', 'mysql']);

// AJAX suggestions — endpoint receives ?q=<typed>, returns ["val1","val2"]
echo $form->field($model, 'tags')->tagifyAjax(['/api/tag-suggest']);

// Mix mode with @mentions
echo $form->field($model, 'body')->tagifyMix(['Alice', 'Bob', 'Carol'], '@');
```

## Direct widget in an ActiveForm field

```php
echo $form->field($model, 'tags')->widget(\okkebal\tagify\Tagify::class, [
    'whitelist'        => ['php', 'yii2', 'mysql'],
    'enforceWhitelist' => true,
    'maxTags'          => 5,
    'placeholder'      => 'Pick a technology…',
]);
```

## All widget properties

| Property | Type | Default | Description |
|---|---|---|---|
| `mode` | `string\|null` | `null` | `null` = tags, `'select'` = single dropdown, `'mix'` = inline |
| `placeholder` | `string\|null` | `null` | Input placeholder text |
| `maxTags` | `int\|null` | `null` | Maximum number of tags |
| `maxLength` | `int\|null` | `null` | Max character length per tag |
| `delimiters` | `string` | `','` | Characters that split input into tags |
| `whitelist` | `array` | `[]` | Allowed values; enables dropdown suggestions |
| `blacklist` | `array` | `[]` | Disallowed values |
| `enforceWhitelist` | `bool` | `false` | Restrict input to whitelist only |
| `userInput` | `bool` | `true` | Allow typing new values |
| `editTags` | `bool\|int` | `false` | Edit tags: `false`=off, `1`=click, `2`=dbl-click |
| `dropdown` | `array` | `[]` | Tagify dropdown sub-options |
| `outputFormat` | `string` | `'csv'` | `'csv'` or `'json'` (Tagify default) |
| `ajaxUrl` | `string\|array\|null` | `null` | Yii route for AJAX whitelist loading |
| `ajaxDebounce` | `int` | `300` | Debounce ms for AJAX calls |
| `clientOptions` | `array` | `[]` | Raw Tagify options (merged last, highest precedence) |

## AJAX endpoint

Your controller action should return JSON:

```php
public function actionTagSuggest()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $q = \Yii::$app->request->get('q', '');
    return Tag::find()
        ->where(['like', 'name', $q])
        ->limit(20)
        ->column(); // returns ["php", "yii2", ...]
}
```

Or with label/value pairs:

```php
return Tag::find()
    ->where(['like', 'name', $q])
    ->limit(20)
    ->asArray()
    ->all(); // returns [["id"=>1,"value"=>"php"], ...]
```

## Value handling

The widget automatically converts an array model attribute to a comma-separated string
before passing it to Tagify. On form submit, `outputFormat='csv'` produces `"php,yii2,mysql"`.

To store/restore as an array in your model:

```php
// In afterFind / beforeValidate:
public function afterFind()
{
    parent::afterFind();
    $this->tags = $this->tags ? explode(',', $this->tags) : [];
}

public function beforeValidate()
{
    if (is_array($this->tags)) {
        $this->tags = implode(',', $this->tags);
    }
    return parent::beforeValidate();
}
```

## Using without ActiveField

```php
TagifyAsset::register($this);

echo Html::textInput('tags', 'php,yii2', ['id' => 'my-tags', 'placeholder' => 'Add…']);

$this->registerJs(<<<JS
    new Tagify(document.getElementById('my-tags'), {
        maxTags: 10,
        originalInputValueFormat: function(v) {
            return v.map(function(i) { return i.value; }).join(',');
        }
    });
JS);
```

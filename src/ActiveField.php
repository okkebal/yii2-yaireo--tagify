<?php

/**
 * @link https://github.com/okkebal/yii2-yaireo--tagify
 * @license https://opensource.org/licenses/MIT MIT License
 */

namespace okkebal\tagify;

use yii\helpers\ArrayHelper;

/**
 * ActiveField extension that adds Tagify convenience methods.
 *
 * Configure your ActiveForm:
 * ```php
 * $form = ActiveForm::begin(['fieldClass' => \okkebal\tagify\ActiveField::class]);
 * ```
 *
 * Then use in views:
 * ```php
 * echo $form->field($model, 'tags')->tagify();
 * echo $form->field($model, 'tags')->tagifyTags(['maxTags' => 10]);
 * echo $form->field($model, 'genre')->tagifySelect(['rock', 'pop', 'jazz']);
 * echo $form->field($model, 'tags')->tagifyAjax(['/api/tags']);
 * ```
 */
class ActiveField extends \yii\widgets\ActiveField
{
    /**
     * Basic Tagify input — free-form tag entry, CSV output.
     *
     * @param array $options Widget options (see Tagify properties).
     * @return static
     */
    public function tagify($options = [])
    {
        $options = ArrayHelper::merge(['outputFormat' => 'csv'], $options);
        return $this->widget(Tagify::class, $options);
    }

    /**
     * Tag input with sensible defaults for a tagging UX:
     * editing disabled, dropdown hidden until typing.
     *
     * @param array $options Widget options.
     * @return static
     */
    public function tagifyTags($options = [])
    {
        $defaults = [
            'editTags'     => false,
            'outputFormat' => 'csv',
            'dropdown'     => ['enabled' => 0],
        ];
        return $this->widget(Tagify::class, ArrayHelper::merge($defaults, $options));
    }

    /**
     * Single-value select (mode='select') with a fixed whitelist.
     *
     * @param array  $items   Allowed values: ['php', 'js'] or [['value'=>'php','label'=>'PHP'], ...]
     * @param array  $options Widget options.
     * @return static
     */
    public function tagifySelect($items = [], $options = [])
    {
        $defaults = [
            'mode'             => 'select',
            'whitelist'        => $items,
            'enforceWhitelist' => !empty($items),
            'userInput'        => empty($items),
            'dropdown'         => ['enabled' => 0, 'maxItems' => count($items) ?: 20],
        ];
        return $this->widget(Tagify::class, ArrayHelper::merge($defaults, $options));
    }

    /**
     * Multi-select from a fixed whitelist.
     *
     * @param array  $items   Allowed values.
     * @param array  $options Widget options.
     * @return static
     */
    public function tagifyMultiSelect($items = [], $options = [])
    {
        $defaults = [
            'whitelist'        => $items,
            'enforceWhitelist' => !empty($items),
            'userInput'        => empty($items),
            'outputFormat'     => 'csv',
            'dropdown'         => ['enabled' => 0, 'maxItems' => count($items) ?: 20, 'closeOnSelect' => false],
        ];
        return $this->widget(Tagify::class, ArrayHelper::merge($defaults, $options));
    }

    /**
     * Tag input with AJAX-loaded whitelist suggestions.
     *
     * The endpoint at $url receives `?q=<typed>` and must return JSON:
     * `["value1", "value2"]` or `[{"value":"v","label":"Label"}, ...]`
     *
     * @param string|array $url     Yii route or URL string for the autocomplete endpoint.
     * @param array        $options Widget options.
     * @return static
     */
    public function tagifyAjax($url, $options = [])
    {
        $defaults = [
            'ajaxUrl'          => $url,
            'enforceWhitelist' => true,
            'outputFormat'     => 'csv',
        ];
        return $this->widget(Tagify::class, ArrayHelper::merge($defaults, $options));
    }

    /**
     * Mix mode — inline tags inside free text (e.g. @mentions, #hashtags).
     *
     * @param array  $items   Whitelist for the dropdown trigger.
     * @param string $trigger Character that opens the dropdown, default '@'.
     * @param array  $options Widget options.
     * @return static
     */
    public function tagifyMix($items = [], $trigger = '@', $options = [])
    {
        $defaults = [
            'mode'      => 'mix',
            'pattern'   => $trigger,
            'whitelist' => $items,
            'dropdown'  => ['enabled' => 1, 'position' => 'text'],
        ];
        return $this->widget(Tagify::class, ArrayHelper::merge($defaults, $options));
    }
}

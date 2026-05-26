<?php

/**
 * @link https://github.com/okkebal/yii2-yaireo--tagify
 * @license https://opensource.org/licenses/MIT MIT License
 */

namespace okkebal\tagify;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

/**
 * Tagify widget for Yii2.
 *
 * Wraps the @yaireo/tagify library (https://github.com/yairEO/tagify).
 *
 * Basic usage in a view:
 * ```php
 * echo \okkebal\tagify\Tagify::widget([
 *     'name'     => 'tags',
 *     'value'    => 'php,yii2',
 *     'maxTags'  => 10,
 *     'placeholder' => 'Add a tag...',
 * ]);
 * ```
 *
 * With an ActiveForm:
 * ```php
 * echo $form->field($model, 'tags')->widget(\okkebal\tagify\Tagify::class, [
 *     'whitelist'   => ['php', 'yii2', 'mysql'],
 *     'maxTags'     => 5,
 * ]);
 * ```
 *
 * AJAX whitelist:
 * ```php
 * echo $form->field($model, 'tags')->widget(\okkebal\tagify\Tagify::class, [
 *     'ajaxUrl'         => ['/api/tags'],
 *     'enforceWhitelist' => true,
 * ]);
 * ```
 */
class Tagify extends InputWidget
{
    /**
     * Tagify mode. null = tags (default), 'select' = single-value dropdown, 'mix' = inline mixed content.
     * @var string|null
     */
    public $mode = null;

    /**
     * Input placeholder (read by Tagify from the HTML attribute).
     * @var string|null
     */
    public $placeholder = null;

    /**
     * Maximum number of tags. null = unlimited.
     * @var int|null
     */
    public $maxTags = null;

    /**
     * Maximum character length per tag. null = unlimited.
     * @var int|null
     */
    public $maxLength = null;

    /**
     * Delimiter string(s) used to split tags on user input.
     * @var string
     */
    public $delimiters = ',';

    /**
     * Allowed tag values. When set, a dropdown is shown. Pass strings or
     * associative arrays: [['value'=>'php','label'=>'PHP'], ...]
     * @var array
     */
    public $whitelist = [];

    /**
     * Disallowed tag values.
     * @var array
     */
    public $blacklist = [];

    /**
     * When true, only values from $whitelist are accepted.
     * @var bool
     */
    public $enforceWhitelist = false;

    /**
     * Allow the user to type free-form input (create new tags).
     * Set to false when using enforceWhitelist.
     * @var bool
     */
    public $userInput = true;

    /**
     * Allow editing existing tags. false = disabled, 1 = on click, 2 = on double-click.
     * @var bool|int
     */
    public $editTags = false;

    /**
     * Tagify dropdown sub-options.
     * @see https://github.com/yairEO/tagify#dropdown-settings
     * @var array
     */
    public $dropdown = [];

    /**
     * How the underlying input value is formatted.
     * 'csv'  — comma-separated plain values (easiest for server-side).
     * 'json' — Tagify's default JSON array string.
     * @var string
     */
    public $outputFormat = 'csv';

    /**
     * URL (Yii route or string) for AJAX whitelist loading.
     * The widget will call this URL with a `q` query parameter as the user types.
     * The endpoint must return a JSON array: ["value1", "value2"] or
     * [{"value":"php","label":"PHP"}, ...]
     * @var string|array|null
     */
    public $ajaxUrl = null;

    /**
     * Debounce delay in ms for AJAX calls.
     * @var int
     */
    public $ajaxDebounce = 300;

    /**
     * Raw Tagify options merged last (take precedence over convenience properties).
     * Supports JsExpression values for callbacks.
     * @var array
     */
    public $clientOptions = [];

    public function init()
    {
        parent::init();
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->hasModel()
                ? Html::getInputId($this->model, $this->attribute)
                : $this->getId();
        }
    }

    public function run()
    {
        $this->registerAssets();
        return $this->renderInput();
    }

    protected function renderInput()
    {
        if ($this->placeholder !== null) {
            $this->options['placeholder'] = $this->placeholder;
        }

        if ($this->hasModel()) {
            $value = Html::getAttributeValue($this->model, $this->attribute);
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $this->options['value'] = (string)($value ?? '');
            return Html::activeTextInput($this->model, $this->attribute, $this->options);
        }

        return Html::textInput($this->name, $this->value, $this->options);
    }

    protected function registerAssets()
    {
        TagifyAsset::register($this->view);

        $id      = $this->options['id'];
        $varName = 'tagify_' . preg_replace('/[^a-zA-Z0-9]/', '_', $id);
        $options = $this->buildClientOptions();
        $encoded = Json::encode($options);

        $this->view->registerJs("var {$varName} = new Tagify(document.getElementById('{$id}'), {$encoded});");

        if ($this->ajaxUrl !== null) {
            $this->registerAjaxScript($varName);
        }
    }

    protected function buildClientOptions()
    {
        $options = $this->clientOptions;

        if ($this->mode !== null) {
            $options['mode'] = $this->mode;
        }
        if ($this->maxTags !== null) {
            $options['maxTags'] = (int)$this->maxTags;
        }
        if ($this->maxLength !== null) {
            $options['maxLength'] = (int)$this->maxLength;
        }
        if ($this->delimiters !== null) {
            $options['delimiters'] = $this->delimiters;
        }
        if (!empty($this->whitelist)) {
            $options['whitelist'] = $this->whitelist;
        }
        if (!empty($this->blacklist)) {
            $options['blacklist'] = $this->blacklist;
        }
        if ($this->enforceWhitelist) {
            $options['enforceWhitelist'] = true;
        }
        if (!$this->userInput) {
            $options['userInput'] = false;
        }
        if ($this->editTags !== null) {
            $options['editTags'] = $this->editTags;
        }
        if (!empty($this->dropdown)) {
            $options['dropdown'] = array_merge($options['dropdown'] ?? [], $this->dropdown);
        }

        // AJAX mode: start with empty whitelist and enable dropdown
        if ($this->ajaxUrl !== null && !isset($options['whitelist'])) {
            $options['whitelist'] = [];
            if (empty($options['dropdown'])) {
                $options['dropdown'] = ['maxItems' => 20, 'enabled' => 0, 'closeOnSelect' => false];
            }
        }

        if ($this->outputFormat === 'csv' && !isset($options['originalInputValueFormat'])) {
            $options['originalInputValueFormat'] = new JsExpression(
                'function(v){return v.map(function(i){return i.value;}).join(",");}'
            );
        }

        return $options;
    }

    protected function registerAjaxScript($varName)
    {
        $url      = Url::to($this->ajaxUrl);
        $debounce = (int)$this->ajaxDebounce;

        $js = <<<JS
{$varName}.on('input', (function() {
    var t;
    return function(e) {
        clearTimeout(t);
        t = setTimeout(function() {
            var q = e.detail.value;
            if (!q) return;
            {$varName}.settings.whitelist.length = 0;
            {$varName}.loading(true).dropdown.hide.call({$varName});
            fetch('{$url}?q=' + encodeURIComponent(q))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    {$varName}.settings.whitelist.splice(0, data.length, ...data);
                    {$varName}.loading(false).dropdown.show.call({$varName}, q);
                })
                .catch(function() { {$varName}.loading(false); });
        }, {$debounce});
    };
})());
JS;
        $this->view->registerJs($js);
    }
}

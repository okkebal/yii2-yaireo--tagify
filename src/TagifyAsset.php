<?php

/**
 * @link https://github.com/okkebal/yii2-yaireo--tagify
 * @license https://opensource.org/licenses/MIT MIT License
 */

namespace okkebal\tagify;

use yii\web\AssetBundle;

class TagifyAsset extends AssetBundle
{
    public $sourcePath = '@tagify/assets';

    public $css = [
        'tagify.css',
    ];

    public $js = [
        'tagify.js',
    ];
}

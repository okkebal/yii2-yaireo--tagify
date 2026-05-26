<?php

/**
 * @link https://github.com/okkebal/yii2-yaireo--tagify
 * @license https://opensource.org/licenses/MIT MIT License
 */

namespace okkebal\tagify;

use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        \Yii::setAlias('@tagify', __DIR__);
    }
}

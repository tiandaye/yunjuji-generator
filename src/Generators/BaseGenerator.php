<?php

/**
 * @Author: admin
 * @Date:   2017-09-21 18:36:22
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-21 18:36:38
 */

namespace Yunjuji\Generator\Generators;

use InfyOm\Generator\Utils\FileUtil;

class BaseGenerator
{
    public function rollbackFile($path, $fileName)
    {
        if (file_exists($path.$fileName)) {
            return FileUtil::deleteFile($path, $fileName);
        }

        return false;
    }
}

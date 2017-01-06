<?php
use GitElephant\Repository;

/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 06.01.17
 * Time: 12:29
 */
class ApplicationFactory {
    public function createRepository ($path) {
        return new Repository($path);
    }
}
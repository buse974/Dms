<?php

namespace Dms\Coding;

interface CodingInterface
{
    public function getCoding();
    public function encode($data);
    public function decode($data);
}

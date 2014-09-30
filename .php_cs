<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('bin')
    ->exclude('tests')
    ->exclude('vendor')
    ->in(__DIR__)
;

return Symfony\CS\Config\Config::create()
    ->finder($finder)
;

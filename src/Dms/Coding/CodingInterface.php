<?php

namespace Dms\Coding;

interface CodingInterface
{
	const CODING_BASE_STR = 'base';
	const CODING_DEFLATE_STR = 'deflate';
	const CODING_GZIP_STR = 'gzip';
	const CODING_URL_STR = 'url';
	const CODING_ZLIB_STR = 'zlib';
	
    public function getCoding();
    public function encode($data);
    public function decode($data);
}
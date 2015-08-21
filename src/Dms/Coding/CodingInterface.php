<?php

namespace Dms\Coding;

interface CodingInterface
{
    const CODING_BASE_STR = 'base';
    const CODING_DEFLATE_STR = 'deflate';
    const CODING_GZIP_STR = 'gzip';
    const CODING_URL_STR = 'url';
    const CODING_ZLIB_STR = 'zlib';

    /**
     * return name of type coding.
     *
     * @return string
     */
    public function getCoding();

    /**
     * string The encoded data, as a string
     * return false for failure.
     *
     * @param <string|false> $data
     *
     * @return <string|false>
     */
    public function encode($data);

    /**
     * string the original data
     * return false for failure.
     *
     * @param string $data
     *
     * @return <string|false>
     */
    public function decode($data);
}

<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * StorageInterface.php
 */
namespace Dms\Storage;

/**
 * Interface Storage.
 */
interface StorageInterface
{
    /**
     * Write Document.
     *
     * @param \Dms\Document\Document $document
     */
    public function write(\Dms\Document\Document $document);

    /**
     * If Document exist.
     *
     * @param \Dms\Document\Document $document
     */
    public function exist(\Dms\Document\Document $document);

    /**
     * Get Path.
     *
     * @param \Dms\Document\Document $document
     * @param string                 $ext
     */
    public function getPath(\Dms\Document\Document $document, $ext = '');

    /**
     * Read Document.
     *
     * @param \Dms\Document\Document $document
     * @param string                 $type
     * @param string                 $print
     */
    public function read(\Dms\Document\Document &$document, $type = null, $print = false);
}

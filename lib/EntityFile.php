<?php
/**
 * Document Storage
 *
 * PHP Version 5
 *
 * @author   Richard Seymour <web@bespoke.support>
 * @license  MIT
 * @link     https://github.com/BespokeSupport/DocumentStorage
 */

namespace BespokeSupport\DocumentStorage;

/**
 * Class EntityFile
 * @package BespokeSupport\DocumentStorage
 */
class EntityFile extends \SplFileInfo
{
    /**
     * @var string
     */
    public $extension;
    /**
     * @var string
     */
    public $hash;
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $mime;
    /**
     * @var string
     */
    public $name;

    /**
     * EntityFile constructor.
     * @param \SplFileInfo|string $file
     */
    public function __construct($file)
    {
        if ($file instanceof \SplFileInfo) {
            $file = $file->getRealPath();
        }

        parent::__construct($file);
    }
}

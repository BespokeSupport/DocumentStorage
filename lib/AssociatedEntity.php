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
 * Class AssociatedEntity
 * @package BespokeSupport\DocumentStorage
 */
class AssociatedEntity
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $entity;

    /**
     * AssociatedEntity constructor.
     * @param $entityString
     * @param $entityId
     */
    public function __construct($entityString, $entityId)
    {
        $this->entity = $entityString;
        $this->id = $entityId;
    }
}

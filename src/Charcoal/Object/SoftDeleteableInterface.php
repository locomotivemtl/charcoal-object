<?php

namespace Charcoal\Object;

/**
 * The `SoftDeleteable` mixin allows objects to be implicitly deleted.
 *
 * The interface adds two properties:
 *
 * - "deleted_date" — A timestamp property.
 * - "deleted_by" — A user reference property.
 *
 * When soft-deleted (see: "trashed"), the object's "deleted_*" properties are marked with a timestamp
 * and a reference to the _deleter_ instead of explicitly removing the object from the database.
 */
interface SoftDeleteableInterface
{
    /**
     * Set the date/time when the object was trashed.
     *
     * @param  \DateTimeInterface|string|null $deleted A date/time value or FALSE.
     * @return SoftDeleteableInterface
     */
    public function setDeletedDate($deleted);

    /**
     * Get the date/time when the object was trashed, if trashed.
     *
     * @return \DateTimeInterface|null
     */
    public function deletedDate();

    /**
     * Set the user that trashed the object.
     *
     * @param  mixed $deleter The one who triggered the trashing.
     * @return SoftDeleteableInterface
     */
    public function setDeletedBy($deleter);

    /**
     * Get the user that trashed the object.
     *
     * @return mixed
     */
    public function deletedBy();

    /**
     * Determine if the object is in the trash.
     *
     * @return boolean
     */
    public function isTrashed();
}

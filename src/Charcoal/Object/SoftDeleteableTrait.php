<?php

namespace Charcoal\Object;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

/**
 * The SoftDeleteable mixin allows objects to be implicitly deleted.
 *
 * Implementation of {@see \Charcoal\Object\SoftDeleteableInterface}.
 */
trait SoftDeleteableTrait
{
    /**
     * The deletion timestamp.
     *
     * Set automatically on {@see StorableInterface::delete()}.
     *
     * @var \DateTimeInterface|null
     */
    private $deletedDate;

    /**
     * The user who deleted the object.
     *
     * @var mixed
     */
    private $deletedBy;

    /**
     * Indicates if the object is currently being explicitly deleted.
     *
     * @var boolean
     */
    private $forceDeleting = false;

    /**
     * Indicates if the object is currently being restored from trash.
     *
     * @var boolean
     */
    private $isRestoring = false;

    /**
     * Retrieve the `SoftDeleteable` mixin's properties.
     *
     * @return string[]
     */
    public function softDeletableProperties()
    {
        return [
            'deleted_date',
            'deleted_by',
        ];
    }

    /**
     * Set the date/time when the object was trashed.
     *
     * @param  \DateTimeInterface|string|null $deleted A date/time value.
     * @throws InvalidArgumentException If the date/time value is invalid.
     * @return self
     */
    public function setDeletedDate($deleted)
    {
        if ($deleted === null) {
            $this->deletedDate = null;
            return $this;
        }

        if (is_string($deleted)) {
            try {
                $deleted = new DateTime($deleted);
            } catch (Exception $e) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid date/time value: %s',
                    $e->getMessage()
                ), $e->getCode(), $e);
            }
        }

        if (!$deleted instanceof DateTimeInterface) {
            throw new InvalidArgumentException(sprintf(
                'Invalid "deleted_date" value. Must be a date/time string or an instance of %s.',
                DateTimeInterface::class
            ));
        }

        $this->deletedDate = $deleted;
        return $this;
    }

    /**
     * Get the date/time when the object was trashed, if trashed.
     *
     * @return \DateTimeInterface|null
     */
    public function deletedDate()
    {
        return $this->deletedDate;
    }

    /**
     * Set the user that trashed the object.
     *
     * @param  mixed $deleter The one who triggered the trashing.
     * @return self
     */
    public function setDeletedBy($deleter)
    {
        $this->deletedBy = $deleter;
        return $this;
    }

    /**
     * Get the user that trashed the object.
     *
     * @return mixed
     */
    public function deletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Determine if the object is in the trash.
     *
     * @return boolean
     */
    public function isTrashed()
    {
        return ($this->deletedDate !== null);
    }

    /**
     * Determine if the object is currently force deleting.
     *
     * @return boolean
     */
    public function isForceDeleting()
    {
        return $this->forceDeleting;
    }

    /**
     * Determine if the object is currently restoring from trash.
     *
     * @return boolean
     */
    public function isRestoring()
    {
        return $this->isRestoring;
    }

    /**
     * Delete the object from storage.
     *
     * This method will trigger either a "soft delete" or a "hard delete"
     * based on the value of {@see self::$forceDeleting}.
     *
     * @see    \Charcoal\Source\StorableTrait::delete()
     * @return boolean
     */
    public function delete()
    {
        $before = $this->preDelete();
        if ($before === false) {
            $this->logger->error(sprintf(
                'Can not delete object "%s:%s"; cancelled by %s::preDelete()',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        if ($this->forceDeleting) {
            $result = $this->source()->deleteItem($this);
        } else {
            $properties = $this->softDeletableProperties();
            $this->saveProperties($properties);

            $result = $this->source()->updateItem($this, $properties);
        }

        if ($result === false) {
            $this->logger->error(sprintf(
                'Can not delete object "%s:%s"; repository failed for %s',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        $after = $this->postDelete();
        if ($after === false) {
            $this->logger->warning(sprintf(
                'Deleted object "%s:%s" but %s::postDelete() failed',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        return $result;
    }

    /**
     * Force a hard delete on the object from storage.
     *
     * @see    \Charcoal\Source\StorableTrait::delete()
     * @return boolean
     */
    public function forceDelete()
    {
        $this->forceDeleting = true;

        $result = $this->delete();

        $this->forceDeleting = false;

        return $result;
    }

    /**
     * Delete hook called before deleting the object.
     *
     * @see    \Charcoal\Source\StorableTrait::preDelete()
     * @return boolean
     */
    protected function preDelete()
    {
        if (!$this->forceDeleting) {
            $this->setDeletedDate('now');
        }

        return true;
    }

    /**
     * Restore a soft-deleted object.
     *
     * @return boolean
     */
    public function restore()
    {
        $before = $this->preRestore();
        if ($before === false) {
            $this->logger->error(sprintf(
                'Can not restore object "%s:%s"; cancelled by %s::preRestore()',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        $properties = $this->softDeletableProperties();
        $this->saveProperties($properties);

        $result = $this->source()->updateItem($this, $properties);

        if ($result === false) {
            $this->logger->error(sprintf(
                'Can not restore object "%s:%s"; repository failed for %s',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        $after = $this->postRestore();
        if ($after === false) {
            $this->logger->error(sprintf(
                'Restored object "%s:%s" but %s::postRestore() failed',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
        }

        return $result;
    }

    /**
     * Restore hook called before restoring the object.
     *
     * @return boolean
     */
    protected function preRestore()
    {
        $this->setDeletedDate(null);
        $this->setDeletedBy(null);

        return true;
    }

    /**
     * Restore hook called after the object is restored.
     *
     * @return boolean
     */
    protected function postRestore()
    {
        return true;
    }
}

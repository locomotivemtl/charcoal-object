<?php

namespace Charcoal\Object;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

/**
 * Defines a model for objects typically submitted by the end-user of the application.
 *
 * @see UserData for basic implementation of interface.
 */
interface UserDataInterface extends ModelInterface
{
    /**
     * Set the client IP address.
     *
     * @param  integer|null $ip The remote IP at object creation.
     * @return self
     */
    public function setIp($ip);

    /**
     * Set the origin language.
     *
     * @param  string $lang The language code.
     * @return self
     */
    public function setLang($lang);

    /**
     * Set the origin of the object submission.
     *
     * @param  string $origin The source URL or identifier of the submission.
     * @return self
     */
    public function setOrigin($origin);

    /**
     * Set when the object was created.
     *
     * @param  \DateTime|string|null $timestamp The timestamp at object's creation.
     * @return self
     */
    public function setTs($timestamp);
}

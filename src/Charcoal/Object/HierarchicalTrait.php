<?php

namespace Charcoal\Object;

use InvalidArgumentException;
use UnexpectedValueException;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

/**
 * Full implementation, as a trait, of the `HierarchicalInterface`
 */
trait HierarchicalTrait
{
    /**
     * The object's parent, if any, in the hierarchy.
     *
     * @var string|integer|null
     */
    protected $master = null;

    /**
     * Store a copy of the object's ancestry.
     *
     * @var HierarchicalInterface[]|null
     */
    private $hierarchy = null;

    /**
     * Store a copy of the object's descendants.
     *
     * @var HierarchicalInterface[]|null
     */
    private $children = null;

    /**
     * Store a copy of the object's siblings.
     *
     * @var HierarchicalInterface[]|null
     */
    private $siblings = null;

    /**
     * The object's parent object, if any, in the hierarchy.
     *
     * @var HierarchicalInterface|null
     */
    private $masterObject = null;

    /**
     * A store of cached objects.
     *
     * @var ModelInterface[] $objectCache
     */
    public static $objectCache = [];

    /**
     * Reset this object's hierarchy.
     *
     * The object's hierarchy can be rebuilt with {@see self::hierarchy()}.
     *
     * @return HierarchicalInterface Chainable
     */
    public function resetHierarchy()
    {
        $this->hierarchy = null;

        return $this;
    }

    /**
     * Set this object's immediate parent.
     *
     * @param mixed $master The object's parent (or master).
     * @return HierarchicalInterface Chainable
     */
    public function setMaster($master)
    {
        $master = is_numeric($master)
            ? (int)$master
            : $master;

        if (!$master) {
            $master = null;
        }

        $this->master = $master;
        $this->masterObject = null;

        $this->resetHierarchy();

        return $this;
    }

    /**
     * Retrieve this object's immediate parent.
     *
     * @return string|null
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * Retrieve this object's immediate parent as object.
     * @return HierarchicalInterface|null
     * @throws UnexpectedValueException The current object cannot be its own parent.
     */
    public function getMasterObject()
    {
        if (!$this->masterObject && $this->hasMaster()) {
            $master = $this->objFromIdent($this->getMaster());

            if ($master instanceof ModelInterface) {
                if ($master->id() === $this->id()) {
                    throw new UnexpectedValueException(sprintf(
                        'Can not be ones own parent: %s',
                        $master->id()
                    ));
                }
            }

            $this->masterObject = $master;
        }

        return $this->masterObject;
    }

    /**
     * Determine if this object's immediate parent exists.
     *
     * @return boolean
     */
    public function hasMasterObject()
    {
        return (bool)$this->getMasterObject();
    }

    /**
     * Determine if this object has a direct parent.
     *
     * @return boolean
     */
    public function hasMaster()
    {
        return (bool)$this->getMaster();
    }

    /**
     * Determine if this object is the head (top-level) of its hierarchy.
     *
     * Top-level objects do not have a parent (master).
     *
     * @return boolean
     */
    public function isTopLevel()
    {
        return !$this->getMaster();
    }

    /**
     * Determine if this object is the tail (last-level) of its hierarchy.
     *
     * Last-level objects do not have a children.
     *
     * @return boolean
     */
    public function isLastLevel()
    {
        return !$this->hasChildren();
    }

    /**
     * Retrieve this object's position (level) in its hierarchy.
     *
     * Starts at "1" (top-level).
     *
     * The level is calculated by loading all ancestors with {@see self::hierarchy()}.
     *
     * @return integer
     */
    public function hierarchyLevel()
    {
        $hierarchy = $this->hierarchy();
        $level     = (count($hierarchy) + 1);

        return $level;
    }

    /**
     * Retrieve the top-level ancestor of this object.
     *
     * @return HierarchicalInterface|null
     */
    public function toplevelMaster()
    {
        $hierarchy = $this->invertedHierarchy();
        if (isset($hierarchy[0])) {
            return $hierarchy[0];
        } else {
            return null;
        }
    }

    /**
     * Determine if this object has any ancestors.
     *
     * @return boolean
     */
    public function hasParents()
    {
        return count($this->hierarchy()) > 0;
    }

    /**
     * Retrieve this object's ancestors (from immediate parent to top-level).
     *
     * @return HierarchicalInterface[]
     */
    public function hierarchy()
    {
        if ($this->hierarchy === null) {
            $this->hierarchy = $this->loadHierarchy();
        }

        return $this->hierarchy;
    }

    /**
     * Build this object's ancestors (from immediate parent to top-level).
     *
     * @return HierarchicalInterface[]
     */
    public function loadHierarchy()
    {
        $hierarchy = [];
        $master    = $this->getMasterObject();
        while ($master) {
            $hierarchy[] = $master;
            $master      = $master->getMasterObject();
        }

        return $hierarchy;
    }

    /**
     * Retrieve this object's ancestors, inverted from top-level to immediate.
     *
     * @return HierarchicalInterface[]
     */
    public function invertedHierarchy()
    {
        $hierarchy = $this->hierarchy();

        return array_reverse($hierarchy);
    }

    /**
     * Determine if the object is the parent of the given object.
     *
     * @param mixed $child The child (or ID) to match against.
     * @return boolean
     */
    public function isMasterOf($child)
    {
        $child = $this->objFromIdent($child);

        return ($child->getMaster() === $this->id());
    }

    /**
     * Determine if the object is a parent/ancestor of the given object.
     *
     * @param mixed $child The child (or ID) to match against.
     * @return boolean
     * @todo Implementation needed.
     */
    public function recursiveIsMasterOf($child)
    {
        $child = $this->objFromIdent($child);

        return false;
    }

    /**
     * Get wether the object has any children at all
     * @return boolean
     */
    public function hasChildren()
    {
        $numChildren = $this->numChildren();

        return ($numChildren > 0);
    }

    /**
     * Get the number of children directly under this object.
     * @return integer
     */
    public function numChildren()
    {
        $children = $this->children();

        return count($children);
    }

    /**
     * Get the total number of children in the entire hierarchy.
     * This method counts all children and sub-children, unlike `numChildren()` which only count 1 level.
     * @return integer
     * @todo Implementation needed.
     */
    public function recursiveNumChildren()
    {
        return 0;
    }

    /**
     * @param mixed[] $children The children to set.
     * @return HierarchicalInterface Chainable
     */
    public function setChildren(array $children)
    {
        $this->children = [];
        foreach ($children as $c) {
            $this->addChild($c);
        }

        return $this;
    }

    /**
     * @param mixed $child The child object (or ident) to add.
     * @return HierarchicalInterface Chainable
     * @throws UnexpectedValueException The current object cannot be its own child.
     */
    public function addChild($child)
    {
        $child = $this->objFromIdent($child);

        if ($child instanceof ModelInterface) {
            if ($child->id() === $this->id()) {
                throw new UnexpectedValueException(sprintf(
                    'Can not be ones own child: %s',
                    $child->id()
                ));
            }
        }

        $this->children[] = $child;

        return $this;
    }

    /**
     * Get the children directly under this object.
     * @return ModelInterface[]
     */
    public function children()
    {
        if ($this->children !== null) {
            return $this->children;
        }

        $this->children = $this->loadChildren();

        return $this->children;
    }

    /**
     * @return array
     */
    abstract public function loadChildren();

    /**
     * @param mixed $master The master object (or ident) to check against.
     * @return boolean
     */
    public function isChildOf($master)
    {
        $master = $this->objFromIdent($master);

        return ($master && $master->id() === $this->getMaster());
    }

    /**
     * @param mixed $master The master object (or ident) to check against.
     * @return boolean
     */
    public function recursiveIsChildOf($master)
    {
        if ($this->isChildOf($master)) {
            return true;
        }

        if ($this->hasParents() && $this->hasMasterObject()) {
            return $this->getMasterObject()->recursiveIsChildOf($master);
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function hasSiblings()
    {
        $numSiblings = $this->numSiblings();

        return ($numSiblings > 1);
    }

    /**
     * @return integer
     */
    public function numSiblings()
    {
        $siblings = $this->siblings();

        return count($siblings);
    }

    /**
     * Get all the objects on the same level as this one.
     * @return ModelInterface[]
     */
    public function siblings()
    {
        if ($this->siblings === null) {
            $this->siblings = $this->loadSiblings();
        }

        return $this->siblings;
    }

    /**
     * Get all the objects on the same level as this one.
     * @return ModelInterface[]
     * @todo Implementation needed.
     */
    public function loadSiblings()
    {
        $master = $this->getMasterObject();
        if ($master) {
            // Todo: Remove "current" object from siblings
            return $master->children();
        }

        // TODO: return all top-level objects.
        return [];
    }

    /**
     * @param mixed $sibling The sibling to check.
     * @return boolean
     */
    public function isSiblingOf($sibling)
    {
        $sibling = $this->objFromIdent($sibling);

        return ($sibling->getMaster() === $this->getMaster());
    }

    /**
     * @param mixed $ident The ident.
     * @return (HierarchicalInterface&ModelInterface)|null
     * @throws InvalidArgumentException If the identifier is not a scalar value.
     */
    private function objFromIdent($ident)
    {
        if ($ident === null) {
            return null;
        }

        $class = get_called_class();

        if (is_object($ident) && ($ident instanceof $class)) {
            return $ident;
        }

        if (is_array($ident) && isset($ident[$this->key()])) {
            $ident = $ident[$this->key()];
        }

        if (!is_scalar($ident)) {
            throw new InvalidArgumentException(sprintf(
                'Can not load object (not a scalar or a "%s")',
                $class
            ));
        }

        $cached = $this->loadObjectFromCache($ident);
        if ($cached !== null) {
            return $cached;
        }

        $obj = $this->loadObjectFromSource($ident);
        if ($obj !== null) {
            $this->addObjectToCache($obj);
        }

        return $obj;
    }

    /**
     * Retrieve an object from the storage source by its ID.
     *
     * @param mixed $id The object id.
     * @return (HierarchicalInterface&ModelInterface)|null
     */
    private function loadObjectFromSource($id)
    {
        $obj = $this->modelFactory()->create($this->objType());
        $obj->load($id);

        if ($obj->id()) {
            return $obj;
        }

        return null;
    }

    /**
     * Retrieve an object from the cache store by its ID.
     *
     * @param mixed $id The object id.
     * @return (HierarchicalInterface&ModelInterface)|null
     */
    private function loadObjectFromCache($id)
    {
        $objType = $this->objType();
        if (isset(static::$objectCache[$objType][$id])) {
            return static::$objectCache[$objType][$id];
        }

        return null;
    }

    /**
     * Add an object to the cache store.
     *
     * @param ModelInterface $obj The object to store.
     * @return HierarchicalInterface Chainable
     */
    private function addObjectToCache(ModelInterface $obj)
    {
        static::$objectCache[$this->objType()][$obj->id()] = $obj;

        return $this;
    }

    /**
     * Retrieve the object model factory.
     *
     * @return \Charcoal\Factory\FactoryInterface
     */
    abstract public function modelFactory();

    /**
     * @return string
     */
    abstract public function id();

    /**
     * Return the model type identifier.
     *
     * @see \Charcoal\Model\AbstractModel::objType()
     *
     * @return string
     */
    abstract public static function objType();
}

<?php

namespace Ezapi\Resource\Methodmap;

class Collection
{
    /**
     * @var array
     */
    protected $map = array();

    /**
     * @param array $map
     */
    public function __construct(array $map = null)
    {
        if ($map) {
            $this->setMap($map);
        }
    }

    /**
     * Set map
     * @param array $map
     * @return $this
     */
    public function setMap(array $map)
    {
        $this->map = $map;
        return $this;
    }

    /**
     * Get map
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Add item to collection
     * @param string|Collection\Item $key
     * @param null|array $map
     * @throws \InvalidArgumentException
     */
    public function add($key, $map = null)
    {
        if ($key instanceof Collection\Item) {
            $map = $key->toArray();
            $key = $map['name'];
        }
        if (!$map) {
            throw new \InvalidArgumentException('Map is not accepted', E_USER_ERROR);
        }
        $this->map[$key] = $map;
    }

    /**
     * Get item from collection
     * It converts Collection\Item to array if necessary
     * @param string $key
     * @return array
     */
    public function get($key) {
        if (is_object($this->map[$key])
            && $this->map[$key] instanceof Collection\Item) {
            $this->add($this->map[$key]);
        }
        return $this->map[$key];
    }

    /**
     * Create Collection\Item and attach to the collection
     * @param $name
     * @return Collection\Item
     */
    public function build($name) {
        $obj = new Collection\Item;
        $obj->name($name);

        $this->map[$name] = $obj;
        return $obj;
    }

} 
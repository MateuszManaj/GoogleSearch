<?php
/**
 * This is the Google search API without any authorization keys.
 *
 * See COPYING for license information.
 *
 * @author Mateusz Manaj <mmanaj@softgraf.pl>
 * @copyright Copyright (c) 2015
 * @package GoogleSearch
 */

namespace GoogleSearch;

use Countable;
use Iterator;

class GoogleSearchResults implements Iterator, Countable
{
    protected $_buffer = Array();
    protected $_position = 0;
    public $Statistics;

    public function Add(GoogleSearchResult $gsresult)
    {
        $this->_buffer[] = $gsresult;
        return $this;
    }

    /**
     * Removes the value with the specified key from the Dictionary.
     * @param $key
     * @return GoogleSearchResults
     */
    public function Remove($key)
    {
        if(isset($this->_buffer[$key])) unset($this->_buffer[$key]);
        return $this;
    }

    /**
     * Determines whether the Dictionary contains the specified key.
     * @param $key
     * @param bool $recursive
     * @return bool
     */
    public function ContainsKey($key, $recursive = false)
    {
        if(!$recursive) return isset($this->_buffer[$key]);
        $result = false;
        array_walk_recursive($this->_buffer, function($v, $k) use($key, &$result)
        {
            if($key === $k) $result = true;
        });

        return $result;
    }

    /**
     * Determines whether the Dictionary contains the specified value.
     */
    public function ContainsValue($value)
    {
        foreach($this->_buffer as $k => $v)
        {
            if($value === $v) return true;
        }

        return false;
    }

    /**
     * Returns value at specific key
     * @param $key
     * @return GoogleSearchResult
     */
    public function Get($key)
    {
        if(isset($this->_buffer[$key]))
        {
            return $this->_buffer[$key];
        }

        return null;
    }

    public function Import(array $array)
    {
        $this->_buffer = $array;
        return $this;
    }

    public function Extend(array $array)
    {
        $this->_buffer = array_merge($this->_buffer, $array);
        return $this;
    }

    function rewind() { $this->_position = 0; }
    function current() { return $this->_buffer[$this->_position]; }
    function key() { return $this->_position; }
    function next() { ++$this->_position; }
    function valid() { return isset($this->_buffer[$this->_position]); }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_buffer);
    }
}

?>
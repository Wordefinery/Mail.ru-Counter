<?php

namespace wordefinery;

class Settings
implements \ArrayAccess, \Iterator, \Countable {

    private static $___registry = array();
    private $___childs;
    private $___key;
    private $___parent;
    private $___old_value;
    private $___value;
    private $___def_value;
    private $___validator;
    private $___changed;
    private $___checked;
    private $___loaded;
    private $___mode;

    private function __construct($data, $parent = null, $key = null) {
        if (is_object($data)) {
            // todo: convert objects to array or die
            $data = '';
        }
        if (is_array($data)) {
            $this->___mode(1);
            $this->___childs = array();
            foreach ($data as $k => $v) $this->___childs[$k] = new self($v, $this, $k);
        } else {
            $this->___mode(0);
            $this->___old_value = $data;
            $this->___changed = 0;
            $this->___checked = 0;
            $this->___loaded = 0;
        }
        if (isset($parent) && $parent instanceof self) $this->___parent = $parent;
        if (isset($key)) $this->___key = $key;
    }

    public function offsetSet($key, $data) {
        if ($key === null) throw new Exception('Settings key cannot be NULL');

        $keys = explode('/', $key);
        foreach ($keys as $k=>$i) $keys[$k] = trim($i);
        $key = array_shift($keys);
        $this->___mode(1);
        if (!isset($this->___childs[$key])) {
            $this->___childs[$key] = new self(null, $this, $key);
        }
        $ret = $this->___childs[$key];
        foreach ($keys as $k) $ret = $ret[$k];
        $ret->___import($data);
    }

    public function offsetGet($key) {
        if ($key === null) throw new Exception('Settings key cannot be NULL');

        $keys = explode('/', $key);
        foreach ($keys as $k=>$i) $keys[$k] = trim($i);
        $key = array_shift($keys);
        $this->___mode(1);
        if (!isset($this->___childs[$key])) {
            $this->___childs[$key] = new self(null, $this, $key);
            $this->___changed = 1;
        }
        $ret = $this->___childs[$key];
        foreach ($keys as $k) $ret = $ret[$k];
        return $ret;
    }

    public function offsetExists($key) {
        if ($key === null) return false;

        $keys = explode('/', $key);
        foreach ($keys as $k=>$i) $keys[$k] = trim($i);
        $key = array_shift($keys);
        $this->___mode(1);
        if (!isset($this->___childs[$key])) return false;
        if (count($keys)) return $this->___childs[$key]->offsetExists(implode('/', $keys));
        return $this->___childs[$key]->count()?true:false;
    }

    public function offsetUnset($key) {
        if ($key === null) throw new Exception('Settings key cannot be NULL');

        $keys = explode('/', $key);
        foreach ($keys as $k=>$i) $keys[$k] = trim($i);
        $key = array_shift($keys);
        $this->___mode(1);
        if (isset($this->___childs[$key])) {
            if (count($keys)) return $this->___childs[$key]->offsetUnset(implode('/', $keys));
            if ($this->___childs[$key]->___unsettable()) {
                unset($this->___childs[$key]);
                $this->___changed = 1;
            }
        }
    }

    public function count() {
        if ($this->___mode == 0) {
            if (isset($this->___old_value) || isset($this->___value) || isset($this->___def_value)) return 1;
            else return 0;
        } else {
            return count($this->___childs);
        }
    }

    public function current() {
        if ($this->___mode == 0) {
            return $this->___value();
        } else {
            if (key($this->___childs)) return $this->offsetGet(key($this->___childs));
        }
    }

    public function key() {
        if ($this->___mode == 0) {
            return 0;
        } else {
            return key($this->___childs);
        }
    }

    public function next() {
        if ($this->___mode == 0) {
            return false;
        } else {
            next($this->___childs);
            return $this->current();
        }
    }

    public function rewind() {
        if ($this->___mode == 0) {
            return true;
        } else {
            return reset($this->___childs);
        }
    }

    public function valid() {
        if ($this->___mode == 0) {
            return true;
        } else {
            return $this->offsetExists(key($this->___childs));
        }
    }


    public function __clone() {
        throw new Exception('Settings cannot be clonned directly');
    }

    public function __get($key) { if ($key == 'value' && $this->___mode == 0) return $this->___value(); else return $this[$key]->___value(); }
    public function __unset($key) { unset($this[$key]); }
    public function __set($key, $data) { if ($key == 'value' && $this->___mode == 0) $this->___import($data); else  $this[$key] = $data; }
    public function __isset($key) { return isset($this[$key]); }
    public function __call($key, $args) { return $this[$key]; }

    public function __toString() { return $this->___mode ? print_r($this->__toArray(), true) : (string) $this->___value(); }

    public function __toArray() {
        $data = array();
        if ($this->___mode == 0) {
            $data = $this->___value();
        } else {
            foreach ($this->___childs as $key => $value) {
                $data[$key] = $value->__toArray();
            }
        }
        if (!$this->___parent && !is_array($data)) $data = array('value'=>$data);
        return $data;
    }

    private function ___value() {
        if ($this->___mode == 0) {
            if ($this->___loaded == 0 && (isset($this->___old_value) || isset($this->___def_value))) {
                $this->___value = isset($this->___old_value) ? $this->___old_value : $this->___def_value;
                $this->___loaded = 1;
            }
            return $this->___value;
        } else {
            return $this;
        }
    }

    private function ___import($data) {
        if (is_object($data)) {
            // todo: convert objects to array or die
            $data = '';
        }
        if (is_array($data)) {
            $this->___mode(1);
            foreach ($data as $k => $v) $this[$k] = $v;
        } else {
            $this->___mode(0);
            $this->___value = $this->___def_value($this->___check($data));
            $this->___changed = (string) $this->___value != (string) $this->___old_value;
            $this->___loaded = 1;
        }
    }

    private function ___mode($mode) {
        if ($mode == 1) {
            if ($this->___mode == 1) return;
            if ($this->___mode == 0 && $this->___loaded == 1) throw new Exception('Settings ['.$this->___key().'] cannot be array');
            $this->___old_value = null;
            $this->___value = null;
            $this->___checked = null;
            $this->___changed = 1;
            $this->___loaded = null;
            $this->___childs = array();
            $this->___mode = 1;
        } else {
            if ($this->___mode == 0) return;
            if ($this->___mode == 1 && $this->___loaded() == 1) throw new Exception('Settings ['.$this->___key().'] cannot be scalar');
            $this->___childs = null;
            $this->___changed = 1;
            $this->___mode = 0;
        }
    }

    private function ___loaded() {
        if ($this->___mode == 0) {
            return $this->___loaded;
        } else {
            foreach ($this->___childs as $value) if ($value->___loaded()) return true;
            return false;
        }
    }

    private function ___changed() {
        if ($this->___mode == 0) {
            return $this->___changed;
        } else {
            if ($this->___changed) return true;
            foreach ($this->___childs as $value) if ($value->___changed()) return true;
            return false;
        }
    }

    private function ___unsettable() {
        if ($this->___mode == 0) {
            return !$this->___def_value;
        } else {
            foreach ($this->___childs as $value) if (!$value->___unsettable()) return false;
            return true;
        }
    }

    private function ___unset() {
        if ($this->___mode == 0) {
            $this->___value = $this->___def_value;
        } else {
            foreach ($this->___childs as $value) $value->___unset();
        }
    }

    private function ___key() {
        if ($this->___parent) return $this->___parent->___key() . '/' . $this->___key;
        else return $this->___key;
    }

    private function ___main_key() {
        if ($this->___parent) return $this->___parent->___main_key();
        else return $this->___key;
    }

    public function export() {
        $data = array();
        if ($this->___mode == 0) {
            $data = $this->___value();
            $this->___old_value = $this->___value;
        } else {
            foreach ($this->___childs as $key => $value) {
                $data[$key] = $value->export();
            }
        }
        $this->___changed = 0;
        return $data;
    }

    public function commit() {
        if ($this->___parent) return $this->___parent->commit();
        if ($this->___changed()) {
            $option = $this->export();
            \remove_filter('pre_update_option_' . $this->___key, array($this, '___pre_update'));
            \remove_filter('pre_option_' . $this->___key, array($this, '___pre'));
            \update_option($this->___key, $option);
            \add_filter('pre_update_option_' . $this->___key, array($this, '___pre_update'));
            \add_filter('pre_option_' . $this->___key, array($this, '___pre'));
        }
    }

    public function ___pre_update($newvalue, $oldvalue = null) {
        if ($this->___parent) return $oldvalue;

        if (is_array($newvalue) && isset($newvalue['__section__'])) {
            $r = $newvalue['__section__'];
            unset($newvalue['__section__']);
            $this[$r] = $newvalue;
        } else {
            $this->___import($newvalue);
        }
        return $this->export();
    }

    public function ___pre() {
        $ret = $this->__toArray();
        if (count($ret) == 1 && key($ret) == 'value') $ret = $ret['value'];
        return $ret;
    }

    private function ___check($data = null) {
        $this->___checked = 1;
        if (!isset($this->___validator)) return $data;
        if (is_callable($this->___validator)) {
            try {
                $ret = \call_user_func($this->___validator, $data);
                if (isset($ret)) $data = $ret;
            } catch (SettingsValidateException $e) {
                $this->___checked = 0;
                if ( function_exists('\\add_settings_error') )
                    \add_settings_error($this->___main_key(), $this->___key, $e->getMessage(), $e->getType());
                if ($e->getType() !== 'error') return $data;
                else return $this->___old_value;
            } catch (Exception $e) {

            }
        }
        return $data;
    }

    public function validator($func = null) {
        if (is_array($func) && !is_callable($func)) {
            $this->___mode(1);
            foreach ($func as $k=>$f) $this[$k]->validator($f);
        } else {
            $this->___mode(0);
            $this->___validator = $func;
        }
    }

    private function ___def_value($data = null) {
        if (!isset($this->___def_value)) return $data;
        if (!isset($data)) return $this->___def_value;
//        if ($data === '' && is_string($this->___def_value) && $this->___def_value !== '') return $this->___def_value;
        return $data;
    }

    public function defvalue($data = null) {
        if (!isset($data)) return;
        if (is_array($data)) {
            $this->___mode(1);
            foreach ($data as $k=>$d) $this[$k]->defvalue($d);
        } else {
            $this->___mode(0);
            $this->___def_value = $data;
            $this->___value();
        }
    }

    public static function bind($keys) {
        if (is_array($keys)) $keys = implode('/', $keys);
        $keys = explode('/', $keys);
        foreach ($keys as $k=>$i) $keys[$k] = trim($i);
        $key = array_shift($keys);

        if (!self::$___registry[$key]) {
            $option = \get_option($key);
            if ($option === false) \add_option($key);
            self::$___registry[$key] = new self($option, null, $key);
            \add_filter('pre_update_option_' . $key, array(self::$___registry[$key], '___pre_update'));
            \add_filter('pre_option_' . $key, array(self::$___registry[$key], '___pre'));
        }

        $ret = self::$___registry[$key];
        foreach ($keys as $k) $ret = $ret[$k];

        return $ret;
    }
}
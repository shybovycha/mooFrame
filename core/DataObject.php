<?php
    class DataObject
    {
        private $__data = array();

        public function __construct($data = null)
        {
            if (isset($data))
            {
                if (is_array($data))
                {
                    foreach ($data as $k => $v)
                        $this->__data[$k] = $v;
                } else
                if (is_object($data))
                {
                    $data = get_object_vars($data);

                    foreach ($data as $k => $v)
                        $this->__data[$k] = $v;
                }
            }
        }

        public function __get($key)
        {
            if (isset($this->__data[$key]))
                return $this->__data[$key]; else
                    return NULL;
        }

        public function __set($key, $value)
        {
            $this->__data[$key] = $value;
        }

        public function get($key, $v = NULL)
        {
            if (isset($this->__data[$key]))
                return $this->__data[$key]; else
                    return $v;
        }
    }
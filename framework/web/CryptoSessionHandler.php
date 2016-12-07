<?php

namespace yii\web;

class CryptoSessionHandler extends \SessionHandler
{
    /**
     * @var string|callable The cryptograpic key as a binary string or function returning a binary string.
     * Function takes session id as parameter.
     */
    protected $key;
    /**
     * @var \SessionHandlerInterface
     */
    protected $handler;

    private $_key;

    /**
     * CryptoSessionHandler constructor.
     * @param string|callable $key The cryptograpic key as a binary string or function
     * returning a binary string. Function takes session id as parameter.
     * @param \SessionHandlerInterface $handler An optional custom session handler. Leave
     * unset to use built-in PHP session handler.
     */
    public function __construct($key, \SessionHandlerInterface $handler = null)
    {
        $this->key = $key;
        $this->handler = $handler;
    }

    protected function getKey($id)
    {
        if ($this->_key === null) {
            $this->_key = is_callable($this->key) ? call_user_func($this->key, $id) : $this->key;
        }

        return $this->_key;
    }

    public function write($id, $data)
    {
        $data = \Yii::$app->security->encryptByKey($data, $this->getKey($id));

        return $this->handler ? $this->handler->write($id, $data) : parent::write($id, $data);
    }

    public function read($id)
    {
        $data = $this->handler ? $this->handler->read($id) : parent::read($id);
        if (!$data) {
            return '';
        }

        return \Yii::$app->security->decryptByKey($data, $this->getKey($id));
    }
}

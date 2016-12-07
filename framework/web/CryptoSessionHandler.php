<?php

namespace yii\web;

class CryptoSessionHandler extends \SessionHandler
{
    /**
     * @var string|callable
     */
    protected $key;
    /**
     * @var \SessionHandlerInterface
     */
    protected $handler;

    /**
     * CryptoSessionHandler constructor.
     * @param string|callable $key
     * @param \SessionHandlerInterface $handler
     */
    public function __construct($key, \SessionHandlerInterface $handler = null)
    {
        $this->key = $key;
        $this->handler = $handler;
    }

    public function write($id, $data)
    {
        $key = is_callable($this->key) ? call_user_func($this->key, $id) : $this->key;
        $data = \Yii::$app->security->encryptByKey($data, $key);

        return $this->handler ? $this->handler->write($id, $data) : parent::write($id, $data);
    }

    public function read($id)
    {
        $data = $this->handler ? $this->handler->read($id) : parent::read($id);
        if (!$data) {
            return '';
        }

        $key = is_callable($this->key) ? call_user_func($this->key, $id) : $this->key;

        return \Yii::$app->security->decryptByKey($data, $key);
    }
}

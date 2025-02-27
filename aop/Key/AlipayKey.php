<?php

namespace Alipay\Key;

use Alipay\Exception\AlipayInvalidKeyException;
use Alipay\Exception\AlipayOpenSslException;
use Serializable;

abstract class AlipayKey implements Serializable
{
    protected $resource;

    protected function __construct()
    {
    }

    /**
     * 创建密钥.
     *
     * @param $key
     *
     * @throws AlipayInvalidKeyException
     *
     * @return static
     */
    public static function create($key)
    {
        $instance = new static();
        $instance->load($key);

        return $instance;
    }

    /**
     * 加载密钥.
     *
     * @param string $certificate 密钥字符串或密钥路径
     *
     * @throws AlipayInvalidKeyException
     *
     * @return void
     */
    protected function load($certificate)
    {
        if ($this->isLoaded()) {
            throw new AlipayInvalidKeyException('Resource of key has already been initialized');
        }

        $this->resource = static::getKey($certificate);
    }

    /**
     * 检查此密钥是否可用.
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->resource !== null && is_resource($this->resource);
    }

    /**
     * 加载密钥资源.
     *
     * @param $certificate
     *
     * @throws AlipayInvalidKeyException
     */
    public static function getKey($certificate)
    {
        throw new AlipayInvalidKeyException(openssl_error_string()." ($certificate)");
    }

    /**
     * 使用密钥资源直接初始化本对象.
     *
     * @param resource $resource
     *
     * @return static
     */
    public static function fromResource($resource)
    {
        $instance = new static();
        $instance->resource = $resource;

        return $instance;
    }

    public function __destruct()
    {
        $this->release();
    }

    /**
     * 释放密钥资源.
     *
     * @return void
     */
    protected function release()
    {
        if ($this->isLoaded()) {
            @openssl_free_key($this->resource);
        }
        $this->resource = null;
    }

    /**
     * 深拷贝需要重新加载密钥.
     *
     * @throws AlipayInvalidKeyException
     */
    public function __clone()
    {
        $key = $this->asString();
        $this->resource = null;
        $this->load($key);
    }

    /**
     * 获取密钥字符串.
     *
     * @throws AlipayOpenSslException
     */
    public function asString()
    {
        return static::toString($this->resource);
    }

    /**
     * 将密钥资源转为字符串.
     *
     * @param $resource
     *
     * @throws AlipayOpenSslException
     */
    public static function toString($resource)
    {
        throw new AlipayOpenSslException();
    }

    /**
     * 获取真实的密钥资源.
     *
     * @return resource
     */
    public function asResource()
    {
        return $this->resource;
    }

    public function serialize()
    {
        return $this->asString();
    }
    public function __serialize()
    {
        return $this->asString();
    }
    public function unserialize($data)
    {
        $this->load($data);
    }
    public function __unserialize($data)
    {
        $this->load($data);
    }
}

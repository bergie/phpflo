<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Interaction;

use Evenement\EventEmitter;
use PhpFlo\Common\SocketInterface;

/**
 * Class AbstractPort
 *
 * @package PhpFlo\Interaction
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class AbstractPort extends EventEmitter
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var null
     */
    protected $socket;

    /**
     * @var null
     */
    protected $from;

    /**
     * @var array
     */
    public static $datatypes = [
        'all',
        'bang',
        'string',
        'bool',
        'boolean',
        'number',
        'int',
        'integer',
        'object',
        'array',
        'date',
        'function',
    ];

    /**
     * @param string $name
     * @param array $attributes
     */
    public function __construct($name, array $attributes)
    {
        $defaultAttributes = [
            'datatype' => 'all',
            'required' => false,
            'cached' => false,
            'addressable' => false,
        ];

        $this->name = $name;
        $this->attributes = array_replace($defaultAttributes, $attributes);
        $this->socket = null;
        $this->from = null;
    }

    /**
     * Compare in and outport datatypes.
     *
     * @param string $fromType
     * @param string $toType
     * @return bool
     */
    public static function isCompatible($fromType, $toType)
    {
        switch(true) {
            case (($fromType == $toType) || ($toType == 'all' || $toType == 'bang')):
                $isCompatible = true;
                break;
            case (($fromType == 'int' || $fromType == 'integer') && $toType == 'number'):
                $isCompatible = true;
                break;
            default:
                $isCompatible = false;
        }

        return $isCompatible;
    }

    /**
     * @param SocketInterface $socket
     */
    public function onConnect(SocketInterface $socket)
    {
        $this->emit('connect', [$socket]);
    }

    /**
     * @param SocketInterface $socket
     */
    public function onDisconnect(SocketInterface $socket)
    {
        $this->emit('disconnect', [$socket]);
    }

    public function onDetach()
    {
        $this->emit('detach', [$this->socket]);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getAttribute($name)
    {
        $attribute = null;

        if (array_key_exists($name, $this->attributes)) {
            $attribute = $this->attributes[$name];
        }

        return $attribute;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param SocketInterface $socket
     */
    protected function attachSocket(SocketInterface $socket)
    {
        $this->emit('attach', [$socket]);

        $this->from = $socket->from;

        $socket->on('connect', [$this, 'onConnect']);
        $socket->on('begin.group', [$this, 'onBeginGroup']);
        $socket->on('data', [$this, 'onData']);
        $socket->on('end.group', [$this, 'onEndGroup']);
        $socket->on('disconnect', [$this, 'onDisconnect']);
        $socket->on('detach', [$this, 'onDetach']);
        $this->once('shutdown', [$this, 'onShutdown']);
    }
}

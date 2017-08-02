<?php
class APF_APS_Client12
{
    const VERSION = 'APS12';
    static $context = null;
    static $poller = null;
    static $pending_requests = array();
    static $pending_replies = array();
    static $pending_callbacks = array();
    static $sequence = 1000;

    static $default_timeout = 1; // timeout while wait for reply. don't be null
    static $default_abandon = true; // will the requests be abandoned if timeout

    public function __construct($spID=null, $spVer=null, $sender=null,
                                $linger=100, $sndhwm=1000, $rcvhwm=1000) {
        if (self::$context === null) {
            self::$context = new ZMQContext(1, false);
            self::$poller = new ZMQPoll();
        }

        $socket = new ZMQSocket(self::$context, ZMQ::SOCKET_DEALER);
        $socket->setsockopt(ZMQ::SOCKOPT_LINGER, $linger);
        $socket->setsockopt(ZMQ::SOCKOPT_SNDHWM, $sndhwm);
        $socket->setsockopt(ZMQ::SOCKOPT_RCVHWM, $rcvhwm);

        self::$poller->add($socket, ZMQ::POLL_IN);

        $this->socket = $socket;

        $this->spID = $spID;
        $this->spVer = $spVer;
        $this->sender = $sender;
    }

    public function __destruct() {
        self::$poller->remove($this->socket);
        $endpoints = $this->socket->getEndpoints();
        foreach ($endpoints["connect"] as $endpoint) {
            $this->socket->disconnect($endpoint);
        }
        unset($this->socket);
    }

    public function connect($endpoint) {
        $this->socket->connect($endpoint);
    }

    public function disconnect($endpoint) {
        $this->socket->disconnect($endpoint);
    }

    public function start_request($method, $params=null, $expiry=null,
                                  $callback=null, $extras=null) {
        $ref = $this->oneway_request($method, $params, $expiry, $extras);
        if ($ref !== false) {
            self::$pending_requests[$ref] = true;
            if ($callback) {
                self::$pending_callbacks[$ref] = $callback;
            }
        }
        return $ref;
    }

    public function oneway_request($method, $params=null, $expiry=null,
                                   $extras=null) {
        $ref = (++self::$sequence) % 1073741824;

        $frames[] = '';
        $frames[] = self::VERSION;
        $frames[] = msgpack_pack(array($ref, microtime(true), $expiry));
        $frames[] = $this->spID ? ":{$this->spID}:{$method}" : $method;
        $frames[] = msgpack_pack($params);
        if ($this->sender) {
            $frames[] = msgpack_pack(array('Sender', $this->sender));
        }
        if ($this->spVer) {
            $frames[] = msgpack_pack(array('Version', $this->spVer));
        }
        if (is_array($extras)) {
            foreach ($extras as $extra) {
                $frames[] = msgpack_pack($extra);
            }
        }
        if ($this->socket->sendmulti($frames, ZMQ::MODE_DONTWAIT) == false) {
            APF::get_instance()->get_logger()->error('APS[send]:error');
            return false;
        }

        return $ref;
    }

    public static function wait_for_replies($refs=null, $timeout=null,
                                            $abandon=null) {
        $replies = array();
        $waiting = array();

        if ($refs === null) {
            $refs = array_merge(
                array_keys(self::$pending_requests),
                array_keys(self::$pending_replies)
            );
        } elseif (!is_array($refs)) {
            $refs = array($refs);
        }

        foreach ($refs as $ref) {
            if (array_key_exists($ref, self::$pending_requests)) {
                $waiting[$ref] = true;
            } elseif (array_key_exists($ref, self::$pending_replies)) {
                $reply = self::$pending_replies[$ref];
                $replies[$ref] = $reply;
                unset(self::$pending_replies[$ref]);
                self::process_reply($reply);
            }
        }

        $readable = $writeable = array();
        if ($timeout === null) {
            $timeout = self::$default_timeout;
        }
        if ($timeout !== -1) {
            $bt = microtime(true);
            $timeout_millis = round($timeout * 1000);
        } else {
            $timeout_millis = -1;
        }
        while (count($waiting) > 0) {
            $events = self::$poller->poll($readable, $writeable,
                                          $timeout_millis);
            if ($events == 0) {
                APF::get_instance()->get_logger()->error('APS[poll]:events = 0');
                break;
            }

            foreach ($readable as $socket) {
                while ($frames = $socket->recvmulti(ZMQ::MODE_DONTWAIT)) {
                    $reply = new StdClass();
                    $sep = array_search('', $frames);
                    $reply->version = $frames[$sep+1];
                    list($reply->sequence, $reply->timestamp, $reply->status) =
                        msgpack_unpack($frames[$sep+2]);
                    $reply->result = msgpack_unpack($frames[$sep+3]);
                    if (count($frames) > $sep+4) {
                        $extras = array();
                        foreach (array_slice($frames, $sep+4) as $extra) {
                            $extras[] = msgpack_unpack($extra);
                        }
                        $reply->extras = $extras;
                    } else {
                        $reply->extras = null;
                    }

                    $ref = $reply->sequence;
                    unset(self::$pending_requests[$ref]);
                    if (array_key_exists($ref, $waiting)) {
                        $replies[$ref] = $reply;
                        unset($waiting[$ref]);
                        self::process_reply($reply);
                    } else {
                        self::$pending_replies[$ref] = $reply;
                    }
                }
            }

            if ($timeout != -1) {
                $timeout_millis = round(($timeout - microtime(true) + $bt) * 1000);
                if ($timeout_millis <= 0) {
                    APF::get_instance()->get_logger()->error('APS[poll]:timeout');
                    break;
                }
            }
        }

        if ($abandon === null) {
            $abandon = self::$default_abandon;
        }
        if ($abandon) {
            self::abandon_requests($waiting);
        }

        return $replies;
    }

    public static function abandon_requests($refs=null) {
        if ($refs === null) {
            self::$pending_requests = array();
            self::$pending_replies = array();
            self::$pending_callbacks = array();
            return;
        }

        if (!is_array($refs)) {
            $refs = array($refs);
        }
        foreach ($refs as $ref) {
            unset(self::$pending_requests[$ref]);
            unset(self::$pending_callbacks[$ref]);
            unset(self::$pending_replies[$ref]);
        }
    }

    protected static function process_reply($reply) {
        $ref = $reply->sequence;
        if (array_key_exists($ref, self::$pending_callbacks)) {
            $callback = self::$pending_callbacks[$ref];
            unset(self::$pending_callbacks[$ref]);
            call_user_func_array($callback,
                array($reply->result, $reply->status, $reply->extras));
        }
    }
}

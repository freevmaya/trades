<?php
    // dev: Frolov Vadim fwadim@mail.ru
    
    define('MEMCACHEHOST', '127.0.0.1'); // ���� �������
    define('MEMCACHEPORT', 11211);  // ���� ������� 
    define('MEMCACHEEXPIRE', 60 * 60); // ����� ����� 
    define('MEMCACHEFLAGS', MEMCACHE_COMPRESSED); // ����, � �����������

    class MCache {
        protected static $memcache;
        
        protected static function connect() {
            if (class_exists('Memcache')) {
                MCache::$memcache = new Memcache();
                if (@MCache::$memcache->connect(MEMCACHEHOST, MEMCACHEPORT)) return true;
                else {
                    MCache::$memcache = null;
                    return false;
                }
            } else return false;
        }
        
        public static function active() {
            return MCache::$memcache != null;
        } 
        
        public function get($key) {
            if (!MCache::active()) MCache::connect();
            if (MCache::active()) return MCache::$memcache->get($key, MEMCACHEFLAGS);
            else return false;
        }
        
        public function set($key, $value, $expire=MEMCACHEEXPIRE, $method='') {
            if (!MCache::active()) MCache::connect();
            $mobj = $method?$value->$method():$value;
            if (MCache::active()) {
                MCache::$memcache->set($key, $mobj, MEMCACHEFLAGS, time() + $expire);
                //trace("SET CACHE {$key}", 'file', 3);
            }
            return $mobj; //.= '<br>mc';
        } 
        
        // getValue - ������������� �������. ���������: 
        //      $key - ���� ��� ��� ���� ��������, 
        //      $value - ������� ������������ ������ ��� ������ ��� ���������� � ���, 
        //      $expire - ����� ����� ����
        //      $method - ���� $value ��� ������, ����� �������� ������ ������������� ������ �������� ��� ���������� � ���
        public function getValue($key, $value, $expire=MEMCACHEEXPIRE, $method='') {
            if (!$mobj = MCache::get($key)) 
                $mobj = MCache::set($key, $value, $expire, $method);
            return $mobj;
        }
        
        public function delete($key) {
            if (!MCache::active()) MCache::connect();
            return MCache::active()?MCache::$memcache->delete($key):false;
        } 
    }

    class ArrCache {
        private $cache;
        function __construct() {
            $this->cache = [];
        }

        function get($key) {
            return @$this->cache[$key];
        }

        function set($key, $value, $expire=MEMCACHEEXPIRE, $method='') {
            return $this->cache[$key] = $value;
        }
    }
?>
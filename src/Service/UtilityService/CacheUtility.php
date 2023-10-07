<?php

namespace App\Service\UtilityService;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;

/**
 * Description of DependenciesModules
 * @author alessandro
 */
class CacheUtility
{
    const MEMCACHE_COMPRESSED = 1;
    protected $container;
    public $phpCache;
    protected $typeLibrary        = 'memcached';
    protected $phpCacheIsConnect  = false;
    private $myCachePool;

    public function __construct(Container $container, TagAwareCacheInterface $myCachePool)
    {
        $this->container        = $container;
        $this->myCachePool      = $myCachePool;
        $this->typeLibrary      = $this->container->getParameter('handler_cache');
    }

    /**
     * Metodo che ritorna la connessione a memcached o a memcache in base al tipo di libreria
     * @return type
     */
    public function initPhpCache($channel = 'myCachePool')
    {
//        if( empty( $this->phpCacheIsConnect ) ) {
        switch ($this->typeLibrary) {
            case 'myCachePool':
                $this->phpCache = $this->myCachePool;
                $this->phpCacheIsConnect = true;

                $client = RedisAdapter::createConnection('redis://localhost/1');
                $this->phpCache = new RedisAdapter($client);
                break;
            case 'redis':
                $this->startRedis($channel);
                break;
            case 'memcached':
                $this->startMemcached();
                break;
            case 'memcache':
                $this->startMemcache();
                break;
        }
//        }
        return $this->phpCache;
    }

    /**
     * Metodo che chiude la connessione a memcached o a memcache  in base al tipo di libreria
     */
    public function closePhpCache()
    {
        switch ($this->typeLibrary) {
            case 'memcached':
                $this->phpCache->quit();
                break;
            case 'memcache':
                $this->phpCache->close();
                break;
        }
    }

    public function phpCacheGet($key, $convert = true, $toArray = false)
    {
        switch ($this->typeLibrary) {
            case 'redis':
                $value = $this->phpCache->get($key);
                if ($convert && $this->isJson($value)) {
                    $value = json_decode($value, $toArray);
                }

                return $value;
                break;
            case 'myCachePool':
                return $this->phpCache->getItem($key)->get();
            break;
            default:
                return $this->phpCache->get($key);
            break;
        }
    }

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function phpCacheSet($key, $value, $ttl = 3600)
    {
        switch ($this->typeLibrary) {
            case 'redis':
                if (is_object($value) || is_array($value)) {
                    $value = json_encode($value);
                }

                return $this->phpCache->setEx($key, $ttl, $value);
            break;
            case 'memcached':
                return $this->phpCache->set($key, $value, $ttl);
            break;
            case 'memcache':
                return $this->phpCache->set($key, $value, self::MEMCACHE_COMPRESSED, $ttl);
            break;
            case 'myCachePool':
                $userFriends = $this->phpCache->getItem($key, function (ItemInterface $item) {
                });
                $userFriends->set($value);
                return $this->phpCache->save($userFriends);
            break;
        }
    }

    /**
     * Metodo che cancella la cache
     */
    public function phpCacheRemove($key)
    {
        switch ($this->typeLibrary) {
            case 'memcached':
                return $this->phpCache->delete($key);
            break;
            case 'memcache':
                return $this->phpCache->delete($key);
            break;
            case 'myCachePool':
                return $this->phpCache->deleteItem($key);
            break;
        }
    }

    /**
     * Metodo che forza il TTl dei dati delle query
     */
    public function setExpire($region, $ttl)
    {
        switch ($this->typeLibrary) {
            case 'myCachePool':
                $keys = $this->phpCache->getItemsByPattern('*');
                foreach ($keys as $key) {
                    if (strpos($key, 'my_' . $region . '_region', '0') !== false) {
                        $this->phpCache->getItem($key, function (ItemInterface $item) {
                            $item->expiresAfter(3600);
                        });
                    }
                }
                break;
            default:
                foreach ($this->phpCache->keys('*') as $key) {
                    if (strpos($key, 'my_' . $region . '_region', '0') !== false) {
                        $realTTL =  $this->phpCache->ttl($key);

                        if (!empty($realTTL) && $realTTL > $ttl) {
                            $this->phpCache->expire($key, $ttl);
                        }
                    }
                }
                break;
        }
    }

    //"my_images_data_article_region_result[my_images_data_article_region_result_appbundle.entity.dataarticle_244__images][1]"
    public function removeKey($region, $idKey = false)
    {
        switch ($this->typeLibrary) {
            case 'myCachePool':
                $keys = $this->phpCache->getItemsByPattern('*');
                foreach ($keys as $key) {
                    if (!empty($idKey) && strpos($key, $idKey, '0') !== false && strpos($key, $region, '0') !== false) {
                        $this->phpCache->deleteItem($key);
                    } elseif (empty($idKey) && strpos($key, $region, '0') !== false) {
                        echo 'cancello2';
                        $this->phpCache->deleteItem($key);
                    }
                }
                break;
            default:
                foreach ($this->phpCache->keys('*') as $key) {
                    if (!empty($idKey) && strpos($key, $idKey, '0') !== false && strpos($key, $region, '0') !== false) {
        //                echo 'cancello1';
                        $this->phpCache->delete($key);
                    } elseif (empty($idKey) && strpos($key, $region, '0') !== false) {
                        echo 'cancello2';
                        $this->phpCache->delete($key);
                    }
                }
                break;
        }
    }


    //http://www.thegeekstuff.com/2014/02/phpredis
    private function startRedis($channel)
    {
//        if( empty( $this->phpCacheIsConnect ) ) {
            $this->phpCache = $this->container->get('snc_redis.' . $channel);
            $this->phpCacheIsConnect = true;
//        }
    }


    /**
     * Metodo che  avvia la connesione con Memcache
     * @param string $host
     * @param string $port
     */
    public function startMemcached($host = "localhost", $port = "11211", $isActiveMemcache = true)
    {
//        if ( !class_exists( 'Memcached' ) )
//            $this->config->isActiveMemcache = false;

        // $this->phpCache  = new \Memcached( );
        // $this->phpCache->setOption(\Memcached::OPT_COMPRESSION, true);
        // $this->phpCache->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 2000);

        // // Add server if no connections listed.
        // if ( !count( $this->phpCache->getServerList() ) ) {
        //     $this->phpCache->addServer( $this->container->getParameter( 'session_memcached_host' ), $this->container->getParameter( 'session_memcached_port' ) );
        // }
        // $this->phpCacheIsConnect = true;
    }

    /**
     * Metodo che  avvia la connesione con Memcache
     * @param string $host
     * @param string $port
     */
    public function startMemcache($host = "localhost", $port = "11211", $isActiveMemcache = true)
    {
//        if (!class_exists('Memcache'))
//            exit;
//            $this->config->isActiveMemcache = false;

//         $this->phpCache = new \Memcache();
//         $this->phpCache->addServer( $this->container->getParameter( 'session_memcached_host' ), $this->container->getParameter( 'session_memcached_port' ), true );
//         $stats = @$this->phpCache->getExtendedStats();
// //        $available = (bool) $stats["$host:$port"];

        $this->phpCacheIsConnect = true;
    }
}//End Class

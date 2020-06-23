<?php
//version 0.0.1

$mageFilename = '../app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app();

/**
* Set up the redis client
*/
$_redis = new Credis_Client('localhost', 6379, 90, true);
$_redis->select(0) || Zend_Cache::throwException('The redis database could not be selected.');
/**
* Set up the Session Model to help with compression and other misc items.
*/
$redisSession = new \Cm_RedisSession_Model_Session_Handler();

/**
* Get the resource model
*/
$resource = Mage::getSingleton('core/resource');
/**
* Retrieve the read connection
*/
$readConnection = $resource->getConnection('core_read');

// 1. query to order by session_expires, limit N, save last expire time, and session_id
// 2. modify query with where session_expires >= last expire time, and session_id != session_id
$exptime = 0;
$lastid = 'NONE';
$batchlimit = 100;
do {
    //$query = 'SELECT session_id, session_expires, session_data FROM ' . $resource->getTableName('core/session').
    //         ' WHERE session_expires >= ? AND session_id != ? ORDER BY session_expires ASC LIMIT '.$batchlimit;
    //$results = $readConnection->fetchAll($query, array($exptime, $lastid));
    $query = $readConnection->select()
                        ->from(array('cs'=>$resource->getTableName('core/session')),
                               array('session_id', 'session_expires', 'session_data'))
                        ->where("session_expires > ?", $exptime)
                        ->where("session_id != ?", $lastid)
                        ->limit($batchlimit)
                        ->order('session_expires');
    $results = $readConnection->fetchAll($query);
    //var_dump($results);
    foreach($results as $row) {
        $lastid = $row['session_id'];
        $exptime = $row['session_expires'];
        $sesskey = $lastid;
        $redisSession->writeRawSession($sesskey, $row['session_data'], $exptime);
        echo $lastid . " " . date('Y-m-d H:i:s', $exptime) . "\n";
    }
    echo "----------------------------------\n";
} while( !empty($results) );


<?php
/**
 * Default configuration for Xhgui
 */

return array(
    'debug'     => false,
    'mode'      => 'development',

    // Can be mongodb, file, upload or pdo.

    // For file
    //'save.handler'                    => 'file',
    //'save.handler.filename'           => dirname(__DIR__) . '/cache/' . 'xhgui.data.' . microtime(true) . '_' . substr(md5($_SERVER['REQUEST_URI']), 0, 6),
    //'save.handler.separate_meta'      => false,

    // serialize handler for all compatible data: json, serialize, igbinary. This affects only serialization to files
    // because mongo handler and db handlers use json for native database support. Defaults to json
    //'save.handler.serializer'        => 'json',

    // to make output compatible with old xhprof gui set
    //      save.handler.serializer     to serialize,
    //      save.handler.separate_meta  to true
    //      save.handler.filename       to dirname(__DIR__).'/cache/' . \Xhgui_Util::getXHProfFileName . '.data.xhprof'


    // For upload
    // Saving profile data by upload is only recommended with HTTPS
    // endpoints that have IP whitelists applied.
    //
    // The timeout option is in seconds and defaults to 3 if unspecified.
    //
    //'save.handler'                => 'upload',
    //'save.handler.upload.uri'     => 'https://example.com/run/import',
    //'save.handler.upload.timeout' => 3,


    // For MongoDB
    //'save.handler'  => 'mongodb',
    //'db.host'       => 'mongodb://127.0.0.1:27017',
    //'db.db'         => getenv('XHGUI_MONGO_DB') ?: 'xhprof',


    // for PDO
    //'save.handler'  => 'pdo',
    //'db.dsn'        => 'sqlite:/var/www/web/test.sq3',


    // authentication for db (for both pdo AND mongo)
    //'db.user'       => '',
    //'db.password'   => '',

    // Allows you to pass additional options like replicaSet to MongoClient or pdo settings.
    'db.options'        => array(),

    // store extra data in profile information, for example information about db queries
    //'additional_data'    => ['DB_PROFILE']

    // call fastcgi_finish_request() in shutdown handler
    'fastcgi_finish_request' => true,

    // Profile x in 100 requests. (E.g. set XHGUI_PROFLING_RATIO=50 to profile 50% of requests)
    // You can return true to profile every request.
    'profiler.enable' => function() {
        $ratio = getenv('XHGUI_PROFILING_RATIO') ?: 100;
        return (getenv('XHGUI_PROFILING') !== false) && (mt_rand(1, 100) <= $ratio);
    },

    'profiler.simple_url' => function($url) {
        return preg_replace('/\=\d+/', '', $url);
    },
    
    //'profiler.replace_url' => function($url) {
    //    return str_replace('token', '', $url);
    //},

    // Options passed to (uprofiler|tideways|xhprof)_enable. Mainly ignored_functions list
    'profiler.options' => array(),


    // UI related settings
    'templates.path'    => dirname(__DIR__) . '/src/templates',
    'date.format'       => 'M jS H:i:s',
    'detail.count'      => 6,
    'page.limit'        => 25,
);

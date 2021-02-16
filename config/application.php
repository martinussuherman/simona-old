<?php
    $applicationConfig = [
		'name' => 'API SIMONA',
		'version' => 'v1',

		'database' => [
            'host' => 'localhost',
			'username' => 'postgres',
            'password' => 'y8pgGSC2jALpBaJD',
            'port' => 5503,
			'dbname' => 'simyanfar',
			'schema' => 'sch_simona'
        ], 

		'jwt_secret_key' => 'Z9Xys6VNUScmbzCl8dFMx28wylPzGK3dsF4CYqDEZHkwk60OXTpxPTN8WYhp5W8',

		'middlewares' => [
            'before' => [
                'NotFoundMiddleware',
                'AuthMiddleware',
                'ContentTypeValidationMiddleware'
            ],
            'after' => [
                'TransformResponseMiddleware'
            ]
        ],

        'log' => [
            'prefix' => 'simona',
            'dir' => '../logs/'
        ],

        'routes' => [
            [
                'prefix' => '/v1/account',
                'controller' => 'AccountController',
                'paths' => [
                    [ 'verb' => 'get', 'path' => '/', 'method' => 'index', 'name' => 'account_index' ],
                    [ 'verb' => 'post', 'path' => '/register', 'method' => 'register', 'name' => 'account_register' ],
                    [ 'verb' => 'post', 'path' => '/login', 'method' => 'login', 'name' => 'account_login' ],
                    [ 'verb' => 'post', 'path' => '/logout', 'method' => 'logout', 'name' => 'account_logout' ],
                    [ 'verb' => 'post', 'path' => '/forgot-password', 'method' => 'forgotPassword', 'name' => 'account_forgotpassword' ],
                    [ 'verb' => 'post', 'path' => '/change-password', 'method' => 'changePassword', 'name' => 'account_changepassword' ],
                ]
            ],
            [
                'prefix' => '/v1/wilayah',
                'controller' => 'WilayahController',
                'paths' => [
                    [ 'verb' => 'get', 'path' => '/', 'method' => 'index', 'name' => 'wilayah_index' ],
                    [ 'verb' => 'get', 'path' => '/provinsi', 'method' => 'provinsi', 'name' => 'wilayah_provinsi' ],
                    [ 'verb' => 'get', 'path' => '/kabupatenkota', 'method' => 'kabupatenkota', 'name' => 'wilayah_kabupatenkota' ],
                    [ 'verb' => 'get', 'path' => '/kecamatan', 'method' => 'kecamatan', 'name' => 'wilayah_kecamatan' ],
                    [ 'verb' => 'get', 'path' => '/kelurahandesa', 'method' => 'kelurahandesa', 'name' => 'wilayah_kelurahandesa' ],
                ]
            ],
            [
                'prefix' => '/v1/sia',
                'controller' => 'SiaController',
                'paths' => [
                    [ 'verb' => 'get', 'path' => '/', 'method' => 'index', 'name' => 'sia_index' ],
                    [ 'verb' => 'get', 'path' => '/current', 'method' => 'current', 'name' => 'sia_current' ],
                    [ 'verb' => 'post', 'path' => '/add', 'method' => 'add', 'name' => 'sia_add' ],
                    [ 'verb' => 'post', 'path' => '/update', 'method' => 'update', 'name' => 'sia_update' ],
                    [ 'verb' => 'post', 'path' => '/download/sia', 'method' => 'downloadsia', 'name' => 'sia_downloadsia' ],
                    [ 'verb' => 'post', 'path' => '/download/revoke', 'method' => 'downloadrevoke', 'name' => 'sia_downloadrevoke' ],
                ]
            ],
            [
                'prefix' => '/v1/apotek',
                'controller' => 'ApotekController',
                'paths' => [
                    [ 'verb' => 'get', 'path' => '/', 'method' => 'index', 'name' => 'apotek_index' ],
                    [ 'verb' => 'post', 'path' => '/update', 'method' => 'update', 'name' => 'apotek_update' ],
                ]
            ],
            [
                'prefix' => '/v1/apoteker',
                'controller' => 'ApotekerController',
                'paths' => [
                    [ 'verb' => 'get', 'path' => '/', 'method' => 'index', 'name' => 'apoteker_index' ],
                    [ 'verb' => 'get', 'path' => '/update', 'method' => 'update', 'name' => 'apoteker_update' ],
                ]
            ],
            [
                'prefix' => '/v1/izinpermohonan',
                'controller' => 'IzinPermohonanController',
                'paths' => [
                    [ 'verb' => 'get', 'path' => '/', 'method' => 'index', 'name' => 'izin_permohonan_index' ],
                    [ 'verb' => 'get', 'path' => '/latest', 'method' => 'latest', 'name' => 'izin_permohonan_latest' ],
                    [ 'verb' => 'post', 'path' => '/add', 'method' => 'add', 'name' => 'izin_permohonan_add' ],
                    [ 'verb' => 'post', 'path' => '/update', 'method' => 'update', 'name' => 'izin_permohonan_update' ],
                    [ 'verb' => 'post', 'path' => '/rawupdate', 'method' => 'rawupdate', 'name' => 'izin_permohonan_rawupdate' ],
                    [ 'verb' => 'post', 'path' => '/download', 'method' => 'download', 'name' => 'izin_permohonan_download' ],
                    [ 'verb' => 'post', 'path' => '/doc', 'method' => 'generatedoc', 'name' => 'izin_permohonan_doc' ],
                ]
            ],
            [
                'prefix' => '/v1/izinperubahan',
                'controller' => 'IzinPerubahanController',
                'paths' => [
                    [ 'verb' => 'get', 'path' => '/', 'method' => 'index', 'name' => 'izin_perubahan_index' ],
                    [ 'verb' => 'get', 'path' => '/latest', 'method' => 'latest', 'name' => 'izin_perubahan_latest' ],
                    [ 'verb' => 'post', 'path' => '/add', 'method' => 'add', 'name' => 'izin_perubahan_add' ],
                    [ 'verb' => 'post', 'path' => '/update', 'method' => 'update', 'name' => 'izin_perubahan_update' ],
                    [ 'verb' => 'post', 'path' => '/rawupdate', 'method' => 'rawupdate', 'name' => 'izin_perubahan_rawupdate' ],
                    [ 'verb' => 'post', 'path' => '/download', 'method' => 'download', 'name' => 'izin_perubahan_download' ],
                    [ 'verb' => 'post', 'path' => '/doc', 'method' => 'generatedoc', 'name' => 'izin_perubahan_doc' ],
                ]
            ],
            [
                'prefix' => '/v1/izinperpanjangan',
                'controller' => 'IzinPerpanjanganController',
                'paths' => [
                    [ 'verb' => 'get', 'path' => '/', 'method' => 'index', 'name' => 'izin_perpanjangan_index' ],
                    [ 'verb' => 'get', 'path' => '/latest', 'method' => 'latest', 'name' => 'izin_perpanjangan_latest' ],
                    [ 'verb' => 'post', 'path' => '/add', 'method' => 'add', 'name' => 'izin_perpanjangan_add' ],
                    [ 'verb' => 'post', 'path' => '/update', 'method' => 'update', 'name' => 'izin_perpanjangan_update' ],
                    [ 'verb' => 'post', 'path' => '/rawupdate', 'method' => 'rawupdate', 'name' => 'izin_perpanjangan_rawupdate' ],
                    [ 'verb' => 'post', 'path' => '/download', 'method' => 'download', 'name' => 'izin_perpanjangan_download' ],
                    [ 'verb' => 'post', 'path' => '/doc', 'method' => 'generatedoc', 'name' => 'izin_perpanjangan_doc' ],
                ]
            ],
            [
                'prefix' => '/v1/bappemeriksaan',
                'controller' => 'BAPPemeriksaanController',
                'paths' => [
                    [ 'verb' => 'get', 'path' => '/', 'method' => 'index', 'name' => 'bap_pemeriksaan_index' ],
                    [ 'verb' => 'get', 'path' => '/latest', 'method' => 'latest', 'name' => 'bap_pemeriksaan_latest' ],
                    [ 'verb' => 'post', 'path' => '/add', 'method' => 'add', 'name' => 'bap_pemeriksaan_add' ],
                    [ 'verb' => 'post', 'path' => '/update', 'method' => 'update', 'name' => 'bap_pemeriksaan_update' ],
                    [ 'verb' => 'post', 'path' => '/rawupdate', 'method' => 'rawupdate', 'name' => 'bap_pemeriksaan_rawupdate' ],
                    [ 'verb' => 'post', 'path' => '/download', 'method' => 'download', 'name' => 'bap_pemeriksaan_download' ],
                    [ 'verb' => 'post', 'path' => '/doc', 'method' => 'generatedoc', 'name' => 'bap_pemeriksaan_doc' ],
                ]
            ],
            [
                'prefix' => '/v1/bapperpanjangan',
                'controller' => 'BAPPerpanjanganController',
                'paths' => [
                    [ 'verb' => 'get', 'path' => '/', 'method' => 'index', 'name' => 'bap_perpanjangan_index' ],
                    [ 'verb' => 'get', 'path' => '/latest', 'method' => 'latest', 'name' => 'bap_perpanjangan_latest' ],
                    [ 'verb' => 'post', 'path' => '/add', 'method' => 'add', 'name' => 'bap_perpanjangan_add' ],
                    [ 'verb' => 'post', 'path' => '/update', 'method' => 'update', 'name' => 'bap_perpanjangan_update' ],
                    [ 'verb' => 'post', 'path' => '/rawupdate', 'method' => 'rawupdate', 'name' => 'bap_perpanjangan_rawupdate' ],
                    [ 'verb' => 'post', 'path' => '/download', 'method' => 'download', 'name' => 'bap_perpanjangan_download' ],
                    [ 'verb' => 'post', 'path' => '/doc', 'method' => 'generatedoc', 'name' => 'bap_perpanjangan_doc' ],
                ]
            ],
            [
                'prefix' => '/v1/monev',
                'controller' => 'MonevController',
                'paths' => [
                    [ 'verb' => 'get', 'path' => '/', 'method' => 'index', 'name' => 'monev_index' ],
                    [ 'verb' => 'get', 'path' => '/latest', 'method' => 'latest', 'name' => 'monev_latest' ],
                    [ 'verb' => 'post', 'path' => '/add', 'method' => 'add', 'name' => 'monev_add' ],
                    [ 'verb' => 'post', 'path' => '/update', 'method' => 'update', 'name' => 'monev_update' ],
                    [ 'verb' => 'post', 'path' => '/rawupdate', 'method' => 'rawupdate', 'name' => 'monev_rawupdate' ],
                    [ 'verb' => 'post', 'path' => '/download', 'method' => 'download', 'name' => 'monev_download' ],
                    [ 'verb' => 'post', 'path' => '/doc', 'method' => 'generatedoc', 'name' => 'monev_doc' ],
                ]
            ],
            [
                'prefix' => '/v1/export',
                'controller' => 'ExportController',
                'paths' => [
                    [ 'verb' => 'post', 'path' => '/xlsx', 'method' => 'generatexlsx', 'name' => 'export_xlsx' ],
                ]
            ],
            [
                'prefix' => '/v1/oss',
                'controller' => 'OssController',
                'paths' => [
                    // [ 'verb' => 'get', 'path' => '/token', 'method' => 'token', 'name' => 'request_token' ],
                    [ 'verb' => 'get', 'path' => '/detailNIB', 'method' => 'detailNIB', 'name' => 'detail_nib' ],
                ]
            ],
        ],

        'web_base_url' => 'https://simona.kemkes.go.id/api/v1/',
        'web_api_user' => 'https://simona.kemkes.go.id/api/'
    ];
?>

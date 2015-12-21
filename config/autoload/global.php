<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
$env = getenv('APPLICATION_ENV') ?  : 'prod';
$app_key = 'application';

$config = [
	$app_key => [
		'env' => $env,
		'app' => [
			'version' => '0.1',
			'base_url' => 'http://manager.compagniemiranda.com'
		],
		'mailer' => [
			'default_from_address' => 'info@compagniemiranda.com',
			'default_from_name' => 'La Compagnie Miranda',
			'noreply_from_address' => 'noreply@compagniemiranda.com',
			'noreply_from_name' => 'La Compagnie Miranda',
			'css' => 'public/css/email.css',
			'embbeded_host' => 'miranda',
			'embbeded_content' => [
				'header.png' => [
					'path' => 'public/img/email/miranda-logo.png',
					'content_type' => 'text/png'
				]
			]
		],
		'layout' => [
			'footer' => [
				'url_label' => 'Compagnie Miranda',
				'url_link' => 'http://www.compagniemiranda.com/'
			],
			'css' => [
				'css/jquery-ui-bootstrap/jquery-ui.css',
				'css/bootstrap.css',
				'css/fuelux.css',
				'css/bootstrap-responsive.min.css',
				'css/style.css'
			],
			'compile_less' => false,
			'less_wrapper' => 'less.php',
			'less_compiler' => 'lessphp',
			'less' => [
				'css/jquery-ui-bootstrap/jquery-ui.css',
				'css/style.less',
				'css/bootstrap-responsive.min.css'
			],
			'js' => [
				'js/jquery.min.js',
				'js/json2.js',
				'js/jquery-ui.min.js',
				'js/jquery.loadmask.min.js',
				'js/bootstrap.js',
				'js/fuelux.js',
				'lib/jquery.passstrength.js'
			]
		],
		'authentification' => [
			'not_login_page' => [
				'login',
				'logout',
				'authenticate',
				'unauthorized',
				'forgot-password',
				'reset-password',
				'validate-account'
			],
			'bcrypt' => [
				'cost' => 10
			],
			
			// in minutes
			'password_token_validity' => 180
		],
		'db' => [
			'table_prefix' => ''
		],
		'cache' => [
			'namespace' => 'miranda'
		],
		'data_storage' => [
			'root_path' => 'data/miranda',
			'temp_path' => 'data/miranda/tmp'
		],
		'costume' => [
			'pictures' => [
				'max_width' => 1000,
				'max_height' => 1000,
				
				// relative to data_storage->root_path
				'store_path' => 'pictures/costumes',
				'url_path' => '/pictures/costumes'
			]
		]
	],
	
	'db' => [
		'driver' => 'Mysqli',
		'hostname' => 'localhost',
		'database' => 'miranda',
		'charset' => 'UTF8',
		'options' => [
			// need for executing query while using results of an other query (else there'll be "commands out of sync" Mysql error
			'buffer_results' => true
		]
	],
	
	'navigation' => [
		'default' => [
			[
				'label' => 'Home',
                'icon' => 'fa fa-home',
				'route' => 'home'
			],
			[
				'label' => 'Costumes',
                'icon' => 'iort iort-tshirt',
				'route' => 'costume',
				'resource' => 'list_costumes',
				'pages' => [
					[
						'label' => 'Catalog',
						'icon' => 'fa fa-list',
						'route' => 'costume',
						'action' => 'index',
						'resource' => 'list_costumes',
						'pages' => [
							[
								'label' => 'Show',
								'icon' => 'fa fa-eye',
								'route' => 'costume',
								'action' => 'show',
								'resource' => 'show_costume',
								'visible' => false
							],
							[
								'label' => 'Add',
								'icon' => 'fa fa-plus-circle',
								'route' => 'costume',
								'action' => 'add',
								'resource' => 'add_costume',
								'visible' => false
							],
							[
								'label' => 'Edit',
								'icon' => 'fa fa-pencil',
								'route' => 'costume',
								'action' => 'edit',
								'resource' => 'edit_costume',
								'visible' => false
							],
							[
								'label' => 'Delete',
								'icon' => 'fa fa-trash-o',
								'route' => 'costume',
								'action' => 'delete',
								'resource' => 'delete_costume',
								'visible' => false
							]
						]
					],
					[
						'label' => 'Management',
						'icon' => 'fa fa-wrench',
						'route' => 'costume-admin',
						'action' => 'index',
						'resource' => 'admin_costumes',
						'pages' => [
							[
								'label' => 'Colors',
								'icon' => 'fa fa-tint',
								'route' => 'costume-admin/color',
								'action' => 'index',
								'resource' => 'admin_costumes_colors'
							],
							[
								'label' => 'Materials',
								'icon' => 'fa fa-leaf',
								'route' => 'costume-admin/material',
								'action' => 'index',
								'resource' => 'admin_costumes_materials'
							],
							[
								'label' => 'Tags',
								'icon' => 'fa fa-tags',
								'route' => 'costume-admin/tag',
								'action' => 'index',
								'resource' => 'admin_costumes_tags'
							],
							[
								'label' => 'Parts & types',
								'icon' => 'fa fa-puzzle-piece',
								'route' => 'costume-admin/part',
								'action' => 'index',
								'resource' => 'admin_costumes_parts'
							]
						]
					]
				]
			],
			[
				'label' => 'Admin',
                'icon' => 'fa fa-cog',
				'route' => 'admin',
				'resource' => 'admin_access',
				'pages' => [
					[
						'label' => 'Users',
						'route' => 'admin/user',
						'icon' => 'fa fa-users',
						'resource' => 'admin_list_users',
						'pages' => [
							[
								'label' => 'Show',
								'icon' => 'fa fa-eye',
								'route' => 'admin/user',
								'action' => 'show',
								'resource' => 'admin_show_user'
							],
							[
								'label' => 'Add',
								'icon' => 'fa fa-plus-circle',
								'route' => 'admin/user',
								'action' => 'add',
								'resource' => 'admin_add_user'
							],
							[
								'label' => 'Edit',
								'icon' => 'fa fa-pencil',
								'route' => 'admin/user',
								'action' => 'edit',
								'resource' => 'admin_edit_user'
							],
							[
								'label' => 'Delete',
								'icon' => 'fa fa-trash-o',
								'route' => 'admin/user',
								'action' => 'delete',
								'resource' => 'admin_delete_user'
							]
						]
					],
					[
						'label' => 'Roles',
						'icon' => 'fa fa-unlock',
						'route' => 'admin/role',
						'resource' => 'admin_list_roles',
						'pages' => [
							[
								'label' => 'Show',
								'icon' => 'fa fa-eye',
								'route' => 'admin/role',
								'action' => 'show',
								'resource' => 'admin_show_role'
							],
							[
								'label' => 'Add',
								'icon' => 'fa fa-plus-circle',
								'route' => 'admin/role',
								'action' => 'add',
								'resource' => 'admin_add_role'
							],
							[
								'label' => 'Edit',
								'icon' => 'fa fa-pencil',
								'route' => 'admin/role',
								'action' => 'edit',
								'resource' => 'admin_edit_role'
							],
							[
								'label' => 'Delete',
								'icon' => 'fa fa-trash-o',
								'route' => 'admin/role',
								'action' => 'delete',
								'resource' => 'admin_delete_role'
							]
						]
					],
					[
						'label' => 'Rights',
						'icon' => 'fa fa-key',
						'route' => 'admin/right',
						'resource' => 'admin_list_rights'
					]
				]
			]
		]
	],
	'session' => [
		'config' => [
			'class' => 'Zend\Session\Config\SessionConfig',
			'options' => [
				'name' => 'miranda'
			]
		],
		'storage' => 'Zend\Session\Storage\SessionArrayStorage',
		'validators' => [
			'Zend\Session\Validator\RemoteAddr',
			'Zend\Session\Validator\HttpUserAgent'
		]
	]
];

if ($env == 'prod') {
	$config[$app_key]['layout']['css'] = [
		'css/miranda-full-min.css'
	];
	$config[$app_key]['layout']['js'] = [
		'js/miranda-full-min.js'
	];
}

return $config;
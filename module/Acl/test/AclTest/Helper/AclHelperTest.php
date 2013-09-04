<?php
namespace AclTest\Helper;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Acl\Helper\AclHelper;

class AclHelperTest extends \PHPUnit_Framework_TestCase
{

	protected $_resources = array(
		'admin_list_users',
		'admin_show_user',
		'admin_add_user',
		'admin_edit_user',
		'admin_delete_user',
		'admin_list_roles',
		'admin_show_role',
		'admin_add_role',
		'admin_edit_role',
		'admin_delete_role',
		'admin_list_rights',
		'login',
		'costume_borrow'
	);

	protected $_roles = array(
		'quest' => array(),
		'member' => array(
			'login',
			'costume_borrow'
		),
		'admin' => array(
			'admin_list_users',
			'admin_show_user',
			'admin_add_user',
			'admin_edit_user',
			'admin_delete_user',
			'admin_list_roles',
			'admin_show_role',
			'admin_add_role',
			'admin_edit_role',
			'admin_delete_role',
			'admin_list_rights'
		),
		'admin_user' => array(
			'admin_list_users',
			'admin_show_user',
			'admin_add_user',
			'admin_edit_user',
			'admin_delete_user'
		)
	);

	protected $_users = array(
		'user_admin' => array(
			'admin',
			'member'
		),
		'user_admin_user' => array(
			'admin_user',
			'member'
		),
		'user_member' => array(
			'member'
		),
		'user_guest' => array(),
		'user_not_logged' => array()
	);

	protected $acl;

	protected function setUp()
	{
		$this->acl = new Acl();
		
		foreach ($this->_resources as $ressource) {
			$this->acl->addResource(new Resource($ressource));
		}
		foreach ($this->_roles as $role => $resources) {
			$this->acl->addRole(new Role($role));
			foreach ($resources as $resource) {
				$this->acl->allow($role, $resource);
			}
		}
		
		foreach ($this->_users as $user => $roles) {
			$this->acl->addRole(new Role($user), $roles);
		}
	}

	public function prepareRightsTabProvider()
	{
		return array(
			// Test
			array(
				'admin_list_users',
				array(
					array(
						'admin_list_users'
					)
				)
			),
			// Test
			array(
				array(
					'admin_list_users',
					'admin_list_roles'
				),
				array(
					array(
						'admin_list_users',
						'admin_list_roles'
					)
				)
			),
			// Test
			array(
				array(
					array(
						'admin_list_users',
						'admin_show_user'
					),
					array(
						'admin_list_roles',
						'admin_show_role'
					)
				),
				array(
					array(
						'admin_list_users',
						'admin_show_user'
					),
					array(
						'admin_list_roles',
						'admin_show_role'
					)
				)
			),
			// Test
			array(
				array(
					'admin_list_users',
					array(
						'admin_list_roles',
						'admin_show_role'
					)
				),
				array(
					array(
						'admin_list_users'
					),
					array(
						'admin_list_roles',
						'admin_show_role'
					)
				)
			),
			// Test
			array(
				false,
				false
			),
			// Test
			array(
				true,
				false
			),
			// Test
			array(
				new \stdClass(),
				false
			),
			// Test
			array(
				27,
				false
			),
			// Test
			array(
				-15.8,
				false
			),
			// Test
			array(
				array(
					'admin_list_users',
					false
				),
				false
			),
			// Test
			array(
				array(
					'admin_list_users',
					array(
						'admin_list_roles',
						'admin_show_role',
						17
					)
				),
				false
			)
		);
	}

	public function isAllowedProvider()
	{
		return array(
			// Test
			array(
				'user_admin',
				array(
					array(
						'admin_list_users'
					)
				),
				true				
			)
		);
	}

	/**
	 * @dataProvider prepareRightsTabProvider
	 */
	public function testprepareRightsTab($rights, $expected)
	{
		$this->assertEquals($expected, AclHelper::prepareRightsTab($rights));
	}

	/**
	 * @depends testprepareRightsTab
	 * @dataProvider isAllowedProvider
	 */
	public function testisAllowed($user, $rights, $allowed)
	{
		$this->assertEquals($allowed, AclHelper::isAllowed($this->acl, $user, $rights));
	}
}
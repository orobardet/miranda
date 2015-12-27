<?php
namespace Costume\Controller;

use Zend\View\Model\ViewModel;
use Acl\Controller\AclControllerInterface;

class StatsController extends AbstractCostumeController implements AclControllerInterface
{
	public function aclIsAllowed($action, \Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
				return "stats_costumes";
				break;
			default:
				return false;
				break;
		}
		
		return false;
	}

	public function indexAction()
	{
        $statsData = [
            'costumes' => (object)[
                'count' => 35639
            ],
            'colors' => (object)[
                'count' => 0
            ],
            'materials' => (object)[
                'count' => 0
            ],
            'tags' => (object)[
                'count' => 0
            ],
            'parts' => (object)[
                'count' => 0
            ],
        ];

		return new ViewModel($statsData);
	}
}

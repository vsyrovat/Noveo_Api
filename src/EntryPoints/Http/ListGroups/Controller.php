<?php declare(strict_types=1);

namespace App\EntryPoints\Http\ListGroups;

use App\Domain\Queries\Group\GetGroupList;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class Controller extends AbstractFOSRestController
{
    private $getGroupList;

    public function __construct(GetGroupList $getGroupList)
    {
        $this->getGroupList = $getGroupList;
    }

    /**
     * @Rest\Get("/groups/")
     */
    public function action()
    {
        $groups = $this->getGroupList->execute();
        return View::create(['success' => true, 'data' => $groups]);
    }
}
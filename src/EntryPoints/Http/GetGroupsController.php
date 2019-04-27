<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Query\GetGroups;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class GetGroupsController extends AbstractFOSRestController
{
    private $getGroupList;

    public function __construct(GetGroups $getGroupList)
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
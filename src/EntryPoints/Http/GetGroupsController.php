<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Query\GetGroups;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class GetGroupsController extends AbstractFOSRestController
{
    private $query;

    public function __construct(GetGroups $query)
    {
        $this->query = $query;
    }

    /**
     * @Rest\Get("/groups/")
     */
    public function action()
    {
        $groups = $this->query->execute();
        return View::create(['success' => true, 'data' => $groups]);
    }
}
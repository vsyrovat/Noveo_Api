<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Query\GetUsers;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class GetUsersController extends AbstractFOSRestController
{
    private $query;

    public function __construct(GetUsers $query)
    {
        $this->query = $query;
    }

    /**
     * @Rest\Get("/users/")
     */
    public function action()
    {
        $users = $this->query->execute();
        return View::create(['success' => true, 'data' => $users]);
    }
}
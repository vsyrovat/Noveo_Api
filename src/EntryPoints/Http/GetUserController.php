<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Query\GetUser;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class GetUserController extends AbstractFOSRestController
{
    private $query;

    public function __construct(GetUser $query)
    {
        $this->query = $query;
    }

    /**
     * @Rest\Get("/users/{id}/", requirements={"id"="\d+"})
     */
    public function action(int $id)
    {
        $user = $this->query->execute($id);
        return View::create(['success' => true, 'data' => $user]);
    }
}
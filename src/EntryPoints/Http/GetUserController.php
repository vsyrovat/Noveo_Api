<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class GetUserController
{
    /**
     * @Rest\Get("/users/{id}/", requirements={"id"="\d+"})
     */
    public function action(User $user)
    {
        return View::create(['success' => true, 'data' => $user]);
    }
}
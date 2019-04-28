<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Command\UpdateUser;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class UpdateUserController extends AbstractFOSRestController
{
    private $command;

    public function __construct(UpdateUser $command)
    {
        $this->command = $command;
    }

    /**
     * @Rest\Put("/users/{id}/", requirements={"id"="\d+"})
     */
    public function action(Request $request, int $id)
    {
        $firstName = $request->request->get('firstName');
        $lastName = $request->request->get('lastName');
        $email = $request->request->get('email');
        $isActive = $request->request->get('isActive');
        $this->command->execute($id, $firstName, $lastName, $email, $isActive);
        return View::create(['success' => true]);
    }
}
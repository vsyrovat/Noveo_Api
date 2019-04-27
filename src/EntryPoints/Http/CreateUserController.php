<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Command\CreateUser;
use App\Domain\Exception\DuplicateUserEmail;
use App\Domain\Exception\GroupNotFound;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateUserController extends AbstractFOSRestController
{
    private $command;

    public function __construct(CreateUser $command)
    {
        $this->command = $command;
    }

    /**
     * @Rest\Post("/users/")
     */
    public function actionCreateUser(Request $request)
    {
        try {
            $user = $this->command->execute(
                $request->request->get('firstName'),
                $request->request->get('lastName'),
                $request->request->get('email'),
                $request->request->get('isActive'),
                $request->request->get('groupId')
            );
            return View::create(['success' => true, 'data' => ['id' => $user->getId()]], Response::HTTP_CREATED);
        } catch (GroupNotFound|DuplicateUserEmail $e) {
            return View::create(['success' => false, 'msg' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
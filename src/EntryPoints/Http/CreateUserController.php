<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Command\CreateUser;
use App\Domain\Entity\User;
use App\Domain\Exception\DuplicateUserEmail;
use App\Domain\Exception\GroupNotFound;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
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
     *
     * @SWG\Post(
     *     summary="Create a user",
     *     tags={"Users"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="firstName", type="string"),
     *              @SWG\Property(property="lastName", type="string"),
     *              @SWG\Property(property="email", type="string"),
     *              @SWG\Property(property="isActive", type="boolean"),
     *              @SWG\Property(property="groupId", type="integer")
     *         )
     *     ),
     *
     *     @SWG\Response(
     *         response="201",
     *         description="User created",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean", example=true),
     *              @SWG\Property(property="data", ref=@Model(type=User::class))
     *          )
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="No group exists or email duplicated"
     *     )
     * )
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
            return View::create(['success' => true, 'data' => $user], Response::HTTP_CREATED);
        } catch (GroupNotFound|DuplicateUserEmail $e) {
            return View::create(['success' => false, 'msg' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
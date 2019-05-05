<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Command\CreateUser;
use App\Domain\Command\UpdateUser;
use App\Domain\Entity\User;
use App\Domain\Exception\DuplicateUserEmail;
use App\Domain\Exception\GroupNotFound;
use App\Domain\Exception\ValidationException;
use App\Domain\Query\GetUser;
use App\Domain\Query\GetUsers;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractFOSRestController
{
    private $createUserCommand;
    private $getUserQuery;
    private $getUsersQuery;
    private $updateUserCommand;

    public function __construct(CreateUser $createUserCommand, GetUser $getUserQuery, GetUsers $getUsersQuery, UpdateUser $updateUserCommand)
    {
        $this->createUserCommand = $createUserCommand;
        $this->getUserQuery = $getUserQuery;
        $this->getUsersQuery = $getUsersQuery;
        $this->updateUserCommand = $updateUserCommand;
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
    public function create(Request $request)
    {
        try {
            $user = $this->createUserCommand->execute(
                $request->request->get('firstName'),
                $request->request->get('lastName'),
                $request->request->get('email'),
                $request->request->get('isActive'),
                $request->request->get('groupId')
            );
            return View::create(['success' => true, 'data' => $user], Response::HTTP_CREATED);
        } catch (GroupNotFound|DuplicateUserEmail $e) {
            return View::create(['success' => false, 'msg' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (ValidationException $e) {
            return View::create(['success' => false, 'violatons' => $e->violations], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Rest\Get("/users/{id}/", requirements={"id"="\d+"})
     *
     * @SWG\Get(
     *     summary="Get a user",
     *     tags={"Users"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(name="id", in="path", type="integer", required=true),
     *
     *     @SWG\Response(
     *          response="200",
     *          description="Success",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean", example=true),
     *              @SWG\Property(property="data", ref=@Model(type=User::class))
     *          )
     *     )
     * )
     */
    public function getOne(int $id)
    {
        $user = $this->getUserQuery->execute($id);
        return View::create(['success' => true, 'data' => $user]);
    }

    /**
     * @Rest\Get("/users/")
     *
     * @SWG\Get(
     *     summary="Get list of users",
     *     tags={"Users"},
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *          response="200",
     *          description="Success",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean", example=true),
     *              @SWG\Property(property="data", type="array", @SWG\Items(ref=@Model(type=User::class)))
     *          )
     *      )
     * )
     */
    public function getList()
    {
        $users = $this->getUsersQuery->execute();
        return View::create(['success' => true, 'data' => $users]);
    }

    /**
     * @Rest\Put("/users/{id}/", requirements={"id"="\d+"})
     *
     * @SWG\Put(
     *     summary="Update user info",
     *     tags={"Users"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          type="integer",
     *          required=true
     *     ),
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="firstName", type="string"),
     *              @SWG\Property(property="lastName", type="string"),
     *              @SWG\Property(property="email", type="string"),
     *              @SWG\Property(property="isActive", type="boolean"),
     *              @SWG\Property(property="group", type="integer"),
     *          )
     *     ),
     *
     *     @SWG\Response(
     *          response="200",
     *          description="OK",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean", example=true)
     *          )
     *     )
     * )
     */
    public function update(Request $request, int $id)
    {
        try {
            $this->updateUserCommand->execute($id, $request->request->all());
        } catch (ValidationException $e) {
            return View::create(['success' => false, 'violations' => $e->violations], Response::HTTP_BAD_REQUEST);
        }
        return View::create(['success' => true]);
    }
}
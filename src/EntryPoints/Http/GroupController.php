<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Command\CreateGroup;
use App\Domain\Command\UpdateGroup;
use App\Domain\Entity\Group;
use App\Domain\Exception\DuplicateGroupNameException;
use App\Domain\Exception\GroupNotFound;
use App\Domain\Query\GetGroups;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends AbstractFOSRestController
{
    private $createGroupCommand;
    private $getGroupsQuery;
    private $updateGroupCommand;

    public function __construct(CreateGroup $createGroupCommand, GetGroups $getGroupsQuery, UpdateGroup $updateGroupCommand)
    {
        $this->createGroupCommand = $createGroupCommand;
        $this->getGroupsQuery = $getGroupsQuery;
        $this->updateGroupCommand = $updateGroupCommand;
    }

    /**
     * @Rest\Post("/groups/")
     *
     * @SWG\Post(
     *     summary="Create a group",
     *     tags={"Groups"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             @SWG\Property(property="name", type="string", example="Admins")
     *         )
     *     ),
     *
     *     @SWG\Response(
     *         response="201",
     *         description="Group created",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(property="success", type="boolean", example=true),
     *             @SWG\Property(property="data", ref=@Model(type=Group::class))
     *         )
     *     ),
     *     @SWG\Response(
     *          response="400",
     *          description="Input error",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean", example=false),
     *              @SWG\Property(property="msg", type="string")
     *          )
     *     )
     * )
     */
    public function create(Request $request)
    {
        $name = $request->request->get('name');
        try {
            $group = $this->createGroupCommand->execute($name);
            return View::create(['success' => true, 'data' => $group], Response::HTTP_CREATED);
        } catch (DuplicateGroupNameException $e) {
            return View::create(['success' => false, 'msg' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Rest\Get("/groups/")
     *
     * @SWG\Get(
     *     summary="Get list of groups",
     *     tags={"Groups"},
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *          response="200",
     *          description="Success",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean", example=true),
     *              @SWG\Property(property="data", type="array", @SWG\Items(ref=@Model(type=Group::class)))
     *          )
     *     )
     * )
     */
    public function getList()
    {
        $groups = $this->getGroupsQuery->execute();
        return View::create(['success' => true, 'data' => $groups]);
    }

    /**
     * @Rest\Put("/groups/{id}/", requirements={"id"="\d+"})
     *
     * @SWG\Put(
     *     summary="Update group",
     *     tags={"Groups"},
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
     *              @SWG\Property(property="name", type="string")
     *          )
     *     ),
     *
     *     @SWG\Response(
     *          response="200",
     *          description="Success",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean", example=true)
     *          )
     *     ),
     *     @SWG\Response(
     *          response="400",
     *          description="Input error",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean", example=false),
     *              @SWG\Property(property="msg", type="string")
     *          )
     *     )
     * )
     */
    public function update(Request $request, int $id)
    {
        $name = $request->request->get('name');
        try {
            $this->updateGroupCommand->execute($id, $name);
            return View::create(['success' => true], Response::HTTP_OK);
        } catch (GroupNotFound $e) {
            return View::create(['success' => false, 'msg' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
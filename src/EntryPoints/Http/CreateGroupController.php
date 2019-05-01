<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Command\CreateGroup;
use App\Domain\Entity\Group;
use App\Domain\Exception\DuplicateGroupNameException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateGroupController extends AbstractFOSRestController
{
    private $command;

    public function __construct(CreateGroup $command)
    {
        $this->command = $command;
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
    public function action(Request $request)
    {
        $name = $request->request->get('name');
        try {
            $group = $this->command->execute($name);
            return View::create(['success' => true, 'data' => $group], Response::HTTP_CREATED);
        } catch (DuplicateGroupNameException $e) {
            return View::create(['success' => false, 'msg' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
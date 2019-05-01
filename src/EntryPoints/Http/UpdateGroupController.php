<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Command\UpdateGroup;
use App\Domain\Exception\GroupNotFound;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateGroupController extends AbstractFOSRestController
{
    private $command;

    public function __construct(UpdateGroup $command)
    {
        $this->command = $command;
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
    public function action(Request $request, int $id)
    {
        $name = $request->request->get('name');
        try {
            $this->command->execute($id, $name);
            return View::create(['success' => true], Response::HTTP_OK);
        } catch (GroupNotFound $e) {
            return View::create(['success' => false, 'msg' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
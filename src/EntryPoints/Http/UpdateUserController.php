<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Command\UpdateUser\ChangesetFactory;
use App\Domain\Command\UpdateUser\UpdateUser;
use App\Domain\Entity\User;
use App\Domain\Exception\ValidationException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserController extends AbstractFOSRestController
{
    private $command;
    private $changesetFactory;

    public function __construct(UpdateUser $command, ChangesetFactory $changesetFactory)
    {
        $this->command = $command;
        $this->changesetFactory = $changesetFactory;
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
    public function action(Request $request, int $id)
    {
        try {
            $changeset = $this->changesetFactory->build($request->request->all(), User::class);
            $this->command->execute($id, $changeset);
        } catch (ValidationException $e) {
            return View::create(['success' => false, 'violations' => $e->violations], Response::HTTP_BAD_REQUEST);
        }
        return View::create(['success' => true]);
    }
}
<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Command\UpdateUser;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Swagger\Annotations as SWG;
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
        $firstName = $request->request->get('firstName');
        $lastName = $request->request->get('lastName');
        $email = $request->request->get('email');
        $isActive = $request->request->get('isActive');
        $this->command->execute($id, $firstName, $lastName, $email, $isActive);
        return View::create(['success' => true]);
    }
}
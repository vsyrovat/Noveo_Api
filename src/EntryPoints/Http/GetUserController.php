<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Entity\User;
use App\Domain\Query\GetUser;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class GetUserController extends AbstractFOSRestController
{
    private $query;

    public function __construct(GetUser $query)
    {
        $this->query = $query;
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
    public function action(int $id)
    {
        $user = $this->query->execute($id);
        return View::create(['success' => true, 'data' => $user]);
    }
}
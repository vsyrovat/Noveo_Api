<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Entity\User;
use App\Domain\Query\GetUsers;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class GetUsersController extends AbstractFOSRestController
{
    private $query;

    public function __construct(GetUsers $query)
    {
        $this->query = $query;
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
    public function action()
    {
        $users = $this->query->execute();
        return View::create(['success' => true, 'data' => $users]);
    }
}
<?php
namespace Sfynx\ApiMediaBundle\Layers\Presentation\Coordination\Media\Command;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Generalisation\Interfaces\MediaManagerInterface;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaAlreadyExistException;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\NoMatchedStorageMapperException;

/**
 * Class CreateOneController
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Presentation
 * @subpackage Coordination\Media\Command
 */
class CreateOneController
{
    /** @var MediaManagerInterface */
    protected $manager;

    /**
     * CreateOneController constructor.
     * @param MediaManagerInterface $manager
     */
    public function __construct(MediaManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Create a new media
     *
     * @param Request $request
     */
    public function execute(Request $request)
    {
        $response = new Response();
        try {
            $media = $this->manager->addMedia([
                'media'            => $request->files->get('media'),
                'source'           => $request->request->get('source', null),
                'ip_source'        => $request->getClientIp(),
                'name'             => $request->request->get('name', null),
                'description'      => $request->request->get('description', null),
                'storage_provider' => $request->request->get('storage_provider', null),
                'metadata'         => $request->request->get('metadata', []),
            ]);

            $response->setStatusCode(Response::HTTP_CREATED);
            $response->setContent(json_encode(array_merge(
                $media->toArray(),
                array('publicUri' => $this->manager->getMediaPublicUri($media))
            )));
        } catch (MediaAlreadyExistException $e) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent($e->getMessage());
        } catch (NoMatchedStorageMapperException $e) {
            $response->setStatusCode(Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
            $response->setContent($e->getMessage());
        }  catch (FileException $e) {
            $response->setStatusCode(Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
            $response->setContent($e->getMessage());
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_I_AM_A_TEAPOT);
            $response->setContent($e->getMessage());
        }

        return $response;
    }
}

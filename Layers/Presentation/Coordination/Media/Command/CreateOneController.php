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
    /** @var int */
    protected $quality;

    /**
     * CreateOneController constructor.
     * @param MediaManagerInterface $manager
     * @param int $quality
     */
    public function __construct(MediaManagerInterface $manager, int $quality = 95)
    {
        $this->manager = $manager;
        $this->quality = $quality;
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
                'enabled'          => $request->request->get('enabled', null),
                'ip_source'        => $request->getClientIp(),
                'name'             => $request->request->get('name', null),
                'description'      => $request->request->get('description', null),
                'storage_provider' => $request->request->get('storage_provider', null),
                'source'           => $request->request->get('source', null),
                'media'            => $request->files->get('media'),
                'quality'          => $request->request->get('quality', $this->quality),
                'metadata'         => $request->request->get('metadata', []),
                'signing'          => $request->request->get('signing', []),
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

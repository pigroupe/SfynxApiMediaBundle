<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Token;

use stdClass;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Sfynx\CoreBundle\Layers\Domain\Service\Request\Generalisation\RequestInterface;
use Sfynx\ApiMediaBundle\Layers\Application\Cqrs\Jwt\Command\JwtCommand;

/**
 * Create token from request
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Domain
 * @subpackage Service\Token
 */
class TokenService
{
    /** @var JWTEncoderInterface */
    protected $jwtEncoder;
    /** @var KeyLoaderInterface */
    protected $keyLoader;
    /** @var RequestInterface */
    protected $request;
    /** @var TokenStorageInterface */
    protected $tokenStorage;
    /** @var OptionsResolverInterface */
    protected $resolver;
    /** @var array */
    protected $options;

    /**
     * Algorithm used to sign the token, see
    https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
     */
    const ALGORITHM = 'RS256';

    /**
     * @var array $defaults List of default values for optional parameters.
     */
    protected $defaults = [
        'unique' => false,
        'ipRange' => [],
        'users' => [],
        'medias' => [],
        'start' => 0,
        'expire' => 3600,
        'algorithm' => self::ALGORITHM,
        'tenantId' => '',
        'kid' => null,
        'url' => null,
        'method' => 'POST'
    ];

    /**
     * @var array $defined List of default values for optional parameters.
     */
    protected $defined = [
        'unique',
        'ipRange',
        'users',
        'medias',
        'start',
        'expire',
        'algorithm',
        'tenantId',
        'kid',
        'url',
        'method'
    ];

    /**
     * @var string[] $required List of required parameters for each methods.
     */
    protected $required = [
        'unique',
        'ipRange',
        'users',
        'medias',
        'start',
        'expire',
        'algorithm',
        'tenantId'
    ];

    /**
     * @var array[] $allowedTypes List of allowed types for each methods.
     */
    protected $allowedTypes = [
        'unique' => ['bool'],
        'ipRange' => ['array', 'null'],
        'users' => ['array', 'null'],
        'medias' => ['array', 'null'],
        'start' => ['int'],
        'expire' => ['int'],
        'algorithm' => ['string', 'null'],
        'tenantId' => ['string'],
        'kid' => ['string', 'null'],
        'url' => ['string', 'null'],
        'method' => ['string']
    ];

    /**
     * TokenService constructor.
     * @param JWTEncoderInterface $jwtEncoder
     * @param KeyLoaderInterface $keyLoader
     * @param RequestInterface $request
     * @param TokenStorageInterface $tokenStorage
     * @param array $options
     */
    public function __construct(
        JWTEncoderInterface $jwtEncoder,
        KeyLoaderInterface $keyLoader,
        RequestInterface $request,
        TokenStorageInterface $tokenStorage,
        array $options = []
    ) {
        $this->jwtEncoder = $jwtEncoder;
        $this->keyLoader = $keyLoader;
        $this->request = $request;
        $this->tokenStorage = $tokenStorage;
        $this->options = $options;
    }

    /**
     * @param array $options
     * @return JwtCommand
     */
    public function setSigningKey(array $options): JwtCommand
    {
        $parameters = $this->resolve($options);

        if (null === $parameters->kid) {
            $parameters->kid = \hash('sha256', \random_bytes(36));
        }

        $body = \array_merge($this->setBody($parameters), ['kid' => $parameters->kid]);

        $skey = $this->keyLoader->loadKey('public');
        $secretKey = \base64_decode($skey);
        $token = $this->jwtEncoder->encode($body, $secretKey, $parameters->algorithm);

        return new JwtCommand($parameters->kid, $token);
    }

    /**
     * @param array $options
     * @return stdClass
     */
    protected function resolve(array $options): stdClass
    {
        $options = \array_merge($this->options, $options);

        $this->resolver = new OptionsResolver();
        $this->resolver->setDefaults($this->defaults);
        $this->resolver->setDefined($this->defined);
        $this->resolver->setRequired($this->required);
        foreach ($this->allowedTypes as $optionName => $optionTypes) {
            $this->resolver->setAllowedTypes($optionName, $optionTypes);
        }

        return $this->transform($this->resolver->resolve($options), false);
    }

    /**
     * @param stdClass $parameters
     * @return array
     */
    protected function setBody(stdClass $parameters): array
    {
        return [
            'sub' => 'Media download',
            'exp' => \strtotime("now + $parameters->expire seconds"),
            'context' => [
                'date' => [
                    'created_at' => \strtotime('now'),
                    'start' => \strtotime("now + $parameters->start seconds")
                ],
                'rangeip' => $parameters->ipRange,
                'unique' => $parameters->unique,
                'x-tenant-id' => $parameters->tenantId,
                'users' => $parameters->users,
                'medias' => $parameters->medias
            ]
        ];
    }

    /**
     * Converting an stdClass -> array  => $option = true
     * Converting an array -> stdClass  => $option = false
     *
     * @param mixed $data
     * @param boolean $option
     * @return mixed
     */
    protected function transform($data, $option = true)
    {
        return \json_decode(\json_encode($data), $option);
    }
}

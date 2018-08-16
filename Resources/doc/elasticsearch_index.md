# Reference FosElastica configuration Bundle

```yml
fos_elastica:
    clients:
        default:
          host: '%elasticsearch_host%'
          port: '%elasticsearch_port%'
    indexes:
        sfynx_media:
            types:
                media:
                    properties:
                        id: { type: integer }
                        enabled: { type: boolean }
                        name: { type: string, index: not_analyzed }
                        description: { type: string, index: not_analyzed }
                        source: { type: string, index: not_analyzed }
                        referencePrefix: { type: string, index: not_analyzed }
                        extension: { type: string, index: not_analyzed }
                        size: { type: integer }
                        mimeType: { type: string, index: not_analyzed }
                        metadata: { type: object }
                        signing: { type: object }
                        createdAt: { type: date, format: date_time_no_millis }
                    persistence:
                        driver: orm
                        model: Sfynx\ApiMediaBundle\Layers\Domain\Entity
                        provider: ~
                        listener: ~
                        finder: ~
```
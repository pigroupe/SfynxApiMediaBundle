# Reference configuration

## Summary

* [A complete configuration example](#a-complete-configuration-example)
* [Configure your signing key system](#configure-your-signing-key-system)
* [Configure your filesystems](#configure-your-filesystems)
* [Configure your mappings storage](#configure-your-mappings-storage)
* [Configure the path of your upload cache folder with gaufrette](#configure-the-path-of-your-upload-cache-folder-with-gaufrette)
* [Configure the path of your upload cache folder without gaufrette](#configure-the-path-of-your-upload-cache-folder-without-gaufrette)
* [Configure your public endpoint](#configure-your-public-endpoint)
* [Configure your mappings entity manager](#configure-your-mappings-entity-manager)
* [Configure the authorized extensions according to media type when creating media](#configure-the-authorized-extensions-according-to-media-type-when-creating-media)
* [Configure the default quality value when creating image media](#configure-the-default-quality-value-when-creating-image-media)
* [Configure the default jwt token parameters when creating](#configure-the-default-jwt-token-parameters-when-creating)
* [Configure the default signing excludes pattern](#configure-the-default-signing-excludes-pattern)

#### Configure your signing key system

First we have to create ssh public and private keys with pass phrase by executing these shell code lines :

```ssh
#!/usr/bin/env bash

mkdir -p new

openssl genrsa \
        -out new/private.pem \
        -aes256 4096

openssl rsa \
        -pubout \
        -in new/private.pem \
        -out new/public.pem

# https://www.digitalocean.com/community/tutorials/openssl-essentials-working-with-ssl-certificates-private-keys-and-csrs
openssl req \
       -key new/private.pem \
       -new \
       -x509 -days 365 -out new/domain.crt
```

Secondly, add the following configuration of the lexik jwt bundle, this allowing to code and decode
an jwt token. In this confugiration.

We have to fill in the public and private key path as well as the phrase pass that was
used when the keys were generated.

```yml
# app/config/config.yml

lexik_jwt_authentication:
    private_key_path: %jwt_private_key_path%
    public_key_path:  %jwt_public_key_path%
    pass_phrase:      %jwt_key_pass_phrase%
    token_ttl:        %jwt_token_ttl%
    # token encoding/decoding settings
    encoder:
        # token encoder/decoder service - default implementation based on the namshi/jose library
        service:            lexik_jwt_authentication.encoder.default
        # crypto engine used by the encoder service
        crypto_engine:  openssl
        # encryption algorithm used by the encoder service
        signature_algorithm: RS256
    # token extraction settings
    token_extractors:
        authorization_header:      # look for a token as Authorization Header
            enabled: true
            prefix:  Bearer
            name:    Authorization
        cookie:                    # check token in a cookie
            enabled: false
            name:    BEARER
        query_parameter:           # check token in query string parameter
            enabled: false
            name:    bearer
```

#### Configure your filesystems

The filesystem abstract layer permits you to develop your application without the need to know where your media
will be stored and how. Another advantage of this is the possibility to update your files location
without any impact on the code apart from the definition of your filesystem.

#### Example of Gaufrette Filesystem configuration

The following configuration is a local sample configuration for the KnpGaufretteBundle.
It will create :
- a filesystem service called `gaufrette_storage_gallery_local` which can be used in the MediaBundle.
All the uploaded files will be stored in `/web/uploads` directory.
- a filesystem service called `gaufrette_storage_gallery_azure` which can be used in the MediaBundle.
All the uploaded files will be stored in azure blob storage with connection_string egual to
'%sfynx.api.media.storage.azure.connection%' and in the container with name '%sfynx.api.media.storage.azure.container%'

```yml
# app/config/services.yml

services:
    #
    # azure driver connector
    #
    sfynx_api_media.azure_blob_proxy_factory:
        class: 'Gaufrette\Adapter\AzureBlobStorage\BlobProxyFactory'
        arguments: ['%sfynx.api.media.storage.azure.connection%']
```

```yml
# app/config/config.yml

knp_gaufrette:
    adapters:
        gaufrette_storage_gallery_local:
            local:
                directory: '%kernel.root_dir%/../web/uploads'
                create: true
        gaufrette_storage_gallery_azure:
            azure_blob_storage:
                blob_proxy_factory_id: 'sfynx_api_media.azure_blob_proxy_factory'
                container_name: '%sfynx.api.media.storage.azure.container%'
                create_container: false
    filesystems:
        gaufrette_storage_gallery_local:
            adapter: gaufrette_storage_gallery_local
        gaufrette_storage_gallery_azure:
            adapter: gaufrette_storage_gallery_azure
```
For a complete list of features refer to the [official documentation of GaufretteBundle](https://github.com/KnpLabs/KnpGaufretteBundle.git).

and add an entry in your parameters file

```yml
parameters:
    sfynx.api.media.storage.azure.connection: DefaultEndpointsProtocol=https;AccountName=myproject;AccountKey=91OtmEP/RO3Iq4qYAI0I7J6rWJBsR+Rt7HLrLP2qpf1FcDQPFj/Mbf8HvNnOSP/7xZbw/OKuorIUnzYLwGbE+g==
    sfynx.api.media.storage.azure.container: myContainerName
```

#### Configure your mappings storage

Pass the Gaufrette services configured in the previous step to the `storage_provider` property.

**Available rules :**

- **mime_types** : defines an array of valid mime types.
- **max_size** : defines the maximum allowed size of a media.
- **min_size** : defines the minimum allowed size of a media.
- **created_before** : defines if the media was created before this date.
- **created_after** : defines if the media was created after this date.

Notice that the value of *max_size* and *min_size* properties can only be expressed in **KB**, **MB**, **GB**, **TB** and **PB**.

```yml
# app/config/config.yml

sfynx_api_media:
    storage_providers:
        gaufrette_storage_gallery_local:
            mime_types: ['image/jpg', 'image/png', 'image/jpeg']
            max_size: 5MB
            min_size: 1MB
            created_before: 2014-08-14T12:00:00+0100
            created_after: 2014-07-14T21:00:00+0100
        gaufrette_storage_gallery_azure:
            mime_types: ['image/jpg', 'image/png', 'image/jpeg']
            max_size: 5MB
            min_size: 1MB
            created_before: 2014-08-14T12:00:00+0100
            created_after: 2014-07-14T21:00:00+0100
```

#### Configure the path of your upload cache folder with gaufrette

This, allow you to set the path of your cache folder.
The cache folder stock  post-transformed images which have been already provided to clients.

add the line below in your config file :

```yml
# app/config/config.yml

sfynx_api_media:
    cache_storage_provider:  'gaufrette_storage_gallery_azure_cache'
```

and add an entry in your parameters file :

```yml
# app/config/config.yml

knp_gaufrette:
    adapters:
        gaufrette_storage_gallery_azure_cache:
            local:
                directory: '%kernel.root_dir%/../web/uploads'
                create: true
    filesystems:
        gaufrette_storage_gallery_azure_cache:
            adapter: gaufrette_storage_gallery_azure_cache
```

#### Configure the path of your upload cache folder without gaufrette

This, allow you to set the path of your upload folder.

add the line below in your config file :

```yml
# app/config/config.yml

sfynx_api_media:
    cache_directory:     %sfynx_api_media.cache_directory%
```

and add an entry in your parameters file :

```yml
parameters:
    sfynx_api_media.cache_directory: '%kernel.root_dir%/../web/sample/cache'
```

#### Configure your public endpoint

This feature allow your media urls to be seen with your commercial domain rather than the domain of your bucket, cdn, other.

add the line below in your config file :

```yml
# app/config/config.yml

sfynx_api_media:
    api_public_endpoint: '%sfynx_api_media.api_public_endpoint%'
```
and add an entry in your parameters file

```yml
parameters:
    sfynx_api_media.api_public_endpoint: //my.sampledomain.com
```

#### Configure your mappings entity manager

```yml
# app/config/config.yml

sfynx_api_media:
    mapping:
        entities:
            media:
                class: Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media
                provider_command: 'orm'
                provider_query: 'orm'
                em_command: doctrine.orm.entity_manager
                em_query: doctrine.orm.entity_manager
                repository_command: Sfynx\ApiMediaBundle\Layers\Infrastructure\Persistence\Adapter\Command\Orm\MediaRepository
                repository_query: Sfynx\ApiMediaBundle\Layers\Infrastructure\Persistence\Adapter\Query\Orm\MediaRepository
```

#### Configure the authorized extensions according to media type when creating media

```yml
# app/config/config.yml

sfynx_api_media:
    authorized_extensions:
        image: ['jpeg', 'jpg', 'png', 'gif']
        document: ['pdf', 'doc', 'docx', 'rtf', 'xls', 'xlsx', 'xlsm', 'odt', 'qpl', 'txt', 'pptx', 'ppt']
```

#### Configure the default quality value when creating image media

```yml
# app/config/config.yml

sfynx_api_media:
    media:
        quality: 115
```

#### Configure the default jwt token parameters when creating

```yml
# app/config/config.yml

sfynx_api_media:
    media:
        token: { start: 0, expire: 3600, unique: true, ipRange: {} }
```

#### Configure the default signing excludes pattern

```yml
# app/config/config.yml

sfynx_api_media:
    signing_excludes_pattern:
        - { resize: 1, width: 100 }
        - { resize: 1, height: 300 }
```

When a media requires an authorization token for downloading, and you want to override
this authorization when calling the media with certain parameters, you just have to configure
the list of parameters in question as above.

#### A complete configuration example

```yml
#
# SfynxApiMediaBundle configuration
#
sfynx_api_media:
    api_public_endpoint: '%sfynx.api.media.host%'
    working_directory:  '%sfynx_cache_dir%/apimedia/cache'
    cache_directory: '%sfynx_cache_dir%/apimedia/cache'
    cache_storage_provider: 'gaufrette_storage_gallery_azure_cache'
    storage_providers:
        gaufrette_storage_gallery_local:
            mime_types: ['image/jpg', 'image/png', 'image/jpeg']
            max_size: 5MB
            min_size: 1MB
            created_before: 2014-08-14T12:00:00+0100
            created_after: 2014-07-14T21:00:00+0100
        gaufrette_storage_gallery_azure:
            mime_types: ['image/jpg', 'image/png', 'image/jpeg']
            max_size: 5MB
            min_size: 1MB
            created_before: 2014-08-14T12:00:00+0100
            created_after: 2014-07-14T21:00:00+0100
    mapping:
        entities:
            media:
                class: Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media
                provider_command: 'orm'
                provider_query: 'orm'
                em_command: doctrine.orm.entity_manager
                em_query: doctrine.orm.entity_manager
                repository_command: Sfynx\ApiMediaBundle\Layers\Infrastructure\Persistence\Adapter\Command\Orm\MediaRepository
                repository_query: Sfynx\ApiMediaBundle\Layers\Infrastructure\Persistence\Adapter\Query\Orm\MediaRepository
    signing_excludes_pattern:
        - { resize: 1, width: 100 }
        - { resize: 1, height: 300 }
    media:
        quality: 115
        token: { start: 0, expire: 3600, unique: true, ipRange: {} }
    authorized_extensions:
        image: ['jpeg', 'jpg', 'png', 'gif']
        document: ['pdf', 'doc', 'docx', 'rtf', 'xls', 'xlsx', 'xlsm', 'odt', 'qpl', 'txt', 'pptx', 'ppt']

knp_gaufrette:
    adapters:
        gaufrette_storage_gallery_local:
            local:
                directory: '%kernel.root_dir%/../web/uploads'
                create: true
        gaufrette_storage_gallery_azure:
            azure_blob_storage:
                blob_proxy_factory_id: 'sfynx_api_media.azure_blob_proxy_factory'
                container_name: '%sfynx.api.media.storage.azure.container%'
                create_container: false
        gaufrette_storage_gallery_azure_cache:
            azure_blob_storage:
                blob_proxy_factory_id: 'sfynx_api_media.azure_blob_proxy_factory'
                container_name: '%sfynx.api.media.storage.azure.container_cache%'
                create_container: false
    filesystems:
        gaufrette_storage_gallery_local:
            adapter: gaufrette_storage_gallery_local
        gaufrette_storage_gallery_azure:
            adapter: gaufrette_storage_gallery_azure
        gaufrette_storage_gallery_azure_cache:
            adapter: gaufrette_storage_gallery_azure_cache

lexik_jwt_authentication:
    private_key_path: %jwt_private_key_path%
    public_key_path:  %jwt_public_key_path%
    pass_phrase:      %jwt_key_pass_phrase%
    token_ttl:        %jwt_token_ttl%
    # token encoding/decoding settings
    encoder:
        # token encoder/decoder service - default implementation based on the namshi/jose library
        service:            lexik_jwt_authentication.encoder.default
        # crypto engine used by the encoder service
        crypto_engine:  openssl
        # encryption algorithm used by the encoder service
        signature_algorithm: RS256
    # token extraction settings
    token_extractors:
        authorization_header:      # look for a token as Authorization Header
            enabled: true
            prefix:  Bearer
            name:    Authorization
        cookie:                    # check token in a cookie
            enabled: false
            name:    BEARER
        query_parameter:           # check token in query string parameter
            enabled: false
            name:    bearer
```
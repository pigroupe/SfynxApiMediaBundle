# API: [POST] Media

Create a media from data

## General request

|             | Values
|-------------|-------
| **Method**  | POST
| **Route**   | /media
| **Header**  | Content-Type=multipart/form-data
| **Secured** | true

## HTTP Request parameters

| Name             | Optional | Default      | Requirements | Description
|------------------|----------|--------------|--------------|------------
| media            | false    | 0            | fileContent  | Upload file Content
| storage_provider | false    | true         | string       | knp gaufrette filesystem name given in knp_gaufrette configuration
| source           | false    |              | string       | directory path where the media has to be saved in storage
| name             | false    |              | string       | media name (does not influence the real name of the file)
| enabled          | true     | true         | boolean      | false to bloc the acces media
| description      | true     | empty        | string       | media description
| quality          | true     | config value | float        | media quality used by imagick
| metadata         | true     | []           | array        | media metadata information
| signing          | true     | []           | array        | media permission information

> metadata parameter structure example

```php
$metadata = [
    'idMedia': "28",
    ...
];
```
- `*idMedia*` value of `*metadata*` array value containd the identifier of the media client

> signing parameter structure example

```php
$signing = [
   'connected' => true,
   'roles' => [
       0 => 'ROLE_ADMIN',
       1 => 'ROLE_USER',
       2 => 'ROLE_USER_ERROR',
   ],
   'usernames' => [
       'admin@pi-groupe.net'
   ],
   'rangeip' => [
       '10.255.0.2.2'
   ]
];
```

- `*connected*` with `*true*`  value means that a media call requires a signing key (jwt token)
- `*roles*` value contains the list of roles allowed to download media
- `*usernames*` value contains the list of user names allowed to download media
- `*rangeip*` value contains the list of ip allowed to download media

## HTTP Response codes

| Code | HTTP Status Code       | Description
|------|------------------------|------------
| 201  | Created                | if a valid media is passed
| 400  | Bad Request            | if the media is passed twice (i.e media which already exists in the database and in the filesystem)
| 415  | Unsupported Media Type | if there is no matched storage provider for the media
| 418  | I'am a teapot          | for other media exception types

For a 201 HTTP Response code, you will also get all media informations (in json format) in the response content.

## Example of usage

> with curl

```curl 
curl -F media=@pathToTheFile http://your_domain/api/media
```

> with SfynxRestClientBUndle

```php
<?php
    ...

    /**
     * {@inheritdoc}
     */
    public function create(\Sfynx\MediaBundle\Layers\Domain\Entity\Media & $media, ?array $metadata): Response
    {
        return $this
        ->getRestClient()
        ->post('/media', [
            'source' => $media->getSourceName(),
            'storage_provider' => $media->getProviderName(),
            'name' => $media->getUploadedFile()->getClientOriginalName(),
            'description' => $media->getDescriptif(),
            'quality' => $media->getQuality(),
            'metadata' => array_merge($metadata, [
                'idMedia' => $media->getId(),
                'title' => $media->getTitle(),
                'description' => $media->getDescriptif(),
            ]),
            'signing' => [
                'connected' => $media->getConnected(),
                'roles' => $media->getRoles(),
                'usernames' => $media->getUsernames(),
                'rangeip' => $media->getRangeIp(),
            ],
            'media' => \curl_file_create(
                $media->getUploadedFile()->getPathName(),
                $media->getUploadedFile()->getClientMimeType(),
                $media->getUploadedFile()->getClientOriginalName()
            ),
            'enabled' => $media->getEnabled(),
        ]);
    }
```

> with SfynxMediaClientBundle

```php
<?php
    ...
        $sfynxMedia = (new \Sfynx\MediaBundle\Layers\Domain\Entity\Media())
            ->setUpdatedAt(new \DateTime())
            ->setEnabled(true)
            ->setName($fileName)
            ->setDescriptif($options['descriptif'])
            ->setUploadedFile($file)
            ->setSourceName($source)
            ->setProviderName($param['provider'])
            ->setRoles($this->tokenStorage->getToken()->getRoles())
            ->setUsernames([$this->tokenStorage->getToken()->getUsername()])
            ->setRangeIp(['10.255.0.2'])
            ->setMetadata([
                'businessId' => $options['business']->getId(),
                'businessName' => $options['business']->getName(),
                'businessStatus' => (string)$options['business']->getType(),
                'businessAddress' => (string)$options['business']->getAddress()
            ])
            ->setConnected(true)
            ->setMimeType($file->getClientMimeType())
            ->setQuality($quality);

        $this->mediaManager->persist($sfynxMedia);
```
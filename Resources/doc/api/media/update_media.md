# API: [PUT] Media

Update media from data

## General request

|             | Values
|-------------|-------
| **Method**  | PUT
| **Route**   | /media/{reference}
| **Secured** | true

## Route Parameters description

- *reference* : The unique reference of the media

## HTTP Request parameters

| Name             | Optional | Default      | Requirements | Description
|------------------|----------|--------------|--------------|------------
| enabled          | true     | true         | boolean      | false to bloc the acces media
| metadata         | true     | []           | array        | media metadata information
| signing          | true     | []           | array        | media permission information

## HTTP Response codes

| Code | HTTP Status Code       | Description
|------|------------------------|------------
| 200  | Ok                     | if a correct reference is passed
| 404  | Not found (wrong id)   | if an invalid reference (i.e a reference which does not exist neither in the database or in the filesystem)
| 503  | Service Unavailable    | others

## Example of usage

```curl
curl -X PUT http://your_domain/api/media/reference
```